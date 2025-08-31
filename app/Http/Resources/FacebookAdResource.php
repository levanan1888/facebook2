<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacebookAdResource extends JsonResource
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
            'effective_status' => $this->effective_status,
            'account_id' => $this->account_id,
            'campaign_id' => $this->campaign_id,
            'adset_id' => $this->adset_id,
            
            // Post information - Sử dụng dữ liệu từ post relationship
            'post_id' => $this->post_id,
            'page_id' => $this->page_id,
            'post_message' => $this->whenLoaded('post', function() {
                return $this->post ? $this->post->message : null;
            }),
            'post_type' => $this->whenLoaded('post', function() {
                return $this->post ? $this->post->type : null;
            }),
            'post_status_type' => $this->whenLoaded('post', function() {
                return $this->post ? $this->post->status_type : null;
            }),
            'post_permalink_url' => $this->whenLoaded('post', function() {
                return $this->post ? $this->post->permalink_url : null;
            }),
            'post_created_time' => $this->whenLoaded('post', function() {
                return $this->post ? $this->post->created_time?->toISOString() : null;
            }),
            
            // Ad insights
            'ad_spend' => (float) $this->insights->sum('spend'),
            'ad_reach' => (int) $this->insights->sum('reach'),
            'ad_impressions' => (int) $this->insights->sum('impressions'),
            'ad_clicks' => (int) $this->insights->sum('clicks'),
            'ad_ctr' => (float) $this->insights->avg('ctr'),
            'ad_cpc' => (float) $this->insights->avg('cpc'),
            'ad_cpm' => (float) $this->insights->avg('cpm'),
            'ad_frequency' => (float) $this->insights->avg('frequency'),
            
            // Post insights - Sử dụng dữ liệu từ post relationship
            'post_spend' => (float) $this->whenLoaded('post', function() {
                return $this->post ? $this->insights->sum('spend') : 0;
            }),
            'post_reach' => (int) $this->whenLoaded('post', function() {
                return $this->post ? $this->post->insights->sum('reach') : 0;
            }),
            'post_impressions' => (int) $this->whenLoaded('post', function() {
                return $this->post ? $this->post->insights->sum('impressions') : 0;
            }),
            'post_clicks' => (int) $this->whenLoaded('post', function() {
                return $this->post ? $this->post->insights->sum('clicks') : 0;
            }),
            'post_likes' => (int) $this->whenLoaded('post', function() {
                return $this->post ? $this->post->likes_count : 0;
            }),
            'post_shares' => (int) $this->whenLoaded('post', function() {
                return $this->post ? $this->post->shares_count : 0;
            }),
            'post_comments' => (int) $this->whenLoaded('post', function() {
                return $this->post ? $this->post->comments_count : 0;
            }),
            'post_engagement_rate' => (float) $this->whenLoaded('post', function() {
                if (!$this->post) return 0;
                $total = $this->post->likes_count + $this->post->shares_count + $this->post->comments_count;
                $reach = $this->post->insights->sum('reach');
                return $reach > 0 ? ($total / $reach) * 100 : 0;
            }),
            
            // Timestamps
            'created_time' => $this->created_time?->toISOString(),
            'updated_time' => $this->updated_time?->toISOString(),
            'last_insights_sync' => $this->last_insights_sync?->toISOString(),
            
            // Relationships
            'ad_account' => new FacebookAdAccountResource($this->whenLoaded('adAccount')),
            'campaign' => new FacebookCampaignResource($this->whenLoaded('campaign')),
            'ad_set' => new FacebookAdSetResource($this->whenLoaded('adSet')),
        ];
    }
}
