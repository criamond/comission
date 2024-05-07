# Commission Calculator

Commission Calculator is a PHP application designed to calculate commission fees for financial transactions based on predefined rules.

## Features

- Handles operations provided in CSV format.
- Calculates commission fees based on defined rules.
- Supports multiple currencies.
- Provides flexible and extensible architecture.

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/criamond/comission.git
    ```

2. Install dependencies:

    ```bash
    composer install
    ```

3. Set up configuration:

   Copy the `.env.example` file to `.env` and configure it with your environment variables.

## Usage

To use the Commission Calculator, follow these steps:

1. Prepare a CSV file containing your financial transactions.
2. Run the command-line interface provided by the application:

    ```bash
    php artisan calculate:commissions path/to/transactions.csv
    ```

   Replace `path/to/transactions.csv` with the path to your CSV file.

3. View the calculated commission fees displayed in the console.

## Configuration

You can configure the application behavior by modifying the `.env` file. Available configuration options include:

- Basic currency
- Deposit commission rate
- Withdraw commission rates for private and business clients
- Weekly threshold for private clients
- API endpoints and keys for currency exchange rates

## Testing

To run the tests for the Commission Calculator, use the following command:

```bash
php artisan test
