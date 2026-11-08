<?php

namespace App\Http\Controllers;

use App\Exports\SiswaExport;
use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\SiswaStatusHistory;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\CohortManagementService;
use App\Services\ParentAccountService;
use App\Services\StudentAcademicHistoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        if ($request->filled('angkatan_id')) {
            $query->where('angkatan_id', $request->angkatan_id);
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $siswa = $query->orderBy('nama')->paginate(10)->appends($request->query());

        if ($user && $user->role === 'kaprog') {
            $jurusans = Jurusan::where('id', $user->jurusan_id)->get();
            $kelas = Kelas::with('jurusan')->where('jurusan_id', $user->jurusan_id)->where('is_active', true)->orderBy('nama_kelas')->get();
        } else {
            $jurusans = Jurusan::where('is_active', true)->orderBy('kode_jurusan')->get();
            $kelas = Kelas::with('jurusan')->where('is_active', true)->orderBy('nama_kelas')->get();
        }

        $angkatans = Angkatan::with('jurusan')->orderByDesc('nomor_angkatan')->get();
        $tahunAjarans = TahunAjaran::orderByDesc('tahun_mulai')->get();
        $activeTahunAjaran = TahunAjaran::getActive() ?? $tahunAjarans->first();
        $orangTuaExisting = OrangTua::with('siswa')->orderBy('nama')->get();

        return view('siswa.index', compact('siswa', 'kelas', 'jurusans', 'orangTuaExisting', 'angkatans', 'tahunAjarans', 'activeTahunAjaran') + [
            'search'          => $request->search ?? '',
            'kelas_id'        => $request->kelas_id ?? '',
            'jurusan_id'      => $request->jurusan_id ?? '',
            'tingkat'         => $request->tingkat ?? '',
            'angkatan_id'     => $request->angkatan_id ?? '',
            'tahun_ajaran_id' => $request->tahun_ajaran_id ?? '',
            'status'          => $request->status ?? '',
        ]);
    }

    public function store(Request $request)
    {
        // Enforce fixed domain @myskanic.com for student accounts
        $rawUsername = strtolower(trim($request->username ?? $request->email ?? $request->nis ?? ''));
        if (str_contains($rawUsername, '@')) {
            $rawUsername = explode('@', $rawUsername)[0];
        }
        $rawUsername = preg_replace('/[^a-z0-9._-]/', '', $rawUsername);

        if (empty($rawUsername)) {
            return back()->withErrors(['nis' => 'NIS atau Username email siswa wajib diisi.'])->withInput();
        }

        $finalEmail = $rawUsername . '@myskanic.com';
        $defaultPassword = $request->password ?: ($request->nis ?: 'password123');
        $request->merge([
            'email'    => $finalEmail,
            'username' => $rawUsername,
            'password' => $defaultPassword,
        ]);

        $request->validate([
            'nisn'                 => 'nullable|string|max:20|unique:siswas,nisn',
            'nis'                  => 'required|string|max:20|unique:siswas,nis',
            'nama'                 => 'required|string|max:255',
            'jenis_kelamin'        => 'required|in:L,P',
            'tempat_lahir'         => 'nullable|string|max:100',
            'tanggal_lahir'        => 'nullable|date',
            'tahun_ajaran_id'      => 'nullable|exists:tahun_ajarans,id',
            'jurusan_id'           => 'nullable|exists:jurusans,id',
            'angkatan_id'          => 'nullable|exists:angkatans,id',
            'tingkat'              => 'nullable|in:10,11,12',
            'kelas_id'             => 'required|exists:kelas,id',
            'jalur_masuk'          => 'nullable|in:baru,pindahan',
            'asal_sekolah'         => 'nullable|string|max:150',
            'photo'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'parent_name'          => 'required_without:existing_orangtua_id|nullable|string|max:255',
            'parent_jenis_kelamin' => 'required_without:existing_orangtua_id|nullable|in:L,P',
            'parent_phone'         => 'required_without:existing_orangtua_id|nullable|string|max:25',
            'parent_address'       => 'required_without:existing_orangtua_id|nullable|string|max:2000',
            'existing_orangtua_id' => 'nullable|exists:orang_tuas,id',
            'confirm_existing_parent' => $request->filled('existing_orangtua_id') ? 'required|accepted' : 'nullable',
            'email'                => 'required|email|unique:users,email',
            'password'             => 'required|min:6',
        ], [
            'email.unique'                  => "Username '{$rawUsername}' sudah digunakan oleh siswa lain (@myskanic.com).",
            'parent_phone.required_without' => 'Nomor WhatsApp orang tua/wali wajib diisi.',
            'nis.unique'                    => 'NIS sudah terdaftar.',
            'nisn.unique'                   => 'NISN sudah terdaftar.',
        ]);

        $targetKelas = Kelas::with(['jurusan', 'tahunAjaran'])->findOrFail($request->kelas_id);
        if (Auth::user() && Auth::user()->role === 'kaprog' && Auth::user()->jurusan_id !== $targetKelas->jurusan_id) {
            return back()->with('error', 'Akses ditolak: Anda hanya dapat menambahkan siswa ke jurusan Anda sendiri.')->withInput();
        }

        // Backend validation of relation chain
        if ($request->filled('jurusan_id') && (int) $targetKelas->jurusan_id !== (int) $request->jurusan_id) {
            return back()->withErrors(['kelas_id' => 'Rombel yang dipilih tidak sesuai dengan Jurusan yang dipilih.'])->withInput();
        }

        if ($request->filled('tingkat') && (int) $targetKelas->tingkat !== (int) $request->tingkat) {
            return back()->withErrors(['kelas_id' => 'Rombel yang dipilih tidak sesuai dengan Tingkat Kelas yang dipilih.'])->withInput();
        }

        // Resolve Permanent Entry Academic Year (from selection)
        $tahunAjaranMasuk = null;
        if ($request->filled('tahun_ajaran_id')) {
            $tahunAjaranMasuk = TahunAjaran::find($request->tahun_ajaran_id);
        }

        // Active operational academic year
        $activeTa = TahunAjaran::getActive() ?? $targetKelas->tahunAjaran ?? TahunAjaran::first();
        if (!$tahunAjaranMasuk) {
            $tahunAjaranMasuk = $activeTa;
        }

        // Resolve Angkatan
        $angkatan = null;
        if ($request->filled('angkatan_id')) {
            $angkatan = Angkatan::findOrFail($request->angkatan_id);
            if ($angkatan->jurusan_id && (int) $angkatan->jurusan_id !== (int) $targetKelas->jurusan_id) {
                return back()->withErrors(['angkatan_id' => 'Angkatan yang dipilih tidak sesuai dengan Jurusan rombel.'])->withInput();
            }
        }

        if (!$angkatan) {
            $baseYear = $tahunAjaranMasuk->tahun_mulai ?? (int) explode('/', $activeTa->nama)[0];
            $tingkat = (int) ($targetKelas->tingkat ?: ($request->tingkat ?: 10));
            $calculatedEntryYear = $baseYear - ($tingkat - 10);
            $angkatan = app(CohortManagementService::class)->determineCohortByYear($calculatedEntryYear, $targetKelas->jurusan_id);
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

            $activationLink = DB::transaction(function () use ($request, $finalEmail, $parentPhone, $photoPath, $targetKelas, $activeTa, $tahunAjaranMasuk, $angkatan) {
                $user = User::create([
                    'name'                 => $request->nama,
                    'email'                => $finalEmail,
                    'password'             => Hash::make($request->password),
                    'must_change_password' => true,
                    'role'                 => 'siswa',
                ]);

                $entryYear = $angkatan?->tahun_masuk ?? $tahunAjaranMasuk->tahun_mulai ?? ((int) explode('/', $activeTa->nama)[0] - ((int)($targetKelas->tingkat ?: 10) - 10));
                $isPindahan = $request->jalur_masuk === 'pindahan';

                $siswa = Siswa::create([
                    'user_id'               => $user->id,
                    'nisn'                  => $request->nisn,
                    'nis'                   => $request->nis,
                    'nama'                  => $request->nama,
                    'jenis_kelamin'         => $request->jenis_kelamin,
                    'tempat_lahir'          => $request->tempat_lahir,
                    'tanggal_lahir'         => $request->tanggal_lahir,
                    'photo_path'            => $photoPath,
                    'kelas_id'              => $targetKelas->id,
                    'angkatan_id'           => $angkatan?->id,
                    'tahun_ajaran_masuk_id' => $tahunAjaranMasuk->id,
                    'tahun_ajaran_masuk'    => $tahunAjaranMasuk->nama,
                    'tahun_masuk'           => $entryYear,
                    'jalur_masuk'           => $isPindahan ? 'pindahan' : 'baru',
                    'asal_sekolah'          => $request->asal_sekolah,
                    'catatan_status'        => $isPindahan && !empty($request->asal_sekolah)
                        ? 'Siswa pindahan dari ' . $request->asal_sekolah
                        : 'Pendaftaran siswa baru',
                    'biometric_status'      => 'not_enrolled',
                    'status'                => 'aktif',
                ]);

                // Record initial enrollment & academic chronology using StudentAcademicHistoryService
                app(StudentAcademicHistoryService::class)->recordInitialEnrollment(
                    $siswa,
                    $targetKelas,
                    $activeTa,
                    $tahunAjaranMasuk,
                    Auth::user()
                );

            if ($request->filled('existing_orangtua_id')) {
                // A shared parent is only linked after an explicit admin confirmation;
                // phone/name similarity is deliberately never used for auto-merging.
                $siswa->orangtua()->attach($request->existing_orangtua_id);
                return null;
            }

            $parentUser = $this->parentAccounts->createPendingAccount($request->parent_name, $parentPhone, $request->parent_jenis_kelamin);
            $parent = OrangTua::create([
                'user_id'       => $parentUser->id,
                'nama'          => $request->parent_name,
                'jenis_kelamin' => $request->parent_jenis_kelamin,
                'telepon'       => $parentPhone,
                'alamat'        => $request->parent_address,
                // Legacy column is intentionally populated for old reports only.
                'siswa_id'      => $siswa->id,
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

    /**
     * Ensure the acting user may manage this student record.
     *
     * KAPROG is scoped to their own jurusan; other roles are validated by the
     * route middleware. Without this, a kaprog could edit, move or delete a
     * student belonging to a different major simply by supplying that id.
     */
    private function assertCanManageSiswa(Siswa $siswa): void
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'kaprog') {
            return;
        }

        $siswaJurusanId = $siswa->kelas?->jurusan_id;

        if (!$user->jurusan_id || (int) $siswaJurusanId !== (int) $user->jurusan_id) {
            abort(403, 'Akses ditolak: siswa ini bukan bagian dari jurusan Anda.');
        }
    }

    /**
     * Check authorization for viewing student profile and academic history.
     */
    private function assertCanViewSiswa(Siswa $siswa): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if (in_array($user->role, ['superadmin', 'admin'])) {
            return;
        }

        if ($user->role === 'kaprog') {
            $siswaJurusanId = $siswa->kelas?->jurusan_id ?? $siswa->angkatan?->jurusan_id;
            if (!$user->jurusan_id || (int) $siswaJurusanId !== (int) $user->jurusan_id) {
                abort(403, 'Akses ditolak: siswa ini bukan bagian dari jurusan Anda.');
            }
            return;
        }

        if ($user->role === 'siswa') {
            if ((int) $siswa->user_id !== (int) $user->id) {
                abort(403, 'Akses ditolak: Anda hanya dapat melihat profil Anda sendiri.');
            }
            return;
        }

        if ($user->role === 'orangtua') {
            $isChild = $siswa->orangtua()->where('user_id', $user->id)->exists();
            if (!$isChild) {
                abort(403, 'Akses ditolak: Anda hanya dapat melihat profil anak Anda.');
            }
            return;
        }

        if ($user->role === 'guru') {
            return;
        }
    }

    /**
     * Display student profile, permanent entry identity, and academic history timeline.
     */
    public function show(Request $request, $id)
    {
        $siswa = Siswa::with([
            'user',
            'kelas.jurusan',
            'angkatan.jurusan',
            'tahunAjaranMasuk',
            'orangtua.user',
            'enrollments.kelas.jurusan',
            'enrollments.tahunAjaran',
            'statusHistories.kelasAsal',
            'statusHistories.kelasTujuan',
            'statusHistories.tahunAjaranAsal',
            'statusHistories.tahunAjaranTujuan',
            'statusHistories.createdBy',
        ])->findOrFail($id);

        $this->assertCanViewSiswa($siswa);

        $activeTahunAjaran = TahunAjaran::getActive() ?? TahunAjaran::first();
        $tahunAjarans = TahunAjaran::orderByDesc('tahun_mulai')->get();

        // Filters for timeline and history tabs
        $selectedTa = $request->query('ta');
        $selectedTingkat = $request->query('tingkat');
        $selectedStatus = $request->query('status');
        $selectedJenisPerubahan = $request->query('jenis_perubahan');

        $enrollments = $siswa->enrollments()
            ->with(['kelas.jurusan', 'tahunAjaran'])
            ->when($selectedTa, fn($q) => $q->where(fn($sub) => $sub->where('tahun_ajaran', $selectedTa)->orWhere('tahun_ajaran_id', $selectedTa)))
            ->when($selectedTingkat, fn($q) => $q->where('tingkat', $selectedTingkat))
            ->when($selectedStatus, fn($q) => $q->where('status_enrollment', $selectedStatus))
            ->orderBy('tingkat')
            ->orderBy('id')
            ->get();

        $statusHistories = $siswa->statusHistories()
            ->with(['kelasAsal', 'kelasTujuan', 'tahunAjaranAsal', 'tahunAjaranTujuan', 'createdBy'])
            ->when($selectedJenisPerubahan, fn($q) => $q->where('jenis_perubahan', $selectedJenisPerubahan))
            ->when($selectedTa, fn($q) => $q->where(fn($sub) => $sub->where('tahun_ajaran', $selectedTa)->orWhere('tahun_ajaran_tujuan_id', $selectedTa)->orWhere('tahun_ajaran_asal_id', $selectedTa)))
            ->orderByDesc('tanggal_perubahan')
            ->orderByDesc('id')
            ->get();

        return view('siswa.show', compact(
            'siswa',
            'activeTahunAjaran',
            'tahunAjarans',
            'enrollments',
            'statusHistories',
            'selectedTa',
            'selectedTingkat',
            'selectedStatus',
            'selectedJenisPerubahan'
        ));
    }

    public function edit(Request $request)
    {
        $siswa = Siswa::with(['user', 'kelas.jurusan', 'orangtua.user', 'angkatan.jurusan'])->findOrFail($request->id);
        $this->assertCanManageSiswa($siswa);
        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $jurusans = Jurusan::orderBy('kode_jurusan')->get();
        $angkatans = Angkatan::with('jurusan')->orderBy('nomor_angkatan', 'desc')->get();
        $studentEmail = $siswa->user?->email ?? '';
        $currentUsername = str_contains($studentEmail, '@') ? explode('@', $studentEmail)[0] : $studentEmail;
        return view('siswa.edit', compact('siswa', 'kelas', 'jurusans', 'angkatans', 'currentUsername'));
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::with(['user', 'orangtua', 'kelas'])->findOrFail($id);
        $this->assertCanManageSiswa($siswa);

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
            'tempat_lahir'     => 'nullable|string|max:100',
            'tanggal_lahir'    => 'nullable|date',
            'kelas_id'         => 'required|exists:kelas,id',
            'angkatan_id'      => 'nullable|exists:angkatans,id',
            'jalur_masuk'      => 'nullable|in:baru,pindahan',
            'asal_sekolah'     => 'nullable|string|max:150',
            'photo'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'parent_name'          => 'required|string|max:255',
            'parent_jenis_kelamin' => 'nullable|in:L,P',
            'parent_phone'         => 'required|string|max:25',
            'parent_address'       => 'required|string|max:2000',
            'email'            => ['required', 'email', Rule::unique('users', 'email')->ignore($siswa->user_id)],
            'password'         => 'nullable|min:6',
        ], [
            'email.unique'              => "Username '{$rawUsername}' sudah digunakan oleh siswa lain (@myskanic.com).",
            'parent_phone.required' => 'Nomor WhatsApp orang tua/wali wajib diisi.',
        ]);

        $targetKelas = Kelas::with(['jurusan', 'tahunAjaran'])->findOrFail($request->kelas_id);

        if (Auth::user() && Auth::user()->role === 'kaprog') {
            if (Auth::user()->jurusan_id !== $targetKelas->jurusan_id) {
                return back()->with('error', 'Akses ditolak: Anda hanya dapat memindahkan siswa ke jurusan Anda sendiri.');
            }
        }

        if ($request->filled('angkatan_id')) {
            $akt = Angkatan::find($request->angkatan_id);
            if ($akt && $akt->jurusan_id && (int) $akt->jurusan_id !== (int) $targetKelas->jurusan_id) {
                return back()->withErrors(['angkatan_id' => 'Angkatan yang dipilih tidak sesuai dengan Jurusan rombel.'])->withInput();
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

            DB::transaction(function () use ($request, $siswa, $finalEmail, $parentPhone, $photoPath, $targetKelas) {
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
            $angkatanId = $request->angkatan_id ?: $siswa->angkatan_id;

            $siswa->update([
                'nisn'             => $request->nisn,
                'nis'              => $request->nis,
                'nama'             => $request->nama,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'tempat_lahir'     => $request->tempat_lahir,
                'tanggal_lahir'    => $request->tanggal_lahir,
                'photo_path'       => $photoPath,
                'kelas_id'         => $request->kelas_id,
                'angkatan_id'      => $angkatanId,
                'jalur_masuk'      => $request->jalur_masuk ?? $siswa->jalur_masuk,
                'asal_sekolah'     => $request->filled('asal_sekolah') ? $request->asal_sekolah : $siswa->asal_sekolah,
            ]);
            if ($kelasChanged) {
                app(StudentAcademicHistoryService::class)->transferClass(
                    $siswa,
                    $targetKelas,
                    'Perubahan rombel kelas melalui edit data siswa',
                    Auth::user()
                );
            }

            $parent = $siswa->orangtua->first();
            if (!$parent) {
                throw new \RuntimeException('Profil orang tua belum tersedia. Buat relasi melalui pengelolaan orang tua.');
            }
            $parentData = [
                'nama'    => $request->parent_name,
                'telepon' => $parentPhone,
                'alamat'  => $request->parent_address,
            ];
            if ($request->filled('parent_jenis_kelamin')) {
                $parentData['jenis_kelamin'] = $request->parent_jenis_kelamin;
                if ($parent->user) {
                    $parent->user->update(['jenis_kelamin' => $request->parent_jenis_kelamin]);
                }
            }
            $parent->update($parentData);
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
        $siswa = Siswa::with('kelas')->findOrFail($id);
        $this->assertCanManageSiswa($siswa);

        if ($siswa->promotionItems()->exists()) {
            return redirect()->back()->with('error', "Siswa {$siswa->nama} memiliki catatan riwayat kenaikan kelas / kelulusan dan tidak dapat dihapus demi integritas data.");
        }

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
        $query = $this->scopedSiswaQuery();

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
        // BUG-018: exports used to build their own unscoped query, so a kaprog
        // could download every student in the school even though the on-screen
        // list was correctly restricted to their own major.
        return Excel::download(
            new SiswaExport($request->search ?? '', $request->kelas_id ?? '', Auth::user()),
            'Data_Siswa.xlsx'
        );
    }

    /**
     * Base query for student listings, restricted to the acting kaprog's major.
     */
    private function scopedSiswaQuery()
    {
        $user = Auth::user();
        $query = Siswa::with(['kelas.jurusan', 'user']);

        if ($user && $user->role === 'kaprog') {
            $query->whereHas('kelas', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        }

        return $query;
    }
}
