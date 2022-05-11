<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Transaction;
use Tests\TestCase;

class ApiTransactionControllerTest extends TestCase
{
    public function test_send_money_must_be_for_authorized_user()
    {
        $this->json('POST', 'api/send-money')->assertUnauthorized();
    }

    public function test_send_money_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->json('POST', 'api/send-money')
            ->assertInvalid([
                'receiver_id' => ["The receiver id field is required."],
                'amount' => ["The amount field is required."],
            ]);

        $this->json('POST', 'api/send-money', ['receiver_id' => 'abc', 'amount' => 'xyz'])
            ->assertInvalid([
                'receiver_id' => ["The receiver id must be an integer."],
                'amount' => [
                    "The amount must be a number.",
                    "The amount must be greater than 0.",
                ],
            ]);
    }

    public function test_send_money_validate_invalid_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->json('POST', 'api/send-money', ['receiver_id' => $user->id+1, 'amount' => 10])->assertNotFound();
    }

    public function test_send_money_not_possible_to_own_wallet()
    {
        $senderUser = User::factory()->create();
        $this->actingAs($senderUser, 'api');

        $this->json('POST', 'api/send-money', ['receiver_id' => $senderUser->id, 'amount' => 10])->assertUnprocessable();
    }

    public function test_send_money_not_possible_for_insufficient_wallet_balance()
    {
        $senderUser = User::factory()->create(['wallet' => 10]);
        $receiverUser = User::factory()->create();
        $this->actingAs($senderUser, 'api');

        $this->json('POST', 'api/send-money', ['receiver_id' => $receiverUser->id, 'amount' => 11])->assertUnprocessable();
    }

    public function test_send_money_currency_conversion_client_error_response()
    {
        $senderUser = User::factory()->create(['wallet' => 10, 'currency' => 'ABC']);
        $receiverUser = User::factory()->create();
        $this->actingAs($senderUser, 'api');

        $this->json('POST', 'api/send-money', ['receiver_id' => $receiverUser->id, 'amount' => 5])->assertUnauthorized();
    }

    public function test_send_money_successful_for_same_currency_sender_receiver()
    {
        $senderUser = User::factory()->create(['wallet' => 10, 'currency' => 'USD']);
        $receiverUser = User::factory()->create(['wallet' => 20, 'currency' => 'USD']);
        $this->actingAs($senderUser, 'api');

        $this->json('POST', 'api/send-money', ['receiver_id' => $receiverUser->id, 'amount' => 5])
            ->assertStatus(201)
            ->assertJsonPath('data.sender_user_id', $senderUser->id)
            ->assertJsonPath('data.sender_currency', $senderUser->currency)
            ->assertJsonPath('data.sending_amount', 5)
            ->assertJsonPath('data.receiver_user_id', $receiverUser->id)
            ->assertJsonPath('data.receiver_currency', $receiverUser->currency)
            ->assertJsonPath('data.receiving_amount', 5);

        $senderWallet = User::find($senderUser->id)->wallet;
        $receiverWallet = User::find($receiverUser->id)->wallet;

        $this->assertEquals(5, $senderWallet);
        $this->assertEquals(25, $receiverWallet);
    }

    public function test_send_money_successful_for_different_currency_sender_receiver()
    {
        $senderUser = User::factory()->create(['wallet' => 10, 'currency' => 'USD']);
        $receiverUser = User::factory()->create(['wallet' => 20, 'currency' => 'EUR']);
        $this->actingAs($senderUser, 'api');

        $response = $this->json('POST', 'api/send-money', ['receiver_id' => $receiverUser->id, 'amount' => 5])
            ->assertStatus(201)
            ->assertJsonPath('data.sender_user_id', $senderUser->id)
            ->assertJsonPath('data.sender_currency', $senderUser->currency)
            ->assertJsonPath('data.sending_amount', 5)
            ->assertJsonPath('data.receiver_user_id', $receiverUser->id)
            ->assertJsonPath('data.receiver_currency', $receiverUser->currency);

        $senderWallet = User::find($senderUser->id)->wallet;
        $receiverWallet = User::find($receiverUser->id)->wallet;

        $this->assertEquals(5, $senderWallet);
        $this->assertEquals(20+$response->json()['data']['receiving_amount'], $receiverWallet);
    }

    public function test_user_transaction_info_must_be_for_authorized_user()
    {
        $this->json('GET', 'api/user-transaction-info')->assertUnauthorized();
    }

    public function test_user_transaction_info_return_valid_result()
    {
        $user1 = User::factory()->create(['currency' => 'USD']);
        $user2 = User::factory()->create(['currency' => 'EUR']);
        $user3 = User::factory()->create(['currency' => 'USD']);

        $this->actingAs($user1, 'api');

        Transaction::factory()->create([
            'sender_user_id' => $user1->id,
            'sender_currency' => $user1->currency,
            'sending_amount' => 10,
            'receiver_user_id' => $user2->id,
            'receiver_currency' => $user2->currency,
            'receiving_amount' => 11,
        ]);

        Transaction::factory()->create([
            'sender_user_id' => $user1->id,
            'sender_currency' => $user1->currency,
            'sending_amount' => 20,
            'receiver_user_id' => $user2->id,
            'receiver_currency' => $user2->currency,
            'receiving_amount' => 22,
        ]);

        Transaction::factory()->create([
            'sender_user_id' => $user2->id,
            'sender_currency' => $user2->currency,
            'sending_amount' => 27,
            'receiver_user_id' => $user1->id,
            'receiver_currency' => $user1->currency,
            'receiving_amount' => 30,
        ]);

        Transaction::factory()->create([
            'sender_user_id' => $user2->id,
            'sender_currency' => $user2->currency,
            'sending_amount' => 36,
            'receiver_user_id' => $user1->id,
            'receiver_currency' => $user1->currency,
            'receiving_amount' => 40,
        ]);

        Transaction::factory()->create([
            'sender_user_id' => $user1->id,
            'sender_currency' => $user1->currency,
            'sending_amount' => 50,
            'receiver_user_id' => $user3->id,
            'receiver_currency' => $user3->currency,
            'receiving_amount' => 50,
        ]);

        $sendingConvertedAmount = 30;
        $receivingConvertedAmount = 70;
        $thirdHighestTransaction = 30;

        $this->json('GET', 'api/user-transaction-info')
            ->assertOk()
            ->assertJsonPath('data.converted_amount_by_sending', $sendingConvertedAmount)
            ->assertJsonPath('data.converted_amount_by_receiving', $receivingConvertedAmount)
            ->assertJsonPath('data.total_converted_amount', $sendingConvertedAmount+$receivingConvertedAmount)
            ->assertJsonPath('data.third_highest_transaction_amount', $thirdHighestTransaction);
    }

}
