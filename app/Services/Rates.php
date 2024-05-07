<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Exception;

/**
 * Class Rates
 *
 * Implementation of the RatesInterface for managing currency exchange rates.
 */
class Rates implements RatesInterface
{
    /**
     * @var array List of available currencies.
     */
    private array $currenciesList;

    /**
     * @var string The basic currency.
     */
    private string $basicCurrency;

    /**
     * @var array Array of exchange rates.
     */
    private array $rates = [];

    /**
     * Rates constructor.
     */
    public function __construct()
    {
        $this->currenciesList = config('transactionsettings.currencies_list');
        $this->basicCurrency = config('transactionsettings.basic_currency');
        $this->apiURL = config('transactionsettings.api_currency_url');
    }

    /**
     * Clear the exchange rates array.
     */
    public function cleanRates(): void
    {
        $this->rates = [];
    }

    /**
     * Set exchange rates for a specified period.
     *
     * @param string $startDate The start date of the period.
     * @param string $endDate The end date of the period.
     *
     * @throws Exception If an error occurs during API request or response handling.
     */
    public function setRates(string $startDate, string $endDate): void
    {
        $key = array_search($this->basicCurrency, $this->currenciesList);
        if (false !== $key) {
            unset($this->currenciesList[$key]);
        }
        $currenciesForSearch = implode(',', $this->currenciesList);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: text/plain",
            "apikey: " . config('transactionsettings.api_key_currency')
        ));

        try {
            $startDate = Carbon::createFromDate($startDate);
            $endDate   = Carbon::createFromDate($endDate);
        } catch (Exception $e) {
            throw new Exception("Not valid input file format");
        }

        $fullPageResult = [];
        for ($currentStartDate = $startDate; $currentStartDate <= $endDate; $currentStartDate->addYear()) {
            $nextEndDate = $currentStartDate->copy()->addYear()->lt($endDate)
                ? $currentStartDate->copy()->addYear()
                : $endDate;

            $request = "exchangerates_data/timeseries?start_date=" . $currentStartDate->toDateString() .
                "&end_date=" . $nextEndDate->toDateString() .
                "&symbols=$currenciesForSearch&base=$this->basicCurrency";

            curl_setopt($ch, CURLOPT_URL, $this->apiURL . $request);

            $response = curl_exec($ch);

            throw_if(!$response, new Exception("Can't load currencies"));

            try {
                $result = json_decode($response, true);
                $result = $result['rates'];
                $fullPageResult = array_merge($result, $fullPageResult);
            } catch (Exception $e) {
                throw new Exception("Not valid format JSON from API or wrong API key");
            }
        }
        $this->rates = $fullPageResult;
    }

    /**
     * Get the exchange rate for a specific currency and date.
     *
     * @param string $baseCurrency The base currency.
     * @param string $convCurrency The currency to convert to.
     * @param string $date The date of the exchange rate.
     *
     * @return float The exchange rate.
     * @throws Exception
     */
    public function getRate(string $baseCurrency, string $convCurrency, string $date): float
    {
        if ($baseCurrency != $convCurrency) {
            try {
                $rate = $this->rates[$date][$convCurrency];
            } catch (Exception $e) {
                throw new Exception("Not valid currencies in input file");
            }

        } else {
            $rate = 1;
        }
        return $rate;
    }
}
