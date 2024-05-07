<?php

namespace App\Services;

interface RatesInterface
{
    public function getRate(string $baseCurrency, string $convCurrency, string $date): float;
    public function setRates(string $startDate, string $endDate): void;
    public function cleanRates(): void;
}
