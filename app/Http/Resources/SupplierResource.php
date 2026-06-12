<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'contact_name' => $this->contact_name,
            'payment_terms' => $this->payment_terms,
            'delivery_delay_days' => $this->delivery_delay_days,
            'currency' => $this->currency,
            'is_active' => $this->is_active,
            'balance' => (float) $this->balance,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
