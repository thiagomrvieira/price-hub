<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'sku' => $this->getSku(),
            'price' => $this->getValue(),
        ];
    }

    /**
     * Get SKU attribute.
     *
     * @return string|null
     */
    private function getSku(): ?string
    {
        if ($this->resource) {
            return $this->resource->sku ?? $this->resource->product->sku;
        }
    
        return null;
    }

    /**
     * Get value attribute.
     *
     * @return float|null
     */
    private function getValue(): ?float
    {
        if ($this->resource) {
            $price = property_exists($this->resource, 'price') 
                ? $this->resource->price 
                : $this->resource->value;
            
            return number_format($price, 2, '.', '');
        }
    
        return null;
    }
}
