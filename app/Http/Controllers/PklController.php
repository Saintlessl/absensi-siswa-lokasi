<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\PklAttendance;
use App\Models\PklDudiPartner;
use App\Models\PklLogbook;
use App\Models\PklPlacement;
use App\Models\Siswa;
use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PklController extends Controller
{
    /**
     * DUDI Partners List (Admin / Kaprog)
     */
    public function partnersIndex(Request $request)
    {
        $user = Auth::user();
        $query = PklDudiPartner::with(['jurusan', 'placements']);

        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->where(function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id)
                  ->orWhereNull('jurusan_id');
            });
        }

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_dudi', 'like', "%{$s}%")
                  ->orWhere('kota', 'like', "%{$s}%")
                  ->orWhere('bidang_industri', 'like', "%{$s}%");
            });
        }

        $partners = $query->orderBy('nama_dudi')->paginate(15)->withQueryString();
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();

        return view('pkl.partners', compact('partners', 'jurusans'));
    }

    /**
     * Store DUDI Partner
     */
    public function partnerStore(Request $request)
    {
        $request->validate([
            'nama_dudi'       => 'required|string|max:200',
            'bidang_industri' => 'required|string|max:150',
            'alamat'          => 'required|string',
            'kota'            => 'required|string|max:100',
            'kontak_person'   => 'required|string|max:100',
            'email'           => 'nullable|email|max:150',
            'telepon'         => 'nullable|string|max:30',
            'pembimbing_dudi' => 'nullable|string|max:150',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'radius_meter'    => 'nullable|integer|min:20|max:5000',
            'jurusan_id'      => 'nullable|exists:jurusans,id',
        ]);

        $partner = PklDudiPartner::create([
            'nama_dudi'       => $request->nama_dudi,
            'bidang_industri' => $request->bidang_industri,
            'alamat'          => $request->alamat,
            'kota'            => $request->kota,
            'kontak_person'   => $request->kontak_person,
            'email'           => $request->email,
            'telepon'         => $request->telepon,
            'pembimbing_dudi' => $request->pembimbing_dudi,
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'radius_meter'    => $request->radius_meter ?? 150,
            'jurusan_id'      => $request->jurusan_id,
            'status'          => 'aktif',
        ]);

        return redirect()->route('pkl.partners.index')
            ->with('success', "Mitra DU/DI {$partner->nama_dudi} berhasil didaftarkan.");
    }

    /**
     * Placements List (Admin / Kaprog / Pembimbing)
     */
    public function placementsIndex(Request $request)
    {
        $user = Auth::user();
        $query = PklPlacement::with(['siswa.kelas', 'partner', 'guruPembimbing']);

        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->whereHas('siswa', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        } elseif ($user->role === 'guru') {
            $query->where('guru_pembimbing_id', $user->id);
        }

        if ($request->filled('partner_id')) {
            $query->where('pkl_dudi_partner_id', $request->partner_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $placements = $query->latest('id')->paginate(20)->withQueryString();
        $partners = PklDudiPartner::where('status', 'aktif')->orderBy('nama_dudi')->get();
        $gurus = User::whereIn('role', ['guru', 'kaprog'])->orderBy('name')->get();
        $siswas = Siswa::with('kelas')->orderBy('nama')->get();

        return view('pkl.placements', compact('placements', 'partners', 'gurus', 'siswas'));
    }

    /**
     * Store Placement
     */
    public function placementStore(Request $request)
    {
        $request->validate([
            'siswa_id'            => 'required|exists:siswas,id',
            'pkl_dudi_partner_id' => 'required|exists:pkl_dudi_partners,id',
            'guru_pembimbing_id'  => 'nullable|exists:users,id',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
            'catatan'             => 'nullable|string',
        ]);

        // Check if student already has an active placement
        $activeExists = PklPlacement::where('siswa_id', $request->siswa_id)
            ->where('status', 'active')
            ->exists();

        if ($activeExists) {
            return back()->with('error', 'Siswa tersebut masih memiliki penempatan PKL berstatus aktif.');
        }

        $placement = PklPlacement::create([
            'siswa_id'            => $request->siswa_id,
            'pkl_dudi_partner_id' => $request->pkl_dudi_partner_id,
            'guru_pembimbing_id'  => $request->guru_pembimbing_id,
            'tanggal_mulai'       => $request->tanggal_mulai,
            'tanggal_selesai'     => $request->tanggal_selesai,
            'status'              => 'active',
            'catatan'             => $request->catatan,
        ]);

        return redirect()->route('pkl.placements.index')
            ->with('success', "Penempatan PKL untuk siswa berhasil diterbitkan.");
    }

    /**
     * Student PKL Portal
     */
    public function studentPortal()
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        $activePlacement = PklPlacement::with(['partner', 'guruPembimbing'])
            ->where('siswa_id', $siswa->id)
            ->where('status', 'active')
            ->first();

        $todayAttendance = null;
        $recentLogbooks = collect();

        if ($activePlacement) {
            $todayAttendance = PklAttendance::where('pkl_placement_id', $activePlacement->id)
                ->where('tanggal', now()->toDateString())
                ->first();

            $recentLogbooks = PklLogbook::where('pkl_placement_id', $activePlacement->id)
                ->latest('tanggal')
                ->limit(10)
                ->get();
        }

        return view('pkl.student_portal', compact('siswa', 'activePlacement', 'todayAttendance', 'recentLogbooks'));
    }

    /**
     * Student PKL Check-In (Geofenced)
     */
    public function studentCheckIn(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        $placement = PklPlacement::with('partner')
            ->where('siswa_id', $siswa->id)
            ->where('status', 'active')
            ->first();

        if (!$placement) {
            return back()->with('error', 'Anda tidak memiliki penempatan PKL aktif saat ini.');
        }

        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'foto'      => 'nullable|image|max:3072',
            'catatan'   => 'nullable|string|max:255',
        ]);

        $today = now()->toDateString();
        $attendance = PklAttendance::where('pkl_placement_id', $placement->id)
            ->where('tanggal', $today)
            ->first();

        $partner = $placement->partner;
        $distance = $partner->getDistanceFrom($request->latitude, $request->longitude);
        $isValidLocation = $partner->isWithinRadius($request->latitude, $request->longitude);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('pkl/selfie', 'public');
        }

        if (!$attendance) {
            // Check-in masuk
            $jamMasuk = now()->format('H:i:s');
            $status = now()->format('H:i:s') > '08:00:00' ? 'terlambat' : 'hadir';

            PklAttendance::create([
                'pkl_placement_id'  => $placement->id,
                'tanggal'           => $today,
                'jam_masuk'         => $jamMasuk,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'distance_meters'   => $distance,
                'is_location_valid' => $isValidLocation,
                'status'            => $status,
                'foto_selfie_path'  => $fotoPath,
                'catatan'           => $request->catatan,
            ]);

            $message = $isValidLocation
                ? "Presensi masuk PKL berhasil dicatat pada {$jamMasuk} (Jarak: {$distance}m)."
                : "Presensi masuk PKL dicatat di luar radius industri ({$distance}m). Ditandai untuk evaluasi pembimbing.";

            return back()->with($isValidLocation ? 'success' : 'warning', $message);
        } else {
            // Check-out pulang
            $jamPulang = now()->format('H:i:s');
            $attendance->update([
                'jam_pulang' => $jamPulang,
            ]);

            return back()->with('success', "Presensi pulang PKL berhasil dicatat pada {$jamPulang}.");
        }
    }

    /**
     * Submit Student Logbook
     */
    public function studentLogbookStore(Request $request, InAppNotificationService $notifService)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        $placement = PklPlacement::with(['guruPembimbing'])
            ->where('siswa_id', $siswa->id)
            ->where('status', 'active')
            ->first();

        if (!$placement) {
            return back()->with('error', 'Penempatan PKL aktif tidak ditemukan.');
        }

        $request->validate([
            'tanggal'               => 'required|date|before_or_equal:today',
            'aktivitas'             => 'required|string|min:10',
            'kompetensi_dipelajari' => 'nullable|string',
            'foto_dokumentasi'      => 'nullable|image|max:4096',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_dokumentasi')) {
            $fotoPath = $request->file('foto_dokumentasi')->store('pkl/logbooks', 'public');
        }

        $logbook = PklLogbook::updateOrCreate(
            [
                'pkl_placement_id' => $placement->id,
                'tanggal'          => $request->tanggal,
            ],
            [
                'aktivitas'             => $request->aktivitas,
                'kompetensi_dipelajari' => $request->kompetensi_dipelajari,
                'foto_dokumentasi_path' => $fotoPath ?? DB::raw('foto_dokumentasi_path'),
                'status'                => 'submitted',
                'catatan_guru'          => null,
            ]
        );

        if ($placement->guruPembimbing) {
            $notifService->sendToUser(
                $placement->guruPembimbing,
                "Jurnal Harian PKL Diserahkan",
                "Siswa {$siswa->nama} telah menyerahkan logbook PKL untuk tanggal {$request->tanggal}.",
                'academic'
            );
        }

        return back()->with('success', "Jurnal kegiatan harian PKL berhasil disimpan dan dikirim ke guru pembimbing.");
    }

    /**
     * Advisor Logbook Verification List
     */
    public function advisorLogbookIndex(Request $request)
    {
        $user = Auth::user();
        $query = PklLogbook::with(['placement.siswa.kelas', 'placement.partner', 'verifier']);

        if ($user->role === 'guru') {
            $query->whereHas('placement', function ($q) use ($user) {
                $q->where('guru_pembimbing_id', $user->id);
            });
        } elseif ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->whereHas('placement.siswa', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logbooks = $query->latest('tanggal')->latest('id')->paginate(20)->withQueryString();

        return view('pkl.advisor_logbooks', compact('logbooks'));
    }

    /**
     * Review Logbook (Approve or Request Revision)
     */
    public function advisorLogbookReview(Request $request, $id, InAppNotificationService $notifService)
    {
        $request->validate([
            'status'       => 'required|in:approved,revision_requested',
            'catatan_guru' => 'nullable|string|max:500',
        ]);

        $logbook = PklLogbook::with(['placement.siswa.user'])->findOrFail($id);

        $logbook->update([
            'status'       => $request->status,
            'catatan_guru' => $request->catatan_guru,
            'verified_by'  => Auth::id(),
            'verified_at'  => now(),
        ]);

        $statusText = $request->status === 'approved' ? 'disetujui' : 'memerlukan perbaikan';
        $studentUser = $logbook->placement->siswa->user ?? null;

        if ($studentUser) {
            $notifService->sendToUser(
                $studentUser,
                "Status Jurnal PKL Diperbarui",
                "Logbook PKL tanggal {$logbook->tanggal->format('d/m/Y')} {$statusText} oleh pembimbing. " . ($request->catatan_guru ? "Catatan: {$request->catatan_guru}" : ""),
                'academic'
            );
        }

        return back()->with('success', "Verifikasi logbook PKL berhasil disimpan.");
    }

    /**
     * Overall PKL Monitoring Dashboard (Admin / Kaprog)
     */
    public function monitoringDashboard()
    {
        $stats = [
            'total_partners'    => PklDudiPartner::where('status', 'aktif')->count(),
            'total_active_pk'   => PklPlacement::where('status', 'active')->count(),
            'today_attendances' => PklAttendance::where('tanggal', now()->toDateString())->count(),
            'pending_logbooks'  => PklLogbook::where('status', 'submitted')->count(),
        ];

        $todayAttendances = PklAttendance::with(['placement.siswa.kelas', 'placement.partner'])
            ->where('tanggal', now()->toDateString())
            ->latest('id')
            ->limit(30)
            ->get();

        $activePlacements = PklPlacement::with(['siswa.kelas', 'partner', 'guruPembimbing'])
            ->where('status', 'active')
            ->latest('id')
            ->limit(15)
            ->get();

        return view('pkl.monitoring', compact('stats', 'todayAttendances', 'activePlacements'));
    }
}
