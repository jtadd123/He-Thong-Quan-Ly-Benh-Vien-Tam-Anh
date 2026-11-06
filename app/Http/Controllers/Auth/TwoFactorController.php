<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityAuditService;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(protected TotpService $totpService)
    {
    }

    /**
     * Show 2FA setup and configuration page.
     */
    public function showSetup(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->two_factor_secret) {
            $secret = $this->totpService->generateSecret();
            $recoveryCodes = $this->totpService->generateRecoveryCodes();
            
            $user->two_factor_secret = $secret;
            $user->two_factor_recovery_codes = json_encode($recoveryCodes);
            $user->save();
        } else {
            $secret = $user->two_factor_secret;
            $recoveryCodes = json_decode($user->two_factor_recovery_codes ?: '[]', true);
        }

        $qrUrl = $this->totpService->getQrCodeUrl($user->email, $secret);
        $currentOtp = $this->totpService->getCurrentCode($secret);

        return view('auth.two_factor_setup', [
            'user' => $user,
            'secret' => $secret,
            'qrUrl' => $qrUrl,
            'currentOtp' => $currentOtp,
            'recoveryCodes' => $recoveryCodes,
            'isEnabled' => (bool) $user->two_factor_confirmed_at,
        ]);
    }

    /**
     * Confirm and activate 2FA with 6-digit TOTP code.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Vui lòng nhập mã xác thực 6 chữ số.',
            'code.size' => 'Mã xác thực gồm chính xác 6 chữ số.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!$this->totpService->verifyCode($user->two_factor_secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Mã xác thực không chính xác hoặc đã hết hạn. Vui lòng kiểm tra lại đồng hồ thiết bị.']);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        SecurityAuditService::log(
            'two_factor_enabled',
            "Người dùng {$user->email} đã kích hoạt thành công xác thực 2 bước (TOTP)",
            $user->id,
            'info'
        );

        return back()->with('success', 'Xác thực 2 bước (2FA) đã được kích hoạt thành công! Tài khoản của bạn hiện được bảo vệ an toàn.');
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu hiện tại để xác nhận hủy 2FA.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['password' => 'Mật khẩu hiện tại không chính xác.']);
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        SecurityAuditService::log(
            'two_factor_disabled',
            "Người dùng {$user->email} đã hủy kích hoạt xác thực 2 bước",
            $user->id,
            'warning'
        );

        return back()->with('success', 'Đã hủy kích hoạt xác thực 2 bước thành công.');
    }

    /**
     * Show 2FA challenge during login.
     */
    public function showChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.2fa.user_id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('login.2fa.user_id');
        $user = User::find($userId);
        $currentOtp = ($user && $user->two_factor_secret)
            ? $this->totpService->getCurrentCode($user->two_factor_secret)
            : null;

        return view('auth.two_factor_challenge', [
            'currentOtp' => $currentOtp,
        ]);
    }

    /**
     * Verify 2FA challenge and authenticate session.
     */
    public function verifyChallenge(Request $request): RedirectResponse
    {
        if (! $request->session()->has('login.2fa.user_id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('login.2fa.user_id');
        /** @var User $user */
        $user = User::findOrFail($userId);

        $code = trim($request->input('code', ''));
        $recoveryCode = trim($request->input('recovery_code', ''));

        $authenticated = false;

        if (!empty($recoveryCode)) {
            // Verify via recovery code
            $codes = json_decode($user->two_factor_recovery_codes ?: '[]', true);
            $normalizedRecovery = str_replace('-', '', strtolower($recoveryCode));

            foreach ($codes as $idx => $savedCode) {
                if (str_replace('-', '', strtolower($savedCode)) === $normalizedRecovery) {
                    unset($codes[$idx]);
                    $user->two_factor_recovery_codes = json_encode(array_values($codes));
                    $user->save();
                    $authenticated = true;
                    break;
                }
            }
        } elseif (!empty($code)) {
            $authenticated = $this->totpService->verifyCode($user->two_factor_secret, $code);
        }

        if (!$authenticated) {
            SecurityAuditService::log(
                'two_factor_failed',
                "Xác thực mã 2FA thất bại cho tài khoản {$user->email}",
                $user->id,
                'warning'
            );

            return back()->withErrors([
                'code' => 'Mã xác thực 2FA hoặc mã khôi phục không chính xác.',
            ]);
        }

        $remember = $request->session()->pull('login.2fa.remember', false);
        $request->session()->forget('login.2fa.user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        SecurityAuditService::log(
            'login_success',
            "Đăng nhập thành công với 2FA cho tài khoản {$user->email} ({$user->role})",
            $user->id,
            'info'
        );

        return match ($user->role) {
            User::ROLE_ADMIN => redirect()->intended(route('admin.dashboard')),
            User::ROLE_RECEPTIONIST => redirect()->intended(route('reception.appointments.index')),
            User::ROLE_DOCTOR => redirect()->intended(route('doctor.queue')),
            User::ROLE_PATIENT => redirect()->intended(route('patient.history')),
            default => redirect()->intended('/'),
        };
    }
}
