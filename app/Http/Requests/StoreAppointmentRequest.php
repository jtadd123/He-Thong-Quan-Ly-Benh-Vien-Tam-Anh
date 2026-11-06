<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(0[3|5|7|8|9])[0-9]{8}$/'],
            'dob' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'identity_card' => ['nullable', 'string', 'max:20'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . $maxDate],
            'shift' => ['required', 'in:morning,afternoon'],
            'appointment_time' => ['nullable', 'date_format:H:i'],
            'symptom_description' => ['required', 'string', 'min:5', 'max:1000'],
            'join_waitlist' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        $maxDaysAhead = max(1, (int) \App\Models\SystemSetting::get('max_booking_days_ahead', 30));

        return [
            'full_name.required' => 'Vui lòng nhập họ và tên bệnh nhân.',
            'phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'phone.regex' => 'Số điện thoại không đúng định dạng di động Việt Nam (10 chữ số, bắt đầu bằng 03, 05, 07, 08, 09).',
            'dob.required' => 'Vui lòng chọn ngày tháng năm sinh.',
            'dob.before' => 'Ngày sinh phải ở trong quá khứ.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'specialty_id.required' => 'Vui lòng chọn chuyên khoa khám.',
            'specialty_id.exists' => 'Chuyên khoa đã chọn không tồn tại trên hệ thống.',
            'doctor_id.required' => 'Vui lòng chọn bác sĩ phụ trách.',
            'doctor_id.exists' => 'Bác sĩ đã chọn không tồn tại.',
            'appointment_date.required' => 'Vui lòng chọn ngày khám bệnh.',
            'appointment_date.after_or_equal' => 'Ngày khám không thể ở trong quá khứ.',
            'appointment_date.before_or_equal' => "Hệ thống chỉ tiếp nhận đặt lịch trước tối đa {$maxDaysAhead} ngày.",
            'shift.required' => 'Vui lòng chọn ca khám (Sáng hoặc Chiều).',
            'shift.in' => 'Ca khám chỉ có thể là Sáng (morning) hoặc Chiều (afternoon).',
            'appointment_time.date_format' => 'Giờ hẹn phải theo định dạng HH:mm (ví dụ 08:30).',
            'symptom_description.required' => 'Vui lòng mô tả sơ bộ triệu chứng hoặc lý do khám bệnh.',
            'symptom_description.min' => 'Mô tả triệu chứng cần chi tiết tối thiểu 5 ký tự.',
        ];
    }

    /**
     * Configure the validator instance with business logic gates.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $doctorId = $this->input('doctor_id');
            $specialtyId = $this->input('specialty_id');
            $appointmentDate = $this->input('appointment_date');
            $shift = $this->input('shift');

            if (!$doctorId || !$appointmentDate || !$shift) {
                return;
            }

            // 1. Check doctor belongs to specialty and is available
            $doctor = Doctor::with('user')->find($doctorId);
            if (!$doctor) {
                $validator->errors()->add('doctor_id', 'Bác sĩ không tồn tại.');
                return;
            }

            if ($doctor->specialty_id != $specialtyId) {
                $validator->errors()->add('doctor_id', 'Bác sĩ được chọn không thuộc chuyên khoa đã chọn.');
                return;
            }

            if (!$doctor->is_available) {
                $validator->errors()->add('doctor_id', 'Bác sĩ hiện đang tạm ngưng nhận lịch khám.');
                return;
            }

            // 2. Check real-time cutoff if appointment is booked for today
            $carbonDate = Carbon::parse($appointmentDate);
            if ($carbonDate->isToday()) {
                $nowTime = Carbon::now()->format('H:i');
                if ($shift === 'morning' && $nowTime >= '11:00') {
                    $validator->errors()->add('shift', 'Ca sáng ngày hôm nay đã kết thúc thời gian tiếp nhận đăng ký trực tuyến (sau 11:00). Vui lòng chọn ca chiều hoặc ngày làm việc tiếp theo.');
                    return;
                }
                if ($shift === 'afternoon' && $nowTime >= '16:30') {
                    $validator->errors()->add('shift', 'Ca chiều ngày hôm nay đã kết thúc thời gian tiếp nhận đăng ký trực tuyến (sau 16:30). Vui lòng chọn ngày làm việc tiếp theo.');
                    return;
                }
            }

            // 3. Check doctor works on that day of week and shift
            $dayOfWeek = strtolower($carbonDate->englishDayOfWeek); // monday, tuesday, etc.

            $schedule = DoctorSchedule::where('doctor_id', $doctorId)
                ->where('day_of_week', $dayOfWeek)
                ->where('shift', $shift)
                ->where('is_active', true)
                ->first();

            if (!$schedule) {
                $dayNameMap = [
                    'monday' => 'Thứ Hai', 'tuesday' => 'Thứ Ba', 'wednesday' => 'Thứ Tư',
                    'thursday' => 'Thứ Năm', 'friday' => 'Thứ Sáu', 'saturday' => 'Thứ Bảy', 'sunday' => 'Chủ Nhật'
                ];
                $dayVn = $dayNameMap[$dayOfWeek] ?? $dayOfWeek;
                $shiftVn = $shift === 'morning' ? 'buổi sáng' : 'buổi chiều';

                $validator->errors()->add('shift', "Bác sĩ {$doctor->full_name_with_title} không có lịch trực vào {$dayVn} ({$shiftVn}). Vui lòng chọn ca hoặc ngày khác.");
                return;
            }

            // 3. Overbooking Prevention Algorithm (with Smart Waitlist support)
            $maxPatients = $schedule->max_patients ?? 15;
            $currentAppointmentsCount = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $carbonDate->toDateString())
                ->where('shift', $shift)
                ->where('status', '!=', Appointment::STATUS_CANCELLED)
                ->where('status', '!=', 'waitlist')
                ->count();

            $canJoinWaitlist = \App\Models\SystemSetting::isTrue('enable_smart_waitlist', true)
                && $this->boolean('join_waitlist');

            if ($currentAppointmentsCount >= $maxPatients && !$canJoinWaitlist) {
                $shiftVn = $shift === 'morning' ? 'buổi sáng' : 'buổi chiều';
                $validator->errors()->add('shift', "Ca khám {$shiftVn} ngày {$carbonDate->format('d/m/Y')} của bác sĩ đã nhận đủ số lượng tối đa ({$maxPatients}/{$maxPatients} bệnh nhân). Vui lòng chọn ca khám hoặc ngày khác.");
            }
        });
    }
}
