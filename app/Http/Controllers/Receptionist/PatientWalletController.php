<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\PatientProfile;
use App\Models\PatientWallet;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientWalletController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = PatientWallet::with('patientProfile')
            ->latest('updated_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wallet_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($p) use ($search) {
                      $p->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('patient_code', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        $wallets = $query->paginate(15)->withQueryString();

        $stats = [
            'total_wallets' => PatientWallet::count(),
            'total_funds' => PatientWallet::sum('balance'),
            'positive_wallets' => PatientWallet::where('balance', '>', 0)->count(),
        ];

        return view('receptionist.wallets.index', compact('wallets', 'search', 'stats'));
    }

    public function show(PatientWallet $patientWallet): View
    {
        $patientWallet->load(['patientProfile', 'transactions.processedBy']);
        return view('receptionist.wallets.show', compact('patientWallet'));
    }

    public function deposit(Request $request, PatientProfile $patient): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:50000', 'max:500000000'],
            'payment_method' => ['required', 'in:vietqr,cash,pos,bank_transfer'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.min' => 'Số tiền nạp tạm ứng tối thiểu là 50.000 VNĐ.',
            'amount.max' => 'Số tiền nạp tạm ứng tối đa là 500.000.000 VNĐ.',
        ]);

        DB::transaction(function () use ($patient, $validated) {
            $wallet = $patient->wallet;
            if (!$wallet) {
                $wallet = PatientWallet::create([
                    'patient_profile_id' => $patient->id,
                    'wallet_code' => PatientWallet::generateWalletCode(),
                    'balance' => 0,
                    'status' => 'active',
                ]);
            }

            $newBalance = $wallet->balance + $validated['amount'];
            $wallet->update(['balance' => $newBalance]);

            $txCode = WalletTransaction::generateTransactionCode();

            WalletTransaction::create([
                'patient_wallet_id' => $wallet->id,
                'transaction_code' => $txCode,
                'type' => 'deposit',
                'amount' => $validated['amount'],
                'balance_after' => $newBalance,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? 'Nạp tiền tạm ứng viện phí một chạm',
                'processed_by_user_id' => Auth::id(),
            ]);

            ClinicalAuditLog::record(
                $patient,
                'wallet_deposit',
                "Nạp tiền tạm ứng viện phí: " . number_format($validated['amount']) . " VNĐ qua phương thức {$validated['payment_method']}. Số dư mới: " . number_format($newBalance) . " VNĐ"
            );
        });

        return back()->with('success', 'Nạp tiền tạm ứng viện phí thành công! Bệnh nhân có thể tiến hành khám và cận lâm sàng mà không cần thanh toán nhiều lần.');
    }

    public function refund(Request $request, PatientWallet $patientWallet): RedirectResponse
    {
        if ($patientWallet->balance <= 0) {
            return back()->with('error', 'Số dư ví hiện tại bằng 0, không thể hoàn ứng.');
        }

        $validated = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:1000', 'max:' . $patientWallet->balance],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $refundAmount = $validated['refund_amount'];

        DB::transaction(function () use ($patientWallet, $refundAmount, $validated) {
            $newBalance = $patientWallet->balance - $refundAmount;
            $patientWallet->update(['balance' => $newBalance]);

            $txCode = WalletTransaction::generateTransactionCode();

            WalletTransaction::create([
                'patient_wallet_id' => $patientWallet->id,
                'transaction_code' => $txCode,
                'type' => 'refund',
                'amount' => $refundAmount,
                'balance_after' => $newBalance,
                'payment_method' => 'vietqr',
                'notes' => $validated['notes'] ?? 'Hoàn trả viện phí thừa khi kết thúc đợt khám',
                'processed_by_user_id' => Auth::id(),
            ]);

            ClinicalAuditLog::record(
                $patientWallet->patientProfile,
                'wallet_refund',
                "Hoàn ứng viện phí: " . number_format($refundAmount) . " VNĐ về tài khoản bệnh nhân. Số dư còn lại: " . number_format($newBalance) . " VNĐ"
            );
        });

        return back()->with('success', "Đã hoàn ứng thành công " . number_format($refundAmount) . " VNĐ cho bệnh nhân {$patientWallet->patientProfile->full_name}.");
    }
}
