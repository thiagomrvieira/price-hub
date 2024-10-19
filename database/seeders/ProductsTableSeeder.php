<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $path = database_path('seeders/sql/products.sql');

        if (File::exists($path)) {
            try {
                $sql = File::get($path);

                DB::unprepared($sql);
            } catch (Exception $e) {
                $this->command->error('An error occurred while seeding the products data.');
                $this->command->error('Error details: ' . $e->getMessage());
            }
        } else {
            $this->command->error("SQL file not found: {$path}");
        }
    }
}
