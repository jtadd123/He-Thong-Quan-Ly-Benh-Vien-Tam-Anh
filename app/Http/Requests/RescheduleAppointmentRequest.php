<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RescheduleAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxDaysAhead = max(1, (int) \App\Models\SystemSetting::get('max_booking_days_ahead', 30));
        $maxDate = Carbon::today()->addDays($maxDaysAhead)->toDateString();

        return [
            'appointment_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . $maxDate],
            'shift' => ['required', 'in:morning,afternoon'],
            'reschedule_reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $maxDaysAhead = max(1, (int) \App\Models\SystemSetting::get('max_booking_days_ahead', 30));

        return [
            'appointment_date.required' => 'Vui lòng chọn ngày khám mới.',
            'appointment_date.date' => 'Ngày khám không đúng định dạng.',
            'appointment_date.after_or_equal' => 'Ngày khám mới phải từ hôm nay trở đi.',
            'appointment_date.before_or_equal' => "Chỉ cho phép đổi lịch khám trước tối đa {$maxDaysAhead} ngày.",
            'shift.required' => 'Vui lòng chọn ca khám mới.',
            'shift.in' => 'Ca khám không hợp lệ (chỉ chấp nhận Sáng hoặc Chiều).',
            'reschedule_reason.max' => 'Lý do đổi lịch không được vượt quá 500 ký tự.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $appointmentDate = $this->input('appointment_date');
            $shift = $this->input('shift');

            if (!$appointmentDate || !$shift) {
                return;
            }

            $carbonDate = Carbon::parse($appointmentDate);
            if ($carbonDate->isToday()) {
                $nowTime = Carbon::now()->format('H:i');
                if ($shift === 'morning' && $nowTime >= '11:00') {
                    $validator->errors()->add('shift', 'Ca sáng ngày hôm nay đã kết thúc thời gian tiếp nhận (sau 11:00). Vui lòng chọn ca chiều hoặc ngày tiếp theo.');
                } elseif ($shift === 'afternoon' && $nowTime >= '16:30') {
                    $validator->errors()->add('shift', 'Ca chiều ngày hôm nay đã kết thúc thời gian tiếp nhận (sau 16:30). Vui lòng chọn ngày làm việc tiếp theo.');
                }
            }
        });
    }
}
