<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DebugDailyInsights extends Command
{
    protected $signature = 'facebook:debug-daily-insights {--post-id=} {--since=} {--until=} {--metric=post_impressions} {--period=} {--access-token=}';
    protected $description = 'Debug raw Graph API response for post insights with period=day';

    private string $graphApiUrl = 'https://graph.facebook.com/v23.0';

    public function handle(): int
    {
        $postId = $this->option('post-id');
        $since = $this->option('since');
        $until = $this->option('until');
        $accessToken = $this->option('access-token');
        $metric = $this->option('metric') ?? 'post_impressions';
        $period = $this->option('period');

        // For lifetime we don't require since/until. For daily, we do.
        if (!$postId) {
            $this->error('Usage: php artisan facebook:debug-daily-insights --post-id=POST_ID [--since=YYYY-MM-DD --until=YYYY-MM-DD] [--metric=post_impressions] [--period=day] [--access-token=TOKEN]');
            return 1;
        }

        // If no access token passed, fetch from DB via page_id of the post
        if (!$accessToken) {
            $post = DB::table('post_facebook_fanpage_not_ads')->where('post_id', $postId)->first();
            if (!$post) {
                $this->error('Post not found in database: ' . $postId);
                return 1;
            }
            $page = DB::table('facebook_fanpage')->where('page_id', $post->page_id)->first();
            if (!$page || empty($page->access_token)) {
                $this->error('Page access token not found for page_id: ' . $post->page_id);
                return 1;
            }
            $accessToken = $page->access_token;
            $this->info('Using page access token for page_id ' . $post->page_id);
        }

        $url = "$this->graphApiUrl/$postId/insights";
        $params = [
            'access_token' => $accessToken,
            'metric' => $metric,
        ];
        if ($period) {
            $params['period'] = $period;
        }
        if ($since && $until) {
            $params['since'] = $since;
            $params['until'] = $until;
        }

        $this->info('GET ' . $url . ' ' . json_encode($params));
        $response = Http::timeout(30)->get($url, $params);

        $this->info('Status: ' . $response->status());
        $this->line($response->body());

        return $response->successful() ? 0 : 1;
    }
}


