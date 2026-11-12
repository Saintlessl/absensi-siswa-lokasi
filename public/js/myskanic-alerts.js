/**
 * MySkanic Design System - Global SweetAlert2 Helper & Standardization
 * Unified Notification & Dialog Service
 */
(() => {
    'use strict';

    const getSwal = () => window.Swal;

    const baseClasses = {
        popup: 'myskanic-swal-popup',
        title: 'myskanic-swal-title',
        htmlContainer: 'myskanic-swal-content',
        confirmButton: 'myskanic-swal-confirm',
        cancelButton: 'myskanic-swal-cancel',
        actions: 'myskanic-swal-actions',
    };

    const baseConfig = {
        buttonsStyling: false,
        reverseButtons: true,
        customClass: baseClasses,
    };

    const fire = (options) => {
        const swal = getSwal();
        if (!swal) {
            console.warn('[MySkanicAlert] SweetAlert2 belum dimuat di halaman ini.');
            return Promise.resolve({ isConfirmed: false });
        }
        return swal.fire({ ...baseConfig, ...options });
    };

    const MySkanicAlert = {
        /**
         * Global Toast Notification (Top-End)
         */
        toast(message, icon = 'success', timer = 3500) {
            const swal = getSwal();
            if (!swal) return Promise.resolve();

            return swal.fire({
                toast: true,
                position: 'top-end',
                icon,
                title: message,
                showConfirmButton: false,
                timer,
                timerProgressBar: true,
                customClass: {
                    popup: 'myskanic-swal-toast',
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', swal.stopTimer);
                    toast.addEventListener('mouseleave', swal.resumeTimer);
                }
            });
        },

        /**
         * Success Dialog / Toast
         */
        success(message, title = 'Berhasil!') {
            return this.toast(message || title, 'success');
        },

        /**
         * Success Modal (When explicit modal dialog is needed)
         */
        successModal(message, title = 'Berhasil!') {
            return fire({
                icon: 'success',
                title,
                text: message,
                confirmButtonText: 'Selesai',
            });
        },

        /**
         * Error Dialog
         */
        error(message, title = 'Terjadi Kesalahan') {
            return fire({
                icon: 'error',
                title,
                text: message,
                confirmButtonText: 'Tutup',
            });
        },

        /**
         * Warning Dialog
         */
        warning(message, title = 'Peringatan') {
            return fire({
                icon: 'warning',
                title,
                text: message,
                confirmButtonText: 'Mengerti',
            });
        },

        /**
         * Information Dialog
         */
        info(message, title = 'Informasi') {
            return fire({
                icon: 'info',
                title,
                text: message,
                confirmButtonText: 'Mengerti',
            });
        },

        /**
         * Form Validation Errors Summary Dialog
         */
        validation(messages, title = 'Periksa Kembali Formulir') {
            const list = Array.isArray(messages) ? messages : [messages];
            const items = list.map((msg) => `<li>${String(msg)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')}</li>`).join('');

            return fire({
                icon: 'error',
                title,
                html: `<ul class="myskanic-swal-list">${items}</ul>`,
                confirmButtonText: 'Perbaiki Data',
            });
        },

        /**
         * Confirmation Dialog (Destructive & Normal)
         */
        confirm({
            title = 'Konfirmasi Tindakan',
            text = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            html = null,
            confirmText = 'Ya, Lanjutkan',
            cancelText = 'Batal',
            danger = false,
            icon = null
        } = {}) {
            return fire({
                icon: icon || (danger ? 'warning' : 'question'),
                title,
                text: html ? undefined : text,
                html: html || undefined,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                customClass: {
                    ...baseClasses,
                    confirmButton: danger ? 'myskanic-swal-danger' : 'myskanic-swal-confirm',
                },
            });
        },

        /**
         * Value Prompt Input Dialog
         */
        prompt({
            title = 'Masukkan Nilai',
            text = '',
            input = 'text',
            inputValue = '',
            inputPlaceholder = '',
            inputAttributes = {},
            inputValidator = null,
            confirmText = 'Simpan',
            cancelText = 'Batal'
        } = {}) {
            return fire({
                title,
                text,
                input,
                inputValue,
                inputPlaceholder,
                inputAttributes,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                inputValidator,
            });
        },

        /**
         * Loading Indicator Dialog
         */
        loading(title = 'Memproses...', text = 'Mohon tunggu sebentar.') {
            const swal = getSwal();
            if (!swal) return;

            return swal.fire({
                title,
                text,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                customClass: {
                    popup: 'myskanic-swal-popup text-center',
                    title: 'myskanic-swal-title',
                    htmlContainer: 'myskanic-swal-content',
                },
                didOpen: () => {
                    swal.showLoading();
                },
            });
        },

        /**
         * Close Active Dialog
         */
        close() {
            const swal = getSwal();
            if (swal) swal.close();
        },

        /**
         * Standard HTTP Error Resolver
         */
        httpError(status, customMessage = null) {
            const messages = {
                400: ['Permintaan tidak valid atau format salah.', 'Permintaan Tidak Valid (400)'],
                401: ['Sesi autentikasi Anda telah berakhir. Silakan login kembali.', 'Autentikasi Diperlukan (401)'],
                403: ['Anda tidak memiliki wewenang untuk melakukan tindakan ini.', 'Akses Ditolak (403)'],
                404: ['Data atau halaman yang Anda cari tidak ditemukan.', 'Tidak Ditemukan (404)'],
                419: ['Sesi keamanan formulir telah kadaluarsa. Halaman akan dimuat ulang.', 'Sesi Kadaluarsa (419)'],
                422: ['Data yang Anda kirimkan tidak memenuhi validasi server.', 'Validasi Gagal (422)'],
                429: ['Terlalu banyak permintaan dalam waktu singkat. Mohon tunggu beberapa saat.', 'Batas Permintaan (429)'],
                500: ['Terjadi kendala teknis pada server. Silakan coba kembali nanti.', 'Kesalahan Server (500)'],
            };

            const [defaultMsg, title] = messages[status] || ['Terjadi kesalahan yang tidak terduga.', 'Terjadi Kesalahan'];
            const message = customMessage || defaultMsg;

            return this.error(message, title);
        },
    };

    window.MySkanicAlert = Object.freeze(MySkanicAlert);

    // =========================================================================
    // GLOBAL CONFIRMATION DELEGATION HANDLER FOR FORMS
    // Supports: <form data-confirm="Teks konfirmasi">
    // =========================================================================
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-confirm], form[data-swal-confirm]');
        if (!form) return;

        // If form has already been confirmed by user, allow submission
        if (form.dataset.swalConfirmed === 'true') {
            delete form.dataset.swalConfirmed;
            return;
        }

        event.preventDefault();

        const title = form.dataset.confirmTitle || form.dataset.swalTitle || 'Konfirmasi Tindakan';
        const text = form.dataset.confirm || form.dataset.swalConfirm || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        const confirmText = form.dataset.confirmBtn || form.dataset.swalConfirmText || 'Ya, Lanjutkan';
        const cancelText = form.dataset.cancelBtn || form.dataset.swalCancelText || 'Batal';

        // Auto-detect danger/destructive actions
        const isDanger = form.dataset.confirmDanger === 'true'
            || form.dataset.swalDanger === 'true'
            || form.getAttribute('method')?.toUpperCase() === 'DELETE'
            || /hapus|delete|batalkan|reset|nonaktifkan|suspend|lepaskan|keluarkan/i.test(text + ' ' + title);

        const result = await MySkanicAlert.confirm({
            title,
            text,
            confirmText,
            cancelText,
            danger: isDanger,
        });

        if (result?.isConfirmed) {
            form.dataset.swalConfirmed = 'true';
            if (isDanger || form.dataset.loading === 'true') {
                MySkanicAlert.loading(form.dataset.loadingText || 'Memproses tindakan...', 'Mohon tunggu sebentar.');
            }
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }
    });

    // =========================================================================
    // GLOBAL CONFIRMATION DELEGATION HANDLER FOR LINKS / BUTTONS
    // Supports: <a data-confirm="Teks konfirmasi"> or <button data-confirm="...">
    // =========================================================================
    document.addEventListener('click', async (event) => {
        const el = event.target.closest('a[data-confirm], button[data-confirm]:not([type="submit"])');
        if (!el) return;

        if (el.dataset.swalConfirmed === 'true') {
            delete el.dataset.swalConfirmed;
            return;
        }

        event.preventDefault();

        const title = el.dataset.confirmTitle || 'Konfirmasi Tindakan';
        const text = el.dataset.confirm;
        const confirmText = el.dataset.confirmBtn || 'Ya, Lanjutkan';
        const cancelText = el.dataset.cancelBtn || 'Batal';
        const isDanger = el.dataset.confirmDanger === 'true' || /hapus|delete|batalkan|reset/i.test(text + ' ' + title);

        const result = await MySkanicAlert.confirm({
            title,
            text,
            confirmText,
            cancelText,
            danger: isDanger,
        });

        if (result?.isConfirmed) {
            el.dataset.swalConfirmed = 'true';
            if (el.tagName === 'A' && el.href) {
                window.location.href = el.href;
            } else if (typeof el.onclick === 'function') {
                el.onclick();
            }
        }
    });

    // =========================================================================
    // SAFETY FALLBACK: Route legacy window.alert to SweetAlert2
    // so no native browser popup can ever leak through
    // =========================================================================
    if (typeof window !== 'undefined') {
        window.alert = function (message) {
            MySkanicAlert.warning(String(message), 'Perhatian');
        };
    }
})();
