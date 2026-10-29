<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:100'],
            'dob' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'in:male,female,other'],
            'phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9,10}$/', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'identity_card' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
            'insurance_code' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9]+$/'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'allergies' => ['nullable', 'string', 'max:255'],
            'medical_history' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ và tên bệnh nhân không được để trống.',
            'full_name.max' => 'Họ và tên không được vượt quá 100 ký tự.',
            'dob.required' => 'Ngày sinh không được để trống.',
            'dob.date' => 'Ngày sinh không đúng định dạng ngày.',
            'dob.before_or_equal' => 'Ngày sinh không được lớn hơn ngày hiện tại.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam (bắt đầu bằng 0 hoặc +84 và gồm 10-11 chữ số).',
            'phone.max' => 'Số điện thoại tối đa 20 ký tự.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'identity_card.max' => 'Số CCCD/CMND không được vượt quá 20 ký tự.',
            'identity_card.regex' => 'Số CCCD/CMND/Hộ chiếu chỉ được chứa chữ cái và chữ số, không chứa ký tự đặc biệt hoặc khoảng trắng.',
            'insurance_code.max' => 'Mã thẻ BHYT không được vượt quá 30 ký tự.',
            'insurance_code.regex' => 'Mã thẻ BHYT chỉ được chứa chữ cái và chữ số không dấu (chuẩn BHXH Việt Nam).',
        ];
    }
}
