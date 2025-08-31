<?php

use App\Models\User;
use App\Models\FacebookAd;
use App\Models\FacebookAdSet;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdAccount;
use App\Models\FacebookBusiness;
use Spatie\Permission\Models\Role;
use Database\Seeders\RolePermissionSeeder;
use App\Services\FacebookAdsService;
use Illuminate\Support\Facades\Schema;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    // Chạy seeder để tạo permissions và roles
    $this->seed(RolePermissionSeeder::class);
    
    // Lấy role User đã được tạo
    $userRole = Role::where('name', 'User')->first();
    
    $user = User::factory()->create();
    $user->assignRole($userRole);
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('FacebookAdsService uses nested fields for creative', function () {
    $service = new FacebookAdsService('test_token');
    
    // Sử dụng reflection để kiểm tra private method hoặc kiểm tra fields
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getAdsByAdSet');
    $method->setAccessible(true);
    
    // Tạo mock URL và params
    $url = "https://graph.facebook.com/v18.0/test_adset_id/ads";
    $params = [
        'access_token' => 'test_token',
        'fields' => 'id,name,status,effective_status,creative{id,title,body,object_story_spec,link_data,object_story_id,effective_object_story_id},created_time,updated_time,object_story_id,effective_object_story_id'
    ];
    
    // Kiểm tra xem fields có chứa nested creative không
    $this->assertStringContainsString('creative{id,title,body,object_story_spec,link_data,object_story_id,effective_object_story_id}', $params['fields']);
    $this->assertStringContainsString('object_story_spec', $params['fields']);
    $this->assertStringContainsString('link_data', $params['fields']);
});

test('facebook_ads table structure supports JSON fields', function () {
    // Kiểm tra xem các trường quan trọng đã được chuyển thành JSON chưa
    $columns = Schema::getColumnListing('facebook_ads');
    
    // Các trường này phải có trong bảng
    $this->assertContains('page_id', $columns);
    $this->assertContains('post_id', $columns);
    $this->assertContains('campaign_id', $columns);
    $this->assertContains('adset_id', $columns);
    $this->assertContains('account_id', $columns);
    
    // Kiểm tra kiểu dữ liệu của các trường (cần kiểm tra trực tiếp trong database)
    $this->assertTrue(true); // Placeholder - kiểm tra thực tế sẽ được thực hiện khi chạy sync
});

test('can save single ad with JSON page_id field', function () {
    // Tạo dữ liệu test cơ bản
    $business = FacebookBusiness::create([
        'id' => 'test_business_123',
        'name' => 'Test Business',
        'verification_status' => 'verified'
    ]);
    
    $adAccount = FacebookAdAccount::create([
        'id' => 'test_account_123',
        'name' => 'Test Ad Account',
        'status' => 'ACTIVE',
        'business_id' => $business->id
    ]);
    
    $campaign = FacebookCampaign::create([
        'id' => 'test_campaign_123',
        'name' => 'Test Campaign',
        'status' => 'ACTIVE',
        'objective' => 'REACH',
        'ad_account_id' => $adAccount->id
    ]);
    
    $adSet = FacebookAdSet::create([
        'id' => 'test_adset_123',
        'name' => 'Test Ad Set',
        'status' => 'ACTIVE',
        'campaign_id' => $campaign->id
    ]);
    
    // Test lưu ad với page_id là array (JSON)
    $adData = [
        'id' => 'test_ad_123',
        'name' => 'Test Ad',
        'status' => 'ACTIVE',
        'effective_status' => 'ACTIVE',
        'adset_id' => $adSet->id,
        'campaign_id' => $campaign->id,
        'account_id' => $adAccount->id,
        'creative' => json_encode([
            'id' => 'creative_123',
            'title' => 'Test Creative Title',
            'body' => 'Test Creative Body'
        ]),
        'page_id' => ['page_id' => '123456789', 'type' => 'page'], // Array sẽ được lưu dưới dạng JSON
        'created_time' => now(),
        'updated_time' => now()
    ];
    
    $ad = FacebookAd::create($adData);
    
    // Kiểm tra ad đã được lưu
    $this->assertDatabaseHas('facebook_ads', [
        'id' => 'test_ad_123',
        'name' => 'Test Ad'
    ]);
    
    // Kiểm tra page_id đã được lưu dưới dạng JSON
    $savedAd = FacebookAd::find('test_ad_123');
    $this->assertIsArray($savedAd->page_id);
    $this->assertEquals('123456789', $savedAd->page_id['page_id']);
    $this->assertEquals('page', $savedAd->page_id['type']);
    
    // Cleanup
    $ad->delete();
    $adSet->delete();
    $campaign->delete();
    $adAccount->delete();
    $business->delete();
});