<?php

use App\Models\FacebookAd;
use App\Models\FacebookAdSet;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdAccount;
use App\Models\FacebookBusiness;

test('can save single ad with JSON fields', function () {
    // Tạo dữ liệu test cơ bản
    $business = FacebookBusiness::create([
        'id' => 'test_business_123',
        'name' => 'Test Business',
        'verification_status' => 'verified'
    ]);
    
    $adAccount = FacebookAdAccount::create([
        'id' => 'test_account_123',
        'name' => 'Test Ad Account',
        'account_status' => 'ACTIVE',
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
    
    // Test lưu ad với các trường JSON
    $adData = [
        'id' => 'test_ad_123',
        'name' => 'Test Ad',
        'status' => 'ACTIVE',
        'effective_status' => 'ACTIVE',
        'adset_id' => $adSet->id, // Sẽ được lưu dưới dạng JSON
        'campaign_id' => $campaign->id, // Sẽ được lưu dưới dạng JSON
        'account_id' => $adAccount->id, // Sẽ được lưu dưới dạng JSON
        'creative' => json_encode([
            'id' => 'creative_123',
            'title' => 'Test Creative Title',
            'body' => 'Test Creative Body'
        ]),
        'page_id' => ['page_id' => '123456789', 'type' => 'page'], // Array sẽ được lưu dưới dạng JSON
        'post_id' => ['post_id' => '987654321', 'type' => 'post'], // Array sẽ được lưu dưới dạng JSON
        'created_time' => now(),
        'updated_time' => now()
    ];
    
    $ad = FacebookAd::create($adData);
    
    // Kiểm tra ad đã được lưu
    $this->assertDatabaseHas('facebook_ads', [
        'id' => 'test_ad_123',
        'name' => 'Test Ad'
    ]);
    
    // Kiểm tra các trường JSON đã được lưu đúng
    $savedAd = FacebookAd::find('test_ad_123');
    
    // Kiểm tra page_id
    $this->assertIsArray($savedAd->page_id);
    $this->assertEquals('123456789', $savedAd->page_id['page_id']);
    $this->assertEquals('page', $savedAd->page_id['type']);
    
    // Kiểm tra post_id
    $this->assertIsArray($savedAd->post_id);
    $this->assertEquals('987654321', $savedAd->post_id['post_id']);
    $this->assertEquals('post', $savedAd->post_id['type']);
    
    // Kiểm tra adset_id, campaign_id, account_id
    $this->assertEquals($adSet->id, $savedAd->adset_id);
    $this->assertEquals($campaign->id, $savedAd->campaign_id);
    $this->assertEquals($adAccount->id, $savedAd->account_id);
    
    echo "✅ Test lưu ads với JSON fields thành công!\n";
    echo "Page ID: " . json_encode($savedAd->page_id) . "\n";
    echo "Post ID: " . json_encode($savedAd->post_id) . "\n";
    echo "AdSet ID: " . $savedAd->adset_id . "\n";
    echo "Campaign ID: " . $savedAd->campaign_id . "\n";
    echo "Account ID: " . $savedAd->account_id . "\n";
    
    // Cleanup
    $ad->delete();
    $adSet->delete();
    $campaign->delete();
    $adAccount->delete();
    $business->delete();
});

