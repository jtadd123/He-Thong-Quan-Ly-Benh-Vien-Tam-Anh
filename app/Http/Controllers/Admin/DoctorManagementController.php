<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DoctorManagementController extends Controller
{
    /**
     * Display a listing of the doctors.
     */
    public function index(Request $request): View
    {
        $query = Doctor::query()->with(['user', 'specialty', 'schedules']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('qualification', 'LIKE', "%{$search}%")
                  ->orWhere('room_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%")
                         ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($specialtyId = $request->input('specialty_id')) {
            $query->where('specialty_id', $specialtyId);
        }

        if ($request->filled('is_available')) {
            $query->where('is_available', $request->input('is_available') === '1');
        }

        $doctors = $query->latest()->paginate(10)->withQueryString();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total' => Doctor::count(),
            'available' => Doctor::where('is_available', true)->count(),
            'unavailable' => Doctor::where('is_available', false)->count(),
            'rooms_count' => Doctor::distinct('room_number')->count('room_number'),
        ];

        return view('admin.doctors.index', compact('doctors', 'specialties', 'stats'));
    }

    /**
     * Show the form for creating a new doctor.
     */
    public function create(): View
    {
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        // Get doctor users who don't have a doctor profile yet
        $availableUsers = User::where('role', User::ROLE_DOCTOR)
            ->whereDoesntHave('doctor')
            ->orderBy('name')
            ->get();

        return view('admin.doctors.create', compact('specialties', 'availableUsers'));
    }

    /**
     * Store a newly created doctor in storage.
     */
    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, &$doctor) {
            if ($request->filled('user_id')) {
                $userId = $data['user_id'];
            } else {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => Hash::make($data['password']),
                    'role' => User::ROLE_DOCTOR,
                ]);
                $userId = $user->id;
            }

            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('doctors', 'public');
            }

            $doctor = Doctor::create([
                'user_id' => $userId,
                'specialty_id' => $data['specialty_id'],
                'qualification' => $data['qualification'],
                'consultation_fee' => $data['consultation_fee'],
                'room_number' => $data['room_number'],
                'avatar' => $avatarPath,
                'bio' => $data['bio'] ?? null,
                'is_available' => $request->has('is_available'),
            ]);

            // Automatically initialize weekly schedules for the new doctor (Monday - Sunday, 2 shifts)
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            foreach ($days as $day) {
                DoctorSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                    'shift' => 'morning',
                    'start_time' => '07:30:00',
                    'end_time' => '11:30:00',
                    'max_patients' => 15,
                    'is_active' => true,
                ]);

                DoctorSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                    'shift' => 'afternoon',
                    'start_time' => '13:30:00',
                    'end_time' => '17:00:00',
                    'max_patients' => 15,
                    'is_active' => in_array($day, ['saturday', 'sunday']) ? false : true,
                ]);
            }
        });

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Thêm bác sĩ mới và khởi tạo lịch trực tuần thành công!');
    }

    /**
     * Display the specified doctor.
     */
    public function show(Doctor $doctor): View
    {
        $doctor->load(['user', 'specialty', 'schedules', 'appointments' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('admin.doctors.show', compact('doctor'));
    }

    /**
     * Show the form for editing the specified doctor.
     */
    public function edit(Doctor $doctor): View
    {
        $doctor->load(['user', 'specialty']);
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('admin.doctors.edit', compact('doctor', 'specialties'));
    }

    /**
     * Update the specified doctor in storage.
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $doctor) {
            // Update associated User details
            if ($doctor->user) {
                $doctor->user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                ]);
            }

            $updateFields = [
                'specialty_id' => $data['specialty_id'],
                'qualification' => $data['qualification'],
                'consultation_fee' => $data['consultation_fee'],
                'room_number' => $data['room_number'],
                'bio' => $data['bio'] ?? null,
                'is_available' => $request->has('is_available'),
            ];

            if ($request->hasFile('avatar')) {
                if ($doctor->avatar && Storage::disk('public')->exists($doctor->avatar)) {
                    Storage::disk('public')->delete($doctor->avatar);
                }
                $updateFields['avatar'] = $request->file('avatar')->store('doctors', 'public');
            }

            // Update Doctor profile
            $doctor->update($updateFields);
        });

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Cập nhật thông tin bác sĩ thành công!');
    }

    /**
     * Remove the specified doctor from storage.
     */
    public function destroy(Doctor $doctor): RedirectResponse
    {
        if ($doctor->appointments()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            return redirect()->route('admin.doctors.index')
                ->with('error', 'Không thể xóa bác sĩ này vì đang có lịch hẹn khám sắp tới hoặc đang chờ xử lý.');
        }

        if ($doctor->avatar && Storage::disk('public')->exists($doctor->avatar)) {
            Storage::disk('public')->delete($doctor->avatar);
        }

        $doctor->delete();

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Đã xóa bác sĩ khỏi hệ thống thành công!');
    }

    /**
     * View weekly schedule configuration for the doctor.
     */
    public function schedule(Doctor $doctor): View
    {
        $doctor->load(['user', 'specialty', 'schedules']);

        $schedules = $doctor->schedules->keyBy(function ($item) {
            return $item->day_of_week . '_' . $item->shift;
        });

        $days = [
            'monday' => 'Thứ Hai',
            'tuesday' => 'Thứ Ba',
            'wednesday' => 'Thứ Tư',
            'thursday' => 'Thứ Năm',
            'friday' => 'Thứ Sáu',
            'saturday' => 'Thứ Bảy',
            'sunday' => 'Chủ Nhật',
        ];

        return view('admin.doctors.schedule', compact('doctor', 'schedules', 'days'));
    }

    /**
     * Update weekly schedule configuration.
     */
    public function updateSchedule(Request $request, Doctor $doctor): RedirectResponse
    {
        $schedulesInput = $request->input('schedules', []);

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $shifts = ['morning', 'afternoon'];

        DB::transaction(function () use ($doctor, $schedulesInput, $days, $shifts) {
            foreach ($days as $day) {
                foreach ($shifts as $shift) {
                    $key = "{$day}_{$shift}";
                    $data = $schedulesInput[$key] ?? [];

                    $isActive = isset($data['is_active']);
                    $startTime = !empty($data['start_time']) ? $data['start_time'] : ($shift === 'morning' ? '07:30:00' : '13:30:00');
                    $endTime = !empty($data['end_time']) ? $data['end_time'] : ($shift === 'morning' ? '11:30:00' : '17:00:00');
                    $maxPatients = !empty($data['max_patients']) ? intval($data['max_patients']) : 15;

                    DoctorSchedule::updateOrCreate([
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                        'shift' => $shift,
                    ], [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'max_patients' => $maxPatients,
                        'is_active' => $isActive,
                    ]);
                }
            }
        });

        return redirect()->route('admin.doctors.schedule', $doctor)
            ->with('success', 'Cập nhật lịch trực tuần của bác sĩ thành công!');
    }

    /**
     * Toggle available status for the doctor.
     */
    public function toggleAvailability(Doctor $doctor): RedirectResponse
    {
        $doctor->is_available = !$doctor->is_available;
        $doctor->save();

        $statusText = $doctor->is_available ? 'sẵn sàng khám' : 'tạm dừng nhận khám';
        return back()->with('success', "Đã chuyển trạng thái bác sĩ sang: {$statusText}!");
    }
}
