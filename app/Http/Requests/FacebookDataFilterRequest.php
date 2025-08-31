<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacebookDataFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Tạm thời bỏ qua authorization để test
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page_id' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'post_type' => 'nullable|string|in:status,photo,video,link,event,offer',
            'status' => 'nullable|string|in:active,paused,deleted',
            'search' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'post_type.in' => 'Loại bài viết không hợp lệ.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'page_id' => 'Trang Facebook',
            'date_from' => 'Từ ngày',
            'date_to' => 'Đến ngày',
            'post_type' => 'Loại bài viết',
            'status' => 'Trạng thái',
            'search' => 'Tìm kiếm',
        ];
    }
} 