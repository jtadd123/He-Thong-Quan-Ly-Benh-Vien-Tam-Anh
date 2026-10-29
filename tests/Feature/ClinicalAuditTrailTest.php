<?php

namespace Tests\Feature;

use App\Models\ClinicalAuditLog;
use App\Models\PatientProfile;
use App\Models\User;
use Tests\TestCase;

class ClinicalAuditTrailTest extends TestCase
{
    private User $receptionist;
    private PatientProfile $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionist = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->patient = PatientProfile::create([
            'patient_code' => 'BN-AUDIT-' . rand(10000, 99999),
            'full_name' => 'Nguyễn Văn Audit ' . rand(100, 999),
            'dob' => '1992-04-10',
            'gender' => 'male',
            'phone' => '093' . rand(1000000, 9999999),
            'blood_group' => 'A+',
        ]);
    }

    public function test_viewing_patient_profile_automatically_creates_audit_log(): void
    {
        $initialLogCount = ClinicalAuditLog::where('patient_profile_id', $this->patient->id)->count();

        $response = $this->actingAs($this->receptionist)
            ->get(route('reception.patients.show', $this->patient));

        $response->assertOk();
        $response->assertSee('Nhật Ký Truy Vết');

        $this->assertDatabaseHas('clinical_audit_logs', [
            'patient_profile_id' => $this->patient->id,
            'user_id' => $this->receptionist->id,
            'action' => 'view',
        ]);

        $this->assertGreaterThan($initialLogCount, ClinicalAuditLog::where('patient_profile_id', $this->patient->id)->count());
    }

    public function test_updating_safety_fields_creates_safety_audit_log_with_diff(): void
    {
        $payload = [
            'full_name' => $this->patient->full_name,
            'dob' => '1992-04-10',
            'gender' => 'male',
            'phone' => $this->patient->phone,
            'blood_group' => 'B+',
            'allergies' => 'Dị ứng nặng Penicillin và Amoxicillin',
            'medical_history' => 'Tăng huyết áp vô căn',
        ];

        $response = $this->actingAs($this->receptionist)
            ->put(route('reception.patients.update', $this->patient), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('clinical_audit_logs', [
            'patient_profile_id' => $this->patient->id,
            'user_id' => $this->receptionist->id,
            'action' => 'update_safety',
        ]);

        $log = ClinicalAuditLog::where('patient_profile_id', $this->patient->id)
            ->where('action', 'update_safety')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('A+', $log->old_values['blood_group'] ?? null);
        $this->assertEquals('B+', $log->new_values['blood_group'] ?? null);
    }
}
