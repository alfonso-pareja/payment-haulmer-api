<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => number_format($this->amount, 2, '.', ''),
            'currency' => $this->currency,
            'cardNumberMasked' => $this->card_number_masked,
            'cardHolder' => $this->card_holder,
            'status' => $this->status,
            'processedAt' => $this->processed_at?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }

    /**
     * Get additional data
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
        ];
    }
}
