<?php

return [
    'currencies_list' => explode(',', env('CURRENCIES_LIST', 'EUR,USD,JPY')),
    'basic_currency' => env('BASIC_CURRENCY', 'EUR'),
    'deposit_commission' => (float) env('DEPOSIT_COMMISSION', 0.0003),
    'withdraw_commission_private' => (float) env('WITHDRAW_COMMISSION_PRIVATE', 0.003),
    'private_threshold' => (float) env('PRIVATE_THRESHOLD', 1000),
    'withdraw_commission_business' => (float) env('WITHDRAW_COMMISSION_BUSINESS', 0.005),
    'count_transactions_week_no_fee' => (int) env('COUNT_TRANSACTIONS_WEEK_NO_FEE', 3),

    'weekly_threshold_private' => (float) env('WEEKLY_THRESHOLD_PRIVATE', 1000),
    'currency_precision' => 2,
    'api_key_currency' => env('API_KEY_CURRENCY', ''),
    'api_currency_url' => 'https://api.apilayer.com/'
];
