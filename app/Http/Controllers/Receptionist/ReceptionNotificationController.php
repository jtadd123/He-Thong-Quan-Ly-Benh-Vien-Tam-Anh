<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientNotification;
use App\Services\PatientJourneyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceptionNotificationController extends Controller
{
    public function __construct(
        protected PatientJourneyService $journeyService
    ) {}

    public function index(Request $request): View
    {
        $query = PatientNotification::with(['appointment.doctor', 'patientProfile'])->latest();

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('type')) {
            $query->where('notification_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient_phone', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($pq) use ($search) {
                      $pq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%");
                  });
            });
        }

        $notifications = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => PatientNotification::count(),
            'zalo_zns' => PatientNotification::where('channel', PatientNotification::CHANNEL_ZALO_ZNS)->count(),
            'sms' => PatientNotification::where('channel', PatientNotification::CHANNEL_SMS)->count(),
            'today' => PatientNotification::whereDate('created_at', today())->count(),
        ];

        return view('receptionist.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Kích hoạt gửi thông báo sắp đến lượt từ quầy lễ tân
     */
    public function dispatchAlert(Appointment $appointment): RedirectResponse
    {
        $notification = $this->journeyService->dispatchQueueAlert($appointment);

        return back()->with('success', "Đã gửi thông báo Zalo ZNS / SMS nhắc lượt khám thành công đến bệnh nhân {$appointment->patientProfile->full_name} ({$notification->recipient_phone})!");
    }

    /**
     * Kích hoạt gửi thông báo đơn thuốc đã sẵn sàng
     */
    public function dispatchPrescriptionAlert(Appointment $appointment): RedirectResponse
    {
        $notification = $this->journeyService->dispatchPrescriptionReadyAlert($appointment);

        return back()->with('success', "Đã gửi thông báo nhận thuốc thành công đến bệnh nhân {$appointment->patientProfile->full_name} ({$notification->recipient_phone})!");
    }
}
