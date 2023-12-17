<?php

namespace App\Http\Controllers;

use App\Services\PriceService;

class PriceController extends Controller
{
    protected PriceService $priceService;

    public function __construct(PriceService $priceService)
    {
        $this->priceService = $priceService;
    }

    /**
     * Get a listing of live prices.
     *
     * @return array
     */
    public function index(): array
    {
        return $this->priceService->getLivePrices();
    }

}
