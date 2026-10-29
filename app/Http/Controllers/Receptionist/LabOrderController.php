<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabService;
use App\Models\PatientProfile;
use App\Services\LabOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LabOrderController extends Controller
{
    public function __construct(
        protected LabOrderService $labService
    ) {}

    public function index(Request $request): View
    {
        $query = LabOrder::with(['patientProfile', 'doctor.user', 'items.labService'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($pq) use ($search) {
                      $pq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => LabOrder::count(),
            'pending' => LabOrder::where('status', LabOrder::STATUS_PENDING)->count(),
            'processing' => LabOrder::where('status', LabOrder::STATUS_PROCESSING)->count(),
            'completed' => LabOrder::where('status', LabOrder::STATUS_COMPLETED)->count(),
            'panic_alerts' => LabOrderItem::where('is_panic_alert', true)->count(),
        ];

        return view('receptionist.lab_orders.index', compact('orders', 'stats'));
    }

    public function create(): View
    {
        $services = LabService::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $patients = PatientProfile::latest()->take(30)->get();

        return view('receptionist.lab_orders.create', compact('services', 'patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'clinical_diagnosis' => 'required|string|max:255',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:lab_services,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);

        $order = $this->labService->createOrder($validated);

        return redirect()->route('reception.lab-orders.show', $order)
            ->with('success', "Đã tạo phiếu chỉ định Cận Lâm Sàng {$order->order_code} thành công!");
    }

    public function show(LabOrder $labOrder): View
    {
        $labOrder->load(['patientProfile', 'doctor.user', 'items.labService', 'items.technician']);
        return view('receptionist.lab_orders.show', compact('labOrder'));
    }

    public function updateItemResult(Request $request, LabOrderItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'result_value' => 'required|string|max:255',
            'technician_notes' => 'nullable|string|max:500',
            'dicom_image_url' => 'nullable|url|max:500',
        ]);

        $updatedItem = $this->labService->recordResult(
            $item,
            $validated['result_value'],
            Auth::id(),
            $validated['technician_notes'] ?? null,
            $validated['dicom_image_url'] ?? null
        );

        if ($updatedItem->is_panic_alert) {
            return back()->with('warning', "🚨 CẢNH BÁO GIÁ TRỊ NGUY HIỂM (CRITICAL PANIC VALUE): Chỉ số {$updatedItem->labService->name} vượt ngưỡng an toàn ({$updatedItem->result_value})! Hệ thống đã kích hoạt báo động cho Bác sĩ điều trị!");
        }

        return back()->with('success', "Đã lưu kết quả {$updatedItem->labService->name} thành công!");
    }

    public function printReport(LabOrder $labOrder): View
    {
        $labOrder->load(['patientProfile', 'doctor.user', 'items.labService', 'items.technician']);
        return view('receptionist.lab_orders.report', compact('labOrder'));
    }
}
