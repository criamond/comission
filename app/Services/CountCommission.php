<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class CountCommission
 *
 * Service class for calculating commission fees.
 */
class CountCommission
{
    private float $withdrawCommissionPrivate;
    private float $weeklyThresholdPrivate;
    private int $countTransactionsWeekNoFee;

    public function __construct(){
        $this->withdrawCommissionPrivate=config('transactionsettings.withdraw_commission_private');
        $this->weeklyThresholdPrivate= config('transactionsettings.weekly_threshold_private');
        $this->countTransactionsWeekNoFee= config('transactionsettings.count_transactions_week_no_fee');
    }
    /**
     * Calculate commission fees for transactions.
     *
     * @param Collection $transactionsCollection The collection of transactions.
     * @param RatesInterface $rates An object providing currency exchange rates.
     *
     * @return array An array of commission fees for each transaction.
     */
    public function getCommission(Collection $transactionsCollection, RatesInterface $rates): array
    {
        $outCommissions = [];

        foreach ($transactionsCollection as $transactionNumber => $transaction) {
            $commission = 0;

            switch ($transaction->transactionType) {
                case 'deposit':
                    $commission = config('transactionsettings.deposit_commission') * $transaction->amount;
                    break;

                case 'withdraw':
                    switch ($transaction->userType) {
                        case 'business':
                            $commission = config('transactionsettings.withdraw_commission_business') * $transaction->amount;
                            break;
                        case 'private':
                            $commission = $this->calculatePrivateCommission($transactionsCollection, $transactionNumber, $rates);
                            break;
                    }
                    break;
            }

            $multiplier = pow(10, config('transactionsettings.currency_precision'));
            $outCommissions[] = ceil($commission * $multiplier) / $multiplier;
        }

        return $outCommissions;
    }


    /**
     * Calculate commission fee for a private user.
     *
     * @param Collection $transactionsCollection The collection of transactions.
     * @param int $transactionNumber The number of the current transaction.
     * @param RatesInterface $rates An object providing currency exchange rates.
     *
     * @return float The commission fee for the private user.
     */
    private function calculatePrivateCommission(Collection $transactionsCollection, int $transactionNumber, RatesInterface $rates): float
    {
        $transaction = $transactionsCollection[$transactionNumber];
        $idClient = $transaction->userId;
        $dateCurrentTransaction = Carbon::parse($transaction->date);

        //filtering transactions that made before for current week
        $filteredTransactions = $transactionsCollection->filter(function ($transaction, $transactionNumberIterator)
        use ($idClient, $dateCurrentTransaction, $transactionNumber)
        {
            if ($transaction->userId == $idClient and "withdraw" == $transaction->transactionType and $transactionNumberIterator <= $transactionNumber) {
                $transactionDate = Carbon::parse($transaction->date);
                $dayOfWeek = $transactionDate->dayOfWeek;
                $dayOfWeekCurrent = $dateCurrentTransaction->dayOfWeek;
                $diffDays = $dateCurrentTransaction->diffInDays($transactionDate);

                if ($diffDays < 7) {
                    if ((0 != $dayOfWeekCurrent and ($dayOfWeekCurrent >= $dayOfWeek) and $dayOfWeek > 0) or
                        (0 == $dayOfWeekCurrent)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }

            return false;
        });

        //if transactions per week >3 we take a commission
        if($filteredTransactions->count()>$this->countTransactionsWeekNoFee){
            return $transaction->amount * $this->withdrawCommissionPrivate;
        }

        $weeklySumm = $filteredTransactions->sum(function ($transaction) use ($rates) {
            return  $transaction->amount / $transaction->rate;
        });


        //if transactions sum on current week > 1000EUR we take commission for current transaction
        if ($weeklySumm > $this->weeklyThresholdPrivate + 0.00001) {
            $summOverLimit = $weeklySumm - $this->weeklyThresholdPrivate;

            if ($summOverLimit < $transaction->amount / $transaction->rate) {
                $summFinal = $summOverLimit * $transaction->rate;
            } else {
                $summFinal = $transaction->amount;
            }

            return $this->withdrawCommissionPrivate * $summFinal;
        } else {
            return 0;
        }
    }
}
