<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacebookAdAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'account_id' => $this->account_id,
            'account_status' => $this->account_status,
            'business_id' => $this->business_id,
            'created_time' => $this->created_time?->toISOString(),
            'updated_time' => $this->updated_time?->toISOString(),
        ];
    }
}
