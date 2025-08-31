<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacebookCampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'objective' => $this->objective,
            'ad_account_id' => $this->ad_account_id,
            'created_time' => $this->created_time?->toISOString(),
            'updated_time' => $this->updated_time?->toISOString(),
        ];
    }
}
