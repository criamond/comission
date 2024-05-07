<?php

namespace App\Services;

use App\DTOs\TransactionDTO;
use Illuminate\Support\Collection;

/**
 * Class TransactionLoader
 *
 * Loads transactions and creates DTO objects based on transaction data.
 */
class TransactionLoader
{
    /**
     * @var string The default basic currency.
     */
    private $basicCurrency;

    /**
     * TransactionLoader constructor.
     */
    public function __construct()
    {
        // Load the basic currency from configuration
        $this->basicCurrency = config('transactionsettings.basic_currency');
    }

    /**
     * Loads transactions and creates DTO objects.
     *
     * @param array $transactions The array of transactions to load.
     * @param RatesInterface $rates An object providing currency exchange rates.
     *
     * @return Collection A collection of TransactionDTO objects.
     */
    public function LoadTransactions(array $transactions, RatesInterface $rates): Collection
    {
        // Extract the start and end date from the transactions array
        $startDate = $transactions[1][1];
        $endDate = $transactions[1][count($transactions[1]) - 1];

        // Set exchange rates for the specified period
        $rates->setRates($startDate, $endDate);

        $transactionsDTOs = [];

        // Create DTO objects for each transaction
        foreach ($transactions[1] as $transactionNumber => $date) {
            $UserId = $transactions[2][$transactionNumber];
            $UserType = $transactions[3][$transactionNumber];
            $transactionType = $transactions[4][$transactionNumber];
            $amount = $transactions[5][$transactionNumber];
            $currency = $transactions[6][$transactionNumber];

            // Get the exchange rate for the specified currency and date
            $rate = $rates->getRate($this->basicCurrency, $currency, $date);

            // Create a TransactionDTO object and add it to the array
            $transactionsDTOs[] = new TransactionDTO(
                $date,
                $UserId,
                $UserType,
                $transactionType,
                $amount,
                $currency,
                $rate
            );
        }

        // Clear the exchange rates after processing transactions
        $rates->cleanRates();

        // Return a collection of DTO transaction objects
        return collect($transactionsDTOs);
    }

}
