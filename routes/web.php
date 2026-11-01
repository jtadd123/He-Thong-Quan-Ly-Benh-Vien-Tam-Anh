<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DoctorManagementController;
use App\Http\Controllers\Admin\SpecialtyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Doctor\ClinicalConsultationController;
use App\Http\Controllers\Doctor\DoctorConsultationController;
use App\Http\Controllers\Doctor\DoctorQueueController;
use App\Http\Controllers\Doctor\MedicalRecordController;
use App\Http\Controllers\Medical\AttachmentController;
use App\Http\Controllers\Patient\PatientPortalController;
use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\DeliveryTrackingController;
use App\Http\Controllers\Public\DoctorPublicController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\KioskController;
use App\Http\Controllers\Public\PatientJourneyController;
use App\Http\Controllers\Public\PatientSurveyController;
use App\Http\Controllers\Receptionist\AmbulanceFleetController;
use App\Http\Controllers\Receptionist\BloodTransfusionController;
use App\Http\Controllers\Receptionist\ChronicRecallController;
use App\Http\Controllers\Receptionist\ClinicalNutritionController;
use App\Http\Controllers\Receptionist\CodeRedEmergencyController;
use App\Http\Controllers\Receptionist\CorporateScreeningController;
use App\Http\Controllers\Receptionist\DayBedController;
use App\Http\Controllers\Receptionist\DaySurgeryController;
use App\Http\Controllers\Receptionist\EmergencyCustodyController;
use App\Http\Controllers\Receptionist\InformedConsentController;
use App\Http\Controllers\Receptionist\InsuranceTransferController;
use App\Http\Controllers\Receptionist\LabOrderController;
use App\Http\Controllers\Receptionist\MedicalIncidentController;
use App\Http\Controllers\Receptionist\MedicalRecordRequestController;
use App\Http\Controllers\Receptionist\MedicationDeliveryController;
use App\Http\Controllers\Receptionist\MedicationScheduleController;
use App\Http\Controllers\Receptionist\MobilityEquipmentController;
use App\Http\Controllers\Receptionist\PatientController;
use App\Http\Controllers\Receptionist\PatientInvoiceController;
use App\Http\Controllers\Receptionist\PatientVitalController;
use App\Http\Controllers\Receptionist\PatientWalletController;
use App\Http\Controllers\Receptionist\PharmacyController;
use App\Http\Controllers\Receptionist\PharmacyInventoryController;
use App\Http\Controllers\Receptionist\PrivateInsuranceController;
use App\Http\Controllers\Receptionist\ReceptionAnalyticsController;
use App\Http\Controllers\Receptionist\ReceptionAppointmentController;
use App\Http\Controllers\Receptionist\ReceptionNotificationController;
use App\Http\Controllers\Receptionist\ReceptionSurveyController;
use App\Http\Controllers\Receptionist\SickLeaveCertificateController;
use App\Http\Controllers\Receptionist\SocialWorkController;
use App\Http\Controllers\Receptionist\VaccinationController;
use App\Http\Controllers\Receptionist\VisitorPassController;
use App\Http\Controllers\Receptionist\News2RapidResponseController;
use App\Http\Controllers\Receptionist\ClinicalRiskAssessmentController;
use App\Http\Controllers\Receptionist\NarcoticBoxController;
use App\Http\Controllers\Receptionist\OccupationalExposureController;
use App\Http\Controllers\Receptionist\ColdChainController;
use App\Http\Controllers\Receptionist\CssdSurgicalTrayController;
use App\Http\Controllers\Receptionist\HemodialysisController;
use App\Http\Controllers\Receptionist\SmartBedBoardController;
use App\Http\Controllers\Receptionist\HospitalWayfindingController;
use App\Http\Controllers\Receptionist\PreVisitScreeningController;
use App\Http\Controllers\Receptionist\InsuranceScrubberController;
use App\Http\Controllers\Receptionist\LivingWillOrganDonationController;
use App\Http\Controllers\OperatingRoomSuiteController;
use App\Http\Controllers\ClinicalAiDiagnosticsController;
use App\Http\Controllers\BiomedicalAndInfectionController;
use App\Http\Controllers\MaternityInfantOncologyController;
use App\Http\Controllers\AdvancedClinicalPharmacyController;
use App\Http\Controllers\RehabTraditionalEsgController;
use App\Http\Controllers\AiClinicalDecisionController;
use App\Http\Controllers\AdvancedSpecialtyController;
use App\Http\Controllers\HospitalOperationsController;
use App\Http\Controllers\FinanceHrController;
use App\Http\Controllers\PatientExperienceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Hệ Thống Quản Lý Bệnh Viện Tâm Anh
| Phân chia theo quy tắc Git Flow & Tránh xung đột tại Mục 11.2 Kế hoạch
|--------------------------------------------------------------------------
*/

// Trang chủ công khai (Landing Page) & Giới thiệu
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');

// =========================================================================
// ====== MODULE 1: AUTH & BOOKING (Phụ trách: NGUYỄN THÀNH QUÝ) ===========
// =========================================================================

// 1.1. Xác thực người dùng (Auth)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// 1.2. Booking Engine (Đặt lịch khám bệnh trực tuyến)
Route::prefix('booking')->group(function () {
    Route::get('/', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/success/{code}', [BookingController::class, 'success'])->name('booking.success');
    Route::get('/lookup', [BookingController::class, 'lookup'])->name('booking.lookup')->middleware('throttle:30,1');

    // AJAX API routes for dynamic dropdown and overbooking slot checking
    Route::get('/doctors-by-specialty/{specialtyId}', [BookingController::class, 'getDoctorsBySpecialty'])->name('booking.doctors_by_specialty');
    Route::get('/doctor-slots', [BookingController::class, 'getDoctorSlots'])->name('booking.doctor_slots');
});

// 1.3. Smart Kiosk Tự Phục Vụ Tại Cửa Sảnh (Self-Service Check-in & Queue Ticket)
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [KioskController::class, 'index'])->name('index');
    Route::post('/checkin', [KioskController::class, 'checkinAppointment'])->name('checkin');
    Route::post('/lookup', [KioskController::class, 'lookupPatient'])->name('lookup');
    Route::post('/register-returning', [KioskController::class, 'registerReturningPatient'])->name('register-returning');
    Route::post('/register-new', [KioskController::class, 'registerNewPatient'])->name('register-new');
    Route::get('/ticket/{appointment}', [KioskController::class, 'printTicket'])->name('ticket');
});

