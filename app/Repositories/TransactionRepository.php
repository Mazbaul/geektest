<?php

namespace App\Repositories;

use App\Interfaces\TransactionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator AS paginateResponse;
use App\Models\Transaction;
use Log;

class TransactionRepository implements TransactionInterface
{
    public function createTransaction(array $transactionDetails): Transaction
    {
        return Transaction::create($transactionDetails);
    }

    public function getThirdHighestTransactionByUserId(int $userId): array
    {
        $sql = "SELECT transactionAmount FROM ";
        $sql .= "(SELECT `sending_amount` AS transactionAmount FROM `transactions` WHERE `sender_user_id`=$userId ";
        $sql .= "UNION ALL ";
        $sql .= "SELECT `receiving_amount` AS transactionAmount FROM `transactions` WHERE `receiver_user_id`=$userId) temp ";
        $sql .= "ORDER BY transactionAmount DESC LIMIT 2, 1";

        return DB::select($sql);
    }

    public function sendMoneyErrorResponse(string $message, int $statusCode, array $logData): JsonResponse
    {
        $logData['error'] = $message;
        Log::error('User transaction failed', $logData);

        return response()->json(['message' => $message], $statusCode);
    }
}
