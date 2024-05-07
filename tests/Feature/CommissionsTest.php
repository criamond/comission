<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommissionsTest extends TestCase
{
    /**
     * @return void
     */

    public function testCommissionCalculationCommandOutput()
    {
        $filePath = 'test.csv';

        $expectedOutput =
            "0.60\n" .
            "3.00\n" .
            "0.00\n" .
            "0.06\n" .
            "1.50\n" .
            "0.00\n" .
            "0.71\n" .
            "0.30\n" .
            "0.30\n" .
            "3.00\n" .
            "0.00\n" .
            "0.00\n" .
            "8,623.23\n";

        $output = shell_exec("php artisan process-commissions $filePath");
        $this->assertEquals($expectedOutput, $output);

    }

}
