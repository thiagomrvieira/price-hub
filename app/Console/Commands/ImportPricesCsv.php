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
        $header = array_map('strtolower', array_shift($csvData));

        $this->info("Loading products, accounts and users mapping...");
        $productMap = Product::pluck('id', 'sku')->toArray();
        $accountMap = Account::pluck('id', 'external_reference')->toArray();
        $userMap = User::pluck('id', 'external_reference')->toArray();

        $bar = $this->output->createProgressBar(count($csvData));
        $bar->start();

        $batchSize = 1000;
        $batchData = [];

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            $sku = $data['sku'];
            $accountRef = $data['account_ref'];
            $userRef = $data['user_ref'];
            $quantity = $data['quantity'];
            $value = $data['value'];

            $productId = $productMap[$sku] ?? null;
            $accountId = $accountMap[$accountRef] ?? null;
            $userId = $userMap[$userRef] ?? null;

            if (is_null($productId)) {
                $this->warn("Linha ignorada devido a referência inválida: SKU=$sku, AccountRef=$accountRef, UserRef=$userRef");
                $bar->advance();
                continue;
            }

            $batchData[] = [
                'product_id' => $productId,
                'account_id' => $accountId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
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

        $bar->finish();

        $this->info('');
        $this->info('Import completed successfully.');
    }
}
