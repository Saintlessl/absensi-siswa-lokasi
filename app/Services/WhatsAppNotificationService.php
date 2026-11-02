<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\WhatsappNotificationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Send automatic attendance WhatsApp notification to parent
     */
    public function sendAttendanceNotification(Siswa $siswa, Absensi $absensi, string $tipe = 'masuk'): array
    {
        // 1. Resolve Parent Phone Number
        $rawPhone = $siswa->no_telp_orangtua ?? ($siswa->orangtua->telepon ?? null);
        $normalizedPhone = $this->normalizePhoneNumber($rawPhone);

        // 2. Format Message Content
        $message = $this->formatMessage($siswa, $absensi, $tipe);

        // If parent phone number is missing
        if (empty($normalizedPhone)) {
            $log = WhatsappNotificationLog::create([
                'absensi_id'       => $absensi->id,
                'siswa_id'         => $siswa->id,
                'tipe'             => $tipe,
                'nomor_tujuan'     => $rawPhone ?? '-',
                'pesan'            => $message,
                'status'           => 'skipped',
                'provider'         => config('services.whatsapp.provider', 'fonnte'),
                'error_message'    => 'Nomor telepon orang tua belum terdaftar di profil siswa.',
            ]);

            return [
                'success' => false,
                'status'  => 'skipped',
                'message' => 'Nomor WhatsApp orang tua tidak ditemukan.',
                'log_id'  => $log->id,
            ];
        }

        // 3. Idempotency Check (Prevent duplicate sending on retry / rapid requests)
        $alreadySent = WhatsappNotificationLog::where('absensi_id', $absensi->id)
            ->where('tipe', $tipe)
            ->where('status', 'sent')
            ->first();

        if ($alreadySent) {
            return [
                'success' => true,
                'status'  => 'already_sent',
                'message' => 'Notifikasi WhatsApp untuk presensi ini sudah pernah terkirim.',
                'log_id'  => $alreadySent->id,
            ];
        }

        // 4. Create Initial Pending Log
        $provider = config('services.whatsapp.provider', env('WHATSAPP_PROVIDER', 'fonnte'));
        $token = config('services.whatsapp.token', env('WHATSAPP_API_TOKEN', ''));
        $apiUrl = config('services.whatsapp.url', env('WHATSAPP_API_URL', 'https://api.fonnte.com/send'));

        $log = WhatsappNotificationLog::create([
            'absensi_id'   => $absensi->id,
            'siswa_id'     => $siswa->id,
            'tipe'         => $tipe,
            'nomor_tujuan' => $normalizedPhone,
            'pesan'        => $message,
            'status'       => 'pending',
            'provider'     => $provider,
        ]);

        // 5. Send via Selected Provider
        try {
            // Simulation / Log driver if token is empty or provider is explicitly 'log'
            if ($provider === 'log' || empty($token)) {
                Log::info("[WhatsApp Simulation] Sent {$tipe} notification to {$normalizedPhone}:\n{$message}");

                $log->update([
                    'status'           => 'sent',
                    'sent_at'          => now(),
                    'response_payload' => json_encode([
                        'mode'    => 'simulation',
                        'notice'  => 'Terkirim via log driver (Isi WHATSAPP_API_TOKEN di .env untuk pengiriman nyata).',
                        'target'  => $normalizedPhone,
                    ]),
                ]);

                return [
                    'success' => true,
                    'status'  => 'sent',
                    'message' => 'Notifikasi WhatsApp berhasil dikirim (mode simulasi/log).',
                    'log_id'  => $log->id,
                ];
            }

            // Real Provider: Fonnte API
            if ($provider === 'fonnte') {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Authorization' => $token,
                    ])
                    ->post($apiUrl, [
                        'target'      => $normalizedPhone,
                        'message'     => $message,
                        'countryCode' => '62',
                    ]);

                $resBody = $response->json();
                $isOk = $response->successful() && ($resBody['status'] ?? false) == true;

                $log->update([
                    'status'           => $isOk ? 'sent' : 'failed',
                    'sent_at'          => $isOk ? now() : null,
                    'response_payload' => json_encode($resBody),
                    'error_message'    => $isOk ? null : ($resBody['reason'] ?? 'Gagal mengirim pesan dari gateway WhatsApp'),
                ]);

                return [
                    'success' => $isOk,
                    'status'  => $isOk ? 'sent' : 'failed',
                    'message' => $isOk ? 'Notifikasi WhatsApp berhasil dikirim ke orang tua.' : 'Gagal mengirim WhatsApp.',
                    'log_id'  => $log->id,
                ];
            }

            // Generic / Other HTTP Provider
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => "Bearer {$token}"])
                ->post($apiUrl, [
                    'phone'   => $normalizedPhone,
                    'message' => $message,
                ]);

            $isOk = $response->successful();
            $log->update([
                'status'           => $isOk ? 'sent' : 'failed',
                'sent_at'          => $isOk ? now() : null,
                'response_payload' => $response->body(),
                'error_message'    => $isOk ? null : 'HTTP ' . $response->status(),
            ]);

            return [
                'success' => $isOk,
                'status'  => $isOk ? 'sent' : 'failed',
                'message' => $isOk ? 'Notifikasi WhatsApp berhasil dikirim.' : 'Gateway WhatsApp merespons error.',
                'log_id'  => $log->id,
            ];

        } catch (\Throwable $e) {
            Log::error("[WhatsApp Error] Gagal mengirim pesan ke {$normalizedPhone}: " . $e->getMessage());

            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status'  => 'failed',
                'message' => 'Terjadi kendala jaringan saat menghubungi gateway WhatsApp.',
                'log_id'  => $log->id,
            ];
        }
    }

    /**
     * Format official WhatsApp message template
     */
    public function formatMessage(Siswa $siswa, Absensi $absensi, string $tipe): string
    {
        $namaSiswa = $siswa->nama ?? 'Siswa';
        $nisn = $siswa->nisn ?? ($siswa->nis ?? '-');
        $namaKelas = $siswa->kelas->nama_kelas ?? '-';
        $namaJurusan = $siswa->kelas->jurusan->nama_jurusan ?? ($siswa->kelas->jurusan->kode_jurusan ?? '-');

        // Indonesian date & time formatting
        $tanggalObj = $absensi->tanggal ? Carbon::parse($absensi->tanggal) : Carbon::today();
        $hariIndo = $this->getHariIndonesia($tanggalObj->dayOfWeekIso);
        $bulanIndo = $this->getBulanIndonesia($tanggalObj->month);
        $tanggalFormatted = "{$hariIndo}, {$tanggalObj->day} {$bulanIndo} {$tanggalObj->year}";

        if ($tipe === 'pulang') {
            $waktu = $absensi->jam_pulang ? Carbon::parse($absensi->jam_pulang)->format('H:i') . ' WIB' : Carbon::now()->format('H:i') . ' WIB';

            return "*NOTIFIKASI PRESENSI PULANG*\n\n"
                . "Yth. Orang Tua/Wali dari {$namaSiswa},\n\n"
                . "Kami informasikan bahwa siswa berikut telah melakukan presensi pulang:\n\n"
                . "Nama: {$namaSiswa}\n"
                . "NISN: {$nisn}\n"
                . "Kelas: {$namaKelas}\n"
                . "Jurusan: {$namaJurusan}\n"
                . "Tanggal: {$tanggalFormatted}\n"
                . "Waktu: {$waktu}\n\n"
                . "Informasi ini merupakan pemberitahuan otomatis dari sistem presensi sekolah.";
        }

        // Default: Masuk
        $waktu = $absensi->jam_masuk ? Carbon::parse($absensi->jam_masuk)->format('H:i') . ' WIB' : Carbon::now()->format('H:i') . ' WIB';

        return "*NOTIFIKASI PRESENSI MASUK*\n\n"
            . "Yth. Orang Tua/Wali dari {$namaSiswa},\n\n"
            . "Kami informasikan bahwa siswa berikut telah melakukan presensi masuk:\n\n"
            . "Nama: {$namaSiswa}\n"
            . "NISN: {$nisn}\n"
            . "Kelas: {$namaKelas}\n"
            . "Jurusan: {$namaJurusan}\n"
            . "Tanggal: {$tanggalFormatted}\n"
            . "Waktu: {$waktu}\n\n"
            . "Informasi ini merupakan pemberitahuan otomatis dari sistem presensi sekolah.";
    }

    /**
     * Normalize Indonesian phone number to E.164 without '+'
     * 08123456789 -> 628123456789
     * +628123456789 -> 628123456789
     */
    public function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (empty($clean)) {
            return null;
        }

        // Convert leading '0' to '62'
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        // Validate reasonable phone length (e.g. 628xxxxxxxx: 10-15 digits)
        if (strlen($clean) < 10 || strlen($clean) > 16) {
            return null;
        }

        return $clean;
    }

    protected function getHariIndonesia(int $dayOfWeek): string
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        return $days[$dayOfWeek] ?? 'Hari ini';
    }

    protected function getBulanIndonesia(int $month): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        return $months[$month] ?? '';
    }
}
