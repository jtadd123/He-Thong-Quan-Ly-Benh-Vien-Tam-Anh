<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttachmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the medical attachment.
     */
    public function view(User $user, Attachment $attachment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($attachment->uploaded_by_user_id === $user->id) {
            return true;
        }

        $record = $attachment->medicalRecord;
        if (! $record) {
            return false;
        }

        // Doctor who examined the record
        if ($user->isDoctor() && $user->doctor && $user->doctor->id === $record->doctor_id) {
            return true;
        }

        // Patient who owns the profile
        if ($user->isPatient() && $user->patientProfile && $user->patientProfile->id === $record->patient_profile_id) {
            return true;
        }

        // Receptionist can view for administrative check
        if ($user->isReceptionist()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can download the attachment.
     */
    public function download(User $user, Attachment $attachment): bool
    {
        // 1. Quản trị viên (Admin) luôn có quyền tải
        if ($user->isAdmin()) {
            return true;
        }

        $record = $attachment->medicalRecord;
        if (!$record) {
            return $attachment->uploaded_by_user_id === $user->id;
        }

        // 2. Bác sĩ điều trị trực tiếp phụ trách ca khám
        if ($user->isDoctor() && $user->doctor && $user->doctor->id === $record->doctor_id) {
            return true;
        }

        // 3. Chính bệnh nhân sở hữu hồ sơ bệnh án này
        if ($user->isPatient() && $user->patientProfile && $user->patientProfile->id === $record->patient_profile_id) {
            return true;
        }

        // 4. Người trực tiếp tải lên tệp này
        if ($attachment->uploaded_by_user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the attachment.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $attachment->uploaded_by_user_id === $user->id;
    }
}
