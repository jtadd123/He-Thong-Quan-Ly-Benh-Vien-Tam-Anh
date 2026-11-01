<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Services\DoctorVcardService;
use App\Services\SymptomMatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DoctorPublicController extends Controller
{
    /**
     * Display a public directory of doctors.
     */
    public function index(Request $request): View
    {
        $query = Doctor::query()
            ->with(['user', 'specialty', 'schedules'])
            ->where('is_available', true)
            ->whereHas('specialty', function ($q) {
                $q->where('is_active', true);
            });

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('qualification', 'LIKE', "%{$search}%")
                  ->orWhere('room_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($specialtyId = $request->input('specialty_id')) {
            $query->where('specialty_id', $specialtyId);
        }

        if ($request->boolean('on_duty_today')) {
            $today = strtolower(now()->format('l'));
            $query->whereHas('schedules', function ($sq) use ($today) {
                $sq->where('day_of_week', $today)->where('is_active', true);
            });
        }

        $doctors = $query->paginate(9)->withQueryString();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        if ($request->ajax()) {
            return view('public.doctors.partials.doctor_grid', compact('doctors'));
        }

        return view('public.doctors.index', compact('doctors', 'specialties'));
    }

    /**
     * Display the specified doctor profile and weekly schedule.
     */
    public function show(Doctor $doctor): View
    {
        $doctor->load(['user', 'specialty', 'schedules' => function ($q) {
            $q->where('is_active', true);
        }]);

        $days = [
            'monday' => 'Thứ Hai',
            'tuesday' => 'Thứ Ba',
            'wednesday' => 'Thứ Tư',
            'thursday' => 'Thứ Năm',
            'friday' => 'Thứ Sáu',
            'saturday' => 'Thứ Bảy',
            'sunday' => 'Chủ Nhật',
        ];

        $qrCodeUrl = DoctorVcardService::getQrCodeUrl($doctor, 200);

        return view('public.doctors.show', compact('doctor', 'days', 'qrCodeUrl'));
    }

    /**
     * Download doctor's digital business card in standard .vcf vCard format.
     */
    public function downloadVcard(Doctor $doctor): Response
    {
        $doctor->load(['user', 'specialty']);
        $vcard = DoctorVcardService::generateVcard($doctor);
        $filename = 'bac_si_' . Str::slug($doctor->user->name ?? 'bac_si') . '.vcf';

        return response($vcard, 200, [
            'Content-Type' => 'text/vcard; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Display printable table tent card for examination room desk.
     */
    public function deskCard(Doctor $doctor): View
    {
        $doctor->load(['user', 'specialty']);
        $qrCodeUrl = DoctorVcardService::getQrCodeUrl($doctor, 280);

        return view('public.doctors.desk_card', compact('doctor', 'qrCodeUrl'));
    }

    /**
     * Show the smart symptom checker assistant page.
     */
    public function symptomChecker(): View
    {
        $specialties = Specialty::where('is_active', true)->withCount('doctors')->get();
        return view('public.symptom_checker', compact('specialties'));
    }

    /**
     * API endpoint to match symptoms to specialty and doctors.
     */
    public function matchSymptoms(Request $request, SymptomMatcherService $matcher): JsonResponse
    {
        $symptoms = $request->input('symptoms', '');
        $result = $matcher->match($symptoms);

        return response()->json($result);
    }
}
