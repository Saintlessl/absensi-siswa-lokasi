<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Room;
use App\Services\TeacherScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function __construct(protected TeacherScheduleService $scheduleService)
    {
    }

    public function index(Request $request)
    {
        $query = Jadwal::with(['kelas.jurusan', 'guru', 'mataPelajaran', 'room']);

        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }

        $jadwal = $query->orderBy('hari')->orderBy('jam_mulai')->paginate(10)->appends($request->query());

        // Resolve live physical room from rolling assignments for each schedule
        $today = Carbon::today();
        foreach ($jadwal as $j) {
            $assignment = \App\Models\RoomAssignment::where('kelas_id', $j->kelas_id)
                ->where(function ($q) use ($today) {
                    $q->whereNull('starts_on')->orWhere('starts_on', '<=', $today->toDateString());
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('ends_on')->orWhere('ends_on', '>=', $today->toDateString());
                })
                ->with('room')
                ->first();

            $j->live_room = $assignment?->room ?? $j->room;
        }

        $kelas = Kelas::where('is_active', true)->orderBy('nama_kelas')->get();
        $guru = Guru::where('is_active', true)->orderBy('nama')->get();
        $mataPelajarans = MataPelajaran::where('is_active', true)->orderBy('nama_mapel')->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        return view('jadwal.index', [
            'jadwal'         => $jadwal,
            'kelas'          => $kelas,
            'guru'           => $guru,
            'mataPelajarans' => $mataPelajarans,
            'rooms'          => $rooms,
            'filter_hari'    => $request->hari ?? '',
            'filter_kelas'   => $request->kelas_id ?? '',
            'filter_guru'    => $request->guru_id ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'guru_id'           => 'required|exists:gurus,id',
            'kelas_id'          => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajarans,id',
            'mata_pelajaran'    => 'nullable|string|max:255',
            'hari'              => 'required|string',
            'jam_mulai'         => 'required|date_format:H:i',
            'jam_selesai'       => 'required|date_format:H:i|after:jam_mulai',
            'tahun_ajaran'      => 'nullable|string|max:20',
            'room_id'           => 'nullable|exists:rooms,id',
        ], [
            'jam_selesai.after'     => 'Jam selesai harus lebih akhir dari jam mulai.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
        ]);

        $this->scheduleService->createSchedule($request->all());

        return redirect()->back()->with('success', 'Jadwal pelajaran berhasil ditambahkan dan divalidasi bebas bentrok.');
    }

    public function edit(Request $request)
    {
        $jadwal = Jadwal::with(['guru', 'kelas', 'mataPelajaran', 'room'])->findOrFail($request->id);
        $kelas = Kelas::where('is_active', true)->orderBy('nama_kelas')->get();
        $guru = Guru::where('is_active', true)->orderBy('nama')->get();
        $mataPelajarans = MataPelajaran::where('is_active', true)->orderBy('nama_mapel')->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        return view('jadwal.edit', compact('jadwal', 'kelas', 'guru', 'mataPelajarans', 'rooms'));
    }

    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);

        $request->validate([
            'guru_id'           => 'required|exists:gurus,id',
            'kelas_id'          => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajarans,id',
            'mata_pelajaran'    => 'nullable|string|max:255',
            'hari'              => 'required|string',
            'jam_mulai'         => 'required|date_format:H:i',
            'jam_selesai'       => 'required|date_format:H:i|after:jam_mulai',
            'tahun_ajaran'      => 'nullable|string|max:20',
            'room_id'           => 'nullable|exists:rooms,id',
        ]);

        $this->scheduleService->updateSchedule($jadwal, $request->all());

        return redirect()->back()->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}
