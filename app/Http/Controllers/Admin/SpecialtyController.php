<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of the specialties.
     */
    public function index(Request $request): View
    {
        $query = Specialty::query()->withCount('doctors');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === '1');
        }

        $specialties = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Specialty::count(),
            'active' => Specialty::where('is_active', true)->count(),
            'inactive' => Specialty::where('is_active', false)->count(),
            'doctors_count' => Doctor::count(),
        ];

        $chartSpecialties = Specialty::withCount('doctors')->orderByDesc('doctors_count')->take(8)->get();
        $chartLabels = $chartSpecialties->pluck('name')->toArray();
        $chartData = $chartSpecialties->pluck('doctors_count')->toArray();

        return view('admin.specialties.index', compact('specialties', 'stats', 'chartLabels', 'chartData'));
    }

    /**
     * Show the form for creating a new specialty.
     */
    public function create(): View
    {
        return view('admin.specialties.create');
    }

    /**
     * Store a newly created specialty in storage.
     */
    public function store(StoreSpecialtyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Specialty::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
            $data['slug'] = $slug;
        }

        $data['is_active'] = $request->has('is_active');

        Specialty::create($data);

        return redirect()->route('admin.specialties.index')
            ->with('success', 'Thêm chuyên khoa mới thành công!');
    }

    /**
     * Display the specified specialty.
     */
    public function show(Specialty $specialty): View
    {
        $specialty->load(['doctors.user', 'doctors.schedules']);

        return view('admin.specialties.show', compact('specialty'));
    }

    /**
     * Show the form for editing the specified specialty.
     */
    public function edit(Specialty $specialty): View
    {
        return view('admin.specialties.edit', compact('specialty'));
    }

    /**
     * Update the specified specialty in storage.
     */
    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Specialty::where('slug', $slug)->where('id', '!=', $specialty->id)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
            $data['slug'] = $slug;
        }

        $data['is_active'] = $request->has('is_active');

        $specialty->update($data);

        return redirect()->route('admin.specialties.index')
            ->with('success', 'Cập nhật thông tin chuyên khoa thành công!');
    }

    /**
     * Remove the specified specialty from storage.
     */
    public function destroy(Specialty $specialty): RedirectResponse
    {
        if ($specialty->doctors()->exists()) {
            return redirect()->route('admin.specialties.index')
                ->with('error', 'Không thể xóa chuyên khoa này vì đang có bác sĩ trực thuộc. Vui lòng chuyển hoặc xóa bác sĩ trước.');
        }

        if ($specialty->appointments()->exists()) {
            return redirect()->route('admin.specialties.index')
                ->with('error', 'Không thể xóa chuyên khoa này vì đã có dữ liệu lịch hẹn khám bệnh liên quan.');
        }

        $specialty->delete();

        return redirect()->route('admin.specialties.index')
            ->with('success', 'Đã xóa chuyên khoa thành công!');
    }

    /**
     * Toggle active status of specialty.
     */
    public function toggleStatus(Specialty $specialty): RedirectResponse
    {
        $specialty->is_active = !$specialty->is_active;
        $specialty->save();

        $statusText = $specialty->is_active ? 'kích hoạt' : 'tạm dừng';
        return back()->with('success', "Đã {$statusText} chuyên khoa {$specialty->name} thành công!");
    }
}
