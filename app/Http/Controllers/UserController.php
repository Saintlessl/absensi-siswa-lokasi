<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
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

    public function edit(Request $request)
    {
        $user = User::findOrFail($request->id);
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        // Defense-in-depth: even if middleware is misconfigured, only admins
        // may change user accounts. (BUG-002 privilege-escalation guard)
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses untuk mengubah data user.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,guru,siswa,orangtua',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // BUG-002: prevent demoting the last remaining admin (lockout guard).
        if ($user->role === 'admin' && $request->role !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->back()->with('error', 'Tidak dapat menurunkan role admin terakhir.');
            }
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Data User berhasil diperbarui');
    }

    public function destroy(int $id)
    {
        // Defense-in-depth role check (BUG-003)
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses untuk menghapus user.');
        }

        $user = User::findOrFail($id);

        // BUG-003: never allow self-delete; never allow deleting last admin.
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Data User berhasil dihapus');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

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
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password berhasil diperbarui.');
    }
}
