<?php

namespace App\Http\Controllers\Medical;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\MedicalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Upload and securely store a medical attachment file.
     */
    public function upload(Request $request, MedicalRecord $record): RedirectResponse
    {
        $user = Auth::user();

        // Kiểm tra quyền: Chỉ Bác sĩ điều trị hoặc Quản trị viên mới được upload tệp vào bệnh án
        if (!$user->isAdmin()) {
            if (!$user->isDoctor() || !$user->doctor || $user->doctor->id !== $record->doctor_id) {
                abort(403, 'Bạn không có quyền đính kèm tệp tài liệu vào hồ sơ bệnh án này.');
            }
        }

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:3072', // Tối đa 3MB (3072 KB)
            ],
            'file_type' => [
                'required',
                'in:lab_result,x_ray,prescription_scan',
            ],
        ], [
            'file.required' => 'Vui lòng chọn tệp tin cần tải lên.',
            'file.mimes' => 'Định dạng tệp không hợp lệ. Chỉ chấp nhận tệp JPG, JPEG, PNG hoặc PDF.',
            'file.max' => 'Dung lượng tệp vượt quá giới hạn tối đa cho phép (3MB).',
            'file_type.required' => 'Vui lòng chọn phân loại tài liệu y tế.',
            'file_type.in' => 'Phân loại tài liệu y tế không hợp lệ.',
        ]);

        $uploadedFile = $request->file('file');
        $extension = $uploadedFile->getClientOriginalExtension();
        $safeHashedName = Str::random(40) . '.' . $extension;

        // Lưu vào thư mục riêng tư storage/app/private/medical_records (disk 'local')
        $storedPath = $uploadedFile->storeAs('medical_records', $safeHashedName, 'local');

        $attachment = Attachment::create([
            'medical_record_id' => $record->id,
            'uploaded_by_user_id' => $user->id,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'file_path' => $storedPath,
            'file_size' => $uploadedFile->getSize(),
            'file_mime' => $uploadedFile->getMimeType() ?? 'application/octet-stream',
            'file_type' => $request->file_type,
        ]);

        return redirect()->back()
            ->with('success', "Đã tải lên tài liệu y tế [{$attachment->original_name}] thành công!");
    }

    /**
     * Securely download or view an attachment with Authorization Policy check.
     */
    public function download(Attachment $attachment): StreamedResponse
    {
        // Kiểm tra phân quyền an toàn thông qua AttachmentPolicy
        if (!Gate::allows('download', $attachment)) {
            abort(403, 'Bạn không có quyền truy cập hoặc tải tệp tài liệu y tế bảo mật này.');
        }

        if (!Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'Tệp tin y tế không tồn tại trên hệ thống lưu trữ.');
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->original_name);
    }
}

