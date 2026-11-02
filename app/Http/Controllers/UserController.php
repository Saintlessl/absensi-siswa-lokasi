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

        $user = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());
        return view('user.index', compact('user') + [
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:superadmin,admin,guru,siswa,orangtua',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Data User berhasil ditambahkan');
    }

    public function edit(Request $request)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'superadmin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $user = User::findOrFail($request->id);
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'superadmin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:superadmin,admin,guru,siswa,orangtua',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = \Hash::make($request->password);
        }

        $user->update($data);

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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

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
            'password' => \Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password berhasil diperbarui.');
    }
}
