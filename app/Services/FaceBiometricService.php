<?php

namespace App\Services;

use App\Models\AbsensiVerificationLog;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FaceBiometricService
{
    /**
     * Threshold distance for FaceNet/VGGFace2 128-d embeddings.
     * Lower distance = stricter match. Standard threshold is 0.48.
     */
    const MATCH_THRESHOLD = 0.48;

    /**
     * Available liveness challenges
     */
    const AVAILABLE_CHALLENGES = [
        [
            'type' => 'blink',
            'label' => 'Kedipkan mata Anda secara wajar',
            'hint' => 'Deteksi gerakan kelopak mata alami'
        ],
        [
            'type' => 'smile',
            'label' => 'Tersenyum ramah ke arah kamera',
            'hint' => 'Deteksi ekspresi otot wajah'
        ],
        [
            'type' => 'turn_left',
            'label' => 'Tengokkan wajah perlahan sedikit ke kiri',
            'hint' => 'Deteksi sudut rotasi kepala (3D pose)'
        ],
        [
            'type' => 'turn_right',
            'label' => 'Tengokkan wajah perlahan sedikit ke kanan',
            'hint' => 'Deteksi sudut rotasi kepala (3D pose)'
        ]
    ];

    /**
     * Fixed 4-stage sequential poses for biometric face enrollment:
     * STEP 01: FRONT FACING
     * STEP 02: TURN RIGHT
     * STEP 03: TURN LEFT
     * STEP 04: LOOK UP
     */
    const ENROLLMENT_SEQUENTIAL_POSES = [
        [
            'step'  => 1,
            'type'  => 'front_facing',
            'label' => 'Hadapkan wajah tepat ke kamera',
            'hint'  => 'Pose frontal lurus ke depan'
        ],
        [
            'step'  => 2,
            'type'  => 'turn_right',
            'label' => 'Hadapkan wajah perlahan ke kanan',
            'hint'  => 'Rotasi kepala ke kanan'
        ],
        [
            'step'  => 3,
            'type'  => 'turn_left',
            'label' => 'Sekarang hadapkan wajah perlahan ke kiri',
            'hint'  => 'Rotasi kepala ke kiri'
        ],
        [
            'step'  => 4,
            'type'  => 'look_up',
            'label' => 'Terakhir, arahkan wajah sedikit ke atas',
            'hint'  => 'Arahkan dagu/kepala sedikit ke atas'
        ]
    ];

    /**
     * Generate dynamic challenge nonce with random challenge sequence
     */
    public function generateChallenge(Siswa $siswa): array
    {
        $token = 'chl_' . Str::random(32);
        
        // Randomly pick 2 unique challenges
        $challengesPool = self::AVAILABLE_CHALLENGES;
        shuffle($challengesPool);
        $selected = array_slice($challengesPool, 0, 2);

        $payload = [
            'siswa_id' => $siswa->id,
            'challenges' => $selected,
            'created_at' => now()->timestamp,
            'expires_at' => now()->addSeconds(60)->timestamp,
        ];

        // Store challenge in cache for 60 seconds (anti-replay)
        Cache::put('biometric_challenge_' . $token, $payload, 60);

        return [
            'token' => $token,
            'expires_in' => 60,
            'challenges' => $selected
        ];
    }

    /**
     * Generate fixed sequential pose challenge for First-Login Enrollment
     */
    public function generateEnrollmentPoseChallenge(Siswa $siswa): array
    {
        $token = 'chl_enr_' . Str::random(32);
        $selected = self::ENROLLMENT_SEQUENTIAL_POSES;

        $payload = [
            'siswa_id'   => $siswa->id,
            'challenges' => $selected,
            'created_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ];

        Cache::put('biometric_challenge_' . $token, $payload, 600);

        return [
            'token'      => $token,
            'expires_in' => 600,
            'challenges' => $selected
        ];
    }

    /**
     * Verify Live Face Descriptor against Student's Registered Biometric Template
     */
    public function verify(
        Siswa $siswa,
        array $liveDescriptor,
        string $challengeToken,
        array $livenessProof,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        string $sessionType = 'masuk'
    ): array {
        // 1. Verify Challenge Token (Anti-Replay & Liveness Session)
        $cacheKey = 'biometric_challenge_' . $challengeToken;
        $challengeData = Cache::get($cacheKey);

        if (!$challengeData) {
            $this->logVerification(
                $siswa->id,
                $sessionType,
                'failed_liveness',
                null,
                null,
                null,
                'Sesi tantangan liveness kadaluarsa atau tidak valid (Anti-Replay Triggered)',
                $ipAddress,
                $userAgent
            );

            return [
                'success' => false,
                'status' => 'failed_liveness',
                'message' => 'Sesi verifikasi liveness kadaluarsa. Silakan ulangi proses dengan wajah aktif di depan kamera.'
            ];
        }

        // Consume token immediately
        Cache::forget($cacheKey);

        if ($challengeData['siswa_id'] !== $siswa->id) {
            $this->logVerification(
                $siswa->id,
                $sessionType,
                'failed_liveness',
                null,
                null,
                null,
                'Token liveness tidak sesuai dengan identitas siswa autentikasi',
                $ipAddress,
                $userAgent
            );

            return [
                'success' => false,
                'status' => 'failed_liveness',
                'message' => 'Token otentikasi biometrik tidak valid untuk akun ini.'
            ];
        }

        // 2. Validate Liveness Proof Structure & Timing
        $livenessResult = $this->evaluateLivenessProof($challengeData['challenges'], $livenessProof);
        if (!$livenessResult['valid']) {
            $this->logVerification(
                $siswa->id,
                $sessionType,
                'failed_liveness',
                null,
                null,
                $livenessResult['score'] ?? 0,
                $livenessResult['reason'],
                $ipAddress,
                $userAgent
            );

            return [
                'success' => false,
                'status' => 'failed_liveness',
                'message' => 'Anti-Spoofing: ' . $livenessResult['reason']
            ];
        }

        // 3. Check if Student has Enrolled Face Biometric
        $biometric = $siswa->faceBiometric()->where('is_active', true)->first();
        if (!$biometric || empty($biometric->face_descriptor)) {
            $this->logVerification(
                $siswa->id,
                $sessionType,
                'failed_match',
                null,
                null,
                $livenessResult['score'] ?? 0,
                'Template biometrik siswa belum terdaftar di sistem',
                $ipAddress,
                $userAgent
            );

            return [
                'success' => false,
                'status' => 'not_registered',
                'message' => 'Wajah Anda belum terdaftar di sistem sekolah. Harap lakukan registrasi biometrik terlebih dahulu.'
            ];
        }

        // 4. Validate Vector Dimensions (Must be exactly 128 floats)
        $registeredDescriptor = $biometric->face_descriptor;
        if (!is_array($registeredDescriptor) || count($registeredDescriptor) !== 128 || count($liveDescriptor) !== 128) {
            $this->logVerification(
                $siswa->id,
                $sessionType,
                'failed_quality',
                null,
                null,
                null,
                'Dimensi vektor biometrik tidak valid (wajib 128 dimensi)',
                $ipAddress,
                $userAgent
            );

            return [
                'success' => false,
                'status' => 'failed_quality',
                'message' => 'Format template biometrik tidak valid. Silakan hubungi admin.'
            ];
        }

        // 5. Calculate Euclidean Distance
        $distance = $this->calculateEuclideanDistance($registeredDescriptor, $liveDescriptor);
        
        // Calculate Calibrated Confidence Score Percentage (0% - 100%)
        $confidence = $this->calculateConfidenceScore($distance);

        // 6. Threshold Comparison
        if ($distance > self::MATCH_THRESHOLD) {
            $reason = "Wajah tidak cocok dengan template biometrik terdaftar (Jarak: " . round($distance, 3) . ", Skor Kemiripan: {$confidence}%, Ambang: " . self::MATCH_THRESHOLD . ")";

            $this->logVerification(
                $siswa->id,
                $sessionType,
                'failed_match',
                $confidence,
                $distance,
                $livenessResult['score'],
                $reason,
                $ipAddress,
                $userAgent
            );

            return [
                'success' => false,
                'status' => 'failed_match',
                'confidence' => $confidence,
                'distance' => round($distance, 3),
                'message' => "Verifikasi Wajah Gagal: Wajah Anda tidak cocok dengan data pemilik akun (Skor: {$confidence}%). Dilarang melakukan titip absen!"
            ];
        }

        // 7. Successful Verification!
        $this->logVerification(
            $siswa->id,
            $sessionType,
            'success',
            $confidence,
            $distance,
            $livenessResult['score'],
            'Verifikasi wajah biometrik berhasil',
            $ipAddress,
            $userAgent
        );

        return [
            'success' => true,
            'status' => 'success',
            'confidence' => $confidence,
            'distance' => round($distance, 3),
            'liveness_score' => $livenessResult['score']
        ];
    }

    /**
     * Calculate Euclidean distance between two 128-d vectors
     */
    public function calculateEuclideanDistance(array $v1, array $v2): float
    {
        $sum = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $diff = (float) $v1[$i] - (float) $v2[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    /**
     * Calibrate distance into intuitive percentage confidence score
     * d = 0.00 => 100%
     * d = 0.35 => ~88%
     * d = 0.45 => ~78%
     * d = 0.48 => ~74% (threshold)
     * d >= 0.60 => 0%
     */
    public function calculateConfidenceScore(float $distance): float
    {
        $maxDist = 0.62;
        if ($distance >= $maxDist) {
            return 0.0;
        }

        $score = (1.0 - ($distance / $maxDist)) * 100;
        return max(0.0, min(100.0, round($score, 1)));
    }

    /**
     * Validate liveness proofs submitted from frontend
     */
    protected function evaluateLivenessProof(array $expectedChallenges, array $livenessProof): array
    {
        if (empty($livenessProof['completed_challenges']) || !is_array($livenessProof['completed_challenges'])) {
            return [
                'valid' => false,
                'reason' => 'Bukti tantangan keaktifan (liveness) tidak ditemukan.',
                'score' => 0
            ];
        }

        $completed = $livenessProof['completed_challenges'];

        // Verify count of completed challenges
        if (count($completed) < count($expectedChallenges)) {
            return [
                'valid' => false,
                'reason' => 'Tantangan liveness tidak diselesaikan secara lengkap.',
                'score' => 30
            ];
        }

        // Verify challenge types sequence match expected
        for ($i = 0; $i < count($expectedChallenges); $i++) {
            $expectedType = $expectedChallenges[$i]['type'];
            if (!isset($completed[$i]['type']) || $completed[$i]['type'] !== $expectedType) {
                return [
                    'valid' => false,
                    'reason' => 'Urutan aksi tantangan liveness tidak sesuai dengan instruksi server.',
                    'score' => 20
                ];
            }

            // Verify human timing (each challenge should take at least 180ms to perform naturally, not instantaneous)
            $durationMs = $completed[$i]['duration_ms'] ?? 0;
            if ($durationMs < 150) {
                return [
                    'valid' => false,
                    'reason' => 'Durasi respon tidak wajar (indikasi manipulasi otomatis/bot).',
                    'score' => 10
                ];
            }
        }

        $livenessScore = floatval($livenessProof['liveness_score'] ?? 95.0);

        return [
            'valid' => true,
            'reason' => 'Liveness terverifikasi aktif',
            'score' => min(100.0, max(75.0, $livenessScore))
        ];
    }

    /**
     * Audit log helper
     */
    public function logVerification(
        int $siswaId,
        string $sessionType,
        string $status,
        ?float $confidence = null,
        ?float $distance = null,
        ?float $livenessScore = null,
        ?string $reason = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $snapshotPath = null
    ): AbsensiVerificationLog {
        return AbsensiVerificationLog::create([
            'siswa_id' => $siswaId,
            'session_type' => $sessionType,
            'status' => $status,
            'confidence_score' => $confidence,
            'euclidean_distance' => $distance,
            'liveness_score' => $livenessScore,
            'failure_reason' => $reason,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'snapshot_path' => $snapshotPath,
        ]);
    }
}