// Cổng Tra Cứu Viện Phí Điện Tử & Thanh Toán VietQR Tự Động (Public Smart Billing Lookup)
Route::get('/billing/lookup', [PatientInvoiceController::class, 'publicLookup'])->name('public.billing.lookup');

// Cổng Theo Dõi Hành Trình Khám Trực Tuyến Thời Gian Thực (Smart Patient Journey Live Tracker)
Route::get('/journey/{code}', [PatientJourneyController::class, 'show'])->name('public.journey.show');

// Cổng Tra Cứu Giao Thuốc Tận Nhà Trực Tuyến (Public Medication Delivery Tracking)
Route::get('/delivery/{tracking_code}', [DeliveryTrackingController::class, 'show'])->name('public.delivery.tracking');

// Khảo Sát Đánh Giá Trải Nghiệm & Hài Lòng Người Bệnh Chuẩn Bộ Y Tế (MOH 83-Criteria CSAT & NPS)
Route::get('/survey/{appointment_code}', [PatientSurveyController::class, 'create'])->name('public.survey.create');
Route::post('/survey', [PatientSurveyController::class, 'store'])->name('public.survey.store');
Route::get('/survey-thankyou', [PatientSurveyController::class, 'thankyou'])->name('public.survey.thankyou');

// 1.3. Quản lý Tài khoản Người dùng (Admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
});

// 1.4. Patient Portal (Lịch sử khám & Kết quả của Bệnh nhân)
Route::middleware(['auth', 'role:patient'])->prefix('patient')->name('patient.')->group(function () {
    Route::get('/history', [PatientPortalController::class, 'history'])->name('history');
    Route::get('/appointments/{appointment}', [PatientPortalController::class, 'showAppointment'])->name('appointments.show');
    Route::get('/appointments/{appointment}/prescription/print', [PatientPortalController::class, 'printPrescription'])->name('appointments.prescription.print');
    Route::get('/certificates/{certificate}/print', [SickLeaveCertificateController::class, 'print'])->name('certificates.print');
    Route::put('/appointments/{appointment}/cancel', [PatientPortalController::class, 'cancelAppointment'])->name('appointments.cancel');
});


// =========================================================================
// ====== MODULE 2: SPECIALTIES & DOCTORS (Phụ trách: NGUYỄN VĂN ĐẠT) ======
// =========================================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Quản lý Chuyên khoa (CRUD & toggle status)
    Route::patch('/specialties/{specialty}/toggle-status', [SpecialtyController::class, 'toggleStatus'])->name('specialties.toggle-status');
    Route::resource('specialties', SpecialtyController::class);

    // Quản lý Bác sĩ & Lịch trực tuần
    Route::get('/doctors/{doctor}/schedule', [DoctorManagementController::class, 'schedule'])->name('doctors.schedule');
    Route::put('/doctors/{doctor}/schedule', [DoctorManagementController::class, 'updateSchedule'])->name('doctors.schedule.update');
    Route::patch('/doctors/{doctor}/toggle-availability', [DoctorManagementController::class, 'toggleAvailability'])->name('doctors.toggle-availability');
    Route::resource('doctors', DoctorManagementController::class);
});

// Cổng thông tin công chúng: Danh bạ Bác sĩ
Route::prefix('doctors')->name('public.doctors.')->group(function () {
    Route::get('/', [DoctorPublicController::class, 'index'])->name('index');
    Route::get('/{doctor}', [DoctorPublicController::class, 'show'])->name('show');
});


