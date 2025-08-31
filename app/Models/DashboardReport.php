<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DashboardReport extends Model
{
    protected $fillable = [
        'report_type',
        'data',
        'last_updated'
    ];

    protected $casts = [
        'data' => 'array',
        'last_updated' => 'datetime'
    ];

    /**
     * Lấy dữ liệu báo cáo theo loại
     */
    public static function getReport(string $type): ?array
    {
        $report = static::where('report_type', $type)
            ->where('last_updated', '>=', now()->subMinutes(30)) // Cache 30 phút
            ->first();
            
        return $report ? $report->data : null;
    }

    /**
     * Cập nhật hoặc tạo mới báo cáo
     */
    public static function updateReport(string $type, array $data): void
    {
        static::updateOrCreate(
            ['report_type' => $type],
            [
                'data' => $data,
                'last_updated' => now()
            ]
        );
    }

    /**
     * Xóa báo cáo cũ
     */
    public static function clearOldReports(): void
    {
        static::where('last_updated', '<', now()->subDays(1))->delete();
    }
}
