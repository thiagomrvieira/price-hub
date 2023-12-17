<?php

namespace App\Services;

class PriceService
{
    /**
     * Get live prices from the JSON file.
     *
     * @return array
     */
    public function getLivePrices(): array
    {
        $filePath = base_path('app/Services/live_prices.json');

        return file_exists($filePath) ? $this->decodeJsonFile($filePath) : [];
    }

    /**
     * Decode JSON file content.
     *
     * @param string $filePath
     * @return array
     */
    private function decodeJsonFile(string $filePath): array
    {
        return json_decode(file_get_contents($filePath), true) ?? [];
    }
}
