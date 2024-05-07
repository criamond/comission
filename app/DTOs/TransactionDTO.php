<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Class TransactionDTO
 *
 * Data Transfer Object representing a transaction.
 */
final class TransactionDTO
{
    /**
     * TransactionDTO constructor.
     *
     * @param string $date The date of the transaction.
     * @param int $userId The ID of the user associated with the transaction.
     * @param string $userType The type of the user associated with the transaction.
     * @param string $transactionType The type of the transaction.
     * @param float $amount The amount of the transaction.
     * @param string $currency The currency of the transaction.
     * @param float|null $rate The exchange rate for the currency of the transaction.
     */
    public function __construct(
        public readonly string $date,
        public readonly int $userId,
        public readonly string $userType,
        public readonly string $transactionType,
        public readonly float $amount,
        public string $currency,
        public ?float $rate = null
    ) {
    }

}
