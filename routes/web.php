<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AngkatanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BiometricEnrollmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FaceBiometricController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\IzinApprovalController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LoginBiometricController;
use App\Http\Controllers\LokasiSekolahController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\ParentActivationController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TeacherActivationController;
use App\Http\Controllers\TeacherViewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AcademicStructureController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\AcademicCalendarController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\HomeroomManagementController;
use App\Http\Controllers\AcademicPromotionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WhatsAppLogController;
use App\Http\Controllers\TeachingJournalController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PklController;
use App\Http\Controllers\DisciplineCounselingController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/home', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['guest:web'])->group(function () {
    Route::get('/', [AuthController::class, 'index'])->name('login');

    // BUG-012: login had no throttling at all, allowing unlimited password
    // guessing. 5 attempts per minute per IP+email is generous for a human and
    // hostile to brute force.
    Route::post('/proseslogin', [AuthController::class, 'proseslogin'])
        ->middleware('throttle:5,1')
        ->name('proseslogin');

    // =========================================================================
    // 2FA BIOMETRIC FACE VERIFICATION ON EVERY LOGIN
    // =========================================================================
    Route::get('/login/verify-face', [LoginBiometricController::class, 'showVerifyFace'])->name('login.verify_face');
    Route::post('/login/verify-face/challenge', [LoginBiometricController::class, 'challenge'])
        ->middleware('throttle:10,1')
        ->name('login.verify_face.challenge');
    Route::post('/login/verify-face/process', [LoginBiometricController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('login.verify_face.process');
    Route::get('/login/verify-face/cancel', [LoginBiometricController::class, 'cancel'])->name('login.verify_face.cancel');
});

Route::get('/aktivasi-orang-tua/{token}', [ParentActivationController::class, 'show'])->name('parent.activate.show');
Route::post('/aktivasi-orang-tua/{token}', [ParentActivationController::class, 'store'])->name('parent.activate.store');

Route::get('/aktivasi-guru/{token}', [TeacherActivationController::class, 'show'])->name('teacher.activate.show');
Route::post('/aktivasi-guru/{token}', [TeacherActivationController::class, 'store'])->name('teacher.activate.store');

