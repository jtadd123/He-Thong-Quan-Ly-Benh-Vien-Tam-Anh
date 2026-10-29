<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isDoctor() || $this->user()->isAdmin());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'symptoms' => ['required', 'string', 'min:5', 'max:3000'],
            'diagnosis' => ['required', 'string', 'min:3', 'max:1000'],
            'clinical_notes' => ['nullable', 'string', 'max:3000'],
            'treatment_plan' => ['nullable', 'string', 'max:3000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'prescriptions' => ['nullable', 'array'],
            'prescriptions.*.medication_name' => ['required_with:prescriptions', 'string', 'max:150'],
            'prescriptions.*.dosage' => ['required_with:prescriptions', 'string', 'max:100'],
            'prescriptions.*.frequency' => ['required_with:prescriptions', 'string', 'max:100'],
            'prescriptions.*.duration_days' => ['required_with:prescriptions', 'integer', 'min:1', 'max:365'],
            'prescriptions.*.instructions' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'appointment_id' => 'Ca hẹn khám',
            'symptoms' => 'Triệu chứng thực thể',
            'diagnosis' => 'Chẩn đoán bệnh',
            'clinical_notes' => 'Ghi chú sinh tồn / Cận lâm sàng',
            'treatment_plan' => 'Kế hoạch điều trị',
            'follow_up_date' => 'Ngày hẹn tái khám',
            'prescriptions' => 'Đơn thuốc',
            'prescriptions.*.medication_name' => 'Tên thuốc',
            'prescriptions.*.dosage' => 'Liều dùng',
            'prescriptions.*.frequency' => 'Tần suất dùng',
            'prescriptions.*.duration_days' => 'Số ngày uống',
            'prescriptions.*.instructions' => 'Cách dùng / dặn dò',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'symptoms.required' => 'Bác sĩ vui lòng ghi nhận triệu chứng thực thể của bệnh nhân.',
            'diagnosis.required' => 'Bác sĩ vui lòng nhập kết luận chẩn đoán bệnh.',
            'follow_up_date.after_or_equal' => 'Ngày hẹn tái khám phải từ ngày hôm nay trở đi.',
            'prescriptions.*.medication_name.required_with' => 'Vui lòng nhập tên thuốc cho dòng đơn thuốc.',
            'prescriptions.*.duration_days.min' => 'Số ngày sử dụng thuốc tối thiểu là 1 ngày.',
        ];
    }
}

