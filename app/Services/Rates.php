<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Exception;

class Rates implements RatesInterface
{
    private array $currenciesList;
    private string $basicCurrency;
    private array $rates=[];


    public function __construct()
    {
        $this->currenciesList=config('transactionsettings.currencies_list');
        $this->basicCurrency=config('transactionsettings.basic_currency');
        $this->apiURL=config('transactionsettings.api_currency_url');

    }

    public function cleanRates():void
    {
        $this->rates=[];
    }

    public function setRates(string $startDate, string $endDate):void{

        $key = array_search($this->basicCurrency, $this->currenciesList);
        if ($key !== false) {
            unset($this->currenciesList[$key]);
        }
        $currenciesForSearch=implode(',',$this->currenciesList);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: text/plain",
            "apikey: " . config('transactionsettings.api_key_currency')
        ));

        $startDate = Carbon::createFromDate($startDate);
        $endDate = Carbon::createFromDate($endDate);

        $fullPageResult=[];
        for($currentStartDate=$startDate;$currentStartDate<=$endDate;$currentStartDate->addYear()) {


            $nextEndDate = $currentStartDate->copy()->addYear()->lt($endDate)
                ? $currentStartDate->copy()->addYear()
                : $endDate;


            $request
                = "exchangerates_data/timeseries?start_date=".$currentStartDate->toDateString().
                    "&end_date=".$nextEndDate->toDateString().
                    "&symbols=$currenciesForSearch&base=$this->basicCurrency";

            curl_setopt(
                $ch,
                CURLOPT_URL,
                $this->apiURL.$request
            );

            $response = curl_exec($ch);

            throw_if(!$response, new Exception("Can't load currencies"));

            try {
                $result = json_decode($response, true);
                $result=$result['rates'];
                $fullPageResult=array_merge($result,$fullPageResult);

            } catch (Exception $e) {
                throw new Exception("Not valid format JSON from API or wrong API key");
            }
        }
        $this->rates=$fullPageResult;
    }

    public function getRate(string $baseCurrency, string $convCurrency, string $date): float
    {
        if($baseCurrency != $convCurrency) {

            $rate= $this->rates[$date][$convCurrency];

        } else {
            $rate = 1;
        }
        return $rate;

    }
}
