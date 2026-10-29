<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MedicationDelivery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryTrackingController extends Controller
{
    /**
     * Tra cứu bưu kiện thuốc ngoại trú trực tuyến bằng mã vận đơn hoặc mã tracking
     */
    public function show(string $tracking_code): View
    {
        $delivery = MedicationDelivery::where('tracking_code', $tracking_code)
            ->orWhere('waybill_number', $tracking_code)
            ->with(['patientProfile', 'medicalRecord.prescriptions', 'medicalRecord.doctor.user'])
            ->firstOrFail();

        return view('public.delivery_tracking', compact('delivery'));
    }
}
