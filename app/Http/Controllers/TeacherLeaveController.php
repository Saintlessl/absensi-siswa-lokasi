<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\Izin;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherLeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:web']);
    }

    /**
     * Teacher leave application dashboard & history
     */
    public function index()
    {
        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            return redirect()->route('dashboard')->with('error', 'Akses dibatasi: Akun Anda tidak terhubung dengan data profil Guru.');
        }

        // Fetch this teacher's own leave requests only (never see other teachers' leaves)
        $riwayat = Izin::with(['decidedBy'])
            ->where('applicant_type', 'guru')
            ->where('guru_id', $guru->id)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->paginate(15);

        return view('guru.izin.index', compact('guru', 'riwayat'));
    }

    /**
     * Submit teacher leave request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Akun Anda bukan Guru aktif.');
        }

        $request->validate([
            'jenis'           => 'required|in:sakit,keperluan_keluarga,keperluan_pribadi,dinas_luar,keperluan_sekolah,lainnya',
            'durasi_tipe'     => 'required|in:sehari_penuh,rentang_hari,sebagian_hari',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jam_mulai'       => 'nullable|required_if:durasi_tipe,sebagian_hari|date_format:H:i',
            'jam_selesai'     => 'nullable|required_if:durasi_tipe,sebagian_hari|date_format:H:i|after:jam_mulai',
            'keterangan'      => 'required|string|max:500',
            'lampiran'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',
        ], [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'jam_selesai.after'              => 'Jam selesai harus lebih akhir dari jam mulai.',
            'lampiran.max'                   => 'Ukuran berkas lampiran maksimal 3MB.',
            'lampiran.mimes'                 => 'Berkas lampiran harus berupa dokumen PDF atau gambar (JPG, PNG).',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai);
        $tanggalSelesai = ($request->durasi_tipe === 'rentang_hari' && $request->filled('tanggal_selesai'))
            ? Carbon::parse($request->tanggal_selesai)
            : $tanggalMulai->copy();

        // Validate overlapping leave dates for the same teacher
        $collision = Izin::where('applicant_type', 'guru')
            ->where('guru_id', $guru->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereBetween('tanggal_mulai', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()])
                  ->orWhereBetween('tanggal_selesai', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()])
                  ->orWhere(function ($sub) use ($tanggalMulai, $tanggalSelesai) {
                      $sub->where('tanggal_mulai', '<=', $tanggalMulai->toDateString())
                          ->where('tanggal_selesai', '>=', $tanggalSelesai->toDateString());
                  });
            })
            ->exists();

        if ($collision) {
            return back()->withInput()->with('error', 'Anda sudah memiliki permohonan izin (Pending atau Disetujui) pada rentang tanggal tersebut.');
        }

        // Handle file upload
        $lampiranPath = null;
        if ($request->hasFile('lampiran') && $request->file('lampiran')->isValid()) {
            $lampiranPath = $request->file('lampiran')->store('lampiran_izin_guru', 'public');
        }

        try {
            $izin = DB::transaction(function () use ($request, $guru, $tanggalMulai, $tanggalSelesai, $lampiranPath) {
                return Izin::create([
                    'applicant_type'  => 'guru',
                    'guru_id'         => $guru->id,
                    'tanggal'         => $tanggalMulai->toDateString(),
                    'tanggal_mulai'   => $tanggalMulai->toDateString(),
                    'tanggal_selesai' => $tanggalSelesai->toDateString(),
                    'durasi_tipe'     => $request->durasi_tipe,
                    'jam_mulai'       => $request->jam_mulai,
                    'jam_selesai'     => $request->jam_selesai,
                    'jenis'           => $request->jenis,
                    'keterangan'      => $request->keterangan,
                    'lampiran_path'   => $lampiranPath,
                    'status'          => 'pending',
                ]);
            });

            AuditLog::log('izin.guru.create', $izin, null, $izin->toArray(), "Guru {$guru->nama} mengajukan izin {$izin->jenis_label} ({$tanggalMulai->format('d/m/Y')})");

            return redirect()->route('teacher.izin.index')->with('success', 'Permohonan izin Anda berhasil dikirim dan menunggu verifikasi persetujuan.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal memproses permohonan izin: ' . $e->getMessage());
        }
    }

    /**
     * Cancel own pending leave request
     */
    public function cancel($id)
    {
        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->firstOrFail();

        $izin = Izin::where('id', $id)
            ->where('applicant_type', 'guru')
            ->where('guru_id', $guru->id)
            ->firstOrFail();

        if ($izin->status !== 'pending') {
            return back()->with('error', 'Hanya permohonan izin dengan status Menunggu (Pending) yang dapat dibatalkan.');
        }

        $oldValues = $izin->toArray();
        $izin->update([
            'status'            => 'cancelled',
            'keputusan_catatan' => 'Dibatalkan oleh pemohon mandiri.',
            'decided_at'        => now(),
        ]);

        AuditLog::log('izin.guru.cancel', $izin, $oldValues, $izin->toArray(), "Guru {$guru->nama} membatalkan permohonan izin {$izin->id}");

        return back()->with('success', 'Permohonan izin berhasil dibatalkan.');
    }
}
