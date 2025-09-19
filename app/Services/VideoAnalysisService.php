<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class VideoAnalysisService
{
    private $geminiApiKey;
    private $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Phân tích video Facebook bằng Gemini API
     */
    public function analyzeVideo(array $data): array
    {
        try {
            // Bước 1: Chuẩn bị video từ Facebook
            Log::info('Bước 1: Đang chuẩn bị video từ Facebook...');
            $videoUrl = $this->prepareVideoFromFacebook($data);
            
            if (!$videoUrl) {
                return [
                    'success' => false,
                    'message' => 'Không thể lấy video từ Facebook'
                ];
            }
            Log::info('Bước 1 hoàn thành: Đã lấy được video URL');

            // Bước 2: Gọi Gemini API để phân tích
            Log::info('Bước 2: Đang gọi Gemini API để phân tích...');
            $analysis = $this->callGeminiApi($videoUrl, $data);
            Log::info('Bước 2 hoàn thành: Đã nhận được kết quả phân tích từ Gemini');

            return [
                'success' => true,
                'analysis' => $analysis,
                'video_url' => $videoUrl
            ];

        } catch (Exception $e) {
            Log::error('Video Analysis Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi phân tích video: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bước 1: Chuẩn bị video từ Facebook
     */
    private function prepareVideoFromFacebook(array $data): ?string
    {
        $videoUrl = $data['video_url'] ?? null;
        $postData = $data['post_data'] ?? [];

        // Nếu có video URL trực tiếp
        if ($videoUrl && $this->isValidVideoUrl($videoUrl)) {
            return $videoUrl;
        }

        // Thử lấy từ post data
        $videoUrls = $postData['video_urls'] ?? [];
        if (!empty($videoUrls)) {
            foreach ($videoUrls as $url) {
                if ($this->isValidVideoUrl($url)) {
                    return $url;
                }
            }
        }

        // Thử lấy từ primary video URL
        $primaryUrl = $postData['primary_video_url'] ?? null;
        if ($primaryUrl && $this->isValidVideoUrl($primaryUrl)) {
            return $primaryUrl;
        }

        return null;
    }

    /**
     * Kiểm tra URL video có hợp lệ không
     */
    private function isValidVideoUrl(string $url): bool
    {
        // Kiểm tra URL có phải video Facebook không
        if (strpos($url, 'facebook.com') === false && 
            strpos($url, 'fbcdn.net') === false &&
            strpos($url, 'video') === false) {
            return false;
        }

        // Kiểm tra định dạng video
        $videoExtensions = ['mp4', 'webm', 'mov', 'avi', 'flv', 'mpg', 'wmv', '3gpp'];
        $urlLower = strtolower($url);
        
        foreach ($videoExtensions as $ext) {
            if (strpos($urlLower, '.' . $ext) !== false) {
                return true;
            }
        }

        // Nếu là URL Facebook post, cũng có thể chứa video
        if (strpos($url, 'facebook.com') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Bước 2: Gọi Gemini API để phân tích
     */
    private function callGeminiApi(string $videoUrl, array $data): string
    {
        $systemPrompt = $this->getSystemPrompt();
        $postData = $data['post_data'] ?? [];
        $videoMetrics = []; // Không gửi và không dùng metrics theo yêu cầu

        // Tạo prompt với thông tin bổ sung
        $enhancedPrompt = $this->buildEnhancedPrompt($systemPrompt, $postData, $videoMetrics);

        // Tải video về storage trước
        Log::info('Bước 2.1: Đang tải video về storage...');
        $localVideoPath = $this->downloadVideoToStorage($videoUrl);
        if (!$localVideoPath) {
            throw new Exception('Không thể tải video về storage');
        }
        Log::info('Bước 2.1 hoàn thành: Video đã được tải về storage');

        // Thử inline data trước (cho file <20MB)
        $fileSize = Storage::disk('local')->size($localVideoPath);
        Log::info('Video file size: ' . $fileSize . ' bytes');
        
        if ($fileSize < 20 * 1024 * 1024) { // < 20MB
            // Sử dụng inline data
            Log::info('Bước 2.2: Sử dụng inline data cho video nhỏ...');
            $videoData = base64_encode(file_get_contents(Storage::disk('local')->path($localVideoPath)));
            $mimeType = $this->getMimeType($videoUrl);
            
            // Xóa file local
            Storage::disk('local')->delete($localVideoPath);
            
            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => $enhancedPrompt
                            ],
                            [
                                'inlineData' => [
                                    'mimeType' => $mimeType,
                                    'data' => $videoData
                                ]
                            ]
                        ]
                    ]
                ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => 4096,
                'responseMimeType' => 'text/plain'
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];
        } else {
            // Sử dụng File API cho file lớn
            Log::info('Bước 2.2: Đang upload video lên Gemini File API...');
            $geminiFileName = $this->uploadVideoToGemini($localVideoPath);
            if (!$geminiFileName) {
                throw new Exception('Không thể upload video lên Gemini');
            }
            Log::info('Bước 2.2 hoàn thành: Video đã được upload lên Gemini');

            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => $enhancedPrompt
                            ],
                            [
                                'fileData' => [
                                    'mimeType' => $this->getMimeType($videoUrl),
                                    'fileUri' => $geminiFileName
                                ]
                            ]
                        ]
                    ]
                ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => 4096,
                'responseMimeType' => 'text/plain'
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];
        }

        Log::info('Bước 2.3: Đang gửi request đến Gemini API...', [
            'url' => $this->geminiApiUrl,
            'payload_size' => strlen(json_encode($payload)),
            'method' => $fileSize < 20 * 1024 * 1024 ? 'inline_data' : 'file_api',
            'payload' => json_encode($payload, JSON_PRETTY_PRINT)
        ]);

        $response = Http::timeout(3600)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->geminiApiUrl . '?key=' . $this->geminiApiKey, $payload);
        
        Log::info('Bước 2.3 hoàn thành: Đã nhận được response từ Gemini API', [
            'status_code' => $response->status(),
            'response_body' => $response->body()
        ]);

        if ($response->successful()) {
            $result = $response->json();
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return $result['candidates'][0]['content']['parts'][0]['text'];
            }
        }

        throw new Exception('Gemini API call failed: ' . $response->body());
    }

    /**
     * System Prompt chuyên biệt cho phân tích video y khoa và quảng cáo
     */
    private function getSystemPrompt(): string
    {
        return "Bạn là chuyên gia phân tích QUẢNG CÁO y tế. Viết báo cáo NGẮN GỌN dạng văn xuôi tiếng Việt. TUYỆT ĐỐI KHÔNG sử dụng bất kỳ ký tự/định dạng markdown hoặc ký tự nhấn mạnh nào, ví dụ: **, __, ##, ###, *, _, `, >. Không dùng JSON. Ưu tiên đánh giá góc nhìn quảng cáo (thông điệp, CTA, ưu đãi, target, tính thuyết phục, tính tuân thủ). Kết cấu bắt buộc:

1) Tóm tắt nhanh: 2-3 câu nêu nội dung video và text đi kèm.
2) Đánh giá quảng cáo: nêu rõ thông điệp, CTA, ưu đãi, mức độ thuyết phục, phù hợp đối tượng, rủi ro gây hiểu nhầm. Chỉ ra điểm mạnh và điểm yếu cụ thể trong cách kể chuyện, hình ảnh/âm thanh, nhịp dựng.
3) Tuân thủ – rủi ro: lưu ý các tuyên bố tuyệt đối, khẳng định hiệu quả điều trị, thiếu căn cứ y khoa, thiếu cảnh báo bắt buộc.
4) Khuyến nghị thực thi: 4-6 gợi ý ngắn, hành động cụ thể để cải thiện tỉ lệ chuyển đổi và tuân thủ.

