<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AccountsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(ProductsTableSeeder::class);

        // $backupPath = database_path('seeders/backups/Dump20231217.sql');

        // if (!File::exists($backupPath)) {
        //     $this->command->error("Backup file not found at: {$backupPath}");
        //     return;
        // }

        // $this->command->info('Restoring backup...');
        // $this->command->info('');

        // try {
        //     $this->restoreDatabase($backupPath);
        //     $this->command->info('Backup restored successfully.');
        // } catch (ProcessFailedException $e) {
        //     $this->command->error('Failed to restore backup: ' . $e->getMessage());
        // }

        // $this->command->info('');
    }

    /**
     * Restore the database using the given backup file.
     *
     * @param string $backupPath
     * @throws ProcessFailedException
     */
    protected function restoreDatabase(string $backupPath): void
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $process = Process::fromShellCommandline(
            "mysql --host=$host --user=$username --password=$password $database < $backupPath"
        );

        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
