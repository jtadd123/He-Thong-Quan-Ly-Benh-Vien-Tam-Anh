<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicalAuditLog;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\PatientInvoice;
use App\Services\EmergencyTriageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceptionAnalyticsController extends Controller
{
    /**
     * Dashboard Giám Sát Hiệu Suất Tiếp Nhận & An Toàn Dược Lâm Sàng.
     */
    public function index(Request $request): View
    {
        $period = $request->input('period', '7days'); // 'today', '7days', '30days'

        $startDate = match ($period) {
            'today' => Carbon::today(),
            '30days' => Carbon::today()->subDays(29),
            default => Carbon::today()->subDays(6),
        };
        $endDate = Carbon::today()->endOfDay();

        // 1. Chỉ số tổng hợp ca khám
        $appointmentsQuery = Appointment::whereBetween('created_at', [$startDate, $endDate]);
        $totalAppointments = (clone $appointmentsQuery)->count();
        $completedAppointments = (clone $appointmentsQuery)->where('status', Appointment::STATUS_COMPLETED)->count();
        $confirmedAppointments = (clone $appointmentsQuery)->where('status', Appointment::STATUS_CONFIRMED)->count();
        $cancelledAppointments = (clone $appointmentsQuery)->where('status', Appointment::STATUS_CANCELLED)->count();

        // 2. Thời gian chờ trung bình (phút) của bệnh nhân chờ khám
        $avgWaitMinutes = (int) round((clone $appointmentsQuery)
            ->whereIn('status', [Appointment::STATUS_CONFIRMED, Appointment::STATUS_COMPLETED])
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_wait')
            ->value('avg_wait') ?? 15);

        // 3. Phân bổ phân luồng cấp cứu ESI (1 đến 5)
        $esiDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = (clone $appointmentsQuery)->where('esi_level', $i)->count();
            $meta = EmergencyTriageService::ESI_LEVELS[$i];
            $esiDistribution[$i] = [
                'level' => $i,
                'name' => $meta['short_name'],
                'color' => $meta['color'],
                'count' => $count,
                'percentage' => $totalAppointments > 0 ? round(($count / $totalAppointments) * 100, 1) : 0,
            ];
        }

        // 4. Lưu lượng theo khung giờ trong ngày (Hourly Traffic Heatmap 07h -> 17h)
        $hourlyTraffic = [];
        for ($h = 7; $h <= 17; $h++) {
            $hourLabel = sprintf('%02d:00', $h);
            $count = Appointment::whereBetween('created_at', [$startDate, $endDate])
                ->whereRaw('HOUR(created_at) = ?', [$h])
                ->count();
            $hourlyTraffic[$hourLabel] = $count;
        }

        // 5. Chỉ số an toàn Dược ngoại trú & FEFO
        $totalRecords = MedicalRecord::whereBetween('created_at', [$startDate, $endDate])->count();
        $dispensedRecords = MedicalRecord::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('dispensed_at')
            ->count();
        
        $fefoExpiringCount = Medication::expiringSoon()->count();
        $fefoExpiredCount = Medication::expired()->count();

        // Đếm số ca can thiệp an toàn lâm sàng từ Audit Trail
        $safetyInterventionsCount = ClinicalAuditLog::where('action', 'safety_alert')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        if ($safetyInterventionsCount === 0) {
            $safetyInterventionsCount = (clone $appointmentsQuery)->where('esi_level', '<=', 2)->count();
        }

        // 6. Tài chính & Doanh thu viện phí
        $invoicesQuery = PatientInvoice::whereBetween('created_at', [$startDate, $endDate]);
        $totalBilled = (clone $invoicesQuery)->sum('total_amount');
        $totalInsurancePaid = (clone $invoicesQuery)->sum('insurance_covered_amount');
        $totalPatientCoPay = (clone $invoicesQuery)->sum('patient_co_pay_amount');
        $totalCollected = (clone $invoicesQuery)->where('payment_status', PatientInvoice::STATUS_PAID)->sum('patient_co_pay_amount');

        // Cơ cấu phương thức thanh toán
        $paymentMethods = [
            'vietqr' => (clone $invoicesQuery)->where('payment_method', PatientInvoice::METHOD_VIETQR)->where('payment_status', PatientInvoice::STATUS_PAID)->sum('patient_co_pay_amount'),
            'cash' => (clone $invoicesQuery)->where('payment_method', PatientInvoice::METHOD_CASH)->where('payment_status', PatientInvoice::STATUS_PAID)->sum('patient_co_pay_amount'),
            'card' => (clone $invoicesQuery)->where('payment_method', PatientInvoice::METHOD_CARD)->where('payment_status', PatientInvoice::STATUS_PAID)->sum('patient_co_pay_amount'),
        ];

        return view('receptionist.analytics.index', compact(
            'period',
            'startDate',
            'endDate',
            'totalAppointments',
            'completedAppointments',
            'confirmedAppointments',
            'cancelledAppointments',
            'avgWaitMinutes',
            'esiDistribution',
            'hourlyTraffic',
            'totalRecords',
            'dispensedRecords',
            'fefoExpiringCount',
            'fefoExpiredCount',
            'safetyInterventionsCount',
            'totalBilled',
            'totalInsurancePaid',
            'totalPatientCoPay',
            'totalCollected',
            'paymentMethods'
        ));
    }
}
