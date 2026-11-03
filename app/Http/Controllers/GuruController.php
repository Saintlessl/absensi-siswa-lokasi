<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use App\Services\TeacherAccountService;
use App\Services\TeacherAssignmentService;
use App\Services\TeacherScheduleService;
use App\Services\TeacherWorkloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function __construct(
        protected TeacherAccountService $accountService,
        protected TeacherAssignmentService $assignmentService,
        protected TeacherScheduleService $scheduleService,
        protected TeacherWorkloadService $workloadService
    ) {
    }

    /**
     * Teacher Directory with advanced filtering and assignment badges.
     */
    public function index(Request $request)
    {
        $query = Guru::with([
            'user.jurusan',
            'activeHomeroomAssignment.kelas',
            'activeSubjectAssignments.mataPelajaran',
            'activeSubjectAssignments.kelas'
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nip', 'LIKE', '%' . $search . '%')
                  ->orWhere('nuptk', 'LIKE', '%' . $search . '%')
                  ->orWhere('mapel', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('email', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('status_kepegawaian')) {
            $query->where('status_kepegawaian', $request->status_kepegawaian);
        }

        if ($request->filled('status_akun')) {
            $status = $request->status_akun;
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        if ($request->filled('assignment_type')) {
            $type = $request->assignment_type;
            if ($type === 'homeroom') {
                $query->whereHas('homeroomAssignments', function ($q) {
                    $q->where('is_active', true);
                });
            } elseif ($type === 'kaprog') {
                $query->whereHas('user', function ($q) {
                    $q->whereNotNull('jurusan_id');
                });
            } elseif ($type === 'subject') {
                $query->whereHas('subjectAssignments', function ($q) {
                    $q->where('is_active', true);
                });
            }
        }

        $guru = $query->orderBy('nama')->paginate(10)->appends($request->query());

        // Master options for modals and filters
        $jurusans = Jurusan::where('is_active', true)->orderBy('nama_jurusan')->get();
        $mataPelajarans = MataPelajaran::where('is_active', true)->orderBy('nama_mapel')->get();
        $kelasList = Kelas::where('is_active', true)->orderBy('nama_kelas')->get();

        // High-level statistics
        $totalGurus = Guru::count();
        $activeGurus = Guru::where('is_active', true)->count();
        $homeroomGurus = Guru::whereHas('homeroomAssignments', fn($q) => $q->where('is_active', true))->count();
        $kaprogGurus = User::whereNotNull('jurusan_id')->where('role', 'guru')->count();

        return view('guru.index', [
            'guru'               => $guru,
            'jurusans'           => $jurusans,
            'mataPelajarans'     => $mataPelajarans,
            'kelasList'          => $kelasList,
            'totalGurus'         => $totalGurus,
            'activeGurus'        => $activeGurus,
            'homeroomGurus'      => $homeroomGurus,
            'kaprogGurus'        => $kaprogGurus,
            'search'             => $request->search ?? '',
            'status_kepegawaian' => $request->status_kepegawaian ?? '',
            'status_akun'        => $request->status_akun ?? '',
            'assignment_type'    => $request->assignment_type ?? '',
        ]);
    }

    /**
     * Store new teacher with integrated account and activation invitation option.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'               => 'required|string|max:255',
            'nip'                => 'nullable|string|max:50|unique:gurus,nip',
            'nuptk'              => 'nullable|string|max:50|unique:gurus,nuptk',
            'email'              => 'required|email|unique:users,email',
            'jenis_kelamin'      => 'required|in:L,P',
            'telepon'            => 'nullable|string|max:30',
            'alamat'             => 'nullable|string',
            'status_kepegawaian' => 'nullable|string|max:50',
            'photo'              => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'password_mode'      => 'required|in:direct,invitation',
            'password'           => 'required_if:password_mode,direct|nullable|min:8',
        ]);

        $requiresActivation = $request->password_mode === 'invitation';
        $password = $request->password_mode === 'direct' ? $request->password : null;

        $result = $this->accountService->createTeacher(
            $request->all(),
            $password,
            $requiresActivation
        );

        $msg = "Data Guru {$result['guru']->nama} berhasil dibuat.";
        if ($result['activation_url']) {
            session()->flash('activation_url', $result['activation_url']);
            $msg .= " Tautan aktivasi akun telah disiapkan.";
        }

        return redirect()->route('guru.show', $result['guru']->id)->with('success', $msg);
    }

    /**
     * Teacher Detail & Central Assignment Management Hub.
     */
    public function show(int $id)
    {
        $guru = Guru::with([
            'user.jurusan',
            'subjectAssignments.mataPelajaran',
            'subjectAssignments.kelas.jurusan',
            'homeroomAssignments.kelas.jurusan',
            'activeHomeroomAssignment.kelas.jurusan',
            'jadwal.mataPelajaran',
            'jadwal.kelas.jurusan',
            'jadwal.room'
        ])->findOrFail($id);

        $tahunAjaran = '2026/2027';

        // Workload & teaching schedule metrics
        $workload = $this->workloadService->calculateWorkload($guru, $tahunAjaran);
        $liveSchedules = $this->scheduleService->getTeacherSchedulesWithLiveRooms($guru);

        // Master datasets for assigning
        $jurusans = Jurusan::where('is_active', true)->orderBy('nama_jurusan')->get();
        $mataPelajarans = MataPelajaran::where('is_active', true)->orderBy('nama_mapel')->get();
        $kelasList = Kelas::where('is_active', true)->orderBy('nama_kelas')->get();

        return view('guru.show', [
            'guru'           => $guru,
            'workload'       => $workload,
            'liveSchedules'  => $liveSchedules,
            'jurusans'       => $jurusans,
            'mataPelajarans' => $mataPelajarans,
            'kelasList'      => $kelasList,
            'tahunAjaran'    => $tahunAjaran,
        ]);
    }

    /**
     * Load quick edit form via modal.
     */
    public function edit(Request $request)
    {
        $guru = Guru::with('user')->findOrFail($request->id);
        return view('guru.edit', compact('guru'));
    }

    /**
     * Update teacher profile & account.
     */
    public function update(Request $request, $id)
    {
        $guru = Guru::with('user')->findOrFail($id);

        $request->validate([
            'nama'               => 'required|string|max:255',
            'nip'                => ['nullable', 'string', 'max:50', Rule::unique('gurus', 'nip')->ignore($guru->id)],
            'nuptk'              => ['nullable', 'string', 'max:50', Rule::unique('gurus', 'nuptk')->ignore($guru->id)],
            'email'              => ['required', 'email', Rule::unique('users', 'email')->ignore($guru->user_id)],
            'jenis_kelamin'      => 'required|in:L,P',
            'telepon'            => 'nullable|string|max:30',
            'alamat'             => 'nullable|string',
            'status_kepegawaian' => 'nullable|string|max:50',
            'photo'              => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'password'           => 'nullable|min:8',
            'status'             => 'nullable|in:active,pending,suspended,deactivated',
        ]);

        $this->accountService->updateTeacher($guru, $request->all());

        return redirect()->back()->with('success', "Data profil guru {$guru->nama} berhasil diperbarui.");
    }

    /**
     * Update teacher account status (active, suspended, deactivated).
     */
    public function updateStatus(Request $request, $id)
    {
        $guru = Guru::with('user')->findOrFail($id);
        $request->validate(['status' => 'required|in:active,pending,suspended,deactivated']);

        $this->accountService->setAccountStatus($guru->user, $request->status);

        return redirect()->back()->with('success', "Status akun {$guru->nama} berhasil diubah menjadi " . ucfirst($request->status) . ".");
    }

    /**
     * Regenerate teacher account activation link.
     */
    public function regenerateActivation($id)
    {
        $guru = Guru::with('user')->findOrFail($id);
        $activationUrl = $this->accountService->generateActivationLink($guru->user);

        return redirect()->back()->with('success', 'Tautan aktivasi baru berhasil dibuat: ' . $activationUrl)
            ->with('activation_url', $activationUrl);
    }

    /**
     * Assign Subject & Class to Teacher.
     */
    public function assignSubject(Request $request, $id)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kelas_id'          => 'required|exists:kelas,id',
            'tahun_ajaran'      => 'required|string|max:20',
        ]);

        $this->assignmentService->assignSubject(
            (int) $id,
            (int) $request->mata_pelajaran_id,
            (int) $request->kelas_id,
            $request->tahun_ajaran
        );

        return redirect()->back()->with('success', 'Penugasan mata pelajaran berhasil ditambahkan.');
    }

    /**
     * Remove Subject Assignment.
     */
    public function removeSubjectAssignment($assignmentId)
    {
        $this->assignmentService->removeSubjectAssignment((int) $assignmentId);
        return redirect()->back()->with('success', 'Penugasan mata pelajaran berhasil dihapus.');
    }

    /**
     * Assign Homeroom Teacher (Wali Kelas).
     */
    public function assignHomeroom(Request $request, $id)
    {
        $request->validate([
            'kelas_id'     => 'required|exists:kelas,id',
            'tahun_ajaran' => 'required|string|max:20',
            'catatan'      => 'nullable|string',
        ]);

        $this->assignmentService->assignHomeroom(
            (int) $id,
            (int) $request->kelas_id,
            $request->tahun_ajaran,
            $request->catatan
        );

        return redirect()->back()->with('success', 'Penugasan wali kelas berhasil disimpan. Histori penugasan sebelumnya tetap tersimpan.');
    }

    /**
     * Remove Homeroom Assignment.
     */
    public function removeHomeroomAssignment($assignmentId)
    {
        $this->assignmentService->removeHomeroomAssignment((int) $assignmentId);
        return redirect()->back()->with('success', 'Penugasan wali kelas telah dinonaktifkan.');
    }

    /**
     * Assign KAPROG to Teacher.
     */
    public function assignKaprog(Request $request, $id)
    {
        $request->validate([
            'jurusan_id' => 'required|exists:jurusans,id',
        ]);

        $this->assignmentService->assignKaprog((int) $id, (int) $request->jurusan_id);

        return redirect()->back()->with('success', 'Penugasan Kepala Program Keahlian (KAPROG) berhasil disimpan.');
    }

    /**
     * Remove KAPROG from Teacher.
     */
    public function removeKaprog($id)
    {
        $this->assignmentService->removeKaprog((int) $id);
        return redirect()->back()->with('success', 'Jabatan KAPROG berhasil dilepaskan dari guru ini.');
    }

    /**
     * Teacher Workload Management Center (Beban Kerja Guru).
     */
    public function workload(Request $request)
    {
        $tahunAjaran = $request->tahun_ajaran ?? '2026/2027';
        $search = $request->search;
        $jurusanId = $request->jurusan_id ? (int) $request->jurusan_id : null;

        $report = $this->workloadService->getAllTeachersWorkload($tahunAjaran, $search, $jurusanId);
        $jurusans = Jurusan::where('is_active', true)->orderBy('nama_jurusan')->get();

        return view('guru.workload', array_merge($report, [
            'search'     => $search ?? '',
            'jurusan_id' => $jurusanId ?? '',
            'jurusans'   => $jurusans,
        ]));
    }

    /**
     * Delete teacher and associated account.
     */
    public function destroy($id)
    {
        $guru = Guru::findOrFail($id);
        if ($guru->user) {
            $guru->user->delete();
        } else {
            $guru->delete();
        }

        return redirect()->route('guru')->with('success', 'Data Guru dan akun terkait berhasil dihapus.');
    }
}
