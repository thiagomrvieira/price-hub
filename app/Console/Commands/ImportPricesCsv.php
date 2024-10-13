<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Price;
use App\Models\Product;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ImportPricesCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:prices-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import prices from CSV file to the prices table';

    /**
     * Handle the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $filePath = database_path('seeders/imports/import.csv');

        if (!file_exists($filePath)) {
            $this->error("CSV file not found at $filePath");
            return;
        }

        $this->info("Importing prices from CSV file: $filePath");

        $csvData = array_map('str_getcsv', file($filePath));
        $header = array_shift($csvData);

        $bar = $this->output->createProgressBar(count($csvData));
        $bar->start();

        $this->importData($csvData, $header, $bar);

        $bar->finish();

        $this->info('');
        $this->info('Import completed.');
    }

    /**
     * Import data from CSV to the prices table using batching.
     *
     * @param array $csvData
     * @param array $header
     * @param ProgressBar $bar
     * @return void
     */
    private function importData(array $csvData, array $header, $bar): void
    {
        $batchSize = 1000;
        $batchData = [];

        foreach ($csvData as $row) {
            $skuIndex = array_search('sku', $header);
            $accountRefIndex = array_search('account_ref', $header);
            $userRefIndex = array_search('user_ref', $header);
            $quantityIndex = array_search('quantity', $header);
            $valueIndex = array_search('value', $header);

            $sku = $row[$skuIndex];
            $accountRef = $row[$accountRefIndex];
            $userRef = $row[$userRefIndex];
            $quantity = $row[$quantityIndex];
            $value = $row[$valueIndex];

            $productId = $this->getIdByColumnValue(Product::class, 'sku', $sku);
            $accountId = $this->getIdByColumnValue(Account::class, 'external_reference', $accountRef);
            $userId = $this->getIdByColumnValue(User::class, 'external_reference', $userRef);

            $batchData[] = [
                'product_id' => $productId,
                'account_id' => $accountId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'value' => $value,
            ];

            if (count($batchData) >= $batchSize) {
                DB::table('prices')->insert($batchData);
                $batchData = [];
            }

            $bar->advance();
        }

        if (count($batchData) > 0) {
            DB::table('prices')->insert($batchData);
        }
    }


    /**
     * Get the ID of a model by the value of a specific column.
     *
     * @param  string  $modelClass
     * @param  string  $column
     * @param  mixed   $value
     * @return int|null
     */
    private function getIdByColumnValue(string $modelClass, string $column, $value): ?int
    {
        return $modelClass::where($column, $value)->value('id');
    }
}
