<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestFacebookApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:test-api {--fields=id,name} {--endpoint=me}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Facebook API endpoint with TESTFB access token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Facebook API...');
        
        // Get access token from env
        $accessToken = env('TESTFB');
        
        if (!$accessToken) {
            $this->error('❌ TESTFB access token not found in .env file');
            $this->line('Please add TESTFB=your_access_token to your .env file');
            return 1;
        }
        
        // Get command options
        $endpoint = $this->option('endpoint');
        $fields = $this->option('fields');
        
        // Build API URL
        $apiUrl = "https://graph.facebook.com/v23.0/{$endpoint}";
        $params = [
            'fields' => $fields,
            'access_token' => $accessToken
        ];
        
        $this->line("📡 Testing endpoint: {$apiUrl}");
        $this->line("🔧 Fields: {$fields}");
        $this->line("🔑 Using access token: " . substr($accessToken, 0, 10) . "...");
        $this->newLine();
        
        try {
            // Make API request
            $this->info('⏳ Making API request...');
            
            $response = Http::timeout(30)
                ->get($apiUrl, $params);
            
            $statusCode = $response->status();
            $responseData = $response->json();
            
            // Display results
            $this->newLine();
            $this->line("📊 Response Status: {$statusCode}");
            
            if ($statusCode === 200) {
                $this->info('✅ API request successful!');
                $this->newLine();
                
                // Display response data
                $this->line('📋 Response Data:');
                $this->line(json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                // Log success
                Log::info('Facebook API Test Success', [
                    'endpoint' => $endpoint,
                    'fields' => $fields,
                    'status_code' => $statusCode,
                    'response' => $responseData
                ]);
                
            } else {
                $this->error("❌ API request failed with status: {$statusCode}");
                
                // Display error details
                if (isset($responseData['error'])) {
                    $error = $responseData['error'];
                    $this->line("Error Code: {$error['code']}");
                    $this->line("Error Message: {$error['message']}");
                    
                    if (isset($error['error_subcode'])) {
                        $this->line("Error Subcode: {$error['error_subcode']}");
                    }
                    
                    if (isset($error['error_user_title'])) {
                        $this->line("User Title: {$error['error_user_title']}");
                    }
                    
                    if (isset($error['error_user_msg'])) {
                        $this->line("User Message: {$error['error_user_msg']}");
                    }
                }
                
                // Log error
                Log::error('Facebook API Test Failed', [
                    'endpoint' => $endpoint,
                    'fields' => $fields,
                    'status_code' => $statusCode,
                    'error' => $responseData
                ]);
                
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Exception occurred: {$e->getMessage()}");
            
            // Log exception
            Log::error('Facebook API Test Exception', [
                'endpoint' => $endpoint,
                'fields' => $fields,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
        
        $this->newLine();
        $this->info('🎉 Test completed successfully!');
        
        return 0;
    }
}
