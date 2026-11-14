<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'superadmin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Manajemen User hanya dapat diakses oleh Superadmin.');
        }

        $query = User::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $user = $query->with('jurusan')->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());
        $jurusans = \App\Models\Jurusan::where('is_active', true)->orderBy('nama_jurusan')->get();
        return view('user.index', compact('user', 'jurusans') + [
            'search' => $request->search ?? '',
            'filter_role' => $request->role ?? '',
        ]);
    }

    public function store(Request $request)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'superadmin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Hanya Superadmin yang dapat menambahkan user.');
        }

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|min:6',
            'role'          => 'required|in:superadmin,admin,guru,siswa,orangtua,kaprog',
            'jenis_kelamin' => 'required|in:L,P',
            'jurusan_id'    => 'required_if:role,kaprog|nullable|exists:jurusans,id',
        ]);

        User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => \Hash::make($request->password),
            'role'          => $request->role,
            'jenis_kelamin' => $request->jenis_kelamin,
            'jurusan_id'    => $request->role === 'kaprog' ? $request->jurusan_id : null,
        ]);

        return redirect()->back()->with('success', 'Data User berhasil ditambahkan');
    }

    public function edit(Request $request)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'superadmin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $user = User::findOrFail($request->id);
        $jurusans = \App\Models\Jurusan::where('is_active', true)->orderBy('nama_jurusan')->get();
        return view('user.edit', compact('user', 'jurusans'));
    }

    public function update(Request $request, $id)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'superadmin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role'          => 'required|in:superadmin,admin,guru,siswa,orangtua,kaprog',
            'jenis_kelamin' => 'required|in:L,P',
            'jurusan_id'    => 'required_if:role,kaprog|nullable|exists:jurusans,id',
        ]);

        // Lockout guard: refuse to demote the last superadmin (including self).
        if ($user->role === 'superadmin' && $request->role !== 'superadmin'
            && \App\Models\User::where('role', 'superadmin')->count() <= 1) {
            return redirect()->back()->with('error', 'Tidak dapat menurunkan role satu-satunya akun Superadmin.');
        }

        $data = [
            'name'          => $request->name,
            'email'         => $request->email,
            'role'          => $request->role,
            'jenis_kelamin' => $request->jenis_kelamin,
            'jurusan_id'    => $request->role === 'kaprog' ? $request->jurusan_id : null,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = \Hash::make($request->password);
        }

        $user->update($data);

        // Sync with linked profile if present
        if ($user->guru) {
            $user->guru->update(['jenis_kelamin' => $request->jenis_kelamin]);
        } elseif ($user->orangTua) {
            $user->orangTua->update(['jenis_kelamin' => $request->jenis_kelamin]);
        } elseif ($user->siswa) {
            $user->siswa->update(['jenis_kelamin' => $request->jenis_kelamin]);
        }

        return redirect()->back()->with('success', 'Data User berhasil diperbarui');
    }

    public function destroy($id)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'superadmin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $user = User::findOrFail($id);

        if ($user->id === \Illuminate\Support\Facades\Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === 'superadmin' && User::where('role', 'superadmin')->count() <= 1) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus satu-satunya akun Superadmin.');
        }

        // Security & Data Integrity Check: Protect academic records connected to teacher account
        if ($user->guru && $user->guru->hasAcademicRecords()) {
            return redirect()->back()->with('error', 'Akun Guru ' . $user->guru->nama . ' memiliki rekam jejak akademik historis (asesmen nilai siswa, jurnal mengajar, atau penugasan). Akun tidak dapat dihapus permanen demi integritas data akademik sekolah. Silakan gunakan tindakan Nonaktifkan Akun.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Data User berhasil dihapus');
    }

    public function profile()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return view('user.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'jenis_kelamin' => 'nullable|in:L,P',
        ]);

        $updateData = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('jenis_kelamin')) {
            $updateData['jenis_kelamin'] = $request->jenis_kelamin;
        }

        $user->update($updateData);

        if ($request->filled('jenis_kelamin')) {
            if ($user->guru) {
                $user->guru->update(['jenis_kelamin' => $request->jenis_kelamin]);
            } elseif ($user->orangTua) {
                $user->orangTua->update(['jenis_kelamin' => $request->jenis_kelamin]);
            } elseif ($user->siswa) {
                $user->siswa->update(['jenis_kelamin' => $request->jenis_kelamin]);
            }
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!\Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update([
            'password'             => \Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->back()->with('success', 'Password berhasil diperbarui.');
    }
}