Route::middleware(['auth:web', 'password.changed', 'biometric.enrolled'])->group(function () {
    // Shared Dashboard & Auth
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/proseslogout', [AuthController::class, 'proseslogout'])->name('proseslogout');

    // Profile (All Authenticated Users)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password');

    // =========================================================================
    // FIRST LOGIN BIOMETRIC FACE ENROLLMENT ROUTES (Siswa)
    // =========================================================================
    Route::get('/siswa/biometric-enrollment', [BiometricEnrollmentController::class, 'show'])->name('siswa.enrollment');
    Route::post('/siswa/biometric-enrollment/challenge', [BiometricEnrollmentController::class, 'challenge'])->name('siswa.enrollment.challenge');
    Route::post('/siswa/biometric-enrollment/process', [BiometricEnrollmentController::class, 'process'])->name('siswa.enrollment.process');

    // =========================================================================
    // SUPERADMIN ONLY ROUTES (System Settings, User Management, Critical Actions)
    // =========================================================================
    Route::middleware(['role:superadmin'])->group(function () {
        // Manajemen User (Kelola Akun Seluruh Role)
        Route::get('/user', [UserController::class, 'index'])->name('user');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::post('/user/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::post('/user/{id}', [UserController::class, 'update'])->name('user.update');
        Route::post('/user/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');

        // Hapus Data Absensi (Hanya Superadmin)
        Route::post('/dashboard/absensi/{id}/delete', [DashboardController::class, 'deleteAbsensi'])->name('dashboard.absensi.delete');

        // Reset Template Biometrik Wajah Siswa (Wajib Enroll Ulang)
        Route::post('/admin/biometrik-siswa/{id}/reset', [FaceBiometricController::class, 'adminReset'])->name('admin.biometrik.reset');
    });

    Route::middleware(['role:superadmin,admin,kaprog'])->group(function () {
        // Data Siswa & Import
        Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa');
        Route::get('/siswa/import', [StudentImportController::class, 'index'])->name('siswa.import');
        Route::get('/siswa/import/template', [StudentImportController::class, 'downloadTemplate'])->name('siswa.import.template');
        Route::post('/siswa/import/preview', [StudentImportController::class, 'preview'])->name('siswa.import.preview');
        Route::post('/siswa/import/confirm', [StudentImportController::class, 'confirm'])->name('siswa.import.confirm');
        Route::get('/siswa/import/batch/{id}/report', [StudentImportController::class, 'downloadReport'])->name('siswa.import.report');
        Route::get('/siswa/{id}', [SiswaController::class, 'show'])->name('siswa.show')->whereNumber('id');
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::post('/siswa/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::match(['post', 'put'], '/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::post('/siswa/delete/{id}', [SiswaController::class, 'destroy'])->name('siswa.delete');
        Route::get('/siswa/export-pdf', [SiswaController::class, 'exportPdf'])->name('siswa.export.pdf');
        Route::get('/siswa/export-excel', [SiswaController::class, 'exportExcel'])->name('siswa.export.excel');

        // Manajemen Wali Kelas (Homeroom Management)
        Route::get('/akademik/wali-kelas', [HomeroomManagementController::class, 'index'])->name('academic.wali-kelas.index');
        Route::post('/akademik/wali-kelas/assign', [HomeroomManagementController::class, 'assign'])->name('academic.wali-kelas.assign');
        Route::post('/akademik/wali-kelas/replace', [HomeroomManagementController::class, 'replace'])->name('academic.wali-kelas.replace');
        Route::post('/akademik/wali-kelas/terminate/{assignmentId}', [HomeroomManagementController::class, 'terminate'])->name('academic.wali-kelas.terminate');
        Route::get('/akademik/wali-kelas/history', [HomeroomManagementController::class, 'history'])->name('academic.wali-kelas.history');

        // Struktur Akademik & Ruangan
        Route::get('/qr-codes', [AbsensiController::class, 'qrCodes'])->name('absensi.qrcodes');
        Route::get('/struktur-akademik', [AcademicStructureController::class, 'index'])->name('academic.index');
        Route::get('/struktur-akademik/alumni/{angkatan}', [AcademicStructureController::class, 'alumniCohort'])->name('academic.alumni.show');
        Route::get('/struktur-akademik/alumni/{angkatan}/export-pdf', [AcademicStructureController::class, 'exportAlumniPdf'])->name('academic.alumni.export_pdf');
        Route::get('/struktur-akademik/{jurusan}', [AcademicStructureController::class, 'major'])->name('academic.major');
        Route::get('/struktur-akademik/{jurusan}/alumni/{angkatan}', [AcademicStructureController::class, 'majorAlumni'])->name('academic.major.alumni');
        Route::get('/struktur-akademik/{jurusan}/tingkat/{tingkat}', [AcademicStructureController::class, 'level'])->name('academic.level');
        Route::get('/struktur-akademik/rombel/{kelas}', [AcademicStructureController::class, 'roomClass'])->name('academic.class');
        Route::get('/alokasi-ruangan', [RoomController::class, 'assignments'])->name('rooms.assignments');
        Route::post('/alokasi-ruangan', [RoomController::class, 'storeAssignment'])->name('rooms.assignments.store');
        Route::post('/alokasi-ruangan/{assignment}/delete', [RoomController::class, 'destroyAssignment'])->name('rooms.assignments.destroy');
    });

    // =========================================================================
    // SUPERADMIN & ADMIN ROUTES (Master Data, Monitoring, QR Codes, Export)
    // =========================================================================
    Route::middleware(['role:superadmin,admin'])->group(function () {
        // Lokasi Sekolah & Radius Geofence GPS
        Route::get('/lokasi-sekolah', [LokasiSekolahController::class, 'index'])->name('lokasi_sekolah');
        Route::post('/lokasi-sekolah', [LokasiSekolahController::class, 'update'])->name('lokasi_sekolah.update');

        // Master Tahun Ajaran (Global Academic Cycles)
        Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('academic.tahun-ajaran.index');
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('academic.tahun-ajaran.store');
        Route::get('/tahun-ajaran/{id}/edit', [TahunAjaranController::class, 'edit'])->name('academic.tahun-ajaran.edit');
        Route::post('/tahun-ajaran/{id}', [TahunAjaranController::class, 'update'])->name('academic.tahun-ajaran.update');
        Route::post('/tahun-ajaran/{id}/activate', [TahunAjaranController::class, 'activate'])->name('academic.tahun-ajaran.activate');
        Route::post('/tahun-ajaran/{id}/switch-semester', [TahunAjaranController::class, 'switchSemester'])->name('academic.tahun-ajaran.switch-semester');
        Route::post('/tahun-ajaran/{id}/delete', [TahunAjaranController::class, 'destroy'])->name('academic.tahun-ajaran.destroy');

        // Bulk Class Promotion & Graduation Wizard (Phase 5)
        Route::get('/akademik/kenaikan-kelas', [AcademicPromotionController::class, 'index'])->name('academic.promotion.index');
        Route::get('/akademik/kenaikan-kelas/origin-classes', [AcademicPromotionController::class, 'getOriginClasses'])->name('academic.promotion.origin_classes');
        Route::post('/akademik/kenaikan-kelas/load-students', [AcademicPromotionController::class, 'loadStudents'])->name('academic.promotion.load_students');
        Route::get('/akademik/kenaikan-kelas/destination-classes', [AcademicPromotionController::class, 'getDestinationClasses'])->name('academic.promotion.destination_classes');
        Route::post('/akademik/kenaikan-kelas/replicate-classes', [AcademicPromotionController::class, 'replicateClasses'])->name('academic.promotion.replicate_classes');
        Route::post('/akademik/kenaikan-kelas/execute', [AcademicPromotionController::class, 'execute'])->name('academic.promotion.execute');
        Route::get('/akademik/kenaikan-kelas/riwayat', [AcademicPromotionController::class, 'history'])->name('academic.promotion.history');
        Route::get('/akademik/kenaikan-kelas/riwayat/{id}', [AcademicPromotionController::class, 'show'])->name('academic.promotion.show');

        // Pengaturan Sekolah (jam masuk / jam pulang) & Kalender Akademik
        Route::get('/pengaturan-sekolah', [SchoolSettingController::class, 'index'])->name('settings.index');
        Route::post('/pengaturan-sekolah', [SchoolSettingController::class, 'update'])->name('settings.update');

        Route::get('/kalender-akademik', [AcademicCalendarController::class, 'index'])->name('academic_calendar.index');
        Route::post('/kalender-akademik', [AcademicCalendarController::class, 'store'])->name('academic_calendar.store');
        Route::post('/kalender-akademik/{id}/toggle', [AcademicCalendarController::class, 'toggle'])->name('academic_calendar.toggle');
        Route::post('/kalender-akademik/{id}/delete', [AcademicCalendarController::class, 'destroy'])->name('academic_calendar.destroy');

        // Dashboard Operations
        Route::post('/dashboard/absensi/{id}/update', [DashboardController::class, 'updateAbsensi'])->name('dashboard.absensi.update');
        Route::get('/dashboard/export-pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export.pdf');
        Route::get('/dashboard/export-excel', [DashboardController::class, 'exportExcel'])->name('dashboard.export.excel');

        // Data Jurusan
        Route::get('/jurusan', [JurusanController::class, 'index'])->name('jurusan');
        Route::post('/jurusan', [JurusanController::class, 'store'])->name('jurusan.store');
        Route::post('/jurusan/edit', [JurusanController::class, 'edit'])->name('jurusan.edit');
        Route::post('/jurusan/{id}', [JurusanController::class, 'update'])->name('jurusan.update');
        Route::post('/jurusan/delete/{id}', [JurusanController::class, 'destroy'])->name('jurusan.delete');

        // Data Kelas
        Route::get('/kelas', [KelasController::class, 'index'])->name('kelas');
        Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::post('/kelas/edit', [KelasController::class, 'edit'])->name('kelas.edit');
        Route::post('/kelas/{id}', [KelasController::class, 'update'])->name('kelas.update');
        Route::post('/kelas/delete/{id}', [KelasController::class, 'destroy'])->name('kelas.delete');

        // Manajemen Angkatan & Status Kelulusan
        Route::get('/angkatan', [AngkatanController::class, 'index'])->name('angkatan.index');
        Route::post('/angkatan', [AngkatanController::class, 'store'])->name('angkatan.store');
        Route::get('/angkatan/{id}', [AngkatanController::class, 'show'])->name('angkatan.show');
        Route::post('/angkatan/edit', [AngkatanController::class, 'edit'])->name('angkatan.edit');
        Route::post('/angkatan/{id}', [AngkatanController::class, 'update'])->name('angkatan.update');
        Route::post('/angkatan/delete/{id}', [AngkatanController::class, 'destroy'])->name('angkatan.destroy');
        Route::get('/angkatan/{id}/stats', [AngkatanController::class, 'stats'])->name('angkatan.stats');

        // Data Guru & Teacher Management Center
        Route::get('/guru', [GuruController::class, 'index'])->name('guru');
        Route::post('/guru', [GuruController::class, 'store'])->name('guru.store');
        Route::get('/guru/beban-kerja', [GuruController::class, 'workload'])->name('guru.workload');
        Route::get('/guru/{id}', [GuruController::class, 'show'])->name('guru.show');
        Route::post('/guru/edit', [GuruController::class, 'edit'])->name('guru.edit');
        Route::post('/guru/{id}', [GuruController::class, 'update'])->name('guru.update');
        Route::post('/guru/{id}/status', [GuruController::class, 'updateStatus'])->name('guru.status');
        Route::post('/guru/{id}/activation-link', [GuruController::class, 'regenerateActivation'])->name('guru.activation_link');
        Route::post('/guru/{id}/assign-subject', [GuruController::class, 'assignSubject'])->name('guru.assign_subject');
        Route::post('/guru/assignment-subject/{assignmentId}/delete', [GuruController::class, 'removeSubjectAssignment'])->name('guru.remove_subject_assignment');
        Route::post('/guru/{id}/assign-homeroom', [GuruController::class, 'assignHomeroom'])->name('guru.assign_homeroom');
        Route::post('/guru/assignment-homeroom/{assignmentId}/delete', [GuruController::class, 'removeHomeroomAssignment'])->name('guru.remove_homeroom_assignment');
        Route::post('/guru/{id}/assign-kaprog', [GuruController::class, 'assignKaprog'])->name('guru.assign_kaprog');
        Route::post('/guru/{id}/remove-kaprog', [GuruController::class, 'removeKaprog'])->name('guru.remove_kaprog');
        Route::post('/guru/delete/{id}', [GuruController::class, 'destroy'])->name('guru.delete');

        // Jadwal Pelajaran
        Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal');
        Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
        Route::post('/jadwal/edit', [JadwalController::class, 'edit'])->name('jadwal.edit');
        Route::post('/jadwal/{id}', [JadwalController::class, 'update'])->name('jadwal.update');
        Route::post('/jadwal/delete/{id}', [JadwalController::class, 'destroy'])->name('jadwal.delete');

        // Orang Tua
        Route::get('/orangtua', [OrangTuaController::class, 'index'])->name('orangtua');
        Route::post('/orangtua.store', [OrangTuaController::class, 'store'])->name('orangtua.store');
        Route::post('/orangtua/edit', [OrangTuaController::class, 'edit'])->name('orangtua.edit');
        Route::post('/orangtua/update/{id}', [OrangTuaController::class, 'update'])->name('orangtua.update');
        Route::post('/orangtua/delete/{id}', [OrangTuaController::class, 'destroy'])->name('orangtua.delete');

        // Ruangan Fisik
        Route::get('/ruangan', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/ruangan', [RoomController::class, 'store'])->name('rooms.store');
        Route::post('/ruangan/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::post('/ruangan/{room}/delete', [RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::post('/ruangan/{room}/regenerate-qr', [RoomController::class, 'regenerate'])->name('rooms.regenerate');

        // Biometrik Actions (Superadmin & Admin)
        Route::post('/admin/biometrik-siswa/{id}/request-re-enroll', [FaceBiometricController::class, 'adminRequestReEnroll'])->name('admin.biometrik.re_enroll');
        Route::post('/admin/biometrik-siswa/{id}/toggle-suspend', [FaceBiometricController::class, 'adminToggleSuspend'])->name('admin.biometrik.toggle_suspend');

        // Monitoring WhatsApp Gateway
        Route::get('/admin/whatsapp-logs', [WhatsAppLogController::class, 'index'])->name('admin.whatsapp.logs');
        Route::post('/admin/whatsapp-logs/{id}/retry', [WhatsAppLogController::class, 'retry'])->name('admin.whatsapp.retry');
    });

    // Pendaftaran Biometrik Terpandu Sekolah / Lab Komputer & Monitoring (Superadmin, Admin, Kaprog, Guru)
    Route::middleware(['role:superadmin,admin,kaprog,guru'])->group(function () {
        Route::get('/admin/biometrik-siswa', [FaceBiometricController::class, 'adminIndex'])->name('admin.biometrik');
        Route::get('/admin/biometrik-siswa/{id}/assisted-enroll', [FaceBiometricController::class, 'assistedEnroll'])->name('admin.biometrik.assisted_enroll');
        Route::post('/admin/biometrik-siswa/{id}/assisted-enroll/challenge', [FaceBiometricController::class, 'assistedChallenge'])->name('admin.biometrik.assisted_challenge');
        Route::post('/admin/biometrik-siswa/{id}/assisted-enroll/process', [FaceBiometricController::class, 'processAssistedEnroll'])->name('admin.biometrik.assisted_process');
        Route::post('/admin/biometrik-siswa/manual-override', [FaceBiometricController::class, 'manualOverride'])->name('admin.biometrik.override');
        Route::post('/admin/biometrik-siswa/{id}/reset-devices', [DeviceController::class, 'adminResetDevices'])->name('admin.biometrik.reset_devices');
    });

    // =========================================================================
    // GURU ROUTES (Rekap Kelas, Absensi Harian, Personal Teaching & Homeroom)
    // =========================================================================
    Route::middleware(['role:guru,superadmin,admin,kaprog'])->group(function () {
        Route::get('/absensi-siswa/harian', [AbsensiController::class, 'absensiHarian'])->name('absensi.harian');
        Route::post('/absensi-siswa/harian/update', [AbsensiController::class, 'updateHarian'])->name('absensi.harian.update');
        Route::get('/rekap-absensi', [AbsensiController::class, 'rekapAbsensi'])->name('rekapabsensi');
        Route::get('/rekap-kelas', [AbsensiController::class, 'rekapPerKelas'])->name('rekapkelas');

        // Personal Teaching & Homeroom View Routes
        Route::get('/guru-jadwal-saya', [TeacherViewController::class, 'mySchedule'])->name('teacher.schedule');
        Route::get('/guru-kelas', [TeacherViewController::class, 'myClasses'])->name('teacher.classes');
        Route::get('/guru-kelas/{kelas}', [TeacherViewController::class, 'showClassStudents'])->name('teacher.class.students');
        Route::get('/wali-kelas/monitoring', [TeacherViewController::class, 'homeroomMonitoring'])->name('homeroom.monitoring');

        // Jurnal Mengajar Harian Guru & Presensi Sesi Mapel
        Route::get('/jurnal-mengajar', [TeachingJournalController::class, 'index'])->name('jurnal.index');
        Route::get('/jurnal-mengajar/tambah', [TeachingJournalController::class, 'create'])->name('jurnal.create');
        Route::post('/jurnal-mengajar', [TeachingJournalController::class, 'store'])->name('jurnal.store');
        Route::get('/jurnal-mengajar/{id}', [TeachingJournalController::class, 'show'])->name('jurnal.show');
        Route::delete('/jurnal-mengajar/{id}', [TeachingJournalController::class, 'destroy'])->name('jurnal.destroy');
        Route::get('/monitoring-jurnal', [TeachingJournalController::class, 'adminMonitoring'])->name('jurnal.monitoring');

        // Buku Nilai & Penilaian Akademik (Gradebook)
        Route::get('/buku-nilai', [AssessmentController::class, 'index'])->name('gradebook.index');
        Route::get('/buku-nilai/buat', [AssessmentController::class, 'create'])->name('gradebook.create');
        Route::post('/buku-nilai', [AssessmentController::class, 'store'])->name('gradebook.store');
        Route::get('/buku-nilai/{id}/input', [AssessmentController::class, 'inputScores'])->name('gradebook.input');
        Route::post('/buku-nilai/{id}/input', [AssessmentController::class, 'saveScores'])->name('gradebook.save');
        Route::post('/buku-nilai/{id}/publish', [AssessmentController::class, 'publish'])->name('gradebook.publish');
        Route::delete('/buku-nilai/{id}', [AssessmentController::class, 'destroy'])->name('gradebook.destroy');
    });

    // =========================================================================
    // SISWA & ORANG TUA ROUTES (Absensi, Liveness, Riwayat, Izin)
    // =========================================================================
    // Parent accounts use the read-only dashboard; student-only attendance actions
    // are deliberately unavailable to parent/guardian credentials.
    Route::middleware(['role:siswa'])->group(function () {
        Route::get('/absensi-siswa', [AbsensiController::class, 'index'])->name('absensi');
        Route::post('/absensi-siswa/validate-qr', [AbsensiController::class, 'validateQr'])->name('absensi.validateqr');
        Route::post('/absensi-siswa/challenge', [FaceBiometricController::class, 'challenge'])->name('absensi.challenge');
        Route::post('/absensi-siswa/attendance-challenge', [AbsensiController::class, 'attendanceChallenge'])->name('absensi.attendance_challenge');
        Route::post('/absensi-siswa/store', [AbsensiController::class, 'store'])->name('absensi.store');
        Route::get('/riwayat-kehadiran', [AbsensiController::class, 'riwayatKehadiran'])->name('riwayatkehadiran');

        // Registrasi Biometrik Wajah Mandiri (Alternative Route)
        Route::get('/absensi-siswa/registrasi-wajah', [FaceBiometricController::class, 'showRegistration'])->name('absensi.face.register.show');
        Route::post('/absensi-siswa/registrasi-wajah', [FaceBiometricController::class, 'register'])->name('absensi.face.register');

        // Izin / Sakit
        Route::get('/izin-siswa', [IzinController::class, 'index'])->name('izin');
        Route::post('/izin-siswa', [IzinController::class, 'store'])->name('izin.store');
        Route::post('/izin-siswa/{id}/cancel', [IzinController::class, 'cancel'])->name('izin.cancel');

        // Manajemen Perangkat Siswa (Device Limit Max 2)
        Route::get('/perangkat-saya', [DeviceController::class, 'index'])->name('siswa.devices');
        Route::post('/perangkat-saya/revoke/{id}', [DeviceController::class, 'revoke'])->name('siswa.devices.revoke');

        // Nilai Akademik Siswa
        Route::get('/nilai-saya', [AssessmentController::class, 'studentGradebook'])->name('gradebook.student');
    });

    Route::middleware(['role:orangtua'])->group(function () {
        Route::get('/nilai-anak', [AssessmentController::class, 'parentGradebook'])->name('gradebook.parent');
    });

    // =========================================================================
    // IN-APP NOTIFICATION CENTER (All Authenticated Users)
    // =========================================================================
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::post('/notifikasi/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark_read');
    Route::post('/notifikasi/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
    Route::post('/notifikasi/clear-read', [NotificationController::class, 'clearRead'])->name('notifications.clear_read');
    Route::delete('/notifikasi/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // =========================================================================
    // INVENTARIS & PEMINJAMAN ALAT BENGKEL/LAB
    // =========================================================================
    // Katalog alat & daftar peminjaman pribadi (Semua pengguna terotentikasi)
    Route::get('/inventaris', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/peminjaman-alat', [InventoryController::class, 'borrowingsIndex'])->name('inventory.borrowings');
    Route::post('/peminjaman-alat/ajukan', [InventoryController::class, 'requestBorrow'])->name('inventory.borrow.request');

    // Manajemen Aset & Verifikasi Peminjaman/Pengembalian (Superadmin, Admin, Kaprog, Guru)
    Route::middleware(['role:superadmin,admin,kaprog,guru'])->group(function () {
        Route::get('/inventaris/buat', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventaris', [InventoryController::class, 'store'])->name('inventory.store');
        Route::delete('/inventaris/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::post('/peminjaman-alat/{id}/approve', [InventoryController::class, 'approveBorrow'])->name('inventory.borrow.approve');
        Route::post('/peminjaman-alat/{id}/return', [InventoryController::class, 'returnItem'])->name('inventory.borrow.return');
    });

    // Detail Aset (Ditempatkan setelah /inventaris/buat untuk mencegah routing collision {id}='buat')
    Route::get('/inventaris/{id}', [InventoryController::class, 'show'])->name('inventory.show');

    // =========================================================================
    // PKL / PRAKTIK KERJA LAPANGAN & MAGANG INDUSTRI
    // =========================================================================
    // Pengelolaan Mitra, Penempatan & Monitoring (Superadmin, Admin, Kaprog, Guru Pembimbing)
    Route::middleware(['role:superadmin,admin,kaprog,guru'])->group(function () {
        Route::get('/pkl/mitra-industri', [PklController::class, 'partnersIndex'])->name('pkl.partners.index');
        Route::post('/pkl/mitra-industri', [PklController::class, 'partnerStore'])->name('pkl.partners.store');
        Route::get('/pkl/penempatan', [PklController::class, 'placementsIndex'])->name('pkl.placements.index');
        Route::post('/pkl/penempatan', [PklController::class, 'placementStore'])->name('pkl.placements.store');
        Route::get('/pkl/monitoring', [PklController::class, 'monitoringDashboard'])->name('pkl.monitoring');
        Route::get('/pkl/verifikasi-logbook', [PklController::class, 'advisorLogbookIndex'])->name('pkl.advisor.logbooks');
        Route::post('/pkl/logbook/{id}/review', [PklController::class, 'advisorLogbookReview'])->name('pkl.advisor.review');
    });

    // Portal PKL Siswa (Khusus Siswa yang melaksanakan PKL)
    Route::middleware(['role:siswa'])->group(function () {
        Route::get('/pkl/portal-siswa', [PklController::class, 'studentPortal'])->name('pkl.student.portal');
        Route::post('/pkl/presensi', [PklController::class, 'studentCheckIn'])->name('pkl.student.checkin');
        Route::post('/pkl/logbook', [PklController::class, 'studentLogbookStore'])->name('pkl.student.logbook');
    });

    // =========================================================================
    // KEDISIPLINAN & BIMBINGAN KONSELING (BK)
    // =========================================================================
    // Pencatatan Pelanggaran/Prestasi & Sesi Konseling BK (Superadmin, Admin, Kaprog, Guru)
    Route::middleware(['role:superadmin,admin,kaprog,guru'])->group(function () {
        Route::get('/kedisiplinan', [DisciplineCounselingController::class, 'disciplineIndex'])->name('discipline.index');
        Route::post('/kedisiplinan', [DisciplineCounselingController::class, 'disciplineStore'])->name('discipline.store');
        Route::post('/kedisiplinan/{id}/status', [DisciplineCounselingController::class, 'disciplineUpdateStatus'])->name('discipline.status');
        Route::get('/konseling-bk', [DisciplineCounselingController::class, 'counselingIndex'])->name('counseling.index');
        Route::post('/konseling-bk', [DisciplineCounselingController::class, 'counselingStore'])->name('counseling.store');
    });

    // Rekor Poin Kedisiplinan & Prestasi Siswa (Siswa & Orang Tua)
    Route::middleware(['role:siswa,orangtua'])->group(function () {
        Route::get('/rekor-kedisiplinan-siswa', [DisciplineCounselingController::class, 'studentPortal'])->name('discipline.student.portal');
    });

    // =========================================================================
    // ETALASE KARYA & TEACHING FACTORY (TeFa)
    // =========================================================================
    // Galeri Showcase Publik/Internal (Semua Pengguna Terotentikasi)
    Route::get('/etalase-karya', [PortfolioController::class, 'index'])->name('portfolio.index');

    // Pengajuan Karya Proyek Siswa (Khusus Siswa)
    Route::middleware(['role:siswa'])->group(function () {
        Route::get('/etalase-karya/karya-saya', [PortfolioController::class, 'studentIndex'])->name('portfolio.student.index');
        Route::post('/etalase-karya', [PortfolioController::class, 'store'])->name('portfolio.store');
    });

    // Kurasi & Moderasi Portofolio TeFa (Superadmin, Admin, Kaprog, Guru)
    Route::middleware(['role:superadmin,admin,kaprog,guru'])->group(function () {
        Route::get('/etalase-karya/kurasi', [PortfolioController::class, 'moderationIndex'])->name('portfolio.moderation');
        Route::post('/etalase-karya/{id}/kurasi', [PortfolioController::class, 'moderate'])->name('portfolio.moderate');
    });

    // Detail Portofolio (Ditempatkan di akhir setelah /karya-saya dan /kurasi untuk mencegah slug collision)
    Route::get('/etalase-karya/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

    // =========================================================================
    // PERSETUJUAN IZIN (Admin, Kaprog, Wali Kelas / Guru)
    // =========================================================================
    Route::middleware(['role:superadmin,admin,kaprog,guru'])->group(function () {
        Route::get('/persetujuan-izin', [IzinApprovalController::class, 'index'])->name('izin.approval');
        Route::post('/persetujuan-izin/{id}/approve', [IzinApprovalController::class, 'approve'])->name('izin.approve');
        Route::post('/persetujuan-izin/{id}/reject', [IzinApprovalController::class, 'reject'])->name('izin.reject');
    });
});

if (app()->environment('local')) {
    Route::get('/dev-login', function (\Illuminate\Http\Request $request) {
        $role = $request->query('role', 'admin');
        if ($role === 'kaprog') {
            $user = \App\Models\User::where('role', 'kaprog')->first() ?? \App\Models\User::where('role', 'admin')->first();
        } else {
            $user = \App\Models\User::where('role', 'admin')->first();
        }
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect()->route('dashboard');
    });
}
