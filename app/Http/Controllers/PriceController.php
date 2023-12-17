<?php

namespace App\Http\Controllers;

use App\Services\PriceService;
use App\Http\Resources\PriceResource;

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
        return new PriceResource(
            $this->priceService->getPrices($productCode, $accountId)
        );
    }

}
