<?php

namespace Tests\Feature;

use App\Models\PatientProfile;
use App\Models\User;
use Tests\TestCase;

class PatientWristbandTest extends TestCase
{
    private User $receptionist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionist = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);
    }

    public function test_receptionist_can_print_patient_medical_wristband(): void
    {
        $patient = PatientProfile::create([
            'patient_code' => 'BN-BAND-' . rand(10000, 99999),
            'full_name' => 'Trần Thị Thu Thảo',
            'dob' => '1995-11-20',
            'gender' => 'female',
            'phone' => '098' . rand(1000000, 9999999),
            'blood_group' => 'AB+',
            'allergies' => 'Dị ứng Aspirin & NSAID',
        ]);

        $response = $this->actingAs($this->receptionist)
            ->get(route('reception.patients.print-wristband', $patient));

        $response->assertOk();
        $response->assertSee('Trần Thị Thu Thảo');
        $response->assertSee($patient->patient_code);
        $response->assertSee('Máu: AB+');
        $response->assertSee('DỊ ỨNG');
        $response->assertSee('Dị ứng Aspirin & NSAID');
        $response->assertSee('250mm');
    }
}
