<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\MedicationDelivery;
use App\Services\MedicationDeliveryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicationDeliveryController extends Controller
{
    public function __construct(
        protected MedicationDeliveryService $deliveryService
    ) {}

    public function index(Request $request): View
    {
        $query = MedicationDelivery::with(['patientProfile', 'medicalRecord.prescriptions'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('carrier')) {
            $query->where('carrier_name', $request->carrier);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', "%{$search}%")
                  ->orWhere('waybill_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%");
            });
        }

        $deliveries = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => MedicationDelivery::count(),
            'pending_packaging' => MedicationDelivery::where('status', MedicationDelivery::STATUS_PENDING_PACKAGING)->count(),
            'in_transit' => MedicationDelivery::where('status', MedicationDelivery::STATUS_IN_TRANSIT)->count(),
            'delivered' => MedicationDelivery::where('status', MedicationDelivery::STATUS_DELIVERED)->count(),
            'cold_chain' => MedicationDelivery::where('requires_cold_chain', true)->count(),
        ];

        // Lấy danh sách bệnh án đã kê đơn thuốc để có thể tạo đơn giao
        $recentRecords = MedicalRecord::has('prescriptions')->with(['patientProfile', 'doctor.user'])->latest()->take(20)->get();

        return view('receptionist.deliveries.index', compact('deliveries', 'stats', 'recentRecords'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medical_record_id' => 'required|exists:medical_records,id',
            'recipient_name' => 'required|string|max:150',
            'recipient_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:255',
            'carrier_name' => 'required|in:tam_anh_express,viettel_post,grab_express',
            'shipping_fee' => 'nullable|numeric|min:0',
            'requires_cold_chain' => 'nullable|boolean',
            'pharmacist_notes' => 'nullable|string|max:500',
        ]);

        $delivery = $this->deliveryService->createDeliveryOrder($validated);

        return redirect()->route('reception.deliveries.index')
            ->with('success', "Đã tạo đơn giao thuốc tận nhà {$delivery->tracking_code} thành công! Dược sĩ vui lòng đóng gói bưu kiện.");
    }

    public function updateStatus(Request $request, MedicationDelivery $delivery): RedirectResponse
    {
        $status = $request->input('status');

        if ($status === 'packaged') {
            $this->deliveryService->markPackaged($delivery, $request->input('pharmacist_notes'));
            $msg = "Đã xác nhận đóng gói xong bưu phẩm {$delivery->tracking_code}!";
        } elseif ($status === 'in_transit') {
            $this->deliveryService->markShipped($delivery, $request->input('waybill_number'));
            $msg = "Đã bàn giao bưu phẩm {$delivery->tracking_code} cho shipper vận chuyển!";
        } elseif ($status === 'delivered') {
            $this->deliveryService->markDelivered($delivery);
            $msg = "Đã hoàn tất giao thuốc tận tay người bệnh {$delivery->recipient_name}!";
        } else {
            return back();
        }

        return back()->with('success', $msg);
    }

    public function printWaybill(MedicationDelivery $delivery): View
    {
        $delivery->load(['patientProfile', 'medicalRecord.prescriptions', 'medicalRecord.doctor.user']);
        return view('receptionist.deliveries.waybill', compact('delivery'));
    }
}
