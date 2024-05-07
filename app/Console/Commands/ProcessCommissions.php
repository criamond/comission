<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CountCommission;
use App\Services\RatesInterface;
use App\Services\TransactionLoader;
use Exception;
use Illuminate\Console\Command;

class ProcessCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-commissions {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count comissions of transactions based on input file';

    /**
     * Execute the console command.
     */
    public function handle(CountCommission $commission, RatesInterface $rates, TransactionLoader $transactionLoader)
    {


        try {
            $fileContent = file_get_contents($this->argument('file'));
            $regExpressionParsing='/(\d{4}-\d\d-\d\d),(\d+),(\w+),(\w+),([\d,\.]+),(\w+)/';

            preg_match_all($regExpressionParsing, $fileContent, $transactionMatches);

            if(!$transactionMatches[1][1]){
                exit("Error: wrong file format" );
            }

            $transactionsListDTO = $transactionLoader->LoadTransactions($transactionMatches, $rates);


        } catch (Exception $exception) {

            exit("Error: " . $exception->getMessage());

        }

        $output_data = $commission->getCommission($transactionsListDTO, $rates);

        foreach ($output_data as $item) {
            echo $item, "\n";
        }

    }
}
