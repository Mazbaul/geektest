<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\MoneyReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Requests\SendMoneyRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserResource;
use App\Interfaces\TransactionInterface;
use App\Interfaces\CurrencyConversionInterface;
use App\Interfaces\UserInterface;
use Log;

class TransactionController extends Controller
{
    private TransactionInterface $transactionRepository;
    private CurrencyConversionInterface $currencyConversionRepository;
    private UserInterface $userRepository;

    public function __construct(
        TransactionInterface $transactionRepository,
        CurrencyConversionInterface $currencyConversionRepository,
        UserInterface $userRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->currencyConversionRepository = $currencyConversionRepository;
        $this->userRepository = $userRepository;
    }

    public function sendMoney(SendMoneyRequest $request): TransactionResource|JsonResponse
    {
        $receiverId = $request->receiver_id;
        $sendingAmount = $request->amount;

        $sender = $this->userRepository->getAuthUser();
        $receiver = $this->userRepository->getUserById($receiverId);

        if ($sender->id == $receiver->id) {
            return response()->json(['message' => 'You can not send the money to your own wallet'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else if ($sender->wallet < $sendingAmount) {
            return response()->json(['message' => 'Insufficient wallet balance'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $logData = ['sender_id' => $sender->id, 'receiver_id' => $receiver->id, 'amount' => $sendingAmount];

            try {
                Log::info('User attempt to send money', $logData);

                /* Currency Conversion start */
                if ($sender->currency != $receiver->currency) {
                    $currencyConversion = $this->currencyConversionRepository->getCurrencyConversionData($sender->currency, $receiver->currency, $sendingAmount);

                    if ($currencyConversion->successful()) {
                        $currencyConversionObj = $currencyConversion->object();
                        if($currencyConversionObj->success) {
                            $receivingAmount = $currencyConversionObj->result;
                        } else {
                            return $this->transactionRepository->sendMoneyErrorResponse($currencyConversionObj->error->info, $currencyConversionObj->error->code, $logData);
                        }
                    } else {
                        $error = $currencyConversion->clientError() ? $currencyConversion->object()->message : 'Currency conversion server not connected';
                        return $this->transactionRepository->sendMoneyErrorResponse($error, $currencyConversion->status(), $logData);
                    }
                } else {
                    $receivingAmount = $sendingAmount;
                }
                /* Currency Conversion end */

                /* Transaction start */
                DB::beginTransaction();
                try {
                    $senderWallet = $sender->wallet-$sendingAmount;
                    $receiverWallet = $receiver->wallet+$receivingAmount;

                    $this->userRepository->updateUser($sender->id, ['wallet' => $senderWallet]);
                    $this->userRepository->updateUser($receiver->id, ['wallet' => $receiverWallet]);
                    $data = $this->transactionRepository->createTransaction([
                        'sender_user_id' => $sender->id,
                        'sender_currency' => $sender->currency,
                        'sending_amount' => $sendingAmount,
                        'receiver_user_id' => $receiver->id,
                        'receiver_currency' => $receiver->currency,
                        'receiving_amount' => $receivingAmount,
                        'transaction_at' => now(),
                    ]);
                    DB::commit();

                    /* Mail start */
                    $receiver->wallet = $receiverWallet;
                    Mail::to($receiver->email)->send(new MoneyReceived($sender, $receiver, $data));
                    /* Mail end */

                    Log::info('User transaction successfully done', $data->toArray());
                    return new TransactionResource($data);
                } catch (\Exception $e) {
                    DB::rollback();
                    return $this->transactionRepository->sendMoneyErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, $logData);
                }
                /* Transaction end */
            } catch (\Exception $e) {
                return $this->transactionRepository->sendMoneyErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, $logData);
            }
        }
    }

    public function userTransactionInfo(): UserResource
    {
        $user = $this->userRepository->getAuthUser();

        $sendingConvertedAmount = $this->currencyConversionRepository->getTotalSendingAmountByUserId($user->id);
        $receivingConvertedAmount = $this->currencyConversionRepository->getTotalReceivingAmountByUserId($user->id);
        $thirdHighestTransaction = $this->transactionRepository->getThirdHighestTransactionByUserId($user->id);

        $user->converted_amount_by_sending = $sendingConvertedAmount;
        $user->converted_amount_by_receiving = $receivingConvertedAmount;
        $user->total_converted_amount = $sendingConvertedAmount+$receivingConvertedAmount;
        $user->third_highest_transaction_amount = !empty($thirdHighestTransaction) ? $thirdHighestTransaction[0]->transactionAmount : 0;

        return new UserResource($user);
    }
}
