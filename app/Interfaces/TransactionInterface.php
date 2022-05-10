<?php

namespace App\Interfaces;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator AS paginateResponse;
use App\Models\Transaction;

interface TransactionInterface
{
    public function createTransaction(array $transactionDetails): Transaction;
    public function getThirdHighestTransactionByUserId(int $userId): array;
    public function sendMoneyErrorResponse(string $message, int $statusCode, array $logData): JsonResponse;
}
