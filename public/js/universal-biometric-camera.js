/**
 * UniversalBiometricCamera - Device-Independent Biometric Camera Engine
 * SMK Negeri Biometric Attendance System
 *
 * Supported Devices:
 * - Smartphones: Android & iOS (Front Camera prioritised)
 * - Laptops: Windows, macOS, Linux (Integrated Webcam)
 * - Desktop PCs: Windows, macOS, Linux (USB External Webcams)
 * - Tablets: iPadOS, Android Tablets (Front / Active Camera)
 *
 * Standards:
 * - HTML5 MediaDevices & WebRTC Camera API
 * - HTTPS / Localhost secure context enforcement
 * - Dynamic resolution adaptation & graceful constraint fallbacks
 * - Multi-device enumeration & hot-swapping
 * - Zero raw image persistence in localStorage / browser cache
 */

class UniversalBiometricCamera {
    constructor(options = {}) {
        this.videoEl = options.videoElement || null;
        this.selectEl = options.selectElement || null;
        this.deviceBadgeEl = options.deviceBadgeElement || null;
        this.resolutionBadgeEl = options.resolutionBadgeElement || null;

        this.onReady = options.onReady || (() => {});
        this.onError = options.onError || (() => {});
        this.onDeviceChange = options.onDeviceChange || (() => {});

        this.stream = null;
        this.currentDeviceId = null;
        this.availableDevices = [];
        this.platformInfo = this.detectPlatform();
        this.isStarted = false;

        // Auto clean-up when user navigates away
        window.addEventListener('beforeunload', () => this.stop());
    }

    /**
     * Detect client platform and device form factor
     */
    detectPlatform() {
        const ua = navigator.userAgent || '';
        const isIOS = /iPhone|iPad|iPod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        const isAndroid = /Android/i.test(ua);
        const isMobile = isIOS || isAndroid || /Mobile/i.test(ua);
        const isTablet = /(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua) || (isIOS && !/iPhone/i.test(ua));
        const isDesktop = !isMobile && !isTablet;

        let type = 'desktop';
        let label = 'Desktop PC / Laptop';
        let defaultFacing = 'user';

        if (isTablet) {
            type = 'tablet';
            label = 'Tablet';
            defaultFacing = 'user';
        } else if (isMobile) {
            type = 'smartphone';
            label = isIOS ? 'Smartphone iOS (iPhone)' : 'Smartphone Android';
            defaultFacing = 'user';
        }

        return {
            type,
            label,
            isMobile,
            isTablet,
            isDesktop,
            defaultFacing,
            userAgent: ua
        };
    }

    /**
     * Check if environment supports Camera MediaDevices in a secure context
     */
    static checkCompatibility() {
        const isSecure = window.isSecureContext ||
                         location.protocol === 'https:' ||
                         location.hostname === 'localhost' ||
                         location.hostname === '127.0.0.1';

        const hasMediaDevices = Boolean(navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function');

        if (!isSecure) {
            return {
                supported: false,
                code: 'INSECURE_CONTEXT',
                message: 'Akses kamera diblokir browser karena halaman dibuka tanpa protokol aman (HTTPS atau localhost).'
            };
        }

        if (!hasMediaDevices) {
            return {
                supported: false,
                code: 'API_UNSUPPORTED',
                message: 'Browser Anda tidak mendukung WebRTC Camera API. Gunakan browser modern seperti Google Chrome, Safari, atau Firefox versi terbaru.'
            };
        }

        return { supported: true };
    }

    /**
     * Enumerate all connected video input devices
     */
    async refreshDeviceList() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
            this.availableDevices = [];
            return [];
        }

