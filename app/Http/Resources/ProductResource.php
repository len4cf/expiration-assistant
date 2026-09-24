<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $daysUntilExpiry = (int) today()->diffInDays($this->expiration_date, false);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'expiration_date' => $this->expiration_date->toDateString(),
            'opened_at' => $this->opened_at?->toDateString(),
            'location' => $this->location,
            'status' => $this->status,
            'finished_at' => $this->finished_at?->toIso8601String(),
            'days_until_expiry' => $daysUntilExpiry,
            'is_expired' => $daysUntilExpiry < 0,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
