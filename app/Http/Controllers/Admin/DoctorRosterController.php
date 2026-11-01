<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorRosterController extends Controller
{
    /**
     * Display the weekly hospital doctor shift roster matrix with conflict detection.
     */
    public function index(Request $request): View
    {
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        $selectedSpecialtyId = $request->input('specialty_id');

        $query = DoctorSchedule::query()
            ->with(['doctor.user', 'doctor.specialty'])
            ->where('is_active', true)
            ->whereHas('doctor', function ($dq) {
                $dq->where('is_available', true);
            });

        if ($selectedSpecialtyId) {
            $query->whereHas('doctor', function ($dq) use ($selectedSpecialtyId) {
                $dq->where('specialty_id', $selectedSpecialtyId);
            });
        }

        $allSchedules = $query->get();

        $days = [
            'monday' => 'Thứ Hai',
            'tuesday' => 'Thứ Ba',
            'wednesday' => 'Thứ Tư',
            'thursday' => 'Thứ Năm',
            'friday' => 'Thứ Sáu',
            'saturday' => 'Thứ Bảy',
            'sunday' => 'Chủ Nhật',
        ];

        $shifts = [
            'morning' => [
                'label' => 'Ca Sáng',
                'time' => '07:30 - 11:30',
                'badge' => 'bg-primary',
            ],
            'afternoon' => [
                'label' => 'Ca Chiều',
                'time' => '13:30 - 17:00',
                'badge' => 'bg-warning text-dark',
            ],
        ];

        // Build roster matrix: [day][shift] = Collection of schedules
        $matrix = [];
        foreach (array_keys($days) as $day) {
            foreach (array_keys($shifts) as $shift) {
                $matrix[$day][$shift] = $allSchedules->filter(function ($item) use ($day, $shift) {
                    return $item->day_of_week === $day && $item->shift === $shift;
                });
            }
        }

        // ==========================================
        // Conflict & Anomaly Detection Algorithm
        // ==========================================
        $conflicts = [];
        $coverageWarnings = [];

        foreach (array_keys($days) as $day) {
            foreach (array_keys($shifts) as $shift) {
                $slotSchedules = $matrix[$day][$shift];

                // 1. Double Booking: Same doctor scheduled multiple times in same slot
                $doctorCounts = $slotSchedules->groupBy('doctor_id');
                foreach ($doctorCounts as $docId => $items) {
                    if ($items->count() > 1) {
                        $doc = $items->first()->doctor;
                        $conflicts[] = [
                            'type' => 'double_booking',
                            'severity' => 'danger',
                            'day' => $days[$day],
                            'shift' => $shifts[$shift]['label'],
                            'doctor_name' => $doc->full_name_with_title,
                            'message' => "Bác sĩ {$doc->full_name_with_title} bị phân trùng {$items->count()} lần trong {$shifts[$shift]['label']} ({$days[$day]}).",
                            'doctor_id' => $doc->id,
                        ];
                    }
                }

                // 2. Room Overlap: Multiple distinct doctors in the exact same room
                $roomCounts = $slotSchedules->groupBy(function ($item) {
                    return trim($item->doctor->room_number);
                });

                foreach ($roomCounts as $room => $items) {
                    if (!empty($room) && $items->pluck('doctor_id')->unique()->count() > 1) {
                        $doctorNames = $items->pluck('doctor.full_name_with_title')->unique()->join(', ');
                        $conflicts[] = [
                            'type' => 'room_overlap',
                            'severity' => 'warning',
                            'day' => $days[$day],
                            'shift' => $shifts[$shift]['label'],
                            'room' => $room,
                            'message' => "Phòng khám '{$room}' có {$items->count()} bác sĩ cùng nhận ca: {$doctorNames} ({$shifts[$shift]['label']} - {$days[$day]}).",
                        ];
                    }
                }
            }
        }

        // 3. Specialty coverage gaps on weekdays
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        if (!$selectedSpecialtyId) {
            foreach ($specialties as $spec) {
                foreach ($weekdays as $wday) {
                    foreach (array_keys($shifts) as $shift) {
                        $hasCoverage = $matrix[$wday][$shift]->contains(function ($item) use ($spec) {
                            return $item->doctor && $item->doctor->specialty_id === $spec->id;
                        });

                        if (!$hasCoverage) {
                            $coverageWarnings[] = [
                                'specialty' => $spec->name,
                                'day' => $days[$wday],
                                'shift' => $shifts[$shift]['label'],
                            ];
                        }
                    }
                }
            }
        }

        $stats = [
            'total_shifts' => $allSchedules->count(),
            'active_doctors_rostered' => $allSchedules->pluck('doctor_id')->unique()->count(),
            'conflicts_count' => count($conflicts),
            'coverage_warnings_count' => count($coverageWarnings),
        ];

        return view('admin.schedules.roster', compact(
            'matrix',
            'days',
            'shifts',
            'specialties',
            'selectedSpecialtyId',
            'conflicts',
            'coverageWarnings',
            'stats'
        ));
    }
}