        try {
            const allDevices = await navigator.mediaDevices.enumerateDevices();
            const videoInputs = allDevices.filter(d => d.kind === 'videoinput');

            let frontCount = 0;
            let backCount = 0;
            let usbCount = 0;

            this.availableDevices = videoInputs.map((d, index) => {
                let friendlyLabel = d.label || '';

                if (!friendlyLabel) {
                    if (this.platformInfo.isMobile) {
                        friendlyLabel = index === 0 ? 'Kamera Depan (Front)' : `Kamera ${index + 1}`;
                    } else {
                        friendlyLabel = index === 0 ? 'Webcam Bawaan (Integrated)' : `Webcam Eksternal ${index}`;
                    }
                } else {
                    const lower = friendlyLabel.toLowerCase();
                    if (lower.includes('front') || lower.includes('user') || lower.includes('facetime')) {
                        frontCount++;
                        friendlyLabel = `Kamera Depan (${friendlyLabel})`;
                    } else if (lower.includes('back') || lower.includes('rear') || lower.includes('environment')) {
                        backCount++;
                        friendlyLabel = `Kamera Belakang (${friendlyLabel})`;
                    } else if (lower.includes('usb') || lower.includes('external')) {
                        usbCount++;
                        friendlyLabel = `Webcam USB (${friendlyLabel})`;
                    }
                }

                return {
                    deviceId: d.deviceId,
                    label: friendlyLabel,
                    rawLabel: d.label,
                    groupId: d.groupId
                };
            });

            this.updateSelectDropdown();
            return this.availableDevices;
        } catch (err) {
            console.warn('[UniversalBiometricCamera] enumerateDevices error:', err);
            return [];
        }
    }

    /**
     * Update the HTML select dropdown with detected camera list
     */
    updateSelectDropdown() {
        if (!this.selectEl) return;

        this.selectEl.innerHTML = '';

        if (this.availableDevices.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Kamera Default Perangkat';
            this.selectEl.appendChild(opt);
            return;
        }

        this.availableDevices.forEach((dev, idx) => {
            const opt = document.createElement('option');
            opt.value = dev.deviceId;
            opt.textContent = dev.label;
            if (this.currentDeviceId && dev.deviceId === this.currentDeviceId) {
                opt.selected = true;
            } else if (!this.currentDeviceId && idx === 0) {
                opt.selected = true;
            }
            this.selectEl.appendChild(opt);
        });

        // Hide dropdown if only 1 device is detected, but keep it available
        if (this.availableDevices.length <= 1) {
            this.selectEl.parentElement?.classList.add('device-single-camera');
        } else {
            this.selectEl.parentElement?.classList.remove('device-single-camera');
        }

        // Attach change listener once
        if (!this.selectEl._hasCameraChangeListener) {
            this.selectEl._hasCameraChangeListener = true;
            this.selectEl.addEventListener('change', async (e) => {
                const newDeviceId = e.target.value;
                if (newDeviceId && newDeviceId !== this.currentDeviceId) {
                    await this.switchCamera(newDeviceId);
                }
            });
        }
    }

    /**
     * Start camera stream with adaptive constraints & fallbacks
     * @param {string|null} preferredDeviceId
     */
    async start(preferredDeviceId = null) {
        const compat = UniversalBiometricCamera.checkCompatibility();
        if (!compat.supported) {
            this.handleError(compat.code, compat.message, null);
            return false;
        }

        // Clean any existing stream
        this.stop();

        const targetDeviceId = preferredDeviceId || this.currentDeviceId || (this.selectEl ? this.selectEl.value : null);

        // Build cascading constraint profiles from High Quality down to Basic
        const constraintProfiles = this.buildConstraintProfiles(targetDeviceId);

        let activeStream = null;
        let lastError = null;

        for (const constraints of constraintProfiles) {
            try {
                activeStream = await navigator.mediaDevices.getUserMedia(constraints);
                if (activeStream) break;
            } catch (err) {
                lastError = err;
                // If overconstrained or track start failure, continue to more relaxed profile
                if (err.name === 'OverconstrainedError' || err.name === 'ConstraintNotSatisfiedError') {
                    console.warn('[UniversalBiometricCamera] Relaxing constraints from:', constraints);
                    continue;
                }
                // Terminal errors (permission denied, not found, not readable) break immediately
                break;
            }
        }

        if (!activeStream) {
            this.parseAndDispatchError(lastError);
            return false;
        }

        this.stream = activeStream;
        this.isStarted = true;

        if (this.videoEl) {
            this.videoEl.srcObject = activeStream;
            this.videoEl.setAttribute('playsinline', 'true');
            this.videoEl.setAttribute('autoplay', 'true');
            this.videoEl.muted = true;

            // Wait until video metadata (resolution) is loaded
            await new Promise((resolve) => {
                if (this.videoEl.readyState >= 2) {
                    resolve();
                } else {
                    this.videoEl.onloadedmetadata = () => resolve();
                }
            });

            try {
                await this.videoEl.play();
            } catch (playErr) {
                console.warn('[UniversalBiometricCamera] video.play() note:', playErr);
            }
        }

        // Inspect actual track settings
        const videoTrack = activeStream.getVideoTracks()[0];
        if (videoTrack) {
            const settings = videoTrack.getSettings ? videoTrack.getSettings() : {};
            this.currentDeviceId = settings.deviceId || targetDeviceId;

            // Update badge info
            this.updateBadges(settings);
        }

        // Enumerate devices now that permission is granted (to get proper labels)
        await this.refreshDeviceList();

        this.onReady(this.stream, {
            deviceId: this.currentDeviceId,
            width: this.videoEl ? this.videoEl.videoWidth : 640,
            height: this.videoEl ? this.videoEl.videoHeight : 480,
            platform: this.platformInfo
        });

        return true;
    }

    /**
     * Build constraint profiles tailored to device type & target camera
     */
    buildConstraintProfiles(targetDeviceId) {
        const isMobile = this.platformInfo.isMobile;

        // Profile 1: Optimal for Biometric Face Recognition
        const profile1 = { audio: false, video: {} };
        if (targetDeviceId) {
            profile1.video.deviceId = { exact: targetDeviceId };
        } else if (isMobile) {
            profile1.video.facingMode = { ideal: 'user' };
        }

        if (isMobile) {
            profile1.video.width = { ideal: 640, max: 1280 };
            profile1.video.height = { ideal: 480, max: 960 };
        } else {
            // Laptop / Desktop USB Webcam
            profile1.video.width = { ideal: 1280, min: 640 };
            profile1.video.height = { ideal: 720, min: 480 };
        }

        // Profile 2: Standard SVGA fallback
        const profile2 = { audio: false, video: {} };
        if (targetDeviceId) {
            profile2.video.deviceId = targetDeviceId;
        } else if (isMobile) {
            profile2.video.facingMode = 'user';
        }
        profile2.video.width = { ideal: 640 };
        profile2.video.height = { ideal: 480 };

        // Profile 3: Pure basic fallback (supports low-end 480p/360p webcams)
        const profile3 = {
            audio: false,
            video: targetDeviceId ? { deviceId: targetDeviceId } : true
        };

        return [profile1, profile2, profile3];
    }

    /**
     * Switch to a specific camera device ID
     */
    async switchCamera(newDeviceId) {
        if (!newDeviceId || newDeviceId === this.currentDeviceId) return;

        console.log('[UniversalBiometricCamera] Switching to camera:', newDeviceId);
        this.currentDeviceId = newDeviceId;

        const success = await this.start(newDeviceId);
        if (success) {
            this.onDeviceChange(newDeviceId, this.getSelectedDeviceLabel());
        }
        return success;
    }

    /**
     * Toggle between front and back camera (convenient for mobile devices)
     */
    async toggleFacingMode() {
        if (!this.availableDevices || this.availableDevices.length < 2) return;

        const currentIndex = this.availableDevices.findIndex(d => d.deviceId === this.currentDeviceId);
        const nextIndex = (currentIndex + 1) % this.availableDevices.length;
        const nextDevice = this.availableDevices[nextIndex];

        if (nextDevice) {
            return await this.switchCamera(nextDevice.deviceId);
        }
    }

    /**
     * Stop camera stream & release hardware locks
     */
    stop() {
        this.isStarted = false;
        if (this.stream) {
            try {
                this.stream.getTracks().forEach(track => {
                    track.stop();
                });
            } catch (e) {
                console.warn('[UniversalBiometricCamera] Stop track warning:', e);
            }
            this.stream = null;
        }

        if (this.videoEl) {
            this.videoEl.srcObject = null;
        }

        if (this.deviceBadgeEl) {
            this.deviceBadgeEl.textContent = 'Kamera Nonaktif';
            this.deviceBadgeEl.className = 'px-2 py-0.5 rounded text-[11px] font-medium bg-dark-100 text-dark-500';
        }
    }

    /**
     * Update UI Badges (Device Name & Resolution)
     */
    updateBadges(settings = {}) {
        const width = this.videoEl ? this.videoEl.videoWidth : (settings.width || 0);
        const height = this.videoEl ? this.videoEl.videoHeight : (settings.height || 0);

        if (this.resolutionBadgeEl && width > 0 && height > 0) {
            this.resolutionBadgeEl.textContent = `${width}x${height}px`;
            this.resolutionBadgeEl.className = 'px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
        }

        if (this.deviceBadgeEl) {
            const devLabel = this.getSelectedDeviceLabel() || this.platformInfo.label;
            this.deviceBadgeEl.textContent = devLabel;
            this.deviceBadgeEl.className = 'px-2 py-0.5 rounded text-[11px] font-semibold bg-primary-50 text-primary-700 border border-primary-200 truncate max-w-[180px]';
        }
    }

    /**
     * Get label of currently selected device
     */
    getSelectedDeviceLabel() {
        if (!this.currentDeviceId) return this.platformInfo.label;
        const found = this.availableDevices.find(d => d.deviceId === this.currentDeviceId);
        return found ? found.label : this.platformInfo.label;
    }

    /**
     * Capture high-quality snapshot frame to Data URL (base64)
     * Resizes dynamically to optimal 640x480 ratio for biometric server processing
     */
    captureSnapshot(targetWidth = 640, targetHeight = 480, quality = 0.92) {
        if (!this.videoEl || !this.videoEl.videoWidth) return null;

        const canvas = document.createElement('canvas');
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        const ctx = canvas.getContext('2d');

        // Mirror snapshot if front camera / desktop webcam
        ctx.translate(targetWidth, 0);
        ctx.scale(-1, 1);

        ctx.drawImage(this.videoEl, 0, 0, targetWidth, targetHeight);
        return canvas.toDataURL('image/jpeg', quality);
    }

    /**
     * Evaluate camera stream quality (illumination & clarity)
     */
    assessStreamQuality() {
        if (!this.videoEl || !this.videoEl.videoWidth) {
            return { isAdequate: false, score: 0, reason: 'Kamera belum siap' };
        }

        const width = this.videoEl.videoWidth;
        const height = this.videoEl.videoHeight;

        if (width < 320 || height < 240) {
            return {
                isAdequate: false,
                score: 30,
                reason: 'Resolusi kamera terlalu rendah. Gunakan webcam minimal 480p atau komputer Lab Sekolah.'
            };
        }

        // Sample frame brightness
        const canvas = document.createElement('canvas');
        canvas.width = 64;
        canvas.height = 48;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(this.videoEl, 0, 0, 64, 48);
        const imgData = ctx.getImageData(0, 0, 64, 48).data;

        let totalBrightness = 0;
        const pixelCount = imgData.length / 4;
        for (let i = 0; i < imgData.length; i += 4) {
            // Perceived luminance formula: (0.299*R + 0.587*G + 0.114*B)
            totalBrightness += (0.299 * imgData[i] + 0.587 * imgData[i + 1] + 0.114 * imgData[i + 2]);
        }
        const avgBrightness = totalBrightness / pixelCount;

        if (avgBrightness < 35) {
            return {
                isAdequate: false,
                score: 45,
                brightness: avgBrightness,
                reason: 'Pencahayaan terlalu gelap. Nyalakan lampu atau hadap ke sumber cahaya terang.'
            };
        }

        if (avgBrightness > 235) {
            return {
                isAdequate: false,
                score: 50,
                brightness: avgBrightness,
                reason: 'Wajah silau / overexposure. Hindari cahaya langsung di belakang kepala.'
            };
        }

        return {
            isAdequate: true,
            score: 95,
            brightness: avgBrightness,
            reason: 'Pencahayaan dan resolusi kamera optimal.'
        };
    }

    /**
     * Parse DOM exceptions into precise, user-actionable Indonesian error messages
     */
    parseAndDispatchError(err) {
        if (!err) {
            this.handleError('UNKNOWN_ERROR', 'Terjadi kendala saat menyalakan kamera perangkat.', null);
            return;
        }

        console.error('[UniversalBiometricCamera] Access Error:', err);

        let code = err.name || 'CAMERA_ERROR';
        let message = '';
        let guidance = '';

        switch (err.name) {
            case 'NotAllowedError':
            case 'PermissionDeniedError':
                code = 'PERMISSION_DENIED';
                message = 'Izin Akses Kamera Ditolak';
                guidance = 'Browser Anda memblokir akses ke kamera. Silakan klik ikon gembok / kamera pada bilah alamat (URL bar) browser Anda, lalu ubah status Kamera menjadi "Izinkan" (Allow), kemudian muat ulang halaman.';
                break;

            case 'NotFoundError':
            case 'DevicesNotFoundError':
                code = 'DEVICE_NOT_FOUND';
                message = 'Kamera / Webcam Tidak Ditemukan';
                guidance = 'Sistem tidak mendeteksi adanya perangkat kamera pada perangkat ini. Jika menggunakan Desktop PC, pastikan kabel USB webcam terpasang dengan erat. Anda juga dapat menggunakan komputer di Laboratorium Sekolah.';
                break;

            case 'NotReadableError':
            case 'TrackStartError':
                code = 'DEVICE_IN_USE';
                message = 'Kamera Sedang Digunakan Aplikasi Lain';
                guidance = 'Webcam sedang dipakai oleh aplikasi lain (seperti Zoom, Google Meet, Microsoft Teams, atau Aplikasi Kamera Windows). Silakan tutup aplikasi tersebut terlebih dahulu, lalu klik tombol "Coba Lagi".';
                break;

            case 'OverconstrainedError':
            case 'ConstraintNotSatisfiedError':
                code = 'OVERCONSTRAINED';
                message = 'Spesifikasi Kamera Tidak Memenuhi';
                guidance = 'Resolusi atau parameter kamera tidak didukung oleh perangkat keras webcam Anda.';
                break;

            case 'SecurityError':
                code = 'INSECURE_CONTEXT';
                message = 'Koneksi Tidak Aman';
                guidance = 'Akses kamera WebRTC wajib dijalankan melalui protokol HTTPS atau koneksi localhost.';
                break;

            default:
                code = 'CAMERA_FAILURE';
                message = 'Kamera Tidak Dapat Diaktifkan';
                guidance = err.message || 'Periksa apakah kamera terpasang dengan baik dan browser memiliki izin akses perangkat keras.';
        }

        this.handleError(code, message, guidance, err);
    }

    /**
     * Dispatch error event and notify UI
     */
    handleError(code, title, guidance, rawError = null) {
        this.stop();
        this.onError({
            code,
            title,
            guidance: guidance || title,
            rawError
        });
    }
}

// Export for browser global scope
window.UniversalBiometricCamera = UniversalBiometricCamera;
