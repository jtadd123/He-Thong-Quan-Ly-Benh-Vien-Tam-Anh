<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'specialty_id' => ['required', 'exists:specialties,id'],
            'qualification' => ['required', 'string', 'max:100'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'room_number' => ['required', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'is_available' => ['nullable', 'boolean'],
        ];

        if ($this->filled('user_id')) {
            $rules['user_id'] = ['required', 'exists:users,id', 'unique:doctors,user_id'];
        } else {
            $rules['name'] = ['required', 'string', 'max:100'];
            $rules['email'] = ['required', 'string', 'email', 'max:150', 'unique:users,email'];
            $rules['password'] = ['required', 'string', 'min:6'];
            $rules['phone'] = ['nullable', 'string', 'max:20'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'specialty_id.required' => 'Vui lòng chọn chuyên khoa.',
            'specialty_id.exists' => 'Chuyên khoa không hợp lệ.',
            'qualification.required' => 'Học vị / Học hàm không được để trống.',
            'consultation_fee.required' => 'Giá khám không được để trống.',
            'consultation_fee.numeric' => 'Giá khám phải là số hợp lệ.',
            'room_number.required' => 'Phòng khám không được để trống.',
            'user_id.unique' => 'Tài khoản người dùng này đã được gán cho một bác sĩ khác.',
            'name.required' => 'Họ và tên bác sĩ không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.min' => 'Mật khẩu tối thiểu 6 ký tự.',
        ];
    }
}
