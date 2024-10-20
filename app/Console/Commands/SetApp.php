<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File;

class SetApp extends Command
{
    protected $signature = 'set:app';
    protected $description = 'Setup application environment and install dependencies';

    public function handle()
    {
        $this->info("Starting application setup...\n");

        if (!$this->configureEnvFile()) {
            return 1;
        }

        if (!$this->runComposerInstall()) {
            return 1;
        }

        $this->call('key:generate');

        $this->info("Application setup completed successfully! \n");

        return 0;
    }

    protected function configureEnvFile()
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            File::copy(base_path('.env.example'), $envPath);
            $this->info(".env file copied successfully. \n");
        } else {
            $this->info(".env file already exists, skipping copy. \n");
        }

        $this->updateEnvFile($envPath, 'AP_NAME', 'Price-Hub');
        $this->updateEnvFile($envPath, 'DB_CONNECTION', 'mysql');
        $this->updateEnvFile($envPath, 'DB_HOST', '127.0.0.1');
        $this->updateEnvFile($envPath, 'DB_PORT', '3306');
        $this->updateEnvFile($envPath, 'DB_DATABASE', 'price_hub');
        $this->updateEnvFile($envPath, 'DB_USERNAME', 'root');
        $this->updateEnvFile($envPath, 'DB_PASSWORD', 'root');

        return true;
    }

    protected function runComposerInstall(): bool
    {
        $this->info("Running composer install...\n");
        $process = new Process(['composer', 'install']);
        $process->setWorkingDirectory(base_path());

        try {
            $process->mustRun();
            $this->info($process->getOutput());
            $this->info("Composer install completed successfully. \n");

            return true;
        } catch (ProcessFailedException $exception) {
            $this->error("Composer install failed: {$exception->getMessage()} \n");

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
