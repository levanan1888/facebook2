<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DebugVideoInsights extends Command
{
    protected $signature = 'facebook:debug-video-insights {--video-id=} {--metric=} {--period=} {--since=} {--until=}';
    protected $description = 'Debug raw Graph API response for video_insights with page access token from DB';

    private string $graphApiUrl = 'https://graph.facebook.com/v23.0';

    public function handle(): int
    {
        $videoId = $this->option('video-id');
        $metric = $this->option('metric') ?? implode(',', [
            'total_video_impressions',
            'total_video_views',
            'video_complete_views',
            'video_avg_time_watched'
        ]);
        $period = $this->option('period'); // omit for lifetime
        $since = $this->option('since');
        $until = $this->option('until');

        if (!$videoId) {
            $this->error('Usage: php artisan facebook:debug-video-insights --video-id=VIDEO_ID [--metric=..] [--period=day|lifetime] [--since=YYYY-MM-DD --until=YYYY-MM-DD]');
            return 1;
        }

        // Find any page access token (prefer the page that owns the post if known). For debug, take first token.
        $page = DB::table('facebook_fanpage')->whereNotNull('access_token')->where('access_token','!=','')->first();
        if (!$page) {
            $this->error('No page access token found in DB');
            return 1;
        }
        $accessToken = $page->access_token;

        $url = $this->graphApiUrl . "/{$videoId}/video_insights";
        $params = [
            'access_token' => $accessToken,
            'metric' => $metric,
        ];
        if ($period) { $params['period'] = $period; }
        if ($since && $until) { $params['since'] = $since; $params['until'] = $until; }

        $this->info('GET ' . $url . ' ' . json_encode($params));
        $response = Http::timeout(30)->get($url, $params);
        $this->info('Status: ' . $response->status());
        $this->line($response->body());
        return $response->successful() ? 0 : 1;
    }
}


