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

        $filteredPrices = array_filter($livePrices, function ($price) use ($productCode, $accountId) {
            return isset($price['sku']) && $price['sku'] === $productCode
                && (!isset($price['account']) || ($accountId && $price['account'] === $accountId));
        });

        $firstPrice = reset($filteredPrices);

        return $firstPrice ? (object)$firstPrice : null;
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
