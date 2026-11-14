<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendarDay;
use App\Models\AuditLog;
use App\Models\Izin;
use App\Models\LokasiSekolah;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Settings Home Dashboard
     */
    public function home()
    {
        $user = Auth::user();

        // 1. School Schedule Settings
        $schoolSettings = SchoolSetting::all()->pluck('value', 'key');
        $jamMasuk = $schoolSettings['jam_masuk'] ?? '07:15';
        $jamPulang = $schoolSettings['jam_pulang'] ?? '14:55';
        $namaSekolah = $schoolSettings['nama_sekolah'] ?? 'SMK Negeri 1 Ciomas';

        // 2. Closest Upcoming Academic Calendar Agendas
        $today = Carbon::today();
        $upcomingAgendas = AcademicCalendarDay::where('tanggal', '>=', $today)
            ->where('is_active', true)
            ->orderBy('tanggal', 'asc')
            ->limit(4)
            ->get();

        // If no future events, show recent past events so card is never empty
        if ($upcomingAgendas->isEmpty()) {
            $upcomingAgendas = AcademicCalendarDay::orderBy('tanggal', 'desc')
                ->limit(3)
                ->get();
        }

        // 3. School GPS Geofence Location
        $lokasi = LokasiSekolah::first();

        // 4. Role & Permission Summary
        $activeRolesCount = Role::where('status', 'active')->count();
        $totalPermissionsCount = Permission::count();
        $mainRoles = Role::whereIn('name', ['superadmin', 'admin', 'kaprog', 'guru', 'siswa'])
            ->withCount('users')
            ->get();

        // 5. Leave Requests Summary (Real DB Counts)
        $leaveStats = [
            'total_siswa'   => Izin::where('applicant_type', 'siswa')->count(),
            'total_guru'    => Izin::where('applicant_type', 'guru')->count(),
            'pending_siswa' => Izin::where('applicant_type', 'siswa')->where('status', 'pending')->count(),
            'pending_guru'  => Izin::where('applicant_type', 'guru')->where('status', 'pending')->count(),
            'approved_siswa'=> Izin::where('applicant_type', 'siswa')->where('status', 'approved')->count(),
            'approved_guru' => Izin::where('applicant_type', 'guru')->where('status', 'approved')->count(),
            'rejected_siswa'=> Izin::where('applicant_type', 'siswa')->where('status', 'rejected')->count(),
            'rejected_guru' => Izin::where('applicant_type', 'guru')->where('status', 'rejected')->count(),
        ];
        $leaveStats['total_pending'] = $leaveStats['pending_siswa'] + $leaveStats['pending_guru'];
        $leaveStats['total_approved'] = $leaveStats['approved_siswa'] + $leaveStats['approved_guru'];
        $leaveStats['total_rejected'] = $leaveStats['rejected_siswa'] + $leaveStats['rejected_guru'];

        $recentLeaves = Izin::with(['siswa.kelas', 'guru', 'decidedBy'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('settings.home', compact(
            'jamMasuk',
            'jamPulang',
            'namaSekolah',
            'upcomingAgendas',
            'lokasi',
            'activeRolesCount',
            'totalPermissionsCount',
            'mainRoles',
            'leaveStats',
            'recentLeaves',
            'user'
        ));
    }

    /**
     * Profile & Account settings
     */
    public function profile()
    {
        $user = Auth::user();
        return view('settings.profile', compact('user'));
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $oldData = ['name' => $user->name, 'email' => $user->email];

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        if ($user->siswa) {
            $user->siswa->update(['nama' => $request->name]);
        }
        if ($user->guru) {
            $user->guru->update(['nama' => $request->name]);
        }
        if ($user->orangTua) {
            $user->orangTua->update(['nama' => $request->name]);
        }

        AuditLog::log('user.profile.update', $user, $oldData, ['name' => $user->name, 'email' => $user->email], 'Memperbarui profil akun');

        return back()->with('success', 'Informasi akun berhasil diperbarui.');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(6)],
        ], [
            'current_password.required' => 'Password saat ini harus diisi.',
            'password.confirmed'        => 'Konfirmasi password baru tidak cocok.',
            'password.min'              => 'Password minimal harus 6 karakter.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        AuditLog::log('user.password.update', $user, null, null, 'Mengubah kata sandi akun');

        return back()->with('success', 'Kata sandi akun Anda berhasil diperbarui.');
    }
}