Thêm mục Nhận xét chung: 1-2 câu kết luận tổng thể và xếp hạng mức độ hiệu quả quảng cáo theo ba mức: Kém / Tốt / Xuất sắc (chỉ chọn một).

Viết liền mạch, không dùng JSON, không lặp lại yêu cầu. Nếu nội dung text của bài viết có sẵn, hãy dùng nó để đối chiếu tính nhất quán với video.";
    }

    /**
     * Xây dựng prompt nâng cao với thông tin bổ sung
     */
    private function buildEnhancedPrompt(string $systemPrompt, array $postData, array $videoMetrics): string
    {
        $enhancedPrompt = $systemPrompt . "\n\n";

        // Thêm thông tin về post
        if (!empty($postData['message'])) {
            $enhancedPrompt .= "THÔNG TIN BÀI VIẾT KÈM THEO:\n";
            $enhancedPrompt .= "Nội dung text: " . $postData['message'] . "\n";
            $enhancedPrompt .= "Loại bài viết: " . ($postData['type'] ?? 'N/A') . "\n";
            $enhancedPrompt .= "Thời gian tạo: " . ($postData['created_time'] ?? 'N/A') . "\n\n";
            $enhancedPrompt .= "LƯU Ý: Hãy phân tích cả nội dung text của bài viết và video để đưa ra đánh giá toàn diện.\n";
            $enhancedPrompt .= "So sánh thông điệp trong text với thông điệp trong video để đảm bảo tính nhất quán.\n\n";
        }
        
        Log::info('Video metrics sent to Gemini', [
            'views' => $videoMetrics['views'] ?? null,
            'plays' => $videoMetrics['plays'] ?? null,
            'avg_time' => ($videoMetrics['avg_time'] ?? null) ?: ($videoMetrics['video_avg_time_watched'] ?? null),
            'video_avg_time_watched' => $videoMetrics['video_avg_time_watched'] ?? null
        ]);

        // Bỏ toàn bộ metrics ra khỏi prompt theo yêu cầu

        $enhancedPrompt .= "Hãy phân tích video này dựa trên các tiêu chí đã nêu và trả về kết quả JSON.";

        return $enhancedPrompt;
    }

    /**
     * Xác định MIME type của video
     */
    private function getMimeType(string $videoUrl): string
    {
        $urlLower = strtolower($videoUrl);
        
        if (strpos($urlLower, '.mp4') !== false) {
            return 'video/mp4';
        } elseif (strpos($urlLower, '.webm') !== false) {
            return 'video/webm';
        } elseif (strpos($urlLower, '.mov') !== false) {
            return 'video/quicktime';
        } elseif (strpos($urlLower, '.avi') !== false) {
            return 'video/x-msvideo';
        } elseif (strpos($urlLower, '.flv') !== false) {
            return 'video/x-flv';
        } elseif (strpos($urlLower, '.mpg') !== false || strpos($urlLower, '.mpeg') !== false) {
            return 'video/mpeg';
        } elseif (strpos($urlLower, '.wmv') !== false) {
            return 'video/x-ms-wmv';
        } elseif (strpos($urlLower, '.3gpp') !== false) {
            return 'video/3gpp';
        }
        
        // Mặc định là mp4
        return 'video/mp4';
    }

    /**
     * Đợi file được xử lý xong trên Gemini
     */
    private function waitForFileProcessing(string $fileName): void
    {
        $maxAttempts = 300; // 300 attempts = 300 seconds (5 phút)
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $checkUrl = 'https://generativelanguage.googleapis.com/v1beta/files/' . $fileName . '?key=' . $this->geminiApiKey;
            
            $response = Http::get($checkUrl);
            
            if ($response->successful()) {
                $result = $response->json();
                $state = $result['file']['state'] ?? 'UNKNOWN';
                
                Log::info('File processing state: ' . $state . ' (attempt ' . ($attempt + 1) . ')');
                
                if ($state === 'ACTIVE') {
                    Log::info('File processing completed successfully');
                    return;
                } elseif ($state === 'FAILED') {
                    Log::error('File processing failed');
                    throw new Exception('File processing failed on Gemini');
                }
            } else {
                Log::warning('Failed to check file status: ' . $response->body());
            }
            
            $attempt++;
            sleep(1); // Wait 1 second before next check
        }
        
        Log::warning('File processing timeout after ' . $maxAttempts . ' seconds, but continuing with API call');
        // Không throw exception, tiếp tục thử gọi API
    }

    /**
     * Kiểm tra lỗi trong video (dựa trên metrics)
     */
    public function checkVideoErrors(array $videoMetrics): array
    {
        // Theo yêu cầu: không sử dụng metrics để sinh cảnh báo
        return [];
    }

    /**
     * Tải video từ URL về storage local
     */
    private function downloadVideoToStorage(string $videoUrl): ?string
    {
        try {
            // Tạo tên file unique
            $filename = 'video_analysis_' . time() . '_' . uniqid() . '.mp4';
            $localPath = 'video_analysis/' . $filename;
            
            // Tải video từ URL
            $response = Http::timeout(600)->get($videoUrl);
            
            if (!$response->successful()) {
                Log::error('Failed to download video from URL: ' . $videoUrl);
                return null;
            }
            
            // Lưu video vào storage
            Storage::disk('local')->put($localPath, $response->body());
            
            // Kiểm tra file size (Gemini có giới hạn)
            $fileSize = Storage::disk('local')->size($localPath);
            $maxSize = 100 * 1024 * 1024; // 100MB
            
            if ($fileSize > $maxSize) {
                Log::warning('Video file too large: ' . $fileSize . ' bytes');
                Storage::disk('local')->delete($localPath);
                return null;
            }
            
            Log::info('Video downloaded successfully: ' . $localPath . ' (' . $fileSize . ' bytes)');
            return $localPath;
            
        } catch (Exception $e) {
            Log::error('Error downloading video: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload video lên Gemini và lấy file URI
     */
    private function uploadVideoToGemini(string $localPath): ?string
    {
        try {
            $filePath = Storage::disk('local')->path($localPath);
            $mimeType = mime_content_type($filePath);
            $fileName = basename($filePath);
            
            // Upload file lên Gemini File API (theo tài liệu chính thức)
            $uploadUrl = 'https://generativelanguage.googleapis.com/upload/v1beta/files?key=' . $this->geminiApiKey;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $uploadUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600); // 10 phút timeout
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'file' => new \CURLFile($filePath, $mimeType, $fileName)
            ]);
            
            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                Log::error('Failed to upload video to Gemini: ' . $responseBody);
                return null;
            }
            
            $result = json_decode($responseBody, true);
            Log::info('Gemini File API Response: ' . json_encode($result, JSON_PRETTY_PRINT));
            
            // Lấy file name từ response
            $fileName = $result['file']['name'] ?? null;
            
            if (!$fileName) {
                Log::error('No file name returned from Gemini File API. Response: ' . $responseBody);
                return null;
            }
            
            Log::info('Video uploaded to Gemini File API successfully: ' . $fileName);
            
            // Đợi file được xử lý xong
            $this->waitForFileProcessing($fileName);
            
            // Xóa file local sau khi upload thành công
            Storage::disk('local')->delete($localPath);
            
            return $fileName; // Return file name without 'files/' prefix
            
        } catch (Exception $e) {
            Log::error('Error uploading video to Gemini: ' . $e->getMessage());
            return null;
        }
    }
}