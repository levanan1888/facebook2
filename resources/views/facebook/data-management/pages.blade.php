<x-layouts.app :title="'Quản lý Page (Organic Posts)'"><div class="p-6">
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
                <option value="">Loại bài</option>
                <option value="status" {{ ($filters['post_type'] ?? '')=='status'?'selected':'' }}>Trạng thái</option>
                <option value="photo" {{ ($filters['post_type'] ?? '')=='photo'?'selected':'' }}>Ảnh</option>
                <option value="video" {{ ($filters['post_type'] ?? '')=='video'?'selected':'' }}>Video</option>
                <option value="link" {{ ($filters['post_type'] ?? '')=='link'?'selected':'' }}>Liên kết</option>
            </select>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tìm nội dung..." class="w-56 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"/>
            <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">Lọc</button>
        </div>
    </form>

    @if($selected_page)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex items-center gap-4">
                @if(!empty($selected_page->profile_picture_url))
                    <img src="{{ $selected_page->profile_picture_url }}" alt="avatar" class="w-12 h-12 rounded-full object-cover"/>
                @endif
                <div>
                    <div class="text-lg font-semibold text-gray-900">{{ $selected_page->name }}</div>
                    <div class="text-sm text-gray-600">{{ $selected_page->category ?? 'Unknown' }} • {{ number_format($selected_page->fan_count ?? 0) }} fan</div>
                </div>
                <a href="https://facebook.com/{{ $selected_page->id }}" target="_blank" class="ml-auto inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">Xem Page</a>
            </div>
        </div>

        @if(($posts ?? collect())->count())
            <div class="space-y-4">
                @foreach($posts as $post)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 cursor-pointer" @if(!empty($post->permalink_url)) onclick="window.open('{{ $post->permalink_url }}','_blank')" @endif>
                        <div class="p-4 flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden">
                                @if(!empty($selected_page->profile_picture_url))
                                    <img src="{{ $selected_page->profile_picture_url }}" class="w-10 h-10 object-cover"/>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <span class="font-semibold text-gray-900">{{ $selected_page->name }}</span>
                                    <span>·</span>
                                    <span>{{ \Carbon\Carbon::parse($post->created_time)->diffForHumans() }}</span>
                                    <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">{{ strtoupper($post->type ?? 'post') }}</span>
                                </div>
                                <div class="mt-2 text-gray-900">{!! $post->message ? nl2br(e($post->message)) : 'Không có nội dung' !!}</div>

                                @php
                                    $imageUrl = $post->full_picture ?? $post->picture ?? $post->image_url ?? null;
                                    $videoUrl = $post->video_url ?? $post->source ?? null;
                                @endphp

                                @if(!empty($imageUrl))
                                    <div class="mt-3">
                                        @if(!empty($post->permalink_url))
                                            <a href="{{ $post->permalink_url }}" target="_blank">
                                                <img src="{{ $imageUrl }}" class="w-full rounded-lg border"/>
                                            </a>
                                        @else
                                            <img src="{{ $imageUrl }}" class="w-full rounded-lg border"/>
                                        @endif
                                    </div>
                                @endif

                                @if(!empty($videoUrl))
                                    <div class="mt-3">
                                        <video controls class="w-full rounded-lg border" src="{{ $videoUrl }}"></video>
                                    </div>
                                @endif

                                @php
                                    // shares_data may be JSON string/object/array in the form {"count": 2}
                                    $sharesCount = 0;
                                    if (isset($post->shares_data) && $post->shares_data !== null && $post->shares_data !== '') {
                                        $raw = $post->shares_data;
                                        if (is_string($raw)) {
                                            $decoded = json_decode($raw);
                                            if (json_last_error() === JSON_ERROR_NONE) {
                                                if (is_object($decoded)) { $sharesCount = (int)($decoded->count ?? 0); }
                                                elseif (is_array($decoded)) { $sharesCount = (int)($decoded['count'] ?? 0); }
                                            }
                                        } elseif (is_object($raw)) {
                                            $sharesCount = (int)($raw->count ?? 0);
                                        } elseif (is_array($raw)) {
                                            $sharesCount = (int)($raw['count'] ?? 0);
                                        } elseif (is_numeric($raw)) {
                                            $sharesCount = (int)$raw;
                                        }
                                    } elseif (isset($post->shares_count)) {
                                        $sharesCount = (int)$post->shares_count;
                                    }

                                    // comments_data similar structure
                                    $commentsCount = 0;
                                    if (isset($post->comments_data) && $post->comments_data !== null && $post->comments_data !== '') {
                                        $raw = $post->comments_data;
                                        if (is_string($raw)) {
                                            $decoded = json_decode($raw);
                                            if (json_last_error() === JSON_ERROR_NONE) {
                                                if (is_object($decoded)) { $commentsCount = (int)($decoded->count ?? 0); }
                                                elseif (is_array($decoded)) { $commentsCount = (int)($decoded['count'] ?? 0); }
                                            }
                                        } elseif (is_object($raw)) {
                                            $commentsCount = (int)($raw->count ?? 0);
                                        } elseif (is_array($raw)) {
                                            $commentsCount = (int)($raw['count'] ?? 0);
                                        } elseif (is_numeric($raw)) {
                                            $commentsCount = (int)$raw;
                                        }
                                    } elseif (isset($post->comments_count)) {
                                        $commentsCount = (int)$post->comments_count;
                                    }

                                    // reach total: try several common keys and fall back to 0
                                    $reach = 0;
                                    $reachCandidates = [
                                        'post_impressions', 'post_engaged_users', 'reach', 'reach_total',
                                        'insights_reach', 'post_reach', 'impressions_unique',
                                        'post_impressions_unique', 'unique_impressions',
                                    ];
                                    foreach ($reachCandidates as $key) {
                                        if (isset($post->{$key}) && is_numeric($post->{$key})) { $reach = (int)$post->{$key}; break; }
                                    }
                                @endphp

                                <div class="mt-3 flex items-center gap-4 text-sm text-gray-600">
                                    <div>👀 {{ number_format($reach) }}</div>
                                    <div>👍 {{ number_format($post->reactions_count ?? 0) }}</div>
                                    <div>💬 {{ number_format($commentsCount) }}</div>
                                    <div>↗️ {{ number_format($sharesCount) }}</div>
                                    @if(($post->video_views ?? 0) > 0)
                                        <div>▶️ {{ number_format($post->video_views) }} views</div>
                                    @endif
                                </div>
                                <div class="mt-2 flex items-center gap-3 text-sm">
                                    @if(!empty($post->permalink_url))
                                        <a href="{{ $post->permalink_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">Xem trên Facebook →</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10 bg-white rounded-lg border border-gray-200">
                <div class="text-gray-500">Không có bài viết nào phù hợp.</div>
            </div>
        @endif
    @else
        <div class="text-center py-10 bg-white rounded-lg border border-gray-200">
            <div class="text-gray-500">Vui lòng chọn một page để xem bài viết.</div>
        </div>
    @endif
</div></x-layouts.app>


