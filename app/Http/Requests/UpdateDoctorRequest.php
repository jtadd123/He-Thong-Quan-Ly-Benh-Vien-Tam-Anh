<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $doctor = $this->route('doctor');
        $userId = is_object($doctor) && $doctor->user ? $doctor->user->id : null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'qualification' => ['required', 'string', 'max:100'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'room_number' => ['required', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'is_available' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'specialty_id.required' => 'Vui lòng chọn chuyên khoa.',
            'qualification.required' => 'Học vị / Học hàm không được để trống.',
            'consultation_fee.required' => 'Giá khám không được để trống.',
            'room_number.required' => 'Phòng khám không được để trống.',
            'name.required' => 'Họ và tên bác sĩ không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',
        ];
    }
}
