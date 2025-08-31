<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacebookSyncRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('facebook.sync');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'sync_type' => 'sometimes|string|in:full,incremental,insights_only',
            'account_id' => 'sometimes|string',
            'campaign_id' => 'sometimes|string',
            'date_from' => 'sometimes|date|before_or_equal:today',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'force_refresh' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sync_type.in' => 'Loại đồng bộ không hợp lệ. Chỉ chấp nhận: full, incremental, insights_only',
            'date_from.before_or_equal' => 'Ngày bắt đầu phải trước hoặc bằng hôm nay',
            'date_to.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'sync_type' => 'loại đồng bộ',
            'account_id' => 'ID tài khoản quảng cáo',
            'campaign_id' => 'ID chiến dịch',
            'date_from' => 'ngày bắt đầu',
            'date_to' => 'ngày kết thúc',
            'force_refresh' => 'làm mới cưỡng chế',
        ];
    }
}