// =========================================================================
// ====== MODULE 3: RECEPTION & PATIENTS (Phụ trách: NGUYỄN VĂN HƯNG) ======
// =========================================================================
Route::middleware(['auth', 'role:receptionist,admin'])->prefix('reception')->name('reception.')->group(function () {
    // 3.1. Bàn tiếp nhận, Check-in & Tiếp nhận vãng lai tại quầy
    Route::get('/appointments', [ReceptionAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/export', [ReceptionAppointmentController::class, 'export'])->name('appointments.export');
    Route::get('/appointments/queue-display', [ReceptionAppointmentController::class, 'queueDisplay'])->name('appointments.queue-display');
    Route::get('/appointments/walk-in', [ReceptionAppointmentController::class, 'createWalkin'])->name('appointments.walkin');
    Route::post('/appointments/walk-in', [ReceptionAppointmentController::class, 'storeWalkin'])->name('appointments.store-walkin');
    Route::get('/appointments/{appointment}/print', [ReceptionAppointmentController::class, 'printTicket'])->name('appointments.print');
    Route::get('/appointments/{appointment}/receipt', [ReceptionAppointmentController::class, 'printReceipt'])->name('appointments.receipt');
    Route::put('/appointments/{appointment}/checkin', [ReceptionAppointmentController::class, 'checkin'])->name('appointments.checkin');
    Route::put('/appointments/{appointment}/cancel', [ReceptionAppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::put('/appointments/{appointment}/reassign', [ReceptionAppointmentController::class, 'reassign'])->name('appointments.reassign');
    Route::post('/appointments/{appointment}/call-audio', [ReceptionAppointmentController::class, 'callAudio'])->name('appointments.call-audio');

    // 3.2. Quản lý Hồ sơ Bệnh nhân (CRUD)
    Route::get('/patients/{patient}/print-card', [PatientController::class, 'printCard'])->name('patients.print-card');
    Route::get('/patients/{patient}/print-wristband', [PatientController::class, 'printWristband'])->name('patients.print-wristband');
    Route::post('/patients/{patient}/vitals', [PatientVitalController::class, 'store'])->name('patients.vitals.store');
    Route::delete('/vitals/{vital}', [PatientVitalController::class, 'destroy'])->name('vitals.destroy');
    Route::resource('patients', PatientController::class);

    // 3.3. Quầy Dược Ngoại Trú & Cấp Phát Thuốc (Outpatient Pharmacy Dispensing)
    Route::get('/pharmacy', [PharmacyController::class, 'index'])->name('pharmacy.index');
    Route::post('/pharmacy/{record}/dispense', [PharmacyController::class, 'dispense'])->name('pharmacy.dispense');
    Route::post('/pharmacy/{record}/undo', [PharmacyController::class, 'undoDispense'])->name('pharmacy.undo');
    Route::post('/pharmacy/{record}/call-audio', [PharmacyController::class, 'callAudio'])->name('pharmacy.call-audio');
    Route::get('/pharmacy/{record}/label', [PharmacyController::class, 'printLabel'])->name('pharmacy.label');
    Route::get('/pharmacy/{record}/bottle-labels', [PharmacyController::class, 'printBottleLabels'])->name('pharmacy.bottle-labels');
    Route::get('/pharmacy/prescriptions/{prescription}/bottle-label', [PharmacyController::class, 'printSingleBottleLabel'])->name('pharmacy.single-bottle-label');
    Route::get('/pharmacy/{record}/schedule', [MedicationScheduleController::class, 'show'])->name('pharmacy.schedule');
    Route::get('/pharmacy/{record}/schedule/ical', [MedicationScheduleController::class, 'downloadIcal'])->name('pharmacy.schedule.ical');

    // 3.3b. Quản lý Kho Dược & Danh Mục Thuốc Ngoại Trú (Pharmacy Formulary & Inventory)
    Route::get('/pharmacy/inventory', [PharmacyInventoryController::class, 'index'])->name('pharmacy.inventory.index');
    Route::post('/pharmacy/inventory', [PharmacyInventoryController::class, 'store'])->name('pharmacy.inventory.store');
    Route::put('/pharmacy/inventory/{medication}', [PharmacyInventoryController::class, 'update'])->name('pharmacy.inventory.update');
    Route::post('/pharmacy/inventory/{medication}/restock', [PharmacyInventoryController::class, 'restock'])->name('pharmacy.inventory.restock');
    Route::delete('/pharmacy/inventory/{medication}', [PharmacyInventoryController::class, 'destroy'])->name('pharmacy.inventory.destroy');

    // 3.4. Giấy Chứng Nhận Nghỉ Việc Hưởng BHXH (Mẫu C65-HD Bộ Y Tế)
    Route::get('/certificates', [SickLeaveCertificateController::class, 'index'])->name('certificates.index');
    Route::get('/records/{record}/certificate', [SickLeaveCertificateController::class, 'create'])->name('certificates.create');
    Route::post('/records/{record}/certificate', [SickLeaveCertificateController::class, 'store'])->name('certificates.store');
    Route::get('/certificates/{certificate}/print', [SickLeaveCertificateController::class, 'print'])->name('certificates.print');
    Route::delete('/certificates/{certificate}', [SickLeaveCertificateController::class, 'destroy'])->name('certificates.destroy');

    // 3.5. Bản Cam Đoan Phẫu Thuật / Thủ Thuật Y Khoa Ký Chữ Ký Số Điện Tử (E-Informed Consent)
    Route::get('/consents', [InformedConsentController::class, 'index'])->name('consents.index');
    Route::get('/patients/{patient}/consents/create', [InformedConsentController::class, 'create'])->name('consents.create');
    Route::post('/consents', [InformedConsentController::class, 'store'])->name('consents.store');
    Route::get('/consents/{consent}/print', [InformedConsentController::class, 'print'])->name('consents.print');
    Route::delete('/consents/{consent}', [InformedConsentController::class, 'destroy'])->name('consents.destroy');

    // 3.6. Quản Lý Hóa Đơn Viện Phí & Thanh Toán VietQR Tự Động (Smart Billing)
    Route::get('/invoices', [PatientInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [PatientInvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/appointments/{appointment}/create-invoice', [PatientInvoiceController::class, 'createFromAppointment'])->name('invoices.create-from-appointment');
    Route::post('/invoices/{invoice}/confirm-payment', [PatientInvoiceController::class, 'confirmPayment'])->name('invoices.confirm-payment');
    Route::get('/invoices/{invoice}/print', [PatientInvoiceController::class, 'print'])->name('invoices.print');

    // 3.7. Trung Tâm Giám Sát Hiệu Suất Tiếp Nhận & An Toàn Dược (BI Analytics & Operations)
    Route::get('/analytics', [ReceptionAnalyticsController::class, 'index'])->name('analytics.index');

    // 3.8. Sơ Đồ Giường Ngoại Trú & Khu Lưu Bệnh Ban Ngày (Day-Bed Management)
    Route::get('/day-beds', [DayBedController::class, 'index'])->name('day-beds.index');
    Route::post('/day-beds/admit', [DayBedController::class, 'storeAdmission'])->name('day-beds.admit');
    Route::get('/day-beds/admissions/{admission}', [DayBedController::class, 'showAdmission'])->name('day-beds.show');
    Route::post('/day-beds/admissions/{admission}/infusion', [DayBedController::class, 'storeInfusion'])->name('day-beds.infusion.store');
    Route::post('/day-beds/infusions/{infusion}/complete', [DayBedController::class, 'completeInfusion'])->name('day-beds.infusion.complete');
    Route::post('/day-beds/admissions/{admission}/discharge', [DayBedController::class, 'discharge'])->name('day-beds.discharge');
    Route::post('/day-beds/{bed}/cleaning-done', [DayBedController::class, 'finishCleaning'])->name('day-beds.cleaning-done');

    // 3.9. Trung Tâm Thông Báo Zalo ZNS / SMS Brandname & Cảnh Báo Sắp Đến Lượt
    Route::get('/notifications', [ReceptionNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/appointments/{appointment}/alert', [ReceptionNotificationController::class, 'dispatchAlert'])->name('notifications.alert');
    Route::post('/appointments/{appointment}/prescription-alert', [ReceptionNotificationController::class, 'dispatchPrescriptionAlert'])->name('notifications.prescription-alert');

    // 3.10. Trung Tâm Tiêm Chủng Ngoại Trú (Vaccination Management)
    Route::get('/vaccinations', [VaccinationController::class, 'index'])->name('vaccinations.index');
    Route::get('/vaccinations/create', [VaccinationController::class, 'create'])->name('vaccinations.create');
    Route::post('/vaccinations', [VaccinationController::class, 'store'])->name('vaccinations.store');
    Route::get('/vaccinations/{record}', [VaccinationController::class, 'show'])->name('vaccinations.show');
    Route::post('/vaccinations/{record}/clear', [VaccinationController::class, 'clearObservation'])->name('vaccinations.clear');
    Route::get('/vaccinations/{record}/certificate', [VaccinationController::class, 'printCertificate'])->name('vaccinations.certificate');

    // 3.11. Tiếp Nhận Cấp Cứu Trước Viện & Báo Động Đỏ Toàn Viện (Code Red Emergency)
    Route::get('/code-red', [CodeRedEmergencyController::class, 'index'])->name('code-red.index');
    Route::post('/code-red', [CodeRedEmergencyController::class, 'store'])->name('code-red.store');
    Route::post('/code-red/{codeRed}/mark-arrived', [CodeRedEmergencyController::class, 'markArrived'])->name('code-red.mark-arrived');
    Route::post('/code-red/{codeRed}/mark-intervention', [CodeRedEmergencyController::class, 'markIntervention'])->name('code-red.mark-intervention');
    Route::post('/code-red/{codeRed}/resolve', [CodeRedEmergencyController::class, 'resolve'])->name('code-red.resolve');

    // 3.12. Giao Thuốc Ngoại Trú Tận Nhà & Cấp Thuốc Bệnh Mãn Tính (Medication Delivery)
    Route::get('/deliveries', [MedicationDeliveryController::class, 'index'])->name('deliveries.index');
    Route::post('/deliveries', [MedicationDeliveryController::class, 'store'])->name('deliveries.store');
    Route::post('/deliveries/{delivery}/status', [MedicationDeliveryController::class, 'updateStatus'])->name('deliveries.status');
    Route::get('/deliveries/{delivery}/waybill', [MedicationDeliveryController::class, 'printWaybill'])->name('deliveries.waybill');

    // 3.13. Khảo Sát Đánh Giá Trải Nghiệm & Hài Lòng Bộ Y Tế (Patient CSAT & NPS)
    Route::get('/surveys', [ReceptionSurveyController::class, 'index'])->name('surveys.index');
    Route::post('/surveys/{survey}/resolve', [ReceptionSurveyController::class, 'resolveFollowup'])->name('surveys.resolve');

    // 3.14. Trung Tâm Cận Lâm Sàng & Xét Nghiệm Y Khoa (Lab & Imaging Orders)
    Route::get('/lab-orders', [LabOrderController::class, 'index'])->name('lab-orders.index');
    Route::get('/lab-orders/create', [LabOrderController::class, 'create'])->name('lab-orders.create');
    Route::post('/lab-orders', [LabOrderController::class, 'store'])->name('lab-orders.store');
    Route::get('/lab-orders/{labOrder}', [LabOrderController::class, 'show'])->name('lab-orders.show');
    Route::post('/lab-orders/items/{item}/update', [LabOrderController::class, 'updateItemResult'])->name('lab-orders.items.update');
    Route::get('/lab-orders/{labOrder}/report', [LabOrderController::class, 'printReport'])->name('lab-orders.report');

    // 3.15. Kiểm Soát An Toàn Truyền Máu Lâm Sàng (Blood Transfusion Safety)
    Route::get('/blood-transfusions', [BloodTransfusionController::class, 'index'])->name('blood-transfusions.index');
    Route::get('/blood-transfusions/create', [BloodTransfusionController::class, 'create'])->name('blood-transfusions.create');
    Route::post('/blood-transfusions', [BloodTransfusionController::class, 'store'])->name('blood-transfusions.store');
    Route::get('/blood-transfusions/{transfusion}', [BloodTransfusionController::class, 'show'])->name('blood-transfusions.show');
    Route::post('/blood-transfusions/{transfusion}/start', [BloodTransfusionController::class, 'start'])->name('blood-transfusions.start');
    Route::post('/blood-transfusions/{transfusion}/vitals', [BloodTransfusionController::class, 'recordVitals'])->name('blood-transfusions.vitals');
    Route::post('/blood-transfusions/{transfusion}/complete', [BloodTransfusionController::class, 'complete'])->name('blood-transfusions.complete');
    Route::post('/blood-transfusions/{transfusion}/reaction', [BloodTransfusionController::class, 'reaction'])->name('blood-transfusions.reaction');

    // 3.16. Quản Lý Mượn Trả Xe Lăn & Khử Khuẩn Thiết Bị Di Chuyển (Mobility Equipment)
    Route::get('/mobility', [MobilityEquipmentController::class, 'index'])->name('mobility.index');
    Route::post('/mobility/borrow', [MobilityEquipmentController::class, 'borrow'])->name('mobility.borrow');
    Route::post('/mobility/return/{loan}', [MobilityEquipmentController::class, 'return'])->name('mobility.return');
    Route::post('/mobility/disinfect/{equipment}', [MobilityEquipmentController::class, 'completeDisinfection'])->name('mobility.disinfect');

    // 3.17. Khoa Dinh Dưỡng Lâm Sàng & Thực Đơn Điều Trị (Clinical Nutrition)
    Route::get('/nutrition', [ClinicalNutritionController::class, 'index'])->name('nutrition.index');
    Route::get('/nutrition/create', [ClinicalNutritionController::class, 'create'])->name('nutrition.create');
    Route::post('/nutrition', [ClinicalNutritionController::class, 'store'])->name('nutrition.store');
    Route::get('/nutrition/{assessment}', [ClinicalNutritionController::class, 'show'])->name('nutrition.show');
    Route::get('/nutrition/{assessment}/print-plan', [ClinicalNutritionController::class, 'printPlan'])->name('nutrition.print-plan');

    // 3.18. Giấy Chuyển Tuyến BHYT Điện Tử (Mẫu 40/BYT)
    Route::get('/insurance-transfers', [InsuranceTransferController::class, 'index'])->name('insurance-transfers.index');
    Route::get('/insurance-transfers/create', [InsuranceTransferController::class, 'create'])->name('insurance-transfers.create');
    Route::post('/insurance-transfers', [InsuranceTransferController::class, 'store'])->name('insurance-transfers.store');
    Route::get('/insurance-transfers/{insuranceTransfer}', [InsuranceTransferController::class, 'show'])->name('insurance-transfers.show');
    Route::get('/insurance-transfers/{insuranceTransfer}/print', [InsuranceTransferController::class, 'print'])->name('insurance-transfers.print');

    // 3.19. Ví Ký Quỹ & Tạm Ứng Viện Phí Thông Minh
    Route::get('/wallets', [PatientWalletController::class, 'index'])->name('wallets.index');
    Route::get('/wallets/{patientWallet}', [PatientWalletController::class, 'show'])->name('wallets.show');
    Route::post('/patients/{patient}/wallets/deposit', [PatientWalletController::class, 'deposit'])->name('patients.wallets.deposit');
    Route::post('/wallets/{patientWallet}/refund', [PatientWalletController::class, 'refund'])->name('wallets.refund');

    // 3.20. Bảo Lãnh Viện Phí Tư Nhân TPA & Tiếp Đón VIP (GOP)
    Route::get('/private-insurance', [PrivateInsuranceController::class, 'index'])->name('private-insurance.index');
    Route::get('/private-insurance/create', [PrivateInsuranceController::class, 'create'])->name('private-insurance.create');
    Route::post('/private-insurance', [PrivateInsuranceController::class, 'store'])->name('private-insurance.store');
    Route::get('/private-insurance/{privateInsurance}', [PrivateInsuranceController::class, 'show'])->name('private-insurance.show');
    Route::get('/private-insurance/{privateInsurance}/print', [PrivateInsuranceController::class, 'print'])->name('private-insurance.print');

    // 3.21. Cổng Đề Nghị Sao Lục Bệnh Án Điện Tử (E-Archive Release)
    Route::get('/record-requests', [MedicalRecordRequestController::class, 'index'])->name('record-requests.index');
    Route::get('/record-requests/create', [MedicalRecordRequestController::class, 'create'])->name('record-requests.create');
    Route::post('/record-requests', [MedicalRecordRequestController::class, 'store'])->name('record-requests.store');
    Route::get('/record-requests/{recordRequest}', [MedicalRecordRequestController::class, 'show'])->name('record-requests.show');
    Route::patch('/record-requests/{recordRequest}/status', [MedicalRecordRequestController::class, 'updateStatus'])->name('record-requests.status');
    Route::get('/record-requests/{recordRequest}/print', [MedicalRecordRequestController::class, 'print'])->name('record-requests.print');

    // 3.22. Niêm Phong Tài Sản Cấp Cứu & Đồ Thất Lạc Tại Sảnh
    Route::get('/emergency-custody', [EmergencyCustodyController::class, 'index'])->name('emergency-custody.index');
    Route::post('/emergency-custody', [EmergencyCustodyController::class, 'storeCustody'])->name('emergency-custody.store');
    Route::patch('/emergency-custody/{custody}/return', [EmergencyCustodyController::class, 'returnCustody'])->name('emergency-custody.return');
    Route::get('/emergency-custody/{custody}/print', [EmergencyCustodyController::class, 'printCustody'])->name('emergency-custody.print');
    Route::post('/lost-found', [EmergencyCustodyController::class, 'storeLostFound'])->name('lost-found.store');
    Route::patch('/lost-found/{item}/claim', [EmergencyCustodyController::class, 'claimLostFound'])->name('lost-found.claim');

    // 3.23. Thẻ Thăm Bệnh Điện Tử & Kiểm Soát Nhiễm Khuẩn (Visitor Pass)
    Route::get('/visitor-passes', [VisitorPassController::class, 'index'])->name('visitor-passes.index');
    Route::post('/visitor-passes', [VisitorPassController::class, 'store'])->name('visitor-passes.store');
    Route::patch('/visitor-passes/{visitorPass}/checkout', [VisitorPassController::class, 'checkout'])->name('visitor-passes.checkout');
    Route::get('/visitor-passes/{visitorPass}/print', [VisitorPassController::class, 'print'])->name('visitor-passes.print');

    // 3.24. Báo Cáo Sự Cố Y Khoa Suýt Xảy Ra (Near-Miss Incident)
    Route::get('/medical-incidents', [MedicalIncidentController::class, 'index'])->name('medical-incidents.index');
    Route::get('/medical-incidents/create', [MedicalIncidentController::class, 'create'])->name('medical-incidents.create');
    Route::post('/medical-incidents', [MedicalIncidentController::class, 'store'])->name('medical-incidents.store');
    Route::get('/medical-incidents/{medicalIncident}', [MedicalIncidentController::class, 'show'])->name('medical-incidents.show');
    Route::patch('/medical-incidents/{medicalIncident}/rca', [MedicalIncidentController::class, 'updateRca'])->name('medical-incidents.rca');

    // 3.25. Công Tác Xã Hội, Miễn Giảm Viện Phí & Thông Dịch Viên Y Khoa
    Route::get('/social-work', [SocialWorkController::class, 'index'])->name('social-work.index');
    Route::post('/social-work/cases', [SocialWorkController::class, 'storeSocialCase'])->name('social-work.store');
    Route::patch('/social-work/cases/{case}/approve', [SocialWorkController::class, 'approveSocialCase'])->name('social-work.approve');
    Route::post('/interpreters', [SocialWorkController::class, 'storeInterpreter'])->name('interpreters.store');
    Route::patch('/interpreters/{interpreter}/complete', [SocialWorkController::class, 'completeInterpreter'])->name('interpreters.complete');

    // 3.26. Điều Phối Đội Xe Cấp Cứu Ngoại Viện
    Route::get('/ambulances', [AmbulanceFleetController::class, 'index'])->name('ambulances.index');
    Route::post('/ambulances/missions', [AmbulanceFleetController::class, 'storeMission'])->name('ambulances.store');
    Route::patch('/ambulances/missions/{mission}/status', [AmbulanceFleetController::class, 'updateStatus'])->name('ambulances.status');

    // 3.27. Khám Sức Khỏe Doanh Nghiệp (Thông tư 32/2023/TT-BYT)
    Route::get('/corporate-screening', [CorporateScreeningController::class, 'index'])->name('corporate-screening.index');
    Route::post('/corporate-screening/contracts', [CorporateScreeningController::class, 'storeContract'])->name('corporate-screening.store');
    Route::get('/corporate-screening/contracts/{contract}', [CorporateScreeningController::class, 'show'])->name('corporate-screening.show');
    Route::post('/corporate-screening/contracts/{contract}/employees', [CorporateScreeningController::class, 'addEmployee'])->name('corporate-screening.employees');
    Route::patch('/corporate-screening/employees/{employee}', [CorporateScreeningController::class, 'updateScreening'])->name('corporate-screening.screening');

    // 3.28. Tiếp Nhận Tiền Phẫu & Phẫu Thuật Trong Ngày (WHO Checklist)
    Route::get('/day-surgeries', [DaySurgeryController::class, 'index'])->name('day-surgeries.index');
    Route::post('/day-surgeries', [DaySurgeryController::class, 'store'])->name('day-surgeries.store');
    Route::patch('/day-surgeries/{surgery}/checklist', [DaySurgeryController::class, 'updateChecklist'])->name('day-surgeries.checklist');
    Route::patch('/day-surgeries/{surgery}/status', [DaySurgeryController::class, 'updateStatus'])->name('day-surgeries.status');

    // 3.29. Tái Khám Tự Động & Quản Lý Bệnh Mãn Tính (Recall Automation)
    Route::get('/chronic-recalls', [ChronicRecallController::class, 'index'])->name('chronic-recalls.index');
    Route::post('/chronic-recalls', [ChronicRecallController::class, 'store'])->name('chronic-recalls.store');
    Route::post('/chronic-recalls/{recall}/remind', [ChronicRecallController::class, 'sendReminder'])->name('chronic-recalls.remind');
    Route::patch('/chronic-recalls/{recall}/attended', [ChronicRecallController::class, 'markAttended'])->name('chronic-recalls.attended');

    // 3.30. Cảnh Báo Sớm Sinh Hiệu NEWS2 & Đội Phản Ứng Nhanh (RRT)
    Route::get('/news2-rrt', [News2RapidResponseController::class, 'index'])->name('news2-rrt.index');
    Route::post('/news2-rrt', [News2RapidResponseController::class, 'store'])->name('news2-rrt.store');

    // 3.31. Đánh Giá Nguy Cơ An Toàn Điều Dưỡng (Braden & Morse Scale)
    Route::get('/risk-assessments', [ClinicalRiskAssessmentController::class, 'index'])->name('risk-assessments.index');
    Route::post('/risk-assessments', [ClinicalRiskAssessmentController::class, 'store'])->name('risk-assessments.store');

    // 3.32. Tủ Thuốc Trực Cấp Cứu & Quản Lý Thuốc Gây Nghiện (Thông tư 20/2017/TT-BYT)
    Route::get('/narcotics', [NarcoticBoxController::class, 'index'])->name('narcotics.index');
    Route::post('/narcotics/dispense', [NarcoticBoxController::class, 'dispense'])->name('narcotics.dispense');
    Route::patch('/narcotics/verify-disposal/{log}', [NarcoticBoxController::class, 'verifyDisposal'])->name('narcotics.verify-disposal');

    // 3.33. Giám Sát Tai Nạn Kim Tiêm & Phơi Nhiễm Nghề Nghiệp (Sharps Injury & PEP)
    Route::get('/occupational-exposure', [OccupationalExposureController::class, 'index'])->name('occupational-exposure.index');
    Route::post('/occupational-exposure', [OccupationalExposureController::class, 'store'])->name('occupational-exposure.store');

    // 3.34. Chuỗi Cung Ứng Lạnh Vắc-Xin & Sinh Phẩm Y Tế IoT (Cold Chain Continuous GSP)
    Route::get('/cold-chain', [ColdChainController::class, 'index'])->name('cold-chain.index');
    Route::post('/cold-chain/telemetry', [ColdChainController::class, 'recordTelemetry'])->name('cold-chain.telemetry');

    // 3.35. Quản Lý Tiệt Khuẩn Dụng Cụ Mổ Trung Tâm CSSD & Khay Phẫu Thuật
    Route::get('/cssd', [CssdSurgicalTrayController::class, 'index'])->name('cssd.index');
    Route::post('/cssd/run-cycle', [CssdSurgicalTrayController::class, 'runCycle'])->name('cssd.run-cycle');

    // 3.36. Lọc Máu Thận Nhân Tạo Chu Kỳ & Tái Sử Dụng Quả Lọc
    Route::get('/hemodialysis', [HemodialysisController::class, 'index'])->name('hemodialysis.index');
    Route::post('/hemodialysis', [HemodialysisController::class, 'store'])->name('hemodialysis.store');
    Route::patch('/hemodialysis/{session}/complete', [HemodialysisController::class, 'complete'])->name('hemodialysis.complete');

    // 3.37. Quản Lý Buồng Bệnh, Khử Khuẩn & Điều Phối Giường Trống (Smart Bed Board)
    Route::get('/bed-board', [SmartBedBoardController::class, 'index'])->name('bed-board.index');
    Route::post('/bed-board/assign/{bed}', [SmartBedBoardController::class, 'assignPatient'])->name('bed-board.assign');
    Route::post('/bed-board/discharge/{bed}', [SmartBedBoardController::class, 'dischargePatient'])->name('bed-board.discharge');
    Route::post('/bed-board/clean/{bed}', [SmartBedBoardController::class, 'cleanBed'])->name('bed-board.clean');

    // 3.38. Bản Đồ Điều Hướng Trong Nhà 3D/SVG Tìm Phòng Khám (Indoor Wayfinding)
    Route::get('/wayfinding', [HospitalWayfindingController::class, 'index'])->name('wayfinding.index');
    Route::post('/wayfinding', [HospitalWayfindingController::class, 'store'])->name('wayfinding.store');

    // 3.39. Cổng Tự Khai Báo Tiền Sử & Triệu Chứng Từ Xa Trước Khám (Pre-Visit Screener)
    Route::get('/pre-visit', [PreVisitScreeningController::class, 'index'])->name('pre-visit.index');
    Route::post('/pre-visit', [PreVisitScreeningController::class, 'store'])->name('pre-visit.store');

    // 3.40. Giám Định BHYT Thời Gian Thực Chống Xuất Toán & Cổng Dữ Liệu XML 4210/BYT
    Route::get('/claim-scrubber', [InsuranceScrubberController::class, 'index'])->name('claim-scrubber.index');
    Route::post('/claim-scrubber/scrub', [InsuranceScrubberController::class, 'scrub'])->name('claim-scrubber.scrub');

    // 3.41. Đăng Ký Hiến Tạng Nhân Đạo & Di Chúc Y Tế Giảm Nhẹ (Living Will / DNR)
    Route::get('/living-will', [LivingWillOrganDonationController::class, 'index'])->name('living-will.index');
    Route::post('/living-will', [LivingWillOrganDonationController::class, 'store'])->name('living-will.store');

    // 3.42. Phòng Mổ Thông Minh & Hồi Tỉnh PACU (Smart OR & Aldrete Score)
    Route::get('/operating-room', [OperatingRoomSuiteController::class, 'index'])->name('operating-room.index');
    Route::post('/operating-room/case', [OperatingRoomSuiteController::class, 'storeCase'])->name('operating-room.store-case');
    Route::patch('/operating-room/case/{case}/phase', [OperatingRoomSuiteController::class, 'updatePhase'])->name('operating-room.update-phase');
    Route::post('/operating-room/case/{case}/sponge-count', [OperatingRoomSuiteController::class, 'recordSpongeCount'])->name('operating-room.sponge-count');
    Route::post('/operating-room/case/{case}/aldrete-score', [OperatingRoomSuiteController::class, 'recordAldreteScore'])->name('operating-room.aldrete-score');

    // 3.43. AI Lâm Sàng, Đọc Phim PACS Khẩn Cấp & Sepsis qSOFA
    Route::get('/clinical-ai', [ClinicalAiDiagnosticsController::class, 'index'])->name('clinical-ai.index');
    Route::post('/clinical-ai/pacs', [ClinicalAiDiagnosticsController::class, 'storePacsAnalysis'])->name('clinical-ai.pacs');
    Route::post('/clinical-ai/sepsis', [ClinicalAiDiagnosticsController::class, 'recordSepsisQsofa'])->name('clinical-ai.sepsis');
    Route::post('/clinical-ai/pharmacogenomic', [ClinicalAiDiagnosticsController::class, 'storePharmacogenomic'])->name('clinical-ai.pharmacogenomic');
    Route::post('/clinical-ai/transfusion-reaction', [ClinicalAiDiagnosticsController::class, 'reportTransfusionReaction'])->name('clinical-ai.transfusion-reaction');

    // 3.44. Kỹ Thuật Y Sinh CMMS NĐ 98/2021 & Kiểm Soát Nhiễm Khuẩn (HAI - ASP)
    Route::get('/biomedical-infection', [BiomedicalAndInfectionController::class, 'index'])->name('biomedical-infection.index');
    Route::post('/biomedical-infection/equipment', [BiomedicalAndInfectionController::class, 'storeEquipment'])->name('biomedical-infection.equipment');
    Route::post('/biomedical-infection/repair-sos', [BiomedicalAndInfectionController::class, 'reportRepairSos'])->name('biomedical-infection.repair-sos');
    Route::post('/biomedical-infection/hai', [BiomedicalAndInfectionController::class, 'recordHaiInfection'])->name('biomedical-infection.hai');
    Route::post('/biomedical-infection/asp', [BiomedicalAndInfectionController::class, 'approveAntimicrobial'])->name('biomedical-infection.asp');

    // 3.45. Sản - Nhi An Toàn RFID, Ung Bướu Tumor Board & Hộp Đen Cấp Cứu Giờ Vàng
    Route::get('/maternity-oncology', [MaternityInfantOncologyController::class, 'index'])->name('maternity-oncology.index');
    Route::post('/maternity-oncology/partograph', [MaternityInfantOncologyController::class, 'storePartograph'])->name('maternity-oncology.partograph');
    Route::post('/maternity-oncology/infant-tag', [MaternityInfantOncologyController::class, 'pairInfantTag'])->name('maternity-oncology.infant-tag');
    Route::post('/maternity-oncology/chemo-cycle', [MaternityInfantOncologyController::class, 'recordChemoCycle'])->name('maternity-oncology.chemo-cycle');
    Route::post('/maternity-oncology/tumor-board', [MaternityInfantOncologyController::class, 'storeTumorBoard'])->name('maternity-oncology.tumor-board');
    Route::post('/maternity-oncology/golden-hour', [MaternityInfantOncologyController::class, 'recordGoldenHour'])->name('maternity-oncology.golden-hour');

    // 3.46. Dược Lâm Sàng Chuyên Sâu, Đo Nồng Độ Thuốc TDM & Dinh Dưỡng Tĩnh Mạch TPN
    Route::get('/clinical-pharmacy', [AdvancedClinicalPharmacyController::class, 'index'])->name('clinical-pharmacy.index');
    Route::post('/clinical-pharmacy/tdm', [AdvancedClinicalPharmacyController::class, 'storeTdm'])->name('clinical-pharmacy.tdm');
    Route::post('/clinical-pharmacy/tpn', [AdvancedClinicalPharmacyController::class, 'storeTpn'])->name('clinical-pharmacy.tpn');
    Route::post('/clinical-pharmacy/adr', [AdvancedClinicalPharmacyController::class, 'storeAdr'])->name('clinical-pharmacy.adr');

    // 3.47. Phục Hồi Chức Năng (FIM), Đông Y Thập Bát Phản & Bệnh Viện Xanh ESG
    Route::get('/rehab-esg', [RehabTraditionalEsgController::class, 'index'])->name('rehab-esg.index');
    Route::post('/rehab-esg/rehab-plan', [RehabTraditionalEsgController::class, 'storeRehabPlan'])->name('rehab-esg.rehab-plan');
    Route::post('/rehab-esg/herbal', [RehabTraditionalEsgController::class, 'storeHerbalPrescription'])->name('rehab-esg.herbal');
    Route::post('/rehab-esg/acupoint', [RehabTraditionalEsgController::class, 'storeAcupointTherapy'])->name('rehab-esg.acupoint');
    Route::post('/rehab-esg/jaundice', [RehabTraditionalEsgController::class, 'storeBhutaniEvaluation'])->name('rehab-esg.jaundice');
    Route::post('/rehab-esg/milk-batch', [RehabTraditionalEsgController::class, 'storeMilkBatch'])->name('rehab-esg.milk-batch');
    Route::post('/rehab-esg/radiation', [RehabTraditionalEsgController::class, 'storeRadiationLog'])->name('rehab-esg.radiation');

    // 3.48. AI & Quyết Định Lâm Sàng Thông Minh (Sepsis AI, Rules Engine, ICD-10, Med Rec, Fluid Balance, Anticoagulation)
    Route::get('/ai-clinical-decision', [AiClinicalDecisionController::class, 'index'])->name('ai-clinical-decision.index');
    Route::post('/ai-clinical-decision/sepsis', [AiClinicalDecisionController::class, 'storeSepsis'])->name('ai-clinical-decision.sepsis');
    Route::post('/ai-clinical-decision/clinical-rule', [AiClinicalDecisionController::class, 'storeClinicalRule'])->name('ai-clinical-decision.clinical-rule');
    Route::post('/ai-clinical-decision/icd10', [AiClinicalDecisionController::class, 'storeIcd10'])->name('ai-clinical-decision.icd10');
    Route::post('/ai-clinical-decision/med-reconciliation', [AiClinicalDecisionController::class, 'storeMedReconciliation'])->name('ai-clinical-decision.med-reconciliation');
    Route::post('/ai-clinical-decision/fluid-balance', [AiClinicalDecisionController::class, 'storeFluidBalance'])->name('ai-clinical-decision.fluid-balance');
    Route::post('/ai-clinical-decision/anticoagulation', [AiClinicalDecisionController::class, 'storeAnticoagulation'])->name('ai-clinical-decision.anticoagulation');

    // 3.49. Quản Lý Lâm Sàng Chuyên Khoa Sâu (Stroke Pathway, STEMI, Anesthesia, Ventilator, Wound, Pain, NICU, Mental, Liver, Diabetic Foot)
    Route::get('/advanced-specialty', [AdvancedSpecialtyController::class, 'index'])->name('advanced-specialty.index');
    Route::post('/advanced-specialty/stroke', [AdvancedSpecialtyController::class, 'storeStroke'])->name('advanced-specialty.stroke');
    Route::post('/advanced-specialty/stemi', [AdvancedSpecialtyController::class, 'storeStemi'])->name('advanced-specialty.stemi');
    Route::post('/advanced-specialty/anesthesia', [AdvancedSpecialtyController::class, 'storeAnesthesia'])->name('advanced-specialty.anesthesia');
    Route::post('/advanced-specialty/ventilator', [AdvancedSpecialtyController::class, 'storeVentilator'])->name('advanced-specialty.ventilator');
    Route::post('/advanced-specialty/wound-care', [AdvancedSpecialtyController::class, 'storeWoundCare'])->name('advanced-specialty.wound-care');
    Route::post('/advanced-specialty/pain', [AdvancedSpecialtyController::class, 'storePainAssessment'])->name('advanced-specialty.pain');
    Route::post('/advanced-specialty/nicu', [AdvancedSpecialtyController::class, 'storeNicu'])->name('advanced-specialty.nicu');
    Route::post('/advanced-specialty/mental-health', [AdvancedSpecialtyController::class, 'storeMentalHealth'])->name('advanced-specialty.mental-health');
    Route::post('/advanced-specialty/liver-score', [AdvancedSpecialtyController::class, 'storeLiverScore'])->name('advanced-specialty.liver-score');
    Route::post('/advanced-specialty/diabetic-foot', [AdvancedSpecialtyController::class, 'storeDiabeticFoot'])->name('advanced-specialty.diabetic-foot');

    // 3.50. Vận Hành Bệnh Viện & Hậu Cần Thông Minh (OR Block, Porter Transport, Dietary Orders)
    Route::get('/hospital-operations', [HospitalOperationsController::class, 'index'])->name('hospital-operations.index');
    Route::post('/hospital-operations/or-schedule', [HospitalOperationsController::class, 'storeOrSchedule'])->name('hospital-operations.or-schedule');
    Route::post('/hospital-operations/porter-request', [HospitalOperationsController::class, 'storePorterRequest'])->name('hospital-operations.porter-request');
    Route::post('/hospital-operations/dietary-order', [HospitalOperationsController::class, 'storeDietaryOrder'])->name('hospital-operations.dietary-order');

    // 3.51. Tài Chính & Quản Trị Y Tế Nâng Cao (DRG Billing, Research Projects)
    Route::get('/finance-hr', [FinanceHrController::class, 'index'])->name('finance-hr.index');
    Route::post('/finance-hr/drg-billing', [FinanceHrController::class, 'storeDrgBilling'])->name('finance-hr.drg-billing');
    Route::post('/finance-hr/research-project', [FinanceHrController::class, 'storeResearchProject'])->name('finance-hr.research-project');

    // 3.52. Trải Nghiệm Bệnh Nhân & Chăm Sóc Số (Digital Twin, Telehealth, Discharge, Follow-up, Campaign, Loyalty, Chronic Cohort)
    Route::get('/patient-experience', [PatientExperienceController::class, 'index'])->name('patient-experience.index');
    Route::post('/patient-experience/digital-twin', [PatientExperienceController::class, 'storeDigitalTwin'])->name('patient-experience.digital-twin');
    Route::post('/patient-experience/telehealth', [PatientExperienceController::class, 'storeTelehealth'])->name('patient-experience.telehealth');
    Route::post('/patient-experience/discharge-checklist', [PatientExperienceController::class, 'storeDischargeChecklist'])->name('patient-experience.discharge-checklist');
    Route::post('/patient-experience/followup', [PatientExperienceController::class, 'storeFollowup'])->name('patient-experience.followup');
    Route::post('/patient-experience/screening-campaign', [PatientExperienceController::class, 'storeScreeningCampaign'])->name('patient-experience.screening-campaign');
    Route::post('/patient-experience/loyalty-points', [PatientExperienceController::class, 'storeLoyaltyPoints'])->name('patient-experience.loyalty-points');
    Route::post('/patient-experience/chronic-cohort', [PatientExperienceController::class, 'storeChronicCohort'])->name('patient-experience.chronic-cohort');
});

// Cổng Công Khai Theo Dõi Trạng Thái Phẫu Thuật Cho Người Thân Tại Sảnh Chờ
Route::get('/or-waiting-board', [OperatingRoomSuiteController::class, 'publicWaitingBoard'])->name('public.or-waiting-board');


// =========================================================================
// ====== MODULE 4: EXAMINATION & RECORDS (Phụ trách: VÕ LÊ GIA BẢO) =======
// =========================================================================
// =========================================================================
// ====== MODULE 4: EXAMINATION & RECORDS (Phụ trách: VÕ LÊ GIA BẢO) =======
// =========================================================================
// Hàng đợi phòng khám của Bác sĩ & Khám bệnh
Route::middleware(['auth', 'role:doctor,admin'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/queue', [DoctorQueueController::class, 'index'])->name('queue');
    Route::get('/examine/{appointment}', [DoctorConsultationController::class, 'examine'])->name('examine');
    Route::post('/examine/{appointment}', [DoctorConsultationController::class, 'store'])->name('examine.store');
    Route::post('/examine', [MedicalRecordController::class, 'store'])->name('records.store');
    Route::get('/records/{record}', [DoctorConsultationController::class, 'showRecord'])->name('records.show');
    Route::get('/records/{record}/certificate', [SickLeaveCertificateController::class, 'create'])->name('records.certificate.create');
    Route::post('/records/{record}/certificate', [SickLeaveCertificateController::class, 'store'])->name('records.certificate.store');
    Route::post('/records/{record}/attachments', [DoctorConsultationController::class, 'uploadAttachment'])->name('records.attachments.upload');
    Route::delete('/records/{record}/attachments/{attachment}', [DoctorConsultationController::class, 'destroyAttachment'])->name('records.attachments.destroy');
    Route::get('/records/{record}/print', [DoctorConsultationController::class, 'printPrescription'])->name('records.print');
});

// Nghiệp vụ Tệp y tế: Upload (Bác sĩ, Admin) & Download an toàn (Policy check cho Bệnh nhân, Bác sĩ, Admin)
Route::middleware(['auth'])->group(function () {
    Route::post('/medical-records/{record}/attachments', [AttachmentController::class, 'upload'])
        ->name('records.attachments.upload')
        ->middleware('role:doctor,admin');

    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');
});

// =========================================================================
// ====== MODULE 5: IN-HOSPITAL CLINICAL CONSULTATION =======================
// =========================================================================
Route::middleware(['auth'])->group(function () {
    Route::post('/consultations/{consultation}/respond', [ClinicalConsultationController::class, 'respond'])->name('consultations.respond');
    Route::get('/consultations/{consultation}/print', [ClinicalConsultationController::class, 'print'])->name('consultations.print');
    Route::resource('consultations', ClinicalConsultationController::class);
});
