<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::with(['user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nip', 'LIKE', '%' . $search . '%')
                  ->orWhere('mapel', 'LIKE', '%' . $search . '%');
            });
        }

        $guru = $query->orderBy('nama')->paginate(10)->appends($request->query());
        return view('guru.index', compact('guru') + ['search' => $request->search ?? '']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:gurus,nip',
            'nama' => 'required|string|max:255',
            'mapel' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru',
        ]);

        Guru::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'nama' => $request->nama,
            'mapel' => $request->mapel,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return redirect()->back()->with('success', 'Data Guru berhasil ditambahkan');
    }

    public function edit(Request $request)
    {
        $guru = Guru::with('user')->findOrFail($request->id);
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $request->validate([
            'nip' => ['required', Rule::unique('gurus', 'nip')->ignore($guru->id)],
            'nama' => 'required|string|max:255',
            'mapel' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($guru->user_id)],
            'password' => 'nullable|min:6',
        ]);

        $userData = [
            'name' => $request->nama,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $guru->user->update($userData);

        $guru->update([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'mapel' => $request->mapel,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return redirect()->back()->with('success', 'Data Guru berhasil diperbarui');
    }

    public function destroy($id)
    {
        $guru = Guru::findOrFail($id);
        if ($guru->user) {
            $guru->user->delete(); // This should cascade
        } else {
            $guru->delete();
        }

        return redirect()->back()->with('success', 'Data Guru berhasil dihapus');
    }
}
