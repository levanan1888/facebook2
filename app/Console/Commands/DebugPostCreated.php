<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugPostCreated extends Command
{
    protected $signature = 'facebook:debug-post-created {--post-id=}';
    protected $description = 'Show created_time of a post from DB and its page_id';

    public function handle(): int
    {
        $postId = $this->option('post-id');
        if (!$postId) {
            $this->error('Usage: php artisan facebook:debug-post-created --post-id=POST_ID');
            return 1;
        }
        $post = DB::table('post_facebook_fanpage_not_ads')
            ->select('id','post_id','page_id','created_time','created_time_video')
            ->where('post_id', $postId)
            ->first();
        if (!$post) {
            $this->error('Post not found: ' . $postId);
            return 1;
        }
        $this->info('DB row: ' . json_encode($post));
        return 0;
    }
}


