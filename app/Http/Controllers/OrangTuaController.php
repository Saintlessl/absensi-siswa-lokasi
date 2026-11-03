<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OrangTuaController extends Controller
{
    public function index(Request $request)
    {
        $query = OrangTua::with(['user', 'siswa']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('siswa', function ($q2) use ($search) {
                      $q2->where('nama', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        $orang_tua = $query->orderBy('nama')->paginate(10)->appends($request->query());
        $siswa = Siswa::all();
        return view('orangtua.index', compact('orang_tua', 'siswa') + ['search' => $request->search ?? '']);
    }

    public function store(Request $request)
    {
        return redirect()->route('siswa')->with('error', 'Profil orang tua dibuat melalui Data Siswa agar relasinya selalu valid.');
    }

    public function edit(Request $request)
    {
        $orang_tua = OrangTua::with('user')->findOrFail($request->id);
        $siswa = Siswa::all();
        return view('orangtua.edit', compact('orang_tua', 'siswa'));
    }

    public function update(Request $request, $id)
    {
        $orang_tua = OrangTua::findOrFail($id);
        
        $request->validate([
            'nama'          => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'telepon'       => 'required|string|max:20',
            'alamat'        => 'nullable|string',
            'email'         => ['required', 'email', Rule::unique('users', 'email')->ignore($orang_tua->user_id)],
            'password'      => 'nullable|min:6',
        ]);

        $userData = [
            'name'          => $request->nama,
            'email'         => $request->email,
            'jenis_kelamin' => $request->jenis_kelamin,
        ];
        
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($orang_tua->user) {
            $orang_tua->user->update($userData);
        }

        $orang_tua->update([
            'nama'          => $request->nama,
            'jenis_kelamin' => $request->jenis_kelamin,
            'telepon'       => $request->telepon,
            'alamat'        => $request->alamat,
        ]);

        return redirect()->back()->with('success', 'Data Orang Tua berhasil diperbarui');
    }

    public function destroy($id)
    {
        $orang_tua = OrangTua::findOrFail($id);
        if ($orang_tua->user) {
            $orang_tua->user->delete(); // Cascades on DB level usually, but explicit is good too if needed
        } else {
            $orang_tua->delete();
        }

        return redirect()->back()->with('success', 'Data Orang Tua berhasil dihapus');
    }
}
