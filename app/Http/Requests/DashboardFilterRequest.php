<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('dashboard.view');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'from' => 'sometimes|date|before_or_equal:today',
            'to' => 'sometimes|date|after_or_equal:from',
            'account_id' => 'sometimes|string',
            'campaign_id' => 'sometimes|string',
            'adset_id' => 'sometimes|string',
            'page_id' => 'sometimes|string',
            'post_type' => 'sometimes|string|in:photo,video,carousel_album,link',
            'status' => 'sometimes|string|in:ACTIVE,PAUSED,DELETED',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'from.before_or_equal' => 'Ngày bắt đầu phải trước hoặc bằng hôm nay',
            'to.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
            'post_type.in' => 'Loại post không hợp lệ',
            'status.in' => 'Trạng thái không hợp lệ',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'from' => 'ngày bắt đầu',
            'to' => 'ngày kết thúc',
            'account_id' => 'ID tài khoản quảng cáo',
            'campaign_id' => 'ID chiến dịch',
            'adset_id' => 'ID nhóm quảng cáo',
            'page_id' => 'ID trang',
            'post_type' => 'loại post',
            'status' => 'trạng thái',
        ];
    }
}
