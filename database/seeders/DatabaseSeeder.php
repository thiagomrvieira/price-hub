<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $backupPath = database_path('seeders/backups/Dump20231217.sql');
        $command = "mysql -u " . env('DB_USERNAME') . " -p'" . env('DB_PASSWORD') . "' " . env('DB_DATABASE') . " < $backupPath";
        exec($command);

        $this->command->info('Backup restored successfully.');
    }
}
