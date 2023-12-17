<?php

namespace App\Services;

use App\Models\Price;
use App\Models\Account;
use App\Models\Product;

class PriceService
{

    /**
     * Get prices for a product.
     *
     * @param  string  $productCode
     * @param  string|null  $accountId
     * @return array
     */
    public function getPrices(string $productCode, ?string $accountId = null): array
    {
        $livePrices = $this->getLivePrices($productCode, $accountId);

        $databasePrices = empty($livePrices) ? $this->getDatabasePrices($productCode, $accountId) : [];

        $mergedPrices = $this->mergePrices($databasePrices, $livePrices);

        return $mergedPrices;
    }

    /**
     * Get prices from the database using Eloquent.
     *
     * @param  string  $productCode
     * @param  string|null  $accountId
     * @return array
     */
    private function getDatabasePrices(string $productCode, ?string $accountId = null): array
    {
        $productId = Product::where('sku', $productCode)->value('id');
        $accountId = Account::where('external_reference', $accountId)->value('id');
        $query = Price::where('product_id', $productId);

        if ($accountId !== null) {
            $query->where(function ($query) use ($accountId) {
                $query->where('account_id', $accountId)->orWhereNull('account_id');
            });
        }

        $lowestPrice = $query->orderBy('value')->first();

        return $lowestPrice ? [$lowestPrice->toArray()] : [];
    }

    /**
     * Get live prices from the JSON file.
     *
     * @return array
     */
    private function getLivePrices(string $productCode, ?string $accountId = null): array
    {
        $filePath = base_path('app/Services/live_prices.json');
        $livePrices = file_exists($filePath) ? $this->decodeJsonFile($filePath) : [];

        return array_filter($livePrices, function ($price) use ($productCode, $accountId) {
            return isset($price['sku']) && $price['sku'] === $productCode
                && (!isset($price['account']) || ($accountId && $price['account'] === $accountId));
        });
    }

    /**
     * Merge and apply pricing logic.
     *
     * @param  array  $databasePrices
     * @param  array  $livePrices
     * @return array
     */
    private function mergePrices(array $databasePrices, array $livePrices): array
    {
        $mergedPrices = $databasePrices;

        foreach ($livePrices as $livePrice) {
            $existingPrice = $this->findExistingPrice($livePrice, $mergedPrices);

            if (!$existingPrice || $livePrice['value'] < $existingPrice['value']) {
                $mergedPrices[] = $livePrice;
            }
        }

        return $mergedPrices;
    }

    /**
     *  Finds an existing price in the list of merged prices based on specific criteria.
     *
     * @param  array  $livePrice
     * @param  array  $mergedPrices
     * @return array|null
     */
    private function findExistingPrice(array $livePrice, array $mergedPrices): ?array
    {
        foreach ($mergedPrices as $existingPrice) {
            if (
                isset($existingPrice['sku']) 
                && $existingPrice['sku'] === $livePrice['sku'] 
                && $existingPrice['account_id'] === $livePrice['account_id']
            ) {
                return $existingPrice;
            }
        }
    
        return null;
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
