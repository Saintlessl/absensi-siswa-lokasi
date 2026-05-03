<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,guru,siswa,orangtua',
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
        $user = User::findOrFail($request->id);
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

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

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = \Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Data User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
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
