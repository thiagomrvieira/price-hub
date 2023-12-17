<?php

namespace App\Http\Controllers;

use App\Services\PriceService;

class PriceController extends Controller
{
    public function __construct(protected PriceService $priceService){}

    /**
     * Get prices for a product.
     *
     * @param  string  $productCode
     * @param  string|null  $accountId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $productCode, ?string $accountId = null)
    {
        $prices = $this->priceService->getPrices($productCode, $accountId);

        return response()->json(['prices' => $prices]);
    }

}
