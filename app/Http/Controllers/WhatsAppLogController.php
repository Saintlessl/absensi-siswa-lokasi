<?php

namespace App\Http\Controllers;

use App\Models\WhatsappNotificationLog;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;

class WhatsAppLogController extends Controller
{
    /**
     * Display WhatsApp notification logs and statistics
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $date = $request->query('date', '');
        $search = $request->query('search', '');

        $query = WhatsappNotificationLog::with(['siswa.kelas']);

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($date)) {
            $query->whereDate('created_at', $date);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_tujuan', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function ($sq) use ($search) {
                      $sq->where('nama', 'like', "%{$search}%")
                         ->orWhere('nis', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        // Summary Statistics
        $stats = [
            'total'   => WhatsappNotificationLog::count(),
            'sent'    => WhatsappNotificationLog::where('status', 'sent')->count(),
            'failed'  => WhatsappNotificationLog::where('status', 'failed')->count(),
            'skipped' => WhatsappNotificationLog::where('status', 'skipped')->count(),
            'pending' => WhatsappNotificationLog::where('status', 'pending')->count(),
        ];

        return view('admin.whatsapp.index', compact('logs', 'stats', 'status', 'date', 'search'));
    }

    /**
     * Retry sending a failed WhatsApp message with duplicate protection
     */
    public function retry(Request $request, $id, WhatsAppNotificationService $service)
    {
        $log = WhatsappNotificationLog::with(['siswa', 'absensi'])->findOrFail($id);

        if ($log->status === 'sent') {
            return back()->with('info', 'Pesan ini sudah berstatus terkirim sebelumnya.');
        }

        if (!$log->siswa || !$log->absensi) {
            return back()->with('error', 'Data siswa atau presensi terkait tidak ditemukan.');
        }

        // Reset and trigger resend
        $result = $service->sendAttendanceNotification($log->siswa, $log->absensi, $log->tipe);

        if ($result['success']) {
            return back()->with('success', 'Pengiriman ulang WhatsApp berhasil diproses.');
        }

        return back()->with('error', 'Pengiriman ulang gagal: ' . ($result['message'] ?? 'Terjadi kesalahan.'));
    }
}
