<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File;

class SetApp extends Command
{
    protected $signature = 'app:install';
    protected $description = 'Setup application environment and install dependencies';

    public function handle()
    {
        $this->info('Starting application setup...');

        $this->copyEnvFile();
        $this->generateAppKey();
        $this->configureEnvFile();

        if (!$this->runComposerInstall()) {
            return 1;
        }

        if (!$this->clearConfigCache()) {
            return 1;
        }

        if (!$this->migrateFresh()) {
            return 1;
        }

        if (!$this->seedDatabase()) {
            return 1;
        }

        $this->info('Application setup completed successfully!');
        $this->info('');
        $this->call('import:prices-csv');

        return 0;
    }

    protected function copyEnvFile()
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            File::copy(base_path('.env.example'), $envPath);
            $this->info('.env file copied successfully.');
        } else {
            $this->info('.env file already exists, skipping copy.');
        }

        $this->info('');
    }

    protected function generateAppKey(): void
    {
        $this->info('Generating application key...');
        $this->call('key:generate', ['--show' => true]);
        $this->info('Application key generated successfully.');

        $this->info('');
    }

    protected function configureEnvFile()
    {
        $envFilePath = base_path('.env');

        $this->updateEnvFile($envFilePath, 'DB_CONNECTION', 'mysql');
        $this->updateEnvFile($envFilePath, 'DB_HOST', '127.0.0.1');
        $this->updateEnvFile($envFilePath, 'DB_PORT', '3306');
        $this->updateEnvFile($envFilePath, 'DB_DATABASE', 'pricehub');
        $this->updateEnvFile($envFilePath, 'DB_USERNAME', 'root');
        $this->updateEnvFile($envFilePath, 'DB_PASSWORD', 'root');

        $this->info('Configuration of .env file completed.');
        $this->info('');
    }

    protected function runComposerInstall(): bool
    {
        $this->info('Running composer install...');
        $process = new Process(['composer', 'install']);
        $process->setWorkingDirectory(base_path());

        try {
            $process->mustRun();
            $this->info($process->getOutput());
            $this->info('Composer install completed successfully.');
            $this->info('');
            return true;
        } catch (ProcessFailedException $exception) {
            $this->error('Composer install failed: ' . $exception->getMessage());
            return false;
        }
    }

    protected function clearConfigCache(): bool
    {
        $this->info('Clearing config cache...');

        try {
            $this->call('config:clear');
            $this->call('config:cache');
            $this->info('Config cache cleared successfully.');
            $this->info('');
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to clear config cache: ' . $e->getMessage());
            return false;
        }
    }

    protected function migrateFresh(): bool
    {
        $this->info('Running migrations...');

        try {
            $this->call('migrate:fresh');
            $this->info('Migrations completed successfully.');
            $this->info('');
            return true;
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function seedDatabase(): bool
    {
        $this->info('Seeding database...');

        try {
            $this->call('db:seed');
            $this->info('Database seeding completed successfully.');
            $this->info('');
            return true;
        } catch (\Exception $e) {
            $this->error('Database seeding failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function updateEnvFile(string $path, string $key, string $value): void
    {
        $content = File::get($path);

        if (strpos($content, $key) !== false) {
            $content = preg_replace('/^' . preg_quote($key) . '=.*/m', $key . '=' . $value, $content);
        } else {
            $content .= PHP_EOL . $key . '=' . $value;
        }

        File::put($path, $content);
    }
}
