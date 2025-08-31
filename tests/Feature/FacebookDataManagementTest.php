<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\FacebookPage;
use App\Models\FacebookPost;
use App\Models\FacebookAd;
use App\Models\FacebookAdInsight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FacebookDataManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FacebookPage $page;
    protected FacebookPost $post;
    protected FacebookAd $ad;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Tạo permission
        Permission::create(['name' => 'view-facebook-data', 'guard_name' => 'web']);
        
        // Tạo role và gán permission
        $role = Role::create(['name' => 'Test User', 'guard_name' => 'web']);
        $role->givePermissionTo('view-facebook-data');
        
        // Tạo user
        $this->user = User::factory()->create();
        $this->user->assignRole($role);
        
        // Tạo dữ liệu test
        $this->page = FacebookPage::create([
            'id' => 'test_page_123',
            'name' => 'Test Page',
            'category' => 'Test Category',
            'fan_count' => 1000,
        ]);
        
        $this->post = FacebookPost::create([
            'id' => 'test_post_123',
            'page_id' => $this->page->id,
            'message' => 'Test post message',
            'type' => 'status',
            'status_type' => 'published_story',
            'permalink_url' => 'https://facebook.com/test_post_123',
            'created_time' => now(),
            'likes_count' => 100,
            'shares_count' => 10,
            'comments_count' => 5,
            'reactions_count' => 115,
        ]);
        
        $this->ad = FacebookAd::create([
            'id' => 'test_ad_123',
            'name' => 'Test Ad',
            'status' => 'ACTIVE',
            'effective_status' => 'ACTIVE',
            'post_id' => $this->post->id,
            'page_id' => $this->page->id,
            'adset_id' => 'test_adset_123',
            'campaign_id' => 'test_campaign_123',
            'account_id' => 'test_account_123',
        ]);
        
        FacebookAdInsight::create([
            'ad_id' => $this->ad->id,
            'date' => now()->toDateString(),
            'spend' => 1000,
            'reach' => 5000,
            'impressions' => 10000,
            'clicks' => 100,
            'conversions' => 5,
            'cpc' => 10,
            'cpm' => 100,
        ]);
    }

    /** @test */
    public function user_can_access_facebook_data_management_page_with_permission()
    {
        $response = $this->actingAs($this->user)
            ->get(route('facebook.data-management.index'));

        $response->assertStatus(200);
        $response->assertViewIs('facebook.data-management.index');
        $response->assertSee('Quản lý dữ liệu Facebook');
    }

    /** @test */
    public function user_cannot_access_facebook_data_management_page_without_permission()
    {
        $userWithoutPermission = User::factory()->create();
        
        $response = $this->actingAs($userWithoutPermission)
            ->get(route('facebook.data-management.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function page_selection_dropdown_shows_available_pages()
    {
        $response = $this->actingAs($this->user)
            ->get(route('facebook.data-management.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Page');
        $response->assertSee('1,000 fan');
    }

    /** @test */
    public function posts_are_displayed_when_page_is_selected()
    {
        $response = $this->actingAs($this->user)
            ->get(route('facebook.data-management.index', ['page_id' => $this->page->id]));

        $response->assertStatus(200);
        $response->assertSee('Test post message');
        $response->assertSee('100');
        $response->assertSee('10');
        $response->assertSee('5');
    }

    /** @test */
    public function api_endpoint_returns_posts_for_selected_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('facebook.data-management.posts', ['page_id' => $this->page->id]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'message',
                    'type',
                    'engagement',
                    'ads',
                    'links'
                ]
            ]
        ]);
    }

    /** @test */
    public function spending_stats_are_displayed_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('facebook.data-management.index', ['page_id' => $this->page->id]));

        $response->assertStatus(200);
        $response->assertSee('1,000 VND'); // Total spend
        $response->assertSee('10,000'); // Total impressions
        $response->assertSee('100'); // Total clicks
    }

    /** @test */
    public function filters_work_correctly()
    {
        // Tạo thêm một post với type khác
        FacebookPost::create([
            'id' => 'test_post_456',
            'page_id' => $this->page->id,
            'message' => 'Photo post',
            'type' => 'photo',
            'status_type' => 'published_story',
            'created_time' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('facebook.data-management.index', [
                'page_id' => $this->page->id,
                'post_type' => 'photo'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Photo post');
        $response->assertDontSee('Test post message');
    }

    /** @test */
    public function search_filter_works_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('facebook.data-management.index', [
                'page_id' => $this->page->id,
                'search' => 'Test post'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Test post message');
    }

    /** @test */
    public function date_filters_work_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('facebook.data-management.index', [
                'page_id' => $this->page->id,
                'date_from' => now()->subDays(7)->toDateString(),
                'date_to' => now()->toDateString()
            ]));

        $response->assertStatus(200);
        $response->assertSee('Test post message');
    }

    /** @test */
    public function links_to_facebook_are_generated_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('facebook.data-management.index', ['page_id' => $this->page->id]));

        $response->assertStatus(200);
        $response->assertSee('https://facebook.com/test_post_123');
        $response->assertSee('Xem bài viết');
        $response->assertSee('Xem trang');
    }
} 