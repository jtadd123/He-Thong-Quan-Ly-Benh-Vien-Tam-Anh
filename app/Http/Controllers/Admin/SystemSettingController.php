<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\SecurityAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    /**
     * Display hospital system configuration control center.
     */
    public function index(): View
    {
        // Ensure standard settings are initialized if not present
        $defaults = [
            'hospital_name' => ['value' => 'Bệnh Viện Đa Khoa Tâm Anh', 'group' => 'general', 'description' => 'Tên chính thức hiển thị của bệnh viện'],
            'hospital_hotline' => ['value' => '1800 6858', 'group' => 'general', 'description' => 'Số điện thoại tổng đài cấp cứu và CSKH'],
            'hospital_address' => ['value' => '108 Hoàng Như Tiếp, P. Bồ Đề, Q. Long Biên, Hà Nội', 'group' => 'general', 'description' => 'Địa chỉ trụ sở chính'],
            'booking_maintenance_mode' => ['value' => '0', 'group' => 'booking', 'description' => 'Bật chế độ bảo trì cổng đặt lịch trực tuyến'],
            'booking_maintenance_message' => ['value' => 'Cổng đặt lịch khám trực tuyến đang bảo trì để nâng cấp hạ tầng. Quý khách vui lòng gọi hotline 1800 6858 để được hỗ trợ.', 'group' => 'booking', 'description' => 'Thông điệp thông báo bảo trì'],
            'enable_smart_waitlist' => ['value' => '1', 'group' => 'booking', 'description' => 'Bật tính năng hàng đợi chờ tự động khi đầy ca'],
            'max_booking_days_ahead' => ['value' => '30', 'group' => 'booking', 'description' => 'Số ngày tối đa cho phép đặt lịch trước'],
            'rate_limit_booking' => ['value' => '15', 'group' => 'security', 'description' => 'Số lượt đặt lịch tối đa mỗi IP trong 5 phút'],
            'audit_log_retention_days' => ['value' => '90', 'group' => 'security', 'description' => 'Thời gian lưu trữ nhật ký bảo mật (ngày)'],
        ];

        foreach ($defaults as $key => $meta) {
            if (!SystemSetting::where('key', $key)->exists()) {
                SystemSetting::set($key, $meta['value'], $meta['group'], $meta['description']);
            }
        }

        $allSettings = SystemSetting::all();
        $grouped = $allSettings->groupBy('group');

        return view('admin.settings.index', [
            'groupedSettings' => $grouped,
        ]);
    }

    /**
     * Update settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $settings = $request->input('settings', []);

        // Only default omitted checkboxes to '0' when the full settings form is submitted
        if (array_key_exists('max_booking_days_ahead', $settings)) {
            if (!isset($settings['booking_maintenance_mode'])) {
                $settings['booking_maintenance_mode'] = '0';
            }
            if (!isset($settings['enable_smart_waitlist'])) {
                $settings['enable_smart_waitlist'] = '0';
            }
        }

        foreach ($settings as $key => $value) {
            $existing = SystemSetting::where('key', $key)->first();
            $group = $existing ? $existing->group : 'general';
            $desc = $existing ? $existing->description : null;

            SystemSetting::set($key, $value, $group, $desc);
        }

        SecurityAuditService::log(
            'system_settings_updated',
            "Quản trị viên " . Auth::user()->email . " đã cập nhật cấu hình hệ thống bệnh viện",
            Auth::id(),
            'info'
        );

        return back()->with('success', 'Cập nhật cấu hình hệ thống bệnh viện thành công!');
    }
}
