<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_RECEPTIONIST, User::ROLE_DOCTOR, User::ROLE_PATIENT])],
        ], [
            'name.required' => 'Họ và tên là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.min' => 'Mật khẩu phải tối thiểu 6 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò được chọn không hợp lệ.',
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        \App\Services\SecurityAuditService::log(
            'user_created',
            "Quản trị viên đã tạo mới tài khoản {$newUser->email} (Vai trò: {$newUser->role}).",
            Auth::user(),
            'info',
            ['target_user_id' => $newUser->id, 'role' => $newUser->role],
            $request
        );

        return redirect()->route('admin.users.index')->with('success', 'Tạo tài khoản người dùng mới thành công!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user->load(['doctor.specialty', 'patientProfile']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_RECEPTIONIST, User::ROLE_DOCTOR, User::ROLE_PATIENT])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Họ và tên là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng bởi tài khoản khác.',
            'password.min' => 'Mật khẩu mới phải tối thiểu 6 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò được chọn không hợp lệ.',
        ]);

        // Safety gate 1: Prevent self-demotion from Admin
        if ($user->id === Auth::id() && $validated['role'] !== User::ROLE_ADMIN) {
            return back()->withInput()->with('error', 'Bạn không thể tự hạ quyền Admin của chính mình để tránh mất quyền quản trị hệ thống.');
        }

        // Safety gate 2: Prevent self-deactivation
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : $user->is_active;
        if ($user->id === Auth::id() && !$isActive) {
            return back()->withInput()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình khi đang đăng nhập.');
        }

        $oldRole = $user->role;
        $wasActive = (bool) $user->is_active;

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'is_active' => $isActive,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $eventType = !$isActive && $wasActive ? 'user_locked' : 'user_updated';
        $severity = !$isActive && $wasActive ? 'warning' : 'info';
        \App\Services\SecurityAuditService::log(
            $eventType,
            "Cập nhật tài khoản {$user->email} (Vai trò: {$oldRole} -> {$user->role}, Trạng thái: " . ($isActive ? 'Hoạt động' : 'Đã khóa') . ").",
            Auth::user(),
            $severity,
            ['target_user_id' => $user->id, 'old_role' => $oldRole, 'new_role' => $user->role, 'is_active' => $isActive],
            $request
        );

        return redirect()->route('admin.users.index')->with('success', "Cập nhật tài khoản {$user->name} thành công!");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Bạn không thể tự xóa tài khoản của chính mình khi đang đăng nhập!');
        }

        // Integrity check: Prevent deletion if doctor has appointments or patient has profile with appointments
        $hasDoctorAppointments = $user->doctor && $user->doctor->appointments()->exists();
        $hasPatientAppointments = $user->patientProfile && $user->patientProfile->appointments()->exists();

        if ($hasDoctorAppointments || $hasPatientAppointments) {
            return back()->with('error', "Không thể xóa tài khoản \"{$user->name}\" vì đã có lịch sử khám bệnh/lịch hẹn liên kết. Vui lòng khóa tài khoản thay vì xóa vĩnh viễn.");
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $userRole = $user->role;
        $user->delete();

        \App\Services\SecurityAuditService::log(
            'user_deleted',
            "Quản trị viên đã xóa tài khoản {$userEmail} ({$userName} - Vai trò: {$userRole}).",
            Auth::user(),
            'warning',
            ['deleted_email' => $userEmail, 'deleted_role' => $userRole]
        );

        return redirect()->route('admin.users.index')->with('success', "Đã xóa tài khoản {$userName} thành công.");
    }
}
