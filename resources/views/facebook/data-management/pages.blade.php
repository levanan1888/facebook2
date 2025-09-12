<x-layouts.app :title="'Quản lý Page (Organic Posts)'">
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Quản lý Page</h1>
        <p class="text-gray-600">Chọn page để xem bài viết organic (không chạy ads)</p>
    </div>

    <form method="GET" action="{{ route('facebook.data-management.pages') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-gray-700 min-w-[120px]">Chọn Trang:</label>
            <select name="page_id" class="flex-1 min-w-[280px] rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Chọn trang --</option>
                @foreach(($pages ?? collect()) as $page)
                    <option value="{{ $page->id }}" {{ (($filters['page_id'] ?? request('page_id')) == $page->id) ? 'selected' : '' }}>
                        {{ $page->name }} ({{ number_format($page->fan_count ?? 0) }} fan)
                    </option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"/>
            <span class="text-gray-500">→</span>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"/>
            <select name="post_type" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Tất cả loại bài</option>
                <option value="status" {{ ($filters['post_type'] ?? '')=='status'?'selected':'' }}>Trạng thái</option>
                <option value="photo" {{ ($filters['post_type'] ?? '')=='photo'?'selected':'' }}>Ảnh</option>
                <option value="video" {{ ($filters['post_type'] ?? '')=='video'?'selected':'' }}>Video</option>
                <option value="link" {{ ($filters['post_type'] ?? '')=='link'?'selected':'' }}>Liên kết</option>
                <option value="album" {{ ($filters['post_type'] ?? '')=='album'?'selected':'' }}>Album ảnh</option>
            </select>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tìm nội dung..." class="w-56 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"/>
            <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Lọc
            </button>
            <a href="{{ route('facebook.data-management.pages') }}" class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded-md hover:bg-gray-600 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Xóa lọc
            </a>
        </div>
    </form>

    @if($selected_page)
        @php
            // Lấy thông tin chi tiết page từ facebook_fanpage
            $pageInfo = \App\Models\FacebookFanpage::where('page_id', $selected_page->id)->first();
            
            // Lấy tất cả posts của page với phân trang
            $postsQuery = \App\Models\PostFacebookFanpageNotAds::where('page_id', $selected_page->id);
            
            // Apply filters
            if (!empty($filters['date_from'])) {
                $postsQuery->whereDate('created_time', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $postsQuery->whereDate('created_time', '<=', $filters['date_to']);
            }
            if (!empty($filters['post_type'])) {
                // Phân loại dựa trên attachments thay vì type
                if ($filters['post_type'] == 'photo') {
                    $postsQuery->where(function($q) {
                        $q->where('type', 'photo')
                          ->orWhere('attachments', 'like', '%"media_type":"photo"%')
                          ->orWhere('attachments', 'like', '%"media_type":"album"%');
                    });
                } elseif ($filters['post_type'] == 'video') {
                    $postsQuery->where(function($q) {
                        $q->where('type', 'video')
                          ->orWhere('attachments', 'like', '%"media_type":"video"%');
                    });
                } elseif ($filters['post_type'] == 'link') {
                    $postsQuery->where(function($q) {
                        $q->where('type', 'link')
                          ->orWhere('attachments', 'like', '%"media_type":"link"%');
                    });
                } elseif ($filters['post_type'] == 'album') {
                    $postsQuery->where('attachments', 'like', '%"media_type":"album"%');
                } elseif ($filters['post_type'] == 'status') {
                    $postsQuery->where(function($q) {
                        $q->where('type', 'status')
                          ->orWhere(function($q2) {
                              $q2->whereNull('attachments')
                                  ->orWhere('attachments', '=', '[]')
                                  ->orWhere('attachments', '=', 'null');
                          });
                    });
                }
            }
            if (!empty($filters['search'])) {
                $postsQuery->where(function($q) use ($filters) {
                    $q->where('message', 'like', '%'.$filters['search'].'%')
                      ->orWhere('name', 'like', '%'.$filters['search'].'%')
                      ->orWhere('description', 'like', '%'.$filters['search'].'%');
                });
            }

            // Apply sorting
            $sortBy = $filters['sort_by'] ?? 'created_time_desc';
            switch ($sortBy) {
                case 'created_time_asc':
                    $postsQuery->orderBy('created_time', 'asc');
                    break;
                case 'post_impressions_desc':
                    $postsQuery->orderBy('post_impressions', 'desc');
                    break;
                case 'post_impressions_asc':
                    $postsQuery->orderBy('post_impressions', 'asc');
                    break;
                case 'post_reactions_desc':
                    $postsQuery->orderBy('post_reactions', 'desc');
                    break;
                case 'post_reactions_asc':
                    $postsQuery->orderBy('post_reactions', 'asc');
                    break;
                case 'post_comments_desc':
                    $postsQuery->orderBy('post_comments', 'desc');
                    break;
                case 'post_comments_asc':
                    $postsQuery->orderBy('post_comments', 'asc');
                    break;
                case 'post_shares_desc':
                    $postsQuery->orderBy('post_shares', 'desc');
                    break;
                case 'post_shares_asc':
                    $postsQuery->orderBy('post_shares', 'asc');
                    break;
                default: // created_time_desc
                    $postsQuery->orderBy('created_time', 'desc');
                    break;
            }
            
            // Paginate posts
            $paginatedPosts = $postsQuery->paginate(10)->appends(request()->query());
            
            // Tính tổng các chỉ số của page
            $allPagePosts = \App\Models\PostFacebookFanpageNotAds::where('page_id', $selected_page->id);
            $pagePostsCount = $allPagePosts->count();
            $pageReach = $allPagePosts->sum('post_impressions');
            $pageReachUnique = $allPagePosts->sum('post_impressions_unique');
            $pageReachOrganic = $allPagePosts->sum('post_impressions_organic');
            $pageReachPaid = $allPagePosts->sum('post_impressions_paid');
            $pageEngagements = $allPagePosts->sum('post_engaged_users');
            $pageClicks = $allPagePosts->sum('post_clicks');
            $pageClicksUnique = $allPagePosts->sum('post_clicks_unique');
            $pageVideoViews = $allPagePosts->sum('post_video_views');
            $pageVideoViewsOrganic = $allPagePosts->sum('post_video_views_organic');
            $pageVideoViewsPaid = $allPagePosts->sum('post_video_views_paid');
            $pageVideoCompleteViews = $allPagePosts->sum('post_video_complete_views');
            
            // Tính tổng các loại reactions
            $pageReactionsLike = $allPagePosts->sum('post_reactions_like_total');
            $pageReactionsLove = $allPagePosts->sum('post_reactions_love_total');
            $pageReactionsWow = $allPagePosts->sum('post_reactions_wow_total');
            $pageReactionsHaha = $allPagePosts->sum('post_reactions_haha_total');
            $pageReactionsSorry = $allPagePosts->sum('post_reactions_sorry_total');
            $pageReactionsAnger = $allPagePosts->sum('post_reactions_anger_total');
            $totalReactions = $pageReactionsLike + $pageReactionsLove + $pageReactionsWow + $pageReactionsHaha + $pageReactionsSorry + $pageReactionsAnger;
            
            $pageComments = $allPagePosts->sum('post_comments');
            $pageShares = $allPagePosts->sum('post_shares');
        @endphp

        <!-- Thông tin chi tiết page -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-start gap-6 mb-6">
                <div class="flex-shrink-0">
                    @if(!empty($pageInfo->profile_picture_url))
                        <img src="{{ $pageInfo->profile_picture_url }}" alt="avatar" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200"/>
                    @else
                        <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500 text-2xl">📄</span>
                        </div>
                    @endif
                </div>
                
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $pageInfo->name ?? $selected_page->name }}</h2>
                        <a href="https://facebook.com/{{ $selected_page->id }}" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                            </svg>
                            Xem trên Facebook
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        @if($pageInfo)
                            <div>
                                <span class="font-medium text-gray-700">Danh mục:</span>
                                <span class="text-gray-900">{{ $pageInfo->category ?? 'Unknown' }}</span>
                            </div>
                            
                            @if($pageInfo->about)
                                <div>
                                    <span class="font-medium text-gray-700">Giới thiệu:</span>
                                    <span class="text-gray-900">{{ Str::limit($pageInfo->about, 100) }}</span>
                                </div>
                            @endif
                            
                            @if($pageInfo->website)
                                <div>
                                    <span class="font-medium text-gray-700">Website:</span>
                                    <a href="{{ $pageInfo->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $pageInfo->website }}</a>
                                </div>
                            @endif
                            
                            @if($pageInfo->phone)
                                <div>
                                    <span class="font-medium text-gray-700">Điện thoại:</span>
                                    <span class="text-gray-900">{{ $pageInfo->phone }}</span>
                                </div>
                            @endif
                            
                            @if($pageInfo->email)
                                <div>
                                    <span class="font-medium text-gray-700">Email:</span>
                                    <span class="text-gray-900">{{ $pageInfo->email }}</span>
                                </div>
                            @endif
                            
                            <div>
                                <span class="font-medium text-gray-700">Trạng thái:</span>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full {{ $pageInfo->is_published ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $pageInfo->is_published ? 'Đã xuất bản' : 'Chưa xuất bản' }}
                                </span>
                                @if($pageInfo->is_verified)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full ml-2">
                                        ✓ Đã xác minh
                                    </span>
                @endif
                            </div>
                            
                <div>
                                <span class="font-medium text-gray-700">Cập nhật lần cuối:</span>
                                <span class="text-gray-900">{{ $pageInfo->last_synced_at ? \Carbon\Carbon::parse($pageInfo->last_synced_at)->diffForHumans() : 'Chưa đồng bộ' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cover photo nếu có -->
            @if($pageInfo && $pageInfo->cover_photo_url)
                <div class="mb-6">
                    <img src="{{ $pageInfo->cover_photo_url }}" alt="Cover photo" class="w-full h-32 object-cover rounded-lg"/>
                </div>
            @endif
            
            <!-- Các chỉ số tổng quan của page -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Thống kê tổng quan</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div class="text-center">
                        <div class="text-xl font-bold text-blue-600">{{ number_format($pageInfo->fan_count ?? 0) }}</div>
                        <div class="text-sm text-gray-500">Fans</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-green-600">{{ number_format($pageInfo->followers_count ?? 0) }}</div>
                        <div class="text-sm text-gray-500">Followers</div>
                    </div>
                <div class="text-center">
                        <div class="text-xl font-bold text-purple-600">{{ number_format($pagePostsCount) }}</div>
                    <div class="text-sm text-gray-500">Posts</div>
                </div>
                <div class="text-center">
                        <div class="text-xl font-bold text-orange-600">{{ number_format($pageReach) }}</div>
                    <div class="text-sm text-gray-500">Reach</div>
                </div>
                <div class="text-center">
                        <div class="text-xl font-bold text-pink-600">{{ number_format($totalReactions) }}</div>
                        <div class="text-sm text-gray-500">Reactions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-indigo-600">{{ number_format($pageEngagements) }}</div>
                        <div class="text-sm text-gray-500">Engagements</div>
                    </div>
                </div>
                
                <!-- Chỉ số chi tiết -->
                <div class="mt-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 text-xs">
                    @if($pageReachUnique > 0)
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="font-semibold text-blue-600">{{ number_format($pageReachUnique) }}</div>
                            <div class="text-gray-500">Reach Unique</div>
                        </div>
                    @endif
                    @if($pageReachOrganic > 0)
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="font-semibold text-green-600">{{ number_format($pageReachOrganic) }}</div>
                            <div class="text-gray-500">Reach Organic</div>
                        </div>
                    @endif
                    @if($pageReachPaid > 0)
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="font-semibold text-yellow-600">{{ number_format($pageReachPaid) }}</div>
                            <div class="text-gray-500">Reach Paid</div>
                        </div>
                    @endif
                    @if($pageClicks > 0)
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="font-semibold text-purple-600">{{ number_format($pageClicks) }}</div>
                            <div class="text-gray-500">Clicks</div>
                        </div>
                    @endif
                    @if($pageClicksUnique > 0)
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="font-semibold text-purple-700">{{ number_format($pageClicksUnique) }}</div>
                            <div class="text-gray-500">Clicks Unique</div>
                        </div>
                    @endif
                    @if($pageVideoViews > 0)
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="font-semibold text-red-600">{{ number_format($pageVideoViews) }}</div>
                            <div class="text-gray-500">Video Views</div>
                        </div>
                    @endif
                    @if($pageVideoCompleteViews > 0)
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="font-semibold text-red-700">{{ number_format($pageVideoCompleteViews) }}</div>
                            <div class="text-gray-500">Complete Views</div>
                        </div>
                    @endif
                    @if($pageComments > 0)
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="font-semibold text-blue-500">{{ number_format($pageComments) }}</div>
                            <div class="text-gray-500">Comments</div>
                        </div>
                    @endif
                    @if($pageShares > 0)
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="font-semibold text-green-500">{{ number_format($pageShares) }}</div>
                            <div class="text-gray-500">Shares</div>
                        </div>
                    @endif
                </div>
                
                <!-- Breakdown reactions -->
                @if($totalReactions > 0)
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Phân tích Reactions:</h4>
                        <div class="grid grid-cols-3 md:grid-cols-6 gap-2 text-xs">
                            @if($pageReactionsLike > 0)
                                <div class="text-center p-2 bg-blue-50 rounded">
                                    <div class="font-semibold text-blue-600">{{ number_format($pageReactionsLike) }}</div>
                                    <div class="text-gray-500">👍 Like</div>
                                </div>
                            @endif
                            @if($pageReactionsLove > 0)
                                <div class="text-center p-2 bg-red-50 rounded">
                                    <div class="font-semibold text-red-600">{{ number_format($pageReactionsLove) }}</div>
                                    <div class="text-gray-500">❤️ Love</div>
                                </div>
                            @endif
                            @if($pageReactionsHaha > 0)
                                <div class="text-center p-2 bg-yellow-50 rounded">
                                    <div class="font-semibold text-yellow-600">{{ number_format($pageReactionsHaha) }}</div>
                                    <div class="text-gray-500">😂 Haha</div>
                                </div>
                            @endif
                            @if($pageReactionsWow > 0)
                                <div class="text-center p-2 bg-purple-50 rounded">
                                    <div class="font-semibold text-purple-600">{{ number_format($pageReactionsWow) }}</div>
                                    <div class="text-gray-500">😮 Wow</div>
                                </div>
                            @endif
                            @if($pageReactionsSorry > 0)
                                <div class="text-center p-2 bg-gray-100 rounded">
                                    <div class="font-semibold text-gray-600">{{ number_format($pageReactionsSorry) }}</div>
                                    <div class="text-gray-500">😢 Sad</div>
                                </div>
                            @endif
                            @if($pageReactionsAnger > 0)
                                <div class="text-center p-2 bg-red-100 rounded">
                                    <div class="font-semibold text-red-700">{{ number_format($pageReactionsAnger) }}</div>
                                    <div class="text-gray-500">😡 Angry</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Phần sắp xếp và tìm kiếm -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- Sắp xếp -->
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Sắp xếp:</label>
                    <select name="sort_by" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="created_time_desc" {{ ($filters['sort_by'] ?? 'created_time_desc') == 'created_time_desc' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="created_time_asc" {{ ($filters['sort_by'] ?? '') == 'created_time_asc' ? 'selected' : '' }}>Cũ nhất</option>
                        <option value="post_impressions_desc" {{ ($filters['sort_by'] ?? '') == 'post_impressions_desc' ? 'selected' : '' }}>Reach cao nhất</option>
                        <option value="post_impressions_asc" {{ ($filters['sort_by'] ?? '') == 'post_impressions_asc' ? 'selected' : '' }}>Reach thấp nhất</option>
                        <option value="post_reactions_desc" {{ ($filters['sort_by'] ?? '') == 'post_reactions_desc' ? 'selected' : '' }}>Reactions nhiều nhất</option>
                        <option value="post_reactions_asc" {{ ($filters['sort_by'] ?? '') == 'post_reactions_asc' ? 'selected' : '' }}>Reactions ít nhất</option>
                        <option value="post_comments_desc" {{ ($filters['sort_by'] ?? '') == 'post_comments_desc' ? 'selected' : '' }}>Comments nhiều nhất</option>
                        <option value="post_comments_asc" {{ ($filters['sort_by'] ?? '') == 'post_comments_asc' ? 'selected' : '' }}>Comments ít nhất</option>
                        <option value="post_shares_desc" {{ ($filters['sort_by'] ?? '') == 'post_shares_desc' ? 'selected' : '' }}>Shares nhiều nhất</option>
                        <option value="post_shares_asc" {{ ($filters['sort_by'] ?? '') == 'post_shares_asc' ? 'selected' : '' }}>Shares ít nhất</option>
                    </select>
                </div>

                <!-- Tìm kiếm nhanh -->
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Tìm kiếm nhanh:</label>
                    <input type="text" name="quick_search" value="{{ $filters['quick_search'] ?? '' }}" placeholder="Tìm trong bài viết..." class="w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"/>
                    <button type="button" onclick="performQuickSearch()" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Tìm
                    </button>
                </div>

                <!-- Lọc theo loại bài nhanh -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700">Loại:</span>
                    <div class="flex gap-1">
                        <button type="button" onclick="filterByType('')" class="px-2 py-1 text-xs rounded {{ empty($filters['post_type']) ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-100 text-gray-700 border border-gray-300' }} hover:bg-blue-50">
                            Tất cả
                        </button>
                        <button type="button" onclick="filterByType('status')" class="px-2 py-1 text-xs rounded {{ ($filters['post_type'] ?? '') == 'status' ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-100 text-gray-700 border border-gray-300' }} hover:bg-blue-50">
                            📝 Status
                        </button>
                        <button type="button" onclick="filterByType('photo')" class="px-2 py-1 text-xs rounded {{ ($filters['post_type'] ?? '') == 'photo' ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-100 text-gray-700 border border-gray-300' }} hover:bg-blue-50">
                            📷 Ảnh
                        </button>
                        <button type="button" onclick="filterByType('video')" class="px-2 py-1 text-xs rounded {{ ($filters['post_type'] ?? '') == 'video' ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-100 text-gray-700 border border-gray-300' }} hover:bg-blue-50">
                            🎥 Video
                        </button>
                        <button type="button" onclick="filterByType('album')" class="px-2 py-1 text-xs rounded {{ ($filters['post_type'] ?? '') == 'album' ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-100 text-gray-700 border border-gray-300' }} hover:bg-blue-50">
                            🖼️ Album
                        </button>
                        <button type="button" onclick="filterByType('link')" class="px-2 py-1 text-xs rounded {{ ($filters['post_type'] ?? '') == 'link' ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-100 text-gray-700 border border-gray-300' }} hover:bg-blue-50">
                            🔗 Link
                        </button>
                    </div>
                </div>

                <!-- Thống kê nhanh -->
                <div class="text-sm text-gray-600">
                    <span class="font-medium">{{ $paginatedPosts->total() }}</span> bài viết
                    @if($paginatedPosts->total() > 0)
                        | Hiển thị {{ $paginatedPosts->firstItem() }}-{{ $paginatedPosts->lastItem() }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Danh sách bài viết -->
        @if($paginatedPosts->count() > 0)
            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">
                    Bài viết ({{ $paginatedPosts->total() }} bài)
                </h3>
                <div class="text-sm text-gray-600">
                    Hiển thị {{ $paginatedPosts->firstItem() }}-{{ $paginatedPosts->lastItem() }} của {{ $paginatedPosts->total() }} bài
                </div>
            </div>
            
            <div class="space-y-4">
                @foreach($paginatedPosts as $originalPost)
                    @php
                                    // Xử lý attachments để lấy ảnh/video
                        $imageUrls = [];
                        $videoUrls = [];
                        $attachmentUrls = [];
                        $attachmentTitles = [];
                                    
                                    // Bóc trường attachments nếu có
                        if (!empty($originalPost->attachments)) {
                            $attachments = $originalPost->attachments;
                                        
                                        // Nếu attachments là JSON string
                                        if (is_string($attachments)) {
                                            $attachments = json_decode($attachments, true);
                                        }
                                        
                                        // Nếu attachments là object, chuyển thành array
                                        if (is_object($attachments)) {
                                            $attachments = json_decode(json_encode($attachments), true);
                                        }
                                        
                                        if (is_array($attachments) && isset($attachments['data']) && is_array($attachments['data'])) {
                                            foreach ($attachments['data'] as $attachment) {
                                                if (isset($attachment['media_type'])) {
                                                    if ($attachment['media_type'] === 'video' && isset($attachment['media']['source'])) {
                                            $videoUrls[] = $attachment['media']['source'];
                                            if (isset($attachment['url'])) $attachmentUrls[] = $attachment['url'];
                                            if (isset($attachment['title'])) $attachmentTitles[] = $attachment['title'];
                                                    } elseif ($attachment['media_type'] === 'photo' && isset($attachment['media']['image']['src'])) {
                                            $imageUrls[] = $attachment['media']['image']['src'];
                                            if (isset($attachment['url'])) $attachmentUrls[] = $attachment['url'];
                                            if (isset($attachment['title'])) $attachmentTitles[] = $attachment['title'];
                                        }
                                    }
                                    
                                    // Nếu không có media_type, thử lấy url trực tiếp
                                    if (isset($attachment['url']) && !in_array($attachment['url'], $attachmentUrls)) {
                                        $attachmentUrls[] = $attachment['url'];
                                        if (isset($attachment['title'])) $attachmentTitles[] = $attachment['title'];
                                    }
                                }
                            }
                        }
                        
                        // Fallback cho ảnh từ các trường khác
                        if (empty($imageUrls)) {
                            if (!empty($originalPost->full_picture)) $imageUrls[] = $originalPost->full_picture;
                            elseif (!empty($originalPost->picture)) $imageUrls[] = $originalPost->picture;
                        }
                        
                        // Fallback cho video từ các trường khác
                        if (empty($videoUrls)) {
                            if (!empty($originalPost->source)) $videoUrls[] = $originalPost->source;
                        }

                        // Lấy tất cả các chỉ số từ database
                        $impressions = (int)($originalPost->post_impressions ?? 0);
                        $impressionsUnique = (int)($originalPost->post_impressions_unique ?? 0);
                        $impressionsOrganic = (int)($originalPost->post_impressions_organic ?? 0);
                        $impressionsOrganicUnique = (int)($originalPost->post_impressions_organic_unique ?? 0);
                        $impressionsPaid = (int)($originalPost->post_impressions_paid ?? 0);
                        $impressionsPaidUnique = (int)($originalPost->post_impressions_paid_unique ?? 0);
                        $impressionsViral = (int)($originalPost->post_impressions_viral ?? 0);
                        $impressionsViralUnique = (int)($originalPost->post_impressions_viral_unique ?? 0);
                        
                        $engagedUsers = (int)($originalPost->post_engaged_users ?? 0);
                        $clicks = (int)($originalPost->post_clicks ?? 0);
                        $clicksUnique = (int)($originalPost->post_clicks_unique ?? 0);
                        $sharesCount = (int)($originalPost->post_shares ?? 0);
                        $commentsCount = (int)($originalPost->post_comments ?? 0);
                        $videoViews = (int)($originalPost->post_video_views ?? 0);
                        $videoViewsOrganic = (int)($originalPost->post_video_views_organic ?? 0);
                        $videoViewsPaid = (int)($originalPost->post_video_views_paid ?? 0);
                        $videoCompleteViews = (int)($originalPost->post_video_complete_views ?? 0);
                        
                        // Các loại reactions
                        $reactionsLike = (int)($originalPost->post_reactions_like_total ?? 0);
                        $reactionsLove = (int)($originalPost->post_reactions_love_total ?? 0);
                        $reactionsWow = (int)($originalPost->post_reactions_wow_total ?? 0);
                        $reactionsHaha = (int)($originalPost->post_reactions_haha_total ?? 0);
                        $reactionsSorry = (int)($originalPost->post_reactions_sorry_total ?? 0);
                        $reactionsAnger = (int)($originalPost->post_reactions_anger_total ?? 0);
                        $totalReactions = $reactionsLike + $reactionsLove + $reactionsWow + $reactionsHaha + $reactionsSorry + $reactionsAnger;
                        
                        // Tạo permalink_url nếu chưa có
                        $permalinkUrl = $originalPost->permalink_url ?: $originalPost->link;
                        if (empty($permalinkUrl) && !empty($originalPost->post_id)) {
                            $permalinkUrl = "https://facebook.com/{$originalPost->post_id}";
                                    }
                                @endphp

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-4 flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden">
                                @if(!empty($pageInfo->profile_picture_url))
                                    <img src="{{ $pageInfo->profile_picture_url }}" class="w-10 h-10 object-cover"/>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                    <span class="font-semibold text-gray-900">{{ $pageInfo->name ?? $selected_page->name }}</span>
                                    <span>·</span>
                                    <span>{{ \Carbon\Carbon::parse($originalPost->created_time)->diffForHumans() }}</span>
                                    <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">{{ strtoupper($originalPost->type ?? 'post') }}</span>
                                    @if($originalPost->is_hidden)
                                        <span class="px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded">Ẩn</span>
                                    @endif
                                    @if($originalPost->is_expired)
                                        <span class="px-2 py-0.5 text-xs bg-orange-100 text-orange-700 rounded">Hết hạn</span>
                                    @endif
                                </div>
                                
                                <!-- Nội dung bài viết -->
                                <div class="text-gray-900 mb-3">
                                    {!! $originalPost->message ? nl2br(e($originalPost->message)) : ($originalPost->name ? e($originalPost->name) : ($originalPost->description ? e($originalPost->description) : 'Không có nội dung')) !!}
                                </div>

                                @if(!empty($imageUrls))
                                    <div class="mb-3 grid grid-cols-1 gap-2">
                                        @foreach($imageUrls as $index => $imageUrl)
                                            @if($index < 3)
                                                <div class="relative">
                                                    @if(isset($attachmentUrls[$index]) && !empty($attachmentUrls[$index]))
                                                        <a href="{{ $attachmentUrls[$index] }}" target="_blank">
                                                            <img src="{{ $imageUrl }}" class="w-full rounded-lg border max-h-64 object-cover"/>
                                            </a>
                                        @else
                                                        <img src="{{ $imageUrl }}" class="w-full rounded-lg border max-h-64 object-cover"/>
                                                    @endif
                                                    @if(isset($attachmentTitles[$index]) && !empty($attachmentTitles[$index]))
                                                        <div class="mt-1 text-sm text-gray-600">{{ $attachmentTitles[$index] }}</div>
                                                    @endif
                                                </div>
                                        @endif
                                        @endforeach
                                        @if(count($imageUrls) > 3)
                                            <div class="text-sm text-gray-500">+{{ count($imageUrls) - 3 }} ảnh khác</div>
                                        @endif
                                    </div>
                                @endif

                                @if(!empty($videoUrls))
                                    <div class="mb-3 grid grid-cols-1 gap-2">
                                        @foreach($videoUrls as $index => $videoUrl)
                                            @if($index < 2)
                                                <div class="relative">
                                                    <video controls class="w-full rounded-lg border max-h-64 object-cover" src="{{ $videoUrl }}"></video>
                                                    @if(isset($attachmentTitles[$index]) && !empty($attachmentTitles[$index]))
                                                        <div class="mt-1 text-sm text-gray-600">{{ $attachmentTitles[$index] }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                        @if(count($videoUrls) > 2)
                                            <div class="text-sm text-gray-500">+{{ count($videoUrls) - 2 }} video khác</div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Hiển thị tất cả chỉ số từ database -->
                                <div class="bg-gray-50 rounded-lg p-3 mb-3">
                                    <div class="text-sm font-semibold text-gray-700 mb-2">Chỉ số chi tiết:</div>
                                    
                                    <!-- Impressions breakdown -->
                                    @if($impressions > 0)
                                        <div class="mb-3">
                                            <div class="text-xs font-medium text-gray-600 mb-1">📊 Reach/Impressions:</div>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                                        @if($impressions > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                <div class="font-semibold text-blue-600">{{ number_format($impressions) }}</div>
                                                        <div class="text-gray-500">Total</div>
                                                    </div>
                                                @endif
                                                @if($impressionsUnique > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                        <div class="font-semibold text-blue-700">{{ number_format($impressionsUnique) }}</div>
                                                        <div class="text-gray-500">Unique</div>
                                                    </div>
                                                @endif
                                                @if($impressionsOrganic > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                        <div class="font-semibold text-green-600">{{ number_format($impressionsOrganic) }}</div>
                                                        <div class="text-gray-500">Organic</div>
                                                    </div>
                                                @endif
                                                @if($impressionsPaid > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                        <div class="font-semibold text-yellow-600">{{ number_format($impressionsPaid) }}</div>
                                                        <div class="text-gray-500">Paid</div>
                                                    </div>
                                                @endif
                                                @if($impressionsViral > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                        <div class="font-semibold text-pink-600">{{ number_format($impressionsViral) }}</div>
                                                        <div class="text-gray-500">Viral</div>
                                                    </div>
                                                @endif
                                            </div>
                                            </div>
                                        @endif
                                        
                                    <!-- Engagements -->
                                    @if($engagedUsers > 0 || $clicks > 0)
                                        <div class="mb-3">
                                            <div class="text-xs font-medium text-gray-600 mb-1">👆 Tương tác:</div>
                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                                        @if($engagedUsers > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                <div class="font-semibold text-green-600">{{ number_format($engagedUsers) }}</div>
                                                        <div class="text-gray-500">Engaged Users</div>
                                            </div>
                                        @endif
                                        @if($clicks > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                <div class="font-semibold text-purple-600">{{ number_format($clicks) }}</div>
                                                <div class="text-gray-500">Clicks</div>
                                            </div>
                                        @endif
                                                @if($clicksUnique > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                        <div class="font-semibold text-purple-700">{{ number_format($clicksUnique) }}</div>
                                                        <div class="text-gray-500">Clicks Unique</div>
                                            </div>
                                        @endif
                                            </div>
                                            </div>
                                        @endif
                                        
                                    <!-- Reactions -->
                                    @if($totalReactions > 0)
                                        <div class="mb-3">
                                            <div class="text-xs font-medium text-gray-600 mb-1">❤️ Reactions:</div>
                                            <div class="grid grid-cols-3 md:grid-cols-6 gap-2 text-xs">
                                                @if($reactionsLike > 0)
                                                    <div class="text-center p-1 bg-blue-50 rounded">
                                                        <div class="font-semibold text-blue-600">{{ number_format($reactionsLike) }}</div>
                                                        <div class="text-gray-500">👍</div>
                                                    </div>
                                                @endif
                                                @if($reactionsLove > 0)
                                                    <div class="text-center p-1 bg-red-50 rounded">
                                                        <div class="font-semibold text-red-600">{{ number_format($reactionsLove) }}</div>
                                                        <div class="text-gray-500">❤️</div>
                                                    </div>
                                                @endif
                                        @if($reactionsHaha > 0)
                                                    <div class="text-center p-1 bg-yellow-50 rounded">
                                                <div class="font-semibold text-orange-600">{{ number_format($reactionsHaha) }}</div>
                                                        <div class="text-gray-500">😂</div>
                                                    </div>
                                                @endif
                                                @if($reactionsWow > 0)
                                                    <div class="text-center p-1 bg-purple-50 rounded">
                                                        <div class="font-semibold text-yellow-600">{{ number_format($reactionsWow) }}</div>
                                                        <div class="text-gray-500">😮</div>
                                            </div>
                                        @endif
                                        @if($reactionsSorry > 0)
                                                    <div class="text-center p-1 bg-gray-100 rounded">
                                                <div class="font-semibold text-gray-600">{{ number_format($reactionsSorry) }}</div>
                                                        <div class="text-gray-500">😢</div>
                                            </div>
                                        @endif
                                        @if($reactionsAnger > 0)
                                                    <div class="text-center p-1 bg-red-100 rounded">
                                                <div class="font-semibold text-red-700">{{ number_format($reactionsAnger) }}</div>
                                                        <div class="text-gray-500">😡</div>
                                                    </div>
                                                @endif
                                            </div>
                                            </div>
                                        @endif
                                        
                                    <!-- Comments & Shares -->
                                    @if($commentsCount > 0 || $sharesCount > 0)
                                        <div class="mb-3">
                                            <div class="text-xs font-medium text-gray-600 mb-1">💬 Bình luận & Chia sẻ:</div>
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                        @if($commentsCount > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                <div class="font-semibold text-blue-500">{{ number_format($commentsCount) }}</div>
                                                <div class="text-gray-500">💬 Comments</div>
                                            </div>
                                        @endif
                                        @if($sharesCount > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                <div class="font-semibold text-green-500">{{ number_format($sharesCount) }}</div>
                                                <div class="text-gray-500">↗️ Shares</div>
                                            </div>
                                        @endif
                                            </div>
                                            </div>
                                    @endif
                                    
                                    <!-- Video metrics -->
                                    @if($videoViews > 0)
                                        <div>
                                            <div class="text-xs font-medium text-gray-600 mb-1">🎥 Video:</div>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                                                @if($videoViews > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                        <div class="font-semibold text-purple-500">{{ number_format($videoViews) }}</div>
                                                        <div class="text-gray-500">Views</div>
                                                    </div>
                                                @endif
                                                @if($videoViewsOrganic > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                        <div class="font-semibold text-green-500">{{ number_format($videoViewsOrganic) }}</div>
                                                        <div class="text-gray-500">Organic</div>
                                                    </div>
                                                @endif
                                                @if($videoViewsPaid > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                        <div class="font-semibold text-yellow-500">{{ number_format($videoViewsPaid) }}</div>
                                                        <div class="text-gray-500">Paid</div>
                                                    </div>
                                                @endif
                                                @if($videoCompleteViews > 0)
                                                    <div class="text-center p-1 bg-white rounded">
                                                        <div class="font-semibold text-indigo-500">{{ number_format($videoCompleteViews) }}</div>
                                                        <div class="text-gray-500">Complete</div>
                                                    </div>
                                                @endif
                                            </div>
                                            </div>
                                    @endif
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-3">
                                    @if(!empty($permalinkUrl))
                                            <a href="{{ $permalinkUrl }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">
                                                Xem trên Facebook →
                                            </a>
                                        @endif
                                        @if($originalPost->insights_synced_at)
                                            <span class="text-gray-500 text-xs">
                                                Cập nhật: {{ \Carbon\Carbon::parse($originalPost->insights_synced_at)->diffForHumans() }}
                                            </span>
                                    @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ID: {{ $originalPost->post_id }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $paginatedPosts->links() }}
            </div>
        @else
            <div class="text-center py-10 bg-white rounded-lg border border-gray-200">
                <div class="text-gray-500">Không có bài viết nào phù hợp với bộ lọc.</div>
            </div>
        @endif
    @else
        <div class="text-center py-10 bg-white rounded-lg border border-gray-200">
            <div class="text-gray-500">Vui lòng chọn một page để xem bài viết.</div>
        </div>
    @endif
</div>
</x-layouts.app>

<script>
function performQuickSearch() {
    const searchValue = document.querySelector('input[name="quick_search"]').value;
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('search', searchValue);
    currentUrl.searchParams.delete('page'); // Reset pagination
    window.location.href = currentUrl.toString();
}

function filterByType(type) {
    const currentUrl = new URL(window.location);
    if (type) {
        currentUrl.searchParams.set('post_type', type);
    } else {
        currentUrl.searchParams.delete('post_type');
    }
    currentUrl.searchParams.delete('page'); // Reset pagination
    window.location.href = currentUrl.toString();
}

// Auto-submit khi thay đổi sort
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.querySelector('select[name="sort_by"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('sort_by', this.value);
            currentUrl.searchParams.delete('page'); // Reset pagination
            window.location.href = currentUrl.toString();
        });
    }

    // Enter key cho quick search
    const quickSearchInput = document.querySelector('input[name="quick_search"]');
    if (quickSearchInput) {
        quickSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performQuickSearch();
            }
        });
    }
});
</script>


