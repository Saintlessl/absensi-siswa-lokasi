<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BiometricEnrollmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FaceBiometricController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LoginBiometricController;
use App\Http\Controllers\LokasiSekolahController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserController;
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
    Route::post('/proseslogin', [AuthController::class, 'proseslogin'])->name('proseslogin');

    // =========================================================================
    // 2FA BIOMETRIC FACE VERIFICATION ON EVERY LOGIN
    // =========================================================================
    Route::get('/login/verify-face', [LoginBiometricController::class, 'showVerifyFace'])->name('login.verify_face');
    Route::post('/login/verify-face/challenge', [LoginBiometricController::class, 'challenge'])->name('login.verify_face.challenge');
    Route::post('/login/verify-face/process', [LoginBiometricController::class, 'verify'])->name('login.verify_face.process');
    Route::get('/login/verify-face/cancel', [LoginBiometricController::class, 'cancel'])->name('login.verify_face.cancel');
});

Route::middleware(['auth:web', 'biometric.enrolled'])->group(function () {
    // Shared Dashboard & Auth
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/proseslogout', [AuthController::class, 'proseslogout'])->name('proseslogout');

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
        // Lokasi Sekolah & Radius Geofence GPS
        Route::get('/lokasi-sekolah', [LokasiSekolahController::class, 'index'])->name('lokasi_sekolah');
        Route::post('/lokasi-sekolah', [LokasiSekolahController::class, 'update'])->name('lokasi_sekolah.update');

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

    // =========================================================================
    // SUPERADMIN & ADMIN ROUTES (Master Data, Monitoring, QR Codes, Export)
    // =========================================================================
    Route::middleware(['role:superadmin,admin'])->group(function () {
        // Dashboard Operations
        Route::post('/dashboard/absensi/{id}/update', [DashboardController::class, 'updateAbsensi'])->name('dashboard.absensi.update');
        Route::get('/dashboard/export-pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export.pdf');
        Route::get('/dashboard/export-excel', [DashboardController::class, 'exportExcel'])->name('dashboard.export.excel');

        // Data Siswa
        Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa');
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::post('/siswa/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::post('/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::post('/siswa/delete/{id}', [SiswaController::class, 'destroy'])->name('siswa.delete');
        Route::get('/siswa/export-pdf', [SiswaController::class, 'exportPdf'])->name('siswa.export.pdf');
        Route::get('/siswa/export-excel', [SiswaController::class, 'exportExcel'])->name('siswa.export.excel');

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

        // Data Guru
        Route::get('/guru', [GuruController::class, 'index'])->name('guru');
        Route::post('/guru', [GuruController::class, 'store'])->name('guru.store');
        Route::post('/guru/edit', [GuruController::class, 'edit'])->name('guru.edit');
        Route::post('/guru/{id}', [GuruController::class, 'update'])->name('guru.update');
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

        // QR Code Kelas
        Route::get('/qr-codes', [AbsensiController::class, 'qrCodes'])->name('absensi.qrcodes');

        // Biometrik Actions (Superadmin & Admin)
        Route::post('/admin/biometrik-siswa/{id}/request-re-enroll', [FaceBiometricController::class, 'adminRequestReEnroll'])->name('admin.biometrik.re_enroll');
        Route::post('/admin/biometrik-siswa/{id}/toggle-suspend', [FaceBiometricController::class, 'adminToggleSuspend'])->name('admin.biometrik.toggle_suspend');
    });

    // Pendaftaran Biometrik Terpandu Sekolah / Lab Komputer & Monitoring (Superadmin, Admin, Guru)
    Route::middleware(['role:superadmin,admin,guru'])->group(function () {
        Route::get('/admin/biometrik-siswa', [FaceBiometricController::class, 'adminIndex'])->name('admin.biometrik');
        Route::get('/admin/biometrik-siswa/{id}/assisted-enroll', [FaceBiometricController::class, 'assistedEnroll'])->name('admin.biometrik.assisted_enroll');
        Route::post('/admin/biometrik-siswa/{id}/assisted-enroll/challenge', [FaceBiometricController::class, 'assistedChallenge'])->name('admin.biometrik.assisted_challenge');
        Route::post('/admin/biometrik-siswa/{id}/assisted-enroll/process', [FaceBiometricController::class, 'processAssistedEnroll'])->name('admin.biometrik.assisted_process');
        Route::post('/admin/biometrik-siswa/manual-override', [FaceBiometricController::class, 'manualOverride'])->name('admin.biometrik.override');
        Route::post('/admin/biometrik-siswa/{id}/reset-devices', [DeviceController::class, 'adminResetDevices'])->name('admin.biometrik.reset_devices');
    });

    // =========================================================================
    // GURU ROUTES (Rekap Kelas, Absensi Harian)
    // =========================================================================
    Route::middleware(['role:guru,superadmin,admin'])->group(function () {
        Route::get('/absensi-siswa/harian', [AbsensiController::class, 'absensiHarian'])->name('absensi.harian');
        Route::post('/absensi-siswa/harian/update', [AbsensiController::class, 'updateHarian'])->name('absensi.harian.update');
        Route::get('/rekap-absensi', [AbsensiController::class, 'rekapAbsensi'])->name('rekapabsensi');
        Route::get('/rekap-kelas', [AbsensiController::class, 'rekapPerKelas'])->name('rekapkelas');
    });

    // =========================================================================
    // SISWA & ORANG TUA ROUTES (Absensi, Liveness, Riwayat, Izin)
    // =========================================================================
    Route::middleware(['role:siswa,orangtua'])->group(function () {
        Route::get('/absensi-siswa', [AbsensiController::class, 'index'])->name('absensi');
        Route::post('/absensi-siswa/validate-qr', [AbsensiController::class, 'validateQr'])->name('absensi.validateqr');
        Route::post('/absensi-siswa/challenge', [FaceBiometricController::class, 'challenge'])->name('absensi.challenge');
        Route::post('/absensi-siswa/store', [AbsensiController::class, 'store'])->name('absensi.store');
        Route::get('/riwayat-kehadiran', [AbsensiController::class, 'riwayatKehadiran'])->name('riwayatkehadiran');

        // Registrasi Biometrik Wajah Mandiri (Alternative Route)
        Route::get('/absensi-siswa/registrasi-wajah', [FaceBiometricController::class, 'showRegistration'])->name('absensi.face.register.show');
        Route::post('/absensi-siswa/registrasi-wajah', [FaceBiometricController::class, 'register'])->name('absensi.face.register');

        // Izin / Sakit
        Route::get('/izin-siswa', [IzinController::class, 'index'])->name('izin');
        Route::post('/izin-siswa', [IzinController::class, 'store'])->name('izin.store');

        // Manajemen Perangkat Siswa (Device Limit Max 2)
        Route::get('/perangkat-saya', [DeviceController::class, 'index'])->name('siswa.devices');
        Route::post('/perangkat-saya/revoke/{id}', [DeviceController::class, 'revoke'])->name('siswa.devices.revoke');
    });
});
