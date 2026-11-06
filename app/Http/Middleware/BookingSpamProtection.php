<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class BookingSpamProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Bot Honeypot Check: legitimate users leave this invisible field empty
        if ($request->filled('website_verification_code') || $request->filled('website_url')) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu không hợp lệ (Phát hiện truy cập bất thường)',
            ], 422);
        }

        // 2. Velocity Check: guard against forged future timestamps
        if ($request->filled('_form_load_time')) {
            $loadTime = (int) $request->input('_form_load_time');
            $currentTime = time();
            if ($loadTime > $currentTime) {
                return back()
                    ->withInput()
                    ->withErrors(['honeypot' => 'Thao tác quá nhanh hoặc thời gian biểu mẫu không hợp lệ. Vui lòng thử lại sau giây lát.']);
            }
        }

        // 3. Multi-tier IP Rate Limiting (configurable via SystemSetting rate_limit_booking)
        $ip = $request->ip();
        $rateKey = 'booking_throttle:' . $ip;
        $maxAttempts = max(1, (int) \App\Models\SystemSetting::get('rate_limit_booking', 15));

        if (RateLimiter::tooManyAttempts($rateKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return back()
                ->withInput()
                ->withErrors([
                    'rate_limit' => "Bạn đã gửi quá nhiều yêu cầu đặt lịch. Vui lòng chờ {$seconds} giây trước khi thử lại.",
                ]);
        }

        RateLimiter::hit($rateKey, 300); // 5 minutes

        return $next($request);
    }
}
