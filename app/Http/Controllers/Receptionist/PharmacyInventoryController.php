<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PharmacyInventoryController extends Controller
{
    /**
     * Pharmacy Medication Formulary & Stock Management Dashboard.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('q', ''));
        $status = $request->input('status', 'all'); // 'all', 'low_stock', 'out_of_stock', 'expiring_soon'

        $query = Medication::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('medication_code', 'like', "%{$search}%")
                    ->orWhere('active_ingredient', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        if ($status === 'low_stock') {
            $query->whereRaw('stock_quantity <= min_stock_alert AND stock_quantity > 0');
        } elseif ($status === 'out_of_stock') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($status === 'expiring_soon') {
            $query->whereNotNull('expiry_date')
                ->where('expiry_date', '>=', Carbon::today())
                ->where('expiry_date', '<=', Carbon::today()->addMonths(3))
                ->orderBy('expiry_date', 'asc');
        } elseif ($status === 'expired') {
            $query->whereNotNull('expiry_date')
                ->where('expiry_date', '<', Carbon::today())
                ->orderBy('expiry_date', 'asc');
        } elseif ($status === 'fefo') {
            $query->fefoOrder();
        }

        if ($status !== 'expiring_soon' && $status !== 'expired' && $status !== 'fefo') {
            $query->orderBy('name');
        }

        $medications = $query->paginate(15)->withQueryString();

        // Statistical KPI Counters
        $totalCount = Medication::count();
        $lowStockCount = Medication::whereRaw('stock_quantity <= min_stock_alert AND stock_quantity > 0')->count();
        $outOfStockCount = Medication::where('stock_quantity', '<=', 0)->count();
        $expiringSoonCount = Medication::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', Carbon::today())
            ->where('expiry_date', '<=', Carbon::today()->addMonths(3))
            ->count();
        $expiredCount = Medication::whereNotNull('expiry_date')
            ->where('expiry_date', '<', Carbon::today())
            ->count();
        $totalInventoryValue = (float) Medication::sum(DB::raw('stock_quantity * unit_price'));

        return view('receptionist.pharmacy.inventory', compact(
            'medications',
            'search',
            'status',
            'totalCount',
            'lowStockCount',
            'outOfStockCount',
            'expiringSoonCount',
            'expiredCount',
            'totalInventoryValue'
        ));
    }

    /**
     * Add new medication to formulary.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'active_ingredient' => ['nullable', 'string', 'max:150'],
            'dosage_form' => ['required', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:30'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'batch_number' => ['nullable', 'string', 'max:50'],
            'dosage_default' => ['nullable', 'string', 'max:100'],
            'frequency_default' => ['nullable', 'string', 'max:100'],
            'duration_default' => ['nullable', 'integer', 'min:1'],
            'instructions_default' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Vui lòng nhập tên thuốc và hàm lượng.',
            'dosage_form.required' => 'Vui lòng nhập dạng bào chế.',
            'unit.required' => 'Vui lòng nhập đơn vị tính.',
            'stock_quantity.required' => 'Vui lòng nhập số lượng tồn kho ban đầu.',
            'unit_price.required' => 'Vui lòng nhập đơn giá thuốc.',
        ]);

        $validated['medication_code'] = Medication::generateMedicationCode();

        $medication = Medication::create($validated);

        return back()->with('success', "Đã thêm thuốc mới '{$medication->name}' (Mã: {$medication->medication_code}) vào danh mục kho dược ngoại trú.");
    }

    /**
     * Update medication information.
     */
    public function update(Request $request, Medication $medication): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'active_ingredient' => ['nullable', 'string', 'max:150'],
            'dosage_form' => ['required', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:30'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'batch_number' => ['nullable', 'string', 'max:50'],
            'dosage_default' => ['nullable', 'string', 'max:100'],
            'frequency_default' => ['nullable', 'string', 'max:100'],
            'duration_default' => ['nullable', 'integer', 'min:1'],
            'instructions_default' => ['nullable', 'string', 'max:255'],
        ]);

        $medication->update($validated);

        return back()->with('success', "Đã cập nhật thông tin thuốc '{$medication->name}'.");
    }

    /**
     * Restock / Add quantity to medication inventory.
     */
    public function restock(Request $request, Medication $medication): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'batch_number' => ['nullable', 'string', 'max:50'],
            'expiry_date' => ['nullable', 'date'],
        ], [
            'quantity.required' => 'Vui lòng nhập số lượng nhập kho.',
            'quantity.min' => 'Số lượng nhập tối thiểu là 1.',
        ]);

        $oldStock = $medication->stock_quantity;
        $newStock = $oldStock + (int)$validated['quantity'];

        $updateData = ['stock_quantity' => $newStock];
        if (!empty($validated['batch_number'])) {
            $updateData['batch_number'] = $validated['batch_number'];
        }
        if (!empty($validated['expiry_date'])) {
            $updateData['expiry_date'] = $validated['expiry_date'];
        }

        $medication->update($updateData);

        return back()->with('success', "Đã nhập thêm {$validated['quantity']} {$medication->unit} thuốc '{$medication->name}'. Tồn kho hiện tại: {$newStock} {$medication->unit}.");
    }

    /**
     * Delete or toggle medication.
     */
    public function destroy(Medication $medication): RedirectResponse
    {
        $medication->delete();

        return back()->with('success', "Đã xóa thuốc '{$medication->name}' khỏi danh mục nhà thuốc.");
    }
}
