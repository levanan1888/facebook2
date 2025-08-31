<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FacebookAdsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestAllBreakdowns extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'facebook:test-all-breakdowns 
                            {--ad-id= : Facebook Ad ID để test}
                            {--limit=5 : Số lượng breakdowns để test}';

    /**
     * The console command description.
     */
    protected $description = 'Test tất cả breakdowns có sẵn từ Facebook Marketing API';

    /**
     * Execute the console command.
     */
    public function handle(FacebookAdsService $api): int
    {
        $this->info('Bắt đầu test tất cả breakdowns từ Facebook Marketing API...');

        $adId = $this->option('ad-id');
        $limit = (int) $this->option('limit');

        if (!$adId) {
            // Lấy ad ID đầu tiên từ database
            $firstAd = \App\Models\FacebookAd::first();
            if (!$firstAd) {
                $this->error('Không tìm thấy ad nào trong database');
                return 1;
            }
            $adId = $firstAd->id;
            $this->info("Sử dụng ad ID: {$adId}");
        }

        // 1. Hiển thị tất cả breakdowns có sẵn
        $this->info('=== TẤT CẢ BREAKDOWNS CÓ SẴN ===');
        $allBreakdowns = $api->getAllAvailableBreakdowns();
        
        foreach ($allBreakdowns as $category => $breakdowns) {
            $this->info("\n📁 {$category}:");
            foreach ($breakdowns as $key => $description) {
                $this->line("  • {$key}: {$description}");
            }
        }

        // 2. Hiển thị supported combinations
        $this->info('\n=== SUPPORTED COMBINATIONS ===');
        $combinations = $api->getSupportedBreakdownCombinations();
        
        $this->info('Single breakdowns:');
        foreach ($combinations['single'] as $breakdown) {
            $this->line("  • {$breakdown}");
        }
        
        $this->info('Combinations:');
        foreach ($combinations['combinations'] as $combination) {
            $this->line("  • {$combination}");
        }

        // 3. Test validation
        $this->info('\n=== VALIDATION TEST ===');
        $testBreakdowns = [
            'age',
            'region',
            'hourly_stats_aggregated_by_advertiser_time_zone',
            'app_store_clicks', // Không được hỗ trợ
            'newsfeed_impressions' // Không được hỗ trợ
        ];

        foreach ($testBreakdowns as $breakdown) {
            $validation = $api->validateBreakdowns([$breakdown]);
            $status = $validation['valid'] ? '✅' : '❌';
            $this->line("{$status} {$breakdown}");
            
            if (!empty($validation['errors'])) {
                foreach ($validation['errors'] as $error) {
                    $this->error("    Error: {$error}");
                }
            }
            
            if (!empty($validation['warnings'])) {
                foreach ($validation['warnings'] as $warning) {
                    $this->warn("    Warning: {$warning}");
                }
            }
        }

        // 4. Test API calls với một số breakdowns
        $this->info('\n=== API TEST ===');
        $testBreakdowns = [
            'age',
            'gender', 
            'country',
            'publisher_platform',
            'action_type',
            'action_device'
        ];

        $testCount = 0;
        foreach ($testBreakdowns as $breakdown) {
            if ($testCount >= $limit) break;
            
            try {
                $this->info("Testing breakdown: {$breakdown}");
                $result = $api->getInsightsForAdWithBreakdowns($adId, [$breakdown]);
                
                if (isset($result['data']) && !empty($result['data'])) {
                    $firstRecord = $result['data'][0];
                    $this->info("  ✅ Success - Có " . count($result['data']) . " records");
                    
                    // Debug cấu trúc data
                    $this->info("  📊 Record structure:");
                    $this->line("    - spend: " . ($firstRecord['spend'] ?? 'N/A'));
                    $this->line("    - impressions: " . ($firstRecord['impressions'] ?? 'N/A'));
                    $this->line("    - clicks: " . ($firstRecord['clicks'] ?? 'N/A'));
                    
                    // Kiểm tra breakdowns field
                    if (isset($firstRecord['breakdowns'])) {
                        $this->info("    - breakdowns: " . json_encode($firstRecord['breakdowns']));
                    } else {
                        $this->warn("    - breakdowns: NOT FOUND");
                    }
                    
                    // Kiểm tra các fields khác có thể chứa breakdown data
                    $breakdownFields = ['age', 'gender', 'country', 'region', 'publisher_platform'];
                    foreach ($breakdownFields as $field) {
                        if (isset($firstRecord[$field])) {
                            $this->info("    - {$field}: " . $firstRecord[$field]);
                        }
                    }
                    
                } else {
                    $this->warn("  ⚠️  No data returned");
                    if (isset($result['error'])) {
                        $this->error("    Error: " . json_encode($result['error']));
                    }
                }
                
                $testCount++;
            } catch (\Exception $e) {
                $this->error("  ❌ Error: {$e->getMessage()}");
            }
        }

        // 5. Test action breakdowns
        $this->info('\n=== ACTION BREAKDOWNS TEST ===');
        $actionBreakdowns = [
            'action_type',
            'action_device',
            'action_reaction'
        ];

        foreach ($actionBreakdowns as $breakdown) {
            try {
                $this->info("Testing action breakdown: {$breakdown}");
                $result = $api->getInsightsWithActionBreakdowns($adId, [$breakdown]);
                
                if (isset($result['data']) && !empty($result['data'])) {
                    $this->info("  ✅ Success - Có data");
                } else {
                    $this->warn("  ⚠️  No data returned");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Error: {$e->getMessage()}");
            }
        }

        $this->info('\n=== SUMMARY ===');
        $this->info('✅ Đã test validation logic');
        $this->info('✅ Đã test API calls với các breakdowns cơ bản');
        $this->info('✅ Đã test action breakdowns');
        $this->info('📊 Tổng số breakdowns có sẵn: ' . count(array_merge(...array_values($allBreakdowns))));
        
        return 0;
    }
}
