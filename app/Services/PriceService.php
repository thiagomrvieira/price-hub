<?php

namespace App\Services;

use App\Models\Price;
use App\Models\Account;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class PriceService
{

    /**
     * Get prices for a product.
     *
     * @param  string  $productCode
     * @param  string|null  $accountId
     * @return \Illuminate\Support\Collection
     */
    public function getBestPrice(string $productCode, ?string $accountId = null)
    {
        $liveBestPrice = $this->getLiveBestPrice($productCode, $accountId);

        $bestPrice = empty($liveBestPrice)
            ? $this->getDatabaseBestPrice($productCode, $accountId)
            : $liveBestPrice;

        return $bestPrice;
    }

    /**
     * Get prices from the database using Eloquent.
     *
     * @param  string  $productCode
     * @param  string|null  $accountId
     * @return Price|null
     */
    private function getDatabaseBestPrice(string $productCode, ?string $accountId = null): ?Price
    {
        $result = \DB::select('CALL GetDatabaseBestPrice(?, ?)', [$productCode, $accountId]);

        if (!empty($result)) {
            return (new Price())->forceFill((array) $result[0]);
        }

        return null;
    }


    /**
     * Get live prices from the JSON file.
     *
     * @return object|null
     */
    private function getLiveBestPrice(string $productCode, ?string $accountId = null): ?object
    {
        $filePath = base_path('app/Services/live_prices.json');
        $livePrices = file_exists($filePath) ? $this->decodeJsonFile($filePath) : [];

        $filteredPrices = array_filter($livePrices, fn($price) =>
            $price['sku'] === $productCode &&
            ($accountId === null || (isset($price['account']) && $price['account'] === $accountId))
        );

        if (empty($filteredPrices)) {
            return null;
        }

        $minPriceData = array_reduce($filteredPrices, fn($carry, $item) =>
            $carry === null || $item['price'] < $carry['price'] ? $item : $carry
        );

        return (object)$minPriceData;
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
