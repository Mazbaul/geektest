<?php

namespace App\Interfaces;

use Illuminate\Http\Client\Response AS HttpClientResponse;
use App\Models\Transaction;

interface CurrencyConversionInterface
{
    public function getCurrencyConversionData(string $fromCurrency, string $toCurrency, float $amount): HttpClientResponse;
    public function getMostConversion(): Transaction|null;
    public function getTotalSendingAmountByUserId(int $userId): float;
    public function getTotalReceivingAmountByUserId(int $userId): float;
}
