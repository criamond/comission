<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class CountCommission
{
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
                            $commission = $this->calculatePrivateComission($transactionsCollection, $transactionNumber, $rates);
                            break;
                    }
                    break;
            }
            $multiplier = pow(10, config('transactionsettings.currency_precision'));
            $outCommissions[]= ceil($commission * $multiplier) / $multiplier;
        }

        return $outCommissions;
    }

    private function calculatePrivateComission(Collection $transactionsCollection, int $transactionNumber, RatesInterface $rates): float
    {
        $transaction = $transactionsCollection[$transactionNumber];
        $idClient = $transaction->userId;

        $dateCurrentTransaction = Carbon::parse($transaction->date);

        $filteredTransactions = $transactionsCollection->filter(function ($transaction, $transactionNumberIterator) use ($idClient, $dateCurrentTransaction, $transactionNumber) {
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

        $weeklySumm = $filteredTransactions->sum(function ($transaction) use ($rates) {

            return  $transaction->amount / $transaction->rate;
        });

        if ($weeklySumm > config('transactionsettings.weekly_threshold_private') + 0.00001) {
            $summOverLimit = $weeklySumm - config('transactionsettings.weekly_threshold_private');
            if($summOverLimit < $transaction->amount / $transaction->rate) {
                $summFinal = $summOverLimit * $transaction->rate;
            } else {
                $summFinal = $transaction->amount;
            }

            return config('transactionsettings.withdraw_commission_private') * $summFinal;
        } else {
            return 0;
        }

    }

}
