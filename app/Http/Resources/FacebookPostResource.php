<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacebookPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->post_id,
            'message' => $this->name,
            'type' => 'ad',
            'status_type' => $this->status,
            'permalink_url' => null,
            'created_time' => $this->created_time?->toISOString(),
            'updated_time' => $this->updated_time?->toISOString(),
            'engagement' => [
                'likes_count' => 0,
                'shares_count' => 0,
                'comments_count' => 0,
                'reactions_count' => 0,
            ],
            'ads' => $this->whenLoaded('insights', function () {
                return collect([$this])->map(function ($ad) {
                    return [
                        'id' => $ad->id,
                        'name' => $ad->name,
                        'status' => $ad->status,
                        'effective_status' => $ad->effective_status,
                        'insights_summary' => $ad->insights->sum('spend') > 0 ? [
                            'total_spend' => $ad->insights->sum('spend'),
                            'total_impressions' => $ad->insights->sum('impressions'),
                            'total_reach' => $ad->insights->sum('reach'),
                            'total_clicks' => $ad->insights->sum('clicks'),
                            'total_conversions' => $ad->insights->sum('conversions'),
                            'avg_cpc' => $ad->insights->avg('cpc'),
                            'avg_cpm' => $ad->insights->avg('cpm'),
                        ] : null,
                    ];
                });
            }),
            'links' => [
                'facebook_post' => null,
                'facebook_page' => $this->page_id ? "https://facebook.com/{$this->page_id}" : null,
            ],
        ];
    }
} 