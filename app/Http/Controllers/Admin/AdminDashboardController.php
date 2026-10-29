<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the Admin Central Dashboard with real database stats and Chart.js analytics.
     */
    public function index(): View
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalCompleted = Appointment::where('status', Appointment::STATUS_COMPLETED)->count();
        $totalCancelled = Appointment::where('status', Appointment::STATUS_CANCELLED)->count();
        $totalDoneOrCancelled = $totalCompleted + $totalCancelled;
        $completionRate = $totalDoneOrCancelled > 0 ? round(($totalCompleted / $totalDoneOrCancelled) * 100, 1) : 100.0;

        $stats = [
            'total_users' => User::count(),
            'total_doctors' => Doctor::count(),
            'total_specialties' => Specialty::count(),
            'total_patients' => PatientProfile::count(),
            'appointments_today' => Appointment::whereDate('appointment_date', $today)->count(),
            'appointments_pending' => Appointment::where('status', Appointment::STATUS_PENDING)->count(),
            'appointments_confirmed' => Appointment::where('status', Appointment::STATUS_CONFIRMED)->count(),
            'appointments_completed' => $totalCompleted,
            'appointments_cancelled' => $totalCancelled,
            'appointments_month' => Appointment::where('appointment_date', '>=', $startOfMonth)->count(),
            'total_records' => MedicalRecord::count(),
            'total_prescriptions' => Prescription::count(),
            'total_attachments' => Attachment::count(),
            'completion_rate' => $completionRate,
        ];

        // Financial & Efficiency KPIs
        $todayRevenue = (float) Appointment::whereDate('appointment_date', $today)
            ->whereIn('status', [Appointment::STATUS_CONFIRMED, Appointment::STATUS_COMPLETED])
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->sum('doctors.consultation_fee');

        $monthRevenue = (float) Appointment::whereYear('appointment_date', $today->year)
            ->whereMonth('appointment_date', $today->month)
            ->whereIn('status', [Appointment::STATUS_CONFIRMED, Appointment::STATUS_COMPLETED])
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->sum('doctors.consultation_fee');

        // 7-day appointment trend data for Chart.js
        $trendLabels = [];
        $trendMorning = [];
        $trendAfternoon = [];
        $trendTotal = [];
        $trend7Days = collect();

        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $trendLabels[] = $d->format('d/m');
            $mCount = Appointment::whereDate('appointment_date', $d)->where('shift', 'morning')->count();
            $aCount = Appointment::whereDate('appointment_date', $d)->where('shift', 'afternoon')->count();
            $trendMorning[] = $mCount;
            $trendAfternoon[] = $aCount;
            $trendTotal[] = $mCount + $aCount;
            $trend7Days->push([
                'label' => $d->format('d/m'),
                'count' => $mCount + $aCount,
            ]);
        }

        // Status Chart Data
        $statusChartData = [
            'pending' => $stats['appointments_pending'],
            'confirmed' => $stats['appointments_confirmed'],
            'completed' => $stats['appointments_completed'],
            'cancelled' => $stats['appointments_cancelled'],
        ];

        // Specialty distribution data for Doughnut Chart
        $specialtyDistribution = Specialty::withCount('appointments')
            ->orderByDesc('appointments_count')
            ->take(6)
            ->get();
        $specialtyLabels = $specialtyDistribution->pluck('name')->toArray();
        $specialtyCounts = $specialtyDistribution->pluck('appointments_count')->toArray();
        $specialtyStats = $specialtyDistribution;

        $recentAppointments = Appointment::with(['patientProfile', 'doctor.user', 'specialty'])
            ->latest()
            ->take(8)
            ->get();

        $recentRecords = MedicalRecord::with(['patientProfile', 'doctor.user', 'appointment'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'todayRevenue',
            'monthRevenue',
            'completionRate',
            'trendLabels',
            'trendMorning',
            'trendAfternoon',
            'trendTotal',
            'trend7Days',
            'statusChartData',
            'specialtyLabels',
            'specialtyCounts',
            'specialtyStats',
            'recentAppointments',
            'recentRecords'
        ));
    }
}
