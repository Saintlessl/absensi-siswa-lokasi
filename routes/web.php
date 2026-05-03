<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LokasiSekolahController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserController;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['guest:web'])->group(function () {

    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::post('/proseslogin', [AuthController::class, 'proseslogin'])->name('proseslogin');
});

Route::middleware(['auth:web'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/absensi/{id}/update', [DashboardController::class, 'updateAbsensi'])->name('dashboard.absensi.update');
    Route::post('/dashboard/absensi/{id}/delete', [DashboardController::class, 'deleteAbsensi'])->name('dashboard.absensi.delete');
    Route::get('/dashboard/export-pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export.pdf');
    Route::get('/dashboard/export-excel', [DashboardController::class, 'exportExcel'])->name('dashboard.export.excel');
    Route::get('/proseslogout', [AuthController::class, 'proseslogout'])->name('proseslogout');

    // Profile
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password');

    // Siswa CRUD
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::post('/siswa/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
    Route::post('/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update');
    Route::post('/siswa/delete/{id}', [SiswaController::class, 'destroy'])->name('siswa.delete');
    Route::get('/siswa/export-pdf', [SiswaController::class, 'exportPdf'])->name('siswa.export.pdf');
    Route::get('/siswa/export-excel', [SiswaController::class, 'exportExcel'])->name('siswa.export.excel');

    // Kelas
    Route::get('/kelas', [KelasController::class, 'index'])->name('kelas');
    Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
    Route::post('/kelas/edit', [KelasController::class, 'edit'])->name('kelas.edit');
    Route::post('/kelas/{id}', [KelasController::class, 'update'])->name('kelas.update');
    Route::post('/kelas/delete/{id}', [KelasController::class, 'destroy'])->name('kelas.delete');

    // Guru
    Route::get('/guru', [GuruController::class, 'index'])->name('guru');
    Route::post('/guru', [GuruController::class, 'store'])->name('guru.store');
    Route::post('/guru/edit', [GuruController::class, 'edit'])->name('guru.edit');
    Route::post('/guru/{id}', [GuruController::class, 'update'])->name('guru.update');
    Route::post('/guru/delete/{id}', [GuruController::class, 'destroy'])->name('guru.delete');

    // Jadwal
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

    // Lokasi Sekolah
    Route::get('/lokasi-sekolah', [LokasiSekolahController::class, 'index'])->name('lokasi_sekolah');
    Route::post('/lokasi-sekolah', [LokasiSekolahController::class, 'update'])->name('lokasi_sekolah.update');

    // Users
    Route::get('user', [UserController::class, 'index'])->name('user');
    Route::post('/user', [UserController::class, 'store'])->name('user');
    Route::post('/user/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::post('/user/{id}', [UserController::class, 'update'])->name('user.update');
    Route::post('/user/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');

    // Absensi
    Route::get('/absensi-siswa', [AbsensiController::class, 'index'])->name('absensi');
    Route::post('/absensi-siswa/store', [AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/riwayat-kehadiran', [AbsensiController::class, 'riwayatKehadiran'])->name('riwayatkehadiran');

    Route::get('/absensi-siswa/harian', [AbsensiController::class, 'absensiHarian'])->name('absensi.harian');
    Route::post('/absensi-siswa/harian/update', [AbsensiController::class, 'updateHarian'])->name('absensi.harian.update');

    Route::get('/rekap-absensi', [AbsensiController::class, 'rekapAbsensi'])->name('rekapabsensi');
    Route::get('/rekap-kelas', [AbsensiController::class, 'rekapPerKelas'])->name('rekapkelas');

    // Izin / Sakit
    Route::get('/izin-siswa', [IzinController::class, 'index'])->name('izin');
    Route::post('/izin-siswa', [IzinController::class, 'store'])->name('izin.store');
});
