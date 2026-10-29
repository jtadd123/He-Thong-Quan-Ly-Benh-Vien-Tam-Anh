<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorQueueController extends Controller
{
    /**
     * Display the consultation queue for logged-in doctor.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $allDoctors = collect();
        $doctor = null;

        if ($user->isDoctor()) {
            $doctor = Doctor::with('specialty')->where('user_id', $user->id)->first();
        } elseif ($user->isAdmin()) {
            $allDoctors = Doctor::with(['user', 'specialty'])->get();
            $selectedDoctorId = $request->query('doctor_id');

            if ($selectedDoctorId) {
                $doctor = Doctor::with('specialty')->find($selectedDoctorId);
            } else {
                $doctor = $allDoctors->first();
            }
        }

        $appointments = collect();
        $date = $request->query('date', Carbon::today()->toDateString());
        $status = $request->query('status');
        $search = $request->query('search');

        $counts = [
            'total' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'pending' => 0,
        ];

        if ($doctor) {
            $baseQuery = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $date);

            $counts = [
                'total' => (clone $baseQuery)->count(),
                'confirmed' => (clone $baseQuery)->where('status', Appointment::STATUS_CONFIRMED)->count(),
                'completed' => (clone $baseQuery)->where('status', Appointment::STATUS_COMPLETED)->count(),
                'pending' => (clone $baseQuery)->where('status', Appointment::STATUS_PENDING)->count(),
            ];

            $query = Appointment::with(['patientProfile', 'specialty', 'medicalRecord'])
                ->where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $date);

            if ($status && in_array($status, [
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_PENDING,
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_CANCELLED
            ])) {
                $query->where('status', $status);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('appointment_code', 'LIKE', "%{$search}%")
                        ->orWhereHas('patientProfile', function ($pq) use ($search) {
                            $pq->where('full_name', 'LIKE', "%{$search}%")
                                ->orWhere('patient_code', 'LIKE', "%{$search}%")
                                ->orWhere('phone', 'LIKE', "%{$search}%");
                        });
                });
            }

            $appointments = $query
                ->orderByRaw("FIELD(status, 'confirmed', 'pending', 'completed', 'cancelled')")
                ->orderBy('created_at')
                ->get();
        }

        return view('doctor.queue', compact(
            'doctor',
            'appointments',
            'allDoctors',
            'date',
            'status',
            'search',
            'counts'
        ));
    }
}
