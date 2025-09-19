<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VideoAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FacebookAnalysisController extends Controller
{
    private $videoAnalysisService;

    public function __construct(VideoAnalysisService $videoAnalysisService)
    {
        $this->videoAnalysisService = $videoAnalysisService;
    }

    /**
     * Phân tích video Facebook
     */
    public function analyzeVideo(Request $request): JsonResponse
    {
        // Tăng execution time lên 60 phút
        set_time_limit(3600);
        ini_set('max_execution_time', 3600);
        
        Log::info('Video Analysis API called', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|string',
                'page_id' => 'required|string',
                'video_url' => 'nullable|string|url',
                'post_data' => 'nullable|array',
                'post_data.message' => 'nullable|string',
                'post_data.type' => 'nullable|string',
                'post_data.created_time' => 'nullable|string',
                'post_data.video_urls' => 'nullable|array',
                'post_data.primary_video_url' => 'nullable|string',
                'post_data.video_metrics' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu đầu vào không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }

            $data = $request->all();

            // Kiểm tra Gemini API key
            if (!config('services.gemini.api_key')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API key chưa được cấu hình'
                ], 500);
            }

            // Thực hiện phân tích video
            $result = $this->videoAnalysisService->analyzeVideo($data);

            if ($result['success']) {
                // Kiểm tra lỗi video nếu có metrics
                $videoMetrics = $data['post_data']['video_metrics'] ?? [];
                $videoErrors = [];
                
                if (!empty($videoMetrics)) {
                    $videoErrors = $this->videoAnalysisService->checkVideoErrors($videoMetrics);
                }

                // Parse JSON response từ Gemini
                $analysis = $result['analysis'];
                $parsedAnalysis = $this->parseAnalysisResponse($analysis);

                return response()->json([
                    'success' => true,
                    'analysis' => $parsedAnalysis,
                    'video_url' => $result['video_url'],
                    'video_errors' => $videoErrors,
                    'raw_analysis' => $analysis // Để debug
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Video Analysis API Error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse response từ Gemini API
     */
    private function parseAnalysisResponse(string $analysis): array
    {
        try {
            // Thử parse JSON trực tiếp
            $decoded = json_decode($analysis, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Nếu không phải JSON thuần, tìm JSON trong text
            if (preg_match('/\{.*\}/s', $analysis, $matches)) {
                $jsonString = $matches[0];
                $decoded = json_decode($jsonString, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }

            // Nếu không parse được JSON, trả về text gốc
            return [
                'raw_response' => $analysis,
                'parse_error' => 'Không thể parse JSON từ response'
            ];

        } catch (\Exception $e) {
            return [
                'raw_response' => $analysis,
                'parse_error' => 'Lỗi parse: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy thông tin video từ Facebook post
     */
    public function getVideoInfo(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|string',
                'page_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu đầu vào không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }

            $postId = $request->input('post_id');
            $pageId = $request->input('page_id');

            // Tìm post trong database
            $post = \App\Models\FacebookPost::where('id', $postId)
                ->where('page_id', $pageId)
                ->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bài viết'
                ], 404);
            }

            // Lấy thông tin video
            $videoInfo = $this->extractVideoInfo($post);

            return response()->json([
                'success' => true,
                'video_info' => $videoInfo,
                'post_info' => [
                    'id' => $post->id,
                    'message' => $post->message,
                    'type' => $post->type,
                    'created_time' => $post->created_time
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get Video Info Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trích xuất thông tin video từ post
     */
    private function extractVideoInfo($post): array
    {
        $videoUrls = [];
        $primaryVideoUrl = null;

        // 1) Từ attachments JSON
        if (!empty($post->attachments)) {
            $raw = is_string($post->attachments) ? json_decode($post->attachments, true) : $post->attachments;
            if (is_array($raw) && isset($raw['data']) && is_array($raw['data'])) {
                foreach ($raw['data'] as $att) {
                    if (!empty($att['media_type']) && $att['media_type'] === 'video' && !empty($att['media']['source'])) {
                        $videoUrls[] = $att['media']['source'];
                        if (!$primaryVideoUrl) {
                            $primaryVideoUrl = $att['media']['source'];
                        }
                    }
                }
            }
        }

        // 2) Từ attachments_source array
        if (!empty($post->attachments_source)) {
            $vids = is_string($post->attachments_source) ? json_decode($post->attachments_source, true) : $post->attachments_source;
            if (is_array($vids)) {
                foreach ($vids as $url) {
                    if ($url && !in_array($url, $videoUrls)) {
                        $videoUrls[] = $url;
                        if (!$primaryVideoUrl) {
                            $primaryVideoUrl = $url;
                        }
                    }
                }
            }
        }

        // 3) Từ single fields
        if (!empty($post->attachment_source) && !in_array($post->attachment_source, $videoUrls)) {
            $videoUrls[] = $post->attachment_source;
            if (!$primaryVideoUrl) {
                $primaryVideoUrl = $post->attachment_source;
            }
        }

        if (!empty($post->source) && !in_array($post->source, $videoUrls)) {
            $videoUrls[] = $post->source;
            if (!$primaryVideoUrl) {
                $primaryVideoUrl = $post->source;
            }
        }

        // 4) Fallback to permalink
        if (empty($videoUrls) && !empty($post->permalink_url)) {
            $videoUrls[] = $post->permalink_url;
            $primaryVideoUrl = $post->permalink_url;
        }

        return [
            'video_urls' => array_values(array_unique(array_filter($videoUrls))),
            'primary_video_url' => $primaryVideoUrl,
            'has_video' => !empty($videoUrls)
        ];
    }

    /**
     * Kiểm tra trạng thái Gemini API
     */
    public function checkGeminiStatus(): JsonResponse
    {
        try {
            $apiKey = config('services.gemini.api_key');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API key chưa được cấu hình',
                    'status' => 'not_configured'
                ]);
            }

            // Test API call đơn giản
            $testUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
            
            $response = \Illuminate\Support\Facades\Http::post($testUrl, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Hello, are you working?']
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gemini API hoạt động bình thường',
                    'status' => 'active'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API không phản hồi: ' . $response->status(),
                    'status' => 'error'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kiểm tra Gemini API: ' . $e->getMessage(),
                'status' => 'error'
            ]);
        }
    }
}