<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\SiswaDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class DeviceController extends Controller
{
    /**
     * Show Student's Registered Devices Page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            return redirect()->route('dashboard');
        }

        $siswa = Siswa::with('activeDevices', 'devices')->where('user_id', $user->id)->first();
        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        $devices = SiswaDevice::where('siswa_id', $siswa->id)
            ->orderBy('is_active', 'desc')
            ->orderBy('is_primary', 'desc')
            ->orderBy('last_active_at', 'desc')
            ->get();

        $activeCount = $devices->where('is_active', true)->count();
        $currentDeviceToken = $request->cookie('myskanic_device_token');

        return view('siswa.devices', compact('siswa', 'devices', 'activeCount', 'currentDeviceToken'));
    }

    /**
     * Revoke / Remove a Device Slot
     */
    public function revoke(Request $request, $id)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return back()->with('error', 'Akses ditolak.');
        }

        $device = SiswaDevice::where('id', $id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $device->revoke();

        // If the revoked device was the current device, clear cookie
        if ($request->cookie('myskanic_device_token') === $device->device_token) {
            Cookie::queue(Cookie::forget('myskanic_device_token'));
        }

        return back()->with('success', "Perangkat \"{$device->device_name}\" berhasil dilepaskan. Slot perangkat kini tersedia.");
    }

    /**
     * Admin / Superadmin / Guru reset devices for a student
     */
    public function adminResetDevices(Request $request, $siswaId)
    {
        $siswa = Siswa::findOrFail($siswaId);

        $revokedCount = SiswaDevice::where('siswa_id', $siswa->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'revoked_at' => now(),
            ]);

        return back()->with('success', "Berhasil mereset {$revokedCount} perangkat aktif siswa {$siswa->nama}. Siswa kini dapat mendaftarkan perangkat baru saat login.");
    }
}
