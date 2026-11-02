<?php

namespace App\Http\Controllers;

use App\Exports\SiswaExport;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use App\Models\StudentEnrollment;
use App\Services\ParentAccountService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function __construct(private ParentAccountService $parentAccounts)
    {
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Siswa::with(['kelas.jurusan', 'user', 'orangtua']);

        if ($user && $user->role === 'kaprog') {
            $query->whereHas('kelas', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%')
                  ->orWhere('nisn', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->filled('jurusan_id')) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        if ($request->filled('tingkat')) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('tingkat', $request->tingkat);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswa = $query->orderBy('nama')->paginate(10)->appends($request->query());

        if ($user && $user->role === 'kaprog') {
            $jurusans = Jurusan::where('id', $user->jurusan_id)->get();
            $kelas = Kelas::with('jurusan')->where('jurusan_id', $user->jurusan_id)->where('is_active', true)->orderBy('nama_kelas')->get();
        } else {
            $jurusans = Jurusan::where('is_active', true)->orderBy('kode_jurusan')->get();
            $kelas = Kelas::with('jurusan')->where('is_active', true)->orderBy('nama_kelas')->get();
        }

        $orangTuaExisting = OrangTua::with('siswa')->orderBy('nama')->get();

        return view('siswa.index', compact('siswa', 'kelas', 'jurusans', 'orangTuaExisting') + [
            'search'     => $request->search ?? '',
            'kelas_id'   => $request->kelas_id ?? '',
            'jurusan_id' => $request->jurusan_id ?? '',
            'tingkat'    => $request->tingkat ?? '',
        ]);
    }

    public function store(Request $request)
    {
        // Enforce fixed domain @myskanic.com for student accounts
        $rawUsername = strtolower(trim($request->username ?? $request->email ?? ''));
        if (str_contains($rawUsername, '@')) {
            $rawUsername = explode('@', $rawUsername)[0];
        }
        $rawUsername = preg_replace('/[^a-z0-9._-]/', '', $rawUsername);

        if (empty($rawUsername)) {
            return back()->with('error', 'Username email siswa wajib diisi (hanya huruf, angka, titik, strip, underscore).')->withInput();
        }

        $finalEmail = $rawUsername . '@myskanic.com';
        $request->merge([
            'email'    => $finalEmail,
            'username' => $rawUsername,
        ]);

        $request->validate([
            'nisn'             => 'nullable|string|max:20|unique:siswas,nisn',
            'nis'              => 'required|string|max:20|unique:siswas,nis',
            'nama'             => 'required|string|max:255',
            'jenis_kelamin'    => 'required|in:L,P',
            'kelas_id'         => 'required|exists:kelas,id',
            'photo'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'parent_name'      => 'required_without:existing_orangtua_id|nullable|string|max:255',
            'parent_phone'     => 'required_without:existing_orangtua_id|nullable|string|max:25',
            'parent_address'   => 'required_without:existing_orangtua_id|nullable|string|max:2000',
            'existing_orangtua_id' => 'nullable|exists:orang_tuas,id',
            'confirm_existing_parent' => 'required_with:existing_orangtua_id|accepted',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|min:6',
        ], [
            'email.unique'              => "Username '{$rawUsername}' sudah digunakan oleh siswa lain (@myskanic.com).",
            'parent_phone.required_without' => 'Nomor WhatsApp orang tua/wali wajib diisi.',
            'nis.unique'                => 'NIS sudah terdaftar.',
            'nisn.unique'               => 'NISN sudah terdaftar.',
        ]);

        $targetKelas = Kelas::with('jurusan')->findOrFail($request->kelas_id);
        if (Auth::user() && Auth::user()->role === 'kaprog' && Auth::user()->jurusan_id !== $targetKelas->jurusan_id) {
            return back()->with('error', 'Akses ditolak: Anda hanya dapat menambahkan siswa ke jurusan Anda sendiri.')->withInput();
        }

        try {
            $parentPhone = $request->filled('existing_orangtua_id') ? null : $this->normalizeIndonesianPhone($request->parent_phone);
            if (!$request->filled('existing_orangtua_id') && !$parentPhone) {
                return back()->withErrors(['parent_phone' => 'Nomor WhatsApp harus berupa nomor Indonesia yang valid.'])->withInput();
            }

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('siswa-photos', 'public');
            }

            $activationLink = DB::transaction(function () use ($request, $finalEmail, $parentPhone, $photoPath, $targetKelas) {
            $user = User::create([
                'name'     => $request->nama,
                'email'    => $finalEmail,
                'password' => Hash::make($request->password),
                'role'     => 'siswa',
            ]);

            $siswa = Siswa::create([
                'user_id'          => $user->id,
                'nisn'             => $request->nisn,
                'nis'              => $request->nis,
                'nama'             => $request->nama,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'photo_path'       => $photoPath,
                'kelas_id'         => $request->kelas_id,
                'biometric_status' => 'not_enrolled',
            ]);
            $kelas = $targetKelas;
            StudentEnrollment::create([
                'siswa_id' => $siswa->id, 'kelas_id' => $kelas->id,
                'tahun_ajaran' => $kelas->tahun_ajaran ?: now()->format('Y') . '/' . now()->addYear()->format('Y'),
                'started_at' => now()->toDateString(), 'is_active' => true,
            ]);

            if ($request->filled('existing_orangtua_id')) {
                // A shared parent is only linked after an explicit admin confirmation;
                // phone/name similarity is deliberately never used for auto-merging.
                $siswa->orangtua()->attach($request->existing_orangtua_id);
                return null;
            }

            $parentUser = $this->parentAccounts->createPendingAccount($request->parent_name, $parentPhone);
            $parent = OrangTua::create([
                'user_id' => $parentUser->id,
                'nama' => $request->parent_name,
                'telepon' => $parentPhone,
                'alamat' => $request->parent_address,
                // Legacy column is intentionally populated for old reports only.
                'siswa_id' => $siswa->id,
            ]);
            $siswa->orangtua()->attach($parent->id);
            return $this->parentAccounts->issueActivation($parentUser);
            });

            $message = 'Data siswa dan relasi orang tua berhasil disimpan.';
            if ($activationLink) {
                $message .= ' Akun orang tua menunggu aktivasi.';
            }
            return redirect()->back()->with('success', $message)->with('parent_activation_link', $activationLink);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal menambahkan data siswa dan orang tua. Tidak ada data parsial yang disimpan.')->withInput();
        }
    }

    public function edit(Request $request)
    {
        $siswa = Siswa::with(['user', 'kelas.jurusan', 'orangtua.user'])->findOrFail($request->id);
        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $jurusans = Jurusan::orderBy('kode_jurusan')->get();
        $studentEmail = $siswa->user?->email ?? '';
        $currentUsername = str_contains($studentEmail, '@') ? explode('@', $studentEmail)[0] : $studentEmail;
        return view('siswa.edit', compact('siswa', 'kelas', 'jurusans', 'currentUsername'));
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::with(['user', 'orangtua'])->findOrFail($id);

        // Enforce fixed domain @myskanic.com for student accounts
        $rawUsername = strtolower(trim($request->username ?? $request->email ?? ''));
        if (str_contains($rawUsername, '@')) {
            $rawUsername = explode('@', $rawUsername)[0];
        }
        $rawUsername = preg_replace('/[^a-z0-9._-]/', '', $rawUsername);

        if (empty($rawUsername)) {
            return back()->with('error', 'Username email siswa wajib diisi.')->withInput();
        }

        $finalEmail = $rawUsername . '@myskanic.com';
        $request->merge([
            'email'    => $finalEmail,
            'username' => $rawUsername,
        ]);

        $request->validate([
            'nisn'             => ['nullable', 'string', 'max:20', Rule::unique('siswas', 'nisn')->ignore($siswa->id)],
            'nis'              => ['required', 'string', 'max:20', Rule::unique('siswas', 'nis')->ignore($siswa->id)],
            'nama'             => 'required|string|max:255',
            'jenis_kelamin'    => 'required|in:L,P',
            'kelas_id'         => 'required|exists:kelas,id',
            'photo'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'parent_name'      => 'required|string|max:255',
            'parent_phone'     => 'required|string|max:25',
            'parent_address'   => 'required|string|max:2000',
            'email'            => ['required', 'email', Rule::unique('users', 'email')->ignore($siswa->user_id)],
            'password'         => 'nullable|min:6',
        ], [
            'email.unique'              => "Username '{$rawUsername}' sudah digunakan oleh siswa lain (@myskanic.com).",
            'parent_phone.required' => 'Nomor WhatsApp orang tua/wali wajib diisi.',
        ]);

        if (Auth::user() && Auth::user()->role === 'kaprog') {
            $targetKelas = Kelas::findOrFail($request->kelas_id);
            if (Auth::user()->jurusan_id !== $targetKelas->jurusan_id) {
                return back()->with('error', 'Akses ditolak: Anda hanya dapat memindahkan siswa ke jurusan Anda sendiri.');
            }
        }

        try {
            $parentPhone = $this->normalizeIndonesianPhone($request->parent_phone);
            if (!$parentPhone) {
                return back()->withErrors(['parent_phone' => 'Nomor WhatsApp harus berupa nomor Indonesia yang valid.'])->withInput();
            }

            $photoPath = $siswa->photo_path;
            if ($request->boolean('remove_photo')) {
                if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }
                $photoPath = null;
            }
            if ($request->hasFile('photo')) {
                if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }
                $photoPath = $request->file('photo')->store('siswa-photos', 'public');
            }

            DB::transaction(function () use ($request, $siswa, $finalEmail, $parentPhone, $photoPath) {
            $userData = [
                'name'  => $request->nama,
                'email' => $finalEmail,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if ($siswa->user) {
                $siswa->user->update($userData);
            }

            $kelasChanged = (int) $siswa->kelas_id !== (int) $request->kelas_id;
            $siswa->update([
                'nisn'             => $request->nisn,
                'nis'              => $request->nis,
                'nama'             => $request->nama,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'photo_path'       => $photoPath,
                'kelas_id'         => $request->kelas_id,
            ]);
            if ($kelasChanged) {
                StudentEnrollment::where('siswa_id', $siswa->id)->where('is_active', true)->update(['is_active'=>false, 'ended_at'=>now()->toDateString()]);
                $kelasBaru = Kelas::findOrFail($request->kelas_id);
                StudentEnrollment::updateOrCreate(
                    ['siswa_id'=>$siswa->id, 'tahun_ajaran'=>$kelasBaru->tahun_ajaran ?: now()->format('Y') . '/' . now()->addYear()->format('Y')],
                    ['kelas_id'=>$kelasBaru->id, 'started_at'=>now()->toDateString(), 'ended_at'=>null, 'is_active'=>true]
                );
            }

            $parent = $siswa->orangtua->first();
            if (!$parent) {
                throw new \RuntimeException('Profil orang tua belum tersedia. Buat relasi melalui pengelolaan orang tua.');
            }
            $parent->update(['nama' => $request->parent_name, 'telepon' => $parentPhone, 'alamat' => $request->parent_address]);
            });
            return redirect()->back()->with('success', 'Data Siswa berhasil diperbarui');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal memperbarui data siswa dan orang tua. Tidak ada perubahan parsial yang disimpan.');
        }
    }

    private function normalizeIndonesianPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($digits, '0')) $digits = '62' . substr($digits, 1);
        if (str_starts_with($digits, '8')) $digits = '62' . $digits;
        return preg_match('/^628\d{8,11}$/', $digits) ? '+' . $digits : null;
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        if ($siswa->user) {
            $siswa->user->delete(); // This should cascade and delete Siswa as well based on migration
        } else {
            $siswa->delete();
        }

        return redirect()->back()->with('success', 'Data Siswa berhasil dihapus');
    }

    /**
     * Export siswa data to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Siswa::with(['kelas', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswa = $query->orderBy('nama')->get();
        $search = $request->search ?? '';
        $filterKelas = '';
        if ($request->filled('kelas_id')) {
            $kelas = Kelas::find($request->kelas_id);
            $filterKelas = $kelas ? $kelas->nama_kelas : '';
        }

        $pdf = Pdf::loadView('exports.siswa-pdf', compact('siswa', 'search', 'filterKelas'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Data_Siswa.pdf');
    }

    /**
     * Export siswa data to Excel
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(
            new SiswaExport($request->search ?? '', $request->kelas_id ?? ''),
            'Data_Siswa.xlsx'
        );
    }
}
