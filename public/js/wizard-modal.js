/**
 * MySkanic Multi-Step Wizard Modal Handler
 * Reusable system for multi-step creation modals
 */

// Global modal helpers
window.openModal = function(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        el.scrollTop = 0;
        if (id === 'modal-inputsiswa') {
            const namaEl = document.getElementById('input_nama');
            if (!namaEl || !namaEl.value.trim()) {
                const uEl = document.getElementById('input_student_username');
                if (uEl) uEl.value = '';
            }
        }
    }
};

window.closeModal = function(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
};

/**
 * Dedicated Student Wizard Modal Handler
 */
function studentWizardModal() {
    return {
        step: 1,
        totalSteps: 3,
        modalId: 'modal-inputsiswa',
        formId: 'frmSiswa',
        isSubmitting: false,
        photoPreviewUrl: null,
        hasValidPhoto: false,
        isEmailManuallyEdited: false,

        // Student Data Model
        student: {
            nisn: '',
            nis: '',
            nama: '',
            username: '',
            no_telp_siswa: '',
            jenis_kelamin: '',
            tempat_lahir: '',
            tanggal_lahir: '',
            tahun_ajaran_id: '',
            tahun_ajaran_name: '',
            jalur_masuk: 'baru',
            asal_sekolah: '',
            jurusan_id: '',
            jurusan_name: '',
            angkatan_id: '',
            angkatan_name: '',
            tingkat: '10',
            kelas_id: '',
            kelas_name: '',
            email: '',
            password: '',
            parent_name: '',
            parent_jenis_kelamin: '',
            parent_phone: '',
            parent_address: '',
            verified: false
        },

        _syncLock: false,

        init() {
            // After a failed submit, Blade exposes old() input on window:
            // rehydrate Alpine state and reopen the modal so nothing is lost.
            if (window.__siswaWizardRecover) {
                Object.entries(window.__siswaWizardRecover).forEach(([k, v]) => {
                    if (v === null || v === undefined) return;
                    this.student[k] = String(v);
                    const el = document.getElementById(k) || document.getElementById('modal_' + k) || document.getElementById('input_' + k) || document.getElementById('input_student_' + k);
                    if (el && (el.tagName === 'SELECT' || el.tagName === 'INPUT')) el.value = String(v);
                });
                if (window.__siswaWizardRecover.confirm_parent_verified) this.student.verified = true;
                if (window.__siswaWizardRecover.is_manual_username === '1' || window.__siswaWizardRecover.is_manual_username === true) {
                    this.isEmailManuallyEdited = true;
                }
                if (window.__siswaWizardRecover.username) {
                    this.student.username = String(window.__siswaWizardRecover.username);
                    this.student.email = this.student.username + '@myskanic.com';
                }
            }
            if (window.__siswaWizardReopen) {
                window.openModal(this.modalId);
                delete window.__siswaWizardReopen;
            }

            window.addEventListener('wizard-photo-updated', (e) => {
                this.photoPreviewUrl = e.detail ? e.detail.url : null;
                this.hasValidPhoto = e.detail ? Boolean(e.detail.valid) : false;
            });

            // Watcher on student.nama to auto-generate student email username
            this.$watch('student.nama', (val) => {
                const trimmed = (val || '').trim();
                if (!trimmed) {
                    this.student.username = '';
                    this.student.email = '';
                    this.isEmailManuallyEdited = false;
                    const uInput = document.getElementById('input_student_username');
                    if (uInput) uInput.value = '';
                    return;
                }
                if (!this.isEmailManuallyEdited) {
                    this.generateEmailFromName(trimmed);
                }
            });

            // Watchers for instantaneous auto-detection and cascade synchronization
            this.$watch('student.tahun_ajaran_id', (val) => {
                if (!this._syncLock && val) {
                    this.onTahunAjaranChange();
                }
            });

            this.$watch('student.tingkat', (val) => {
                if (!this._syncLock && val) {
                    this.onTingkatChange();
                }
            });

            this.$watch('student.jurusan_id', (val) => {
                if (!this._syncLock) {
                    this.onJurusanChange();
                }
            });

            this.$watch('student.angkatan_id', (val) => {
                if (!this._syncLock && val) {
                    this.onAngkatanChange();
                }
            });

            // Initialize default active Tahun Ajaran and dependent states
            this.$nextTick(() => {
                const taSelect = document.getElementById('modal_tahun_ajaran');
                if (taSelect) {
                    const activeOpt = Array.from(taSelect.options).find(o => 
                        o.getAttribute('data-active') === '1' || o.text.includes('(Aktif)')
                    );
                    if (activeOpt && !this.student.tahun_ajaran_id) {
                        this.student.tahun_ajaran_id = activeOpt.value;
                        this.student.tahun_ajaran_name = activeOpt.text.trim();
                        taSelect.value = activeOpt.value;
                    } else if (taSelect.value) {
                        this.student.tahun_ajaran_id = taSelect.value;
                        this.student.tahun_ajaran_name = taSelect.options[taSelect.selectedIndex].text.trim();
                    }
                }
                this.onTahunAjaranChange();
            });
        },

        cleanNameForUsername(name) {
            if (!name) return '';
            let clean = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            if (clean.includes('@')) {
                clean = clean.split('@')[0];
            }
            return clean.replace(/[^a-z0-9._-]/g, '');
        },

        generateEmailFromName(name) {
            const trimmed = (name || '').trim();
            if (!trimmed) {
                this.student.username = '';
                this.student.email = '';
                this.isEmailManuallyEdited = false;
                return;
            }
            let clean = this.cleanNameForUsername(trimmed);
            this.student.username = clean;
            this.student.email = clean ? (clean + '@myskanic.com') : '';
        },

        onUsernameInput(event) {
            // Guard: Sebelum nama lengkap diisi, username email WAJIB dibiarkan kosong
            if (!this.student.nama || !this.student.nama.trim()) {
                this.student.username = '';
                this.student.email = '';
                this.isEmailManuallyEdited = false;
                event.target.value = '';
                return;
            }

            let val = event.target.value || '';
            val = val.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            if (val.includes('@')) {
                val = val.split('@')[0];
            }
            val = val.replace(/[^a-z0-9._-]/g, '');

            this.student.username = val;
            this.student.email = val ? (val + '@myskanic.com') : '';

            const defaultFromNama = this.cleanNameForUsername(this.student.nama);
            this.isEmailManuallyEdited = (val !== defaultFromNama);
        },

        onUsernamePaste(event) {
            event.preventDefault();
            // Guard: Sebelum nama lengkap diisi, paste diabaikan dan dibiarkan kosong
            if (!this.student.nama || !this.student.nama.trim()) {
                this.student.username = '';
                this.student.email = '';
                this.isEmailManuallyEdited = false;
                return;
            }

            const pasteData = (event.clipboardData || window.clipboardData).getData('text') || '';
            let val = pasteData.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            if (val.includes('@')) {
                val = val.split('@')[0];
            }
            val = val.replace(/[^a-z0-9._-]/g, '');

            this.student.username = val;
            this.student.email = val ? (val + '@myskanic.com') : '';

            const defaultFromNama = this.cleanNameForUsername(this.student.nama);
            this.isEmailManuallyEdited = (val !== defaultFromNama);
        },

        resetEmailToDefault() {
            this.isEmailManuallyEdited = false;
            this.generateEmailFromName(this.student.nama);
        },

        onNisChange() {
            // Password defaults to NIS or password123, email is NOT based on NIS
            if (!this.student.password) {
                this.student.password = (this.student.nis || '').trim() || 'password123';
            }
        },

        ensureEmailDomain() {
            let u = (this.student.username || '').trim().toLowerCase();
            if (u.includes('@')) {
                u = u.split('@')[0];
            }
            u = u.replace(/[^a-z0-9._-]/g, '');
            this.student.username = u;
            this.student.email = u ? (u + '@myskanic.com') : '';
        },

        getActiveSchoolYear() {
            const taSelect = document.getElementById('modal_tahun_ajaran');
            if (!taSelect) return 2026;
            const activeOpt = Array.from(taSelect.options).find(o => 
                o.getAttribute('data-active') === '1' || o.text.includes('(Aktif)')
            );
            if (activeOpt) {
                const yearAttr = activeOpt.getAttribute('data-tahun-mulai');
                if (yearAttr) return parseInt(yearAttr, 10);
                const m = activeOpt.text.match(/^(\d{4})/);
                if (m) return parseInt(m[1], 10);
            }
            return 2026;
        },

        onTahunAjaranChange() {
            if (this._syncLock) return;
            this._syncLock = true;
            try {
                const taSelect = document.getElementById('modal_tahun_ajaran');
                if (taSelect && taSelect.selectedIndex >= 0) {
                    this.student.tahun_ajaran_id = taSelect.value;
                    this.student.tahun_ajaran_name = taSelect.options[taSelect.selectedIndex].text.trim();
                    const opt = taSelect.options[taSelect.selectedIndex];
                    let entryYear = null;
                    const yearAttr = opt.getAttribute('data-tahun-mulai');
                    if (yearAttr) {
                        entryYear = parseInt(yearAttr, 10);
                    } else {
                        const m = opt.text.match(/^(\d{4})/);
                        if (m) entryYear = parseInt(m[1], 10);
                    }

                    if (entryYear) {
                        const activeYear = this.getActiveSchoolYear();
                        const diff = activeYear - entryYear;
                        let deducedTingkat = '10';
                        if (diff === 1) deducedTingkat = '11';
                        else if (diff >= 2) deducedTingkat = '12';

                        this.student.tingkat = deducedTingkat;
                        const tingkatSelect = document.getElementById('modal_tingkat');
                        if (tingkatSelect) tingkatSelect.value = deducedTingkat;
                    }
                }
                this.updateDependentAngkatan();
                this.updateDependentRombel();
            } finally {
                this._syncLock = false;
            }
        },

        onTingkatChange() {
            if (this._syncLock) return;
            this._syncLock = true;
            try {
                const tingkatSelect = document.getElementById('modal_tingkat');
                const tVal = parseInt(this.student.tingkat || (tingkatSelect ? tingkatSelect.value : '10'), 10);
                this.student.tingkat = String(tVal);
                if (tingkatSelect) tingkatSelect.value = String(tVal);

                const activeYear = this.getActiveSchoolYear();
                const expectedEntryYear = activeYear - (tVal - 10);

                // Auto-select corresponding Tahun Ajaran Masuk
                const taSelect = document.getElementById('modal_tahun_ajaran');
                if (taSelect) {
                    const matchedTa = Array.from(taSelect.options).find(o => {
                        const y = o.getAttribute('data-tahun-mulai');
                        if (y && parseInt(y, 10) === expectedEntryYear) return true;
                        return o.text.startsWith(String(expectedEntryYear));
                    });
                    if (matchedTa) {
                        this.student.tahun_ajaran_id = matchedTa.value;
                        this.student.tahun_ajaran_name = matchedTa.text.trim();
                        taSelect.value = matchedTa.value;
                    }
                }

                this.updateDependentAngkatan();
                this.updateDependentRombel();
            } finally {
                this._syncLock = false;
            }
        },

        onAngkatanChange() {
            if (this._syncLock) return;
            this._syncLock = true;
            try {
                const angkatanSelect = document.getElementById('modal_angkatan');
                if (!angkatanSelect || angkatanSelect.selectedIndex <= 0) {
                    this.student.angkatan_name = '';
                    return;
                }
                const selectedOpt = angkatanSelect.options[angkatanSelect.selectedIndex];
                this.student.angkatan_id = selectedOpt.value;
                this.student.angkatan_name = selectedOpt.text.trim();
                const entryYear = parseInt(selectedOpt.getAttribute('data-tahun-masuk'), 10);

                if (entryYear) {
                    const activeYear = this.getActiveSchoolYear();
                    const diff = activeYear - entryYear;
                    let deducedTingkat = '10';
                    if (diff === 1) deducedTingkat = '11';
                    else if (diff >= 2) deducedTingkat = '12';

                    this.student.tingkat = deducedTingkat;
                    const tingkatSelect = document.getElementById('modal_tingkat');
                    if (tingkatSelect) tingkatSelect.value = deducedTingkat;

                    // Sync Tahun Ajaran
                    const taSelect = document.getElementById('modal_tahun_ajaran');
                    if (taSelect) {
                        const matchedTa = Array.from(taSelect.options).find(o => {
                            const y = o.getAttribute('data-tahun-mulai');
                            if (y && parseInt(y, 10) === entryYear) return true;
                            return o.text.startsWith(String(entryYear));
                        });
                        if (matchedTa) {
                            this.student.tahun_ajaran_id = matchedTa.value;
                            this.student.tahun_ajaran_name = matchedTa.text.trim();
                            taSelect.value = matchedTa.value;
                        }
                    }
                }
                this.updateDependentRombel();
            } finally {
                this._syncLock = false;
            }
        },

        onJurusanChange() {
            const jurSelect = document.getElementById('modal_jurusan');
            if (jurSelect && jurSelect.selectedIndex > 0) {
                this.student.jurusan_id = jurSelect.value;
                const parts = jurSelect.options[jurSelect.selectedIndex].text.split(' - ');
                this.student.jurusan_name = parts[0].trim();
            } else {
                this.student.jurusan_id = '';
                this.student.jurusan_name = '';
            }
            this.updateDependentAngkatan();
            this.updateDependentRombel();
        },

        updateTahunAjaranName() {
            this.onTahunAjaranChange();
        },

        updateAngkatanName() {
            this.onAngkatanChange();
        },

        updateJalurMasuk() {
            if (this.student.jalur_masuk === 'baru') {
                this.student.asal_sekolah = '';
            }
            this.updateDependentAngkatan();
            this.updateDependentRombel();
        },

        updateDependentAngkatan() {
            const jurSelect = document.getElementById('modal_jurusan');
            if (jurSelect && jurSelect.selectedIndex > 0) {
                this.student.jurusan_id = jurSelect.value;
                const parts = jurSelect.options[jurSelect.selectedIndex].text.split(' - ');
                this.student.jurusan_name = parts[0].trim();
            } else {
                this.student.jurusan_name = '';
            }

            const angkatanSelect = document.getElementById('modal_angkatan');
            if (!angkatanSelect) return;

            const jId = String(this.student.jurusan_id || (jurSelect ? jurSelect.value : ''));
            const tVal = parseInt(this.student.tingkat || '10', 10);

            const activeYear = this.getActiveSchoolYear();
            const expectedEntryYear = activeYear - (tVal - 10);

            const options = angkatanSelect.querySelectorAll('option');
            let matchedOption = null;

            options.forEach(opt => {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                const optJ = opt.getAttribute('data-jurusan');
                const optYear = parseInt(opt.getAttribute('data-tahun-masuk'), 10);
                const matchJurusan = (!jId || optJ === jId);
                opt.hidden = !matchJurusan;

                if (matchJurusan && optYear === expectedEntryYear && !matchedOption) {
                    matchedOption = opt;
                }
            });

            // Auto-select matched cohort for this major & grade level
            if (matchedOption) {
                this.student.angkatan_id = matchedOption.value;
                this.student.angkatan_name = matchedOption.text.trim();
                angkatanSelect.value = matchedOption.value;
            } else if (!jId) {
                this.student.angkatan_id = '';
                this.student.angkatan_name = '';
                angkatanSelect.value = '';
            } else {
                const opt = angkatanSelect.options[angkatanSelect.selectedIndex];
                if (opt && opt.value) {
                    this.student.angkatan_name = opt.text.trim();
                } else {
                    this.student.angkatan_id = '';
                    this.student.angkatan_name = '';
                    angkatanSelect.value = '';
                }
            }
        },

        updateAngkatanName() {
            this.onAngkatanChange();
        },

        updateDependentRombel() {
            const rombelSelect = document.getElementById('kelas_id');
            if (!rombelSelect) return;

            const jId = String(this.student.jurusan_id || '');
            const tVal = String(this.student.tingkat || '');
            const options = rombelSelect.querySelectorAll('option');
            let matchingOptions = [];

            options.forEach(opt => {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                const optJ = opt.getAttribute('data-jurusan');
                const optT = opt.getAttribute('data-tingkat');
                const match = (!jId || optJ === jId) && (!tVal || optT === tVal);
                opt.hidden = !match;
                if (match) {
                    matchingOptions.push(opt);
                }
            });

            const currentValid = matchingOptions.some(o => o.value === String(this.student.kelas_id));
            if (!currentValid) {
                if (matchingOptions.length === 1) {
                    this.student.kelas_id = matchingOptions[0].value;
                    this.student.kelas_name = matchingOptions[0].text.trim();
                    rombelSelect.value = matchingOptions[0].value;
                } else {
                    this.student.kelas_id = '';
                    this.student.kelas_name = '';
                    rombelSelect.value = '';
                }
            } else {
                const opt = rombelSelect.options[rombelSelect.selectedIndex];
                if (opt) this.student.kelas_name = opt.text.trim();
            }
        },

        updateKelasName() {
            const rombelSelect = document.getElementById('kelas_id');
            if (rombelSelect && rombelSelect.selectedIndex > 0) {
                this.student.kelas_name = rombelSelect.options[rombelSelect.selectedIndex].text.trim();
            } else {
                this.student.kelas_name = '';
            }
        },

        nextStep() {
            const currentPanel = document.querySelector('#modal-inputsiswa [data-step="' + this.step + '"]');
            if (currentPanel) {
                const inputs = currentPanel.querySelectorAll('input:not([type="hidden"]), select, textarea');
                let isValid = true;

                for (const input of inputs) {
                    if (input.offsetParent === null) continue;
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('ring-2', 'ring-rose-500');
                        input.reportValidity();
                        break;
                    } else {
                        input.classList.remove('ring-2', 'ring-rose-500');
                    }
                }

                if (!isValid) return;

                // Step 1 email & name sync validation
                if (this.step === 1) {
                    // Mandatory Student Profile Photo Check
                    const fileInput = document.getElementById('student_photo');
                    const hasPhoto = (fileInput && fileInput.files && fileInput.files.length > 0) || this.hasValidPhoto || this.photoPreviewUrl;
                    if (!hasPhoto) {
                        if (window.MySkanicAlert) {
                            MySkanicAlert.warning('Foto profil siswa wajib diunggah sebelum melanjutkan ke tahap berikutnya.');
                        } else {
                            alert('Foto profil siswa wajib diunggah sebelum melanjutkan.');
                        }
                        const photoBox = document.querySelector('[data-photo-container]');
                        if (photoBox) {
                            photoBox.classList.add('ring-2', 'ring-rose-500', 'border-rose-500');
                            photoBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return;
                    }

                    this.updateTahunAjaranName();
                    this.updateDependentAngkatan();
                    this.updateAngkatanName();
                    this.updateDependentRombel();
                    this.updateKelasName();
                    this.ensureEmailDomain();
                    if (!this.student.username || !this.student.email.endsWith('@myskanic.com')) {
                        window.MySkanicAlert ? MySkanicAlert.warning('Email akun siswa wajib diisi dan berakhiran @myskanic.com') : alert('Email akun siswa wajib diisi dan berakhiran @myskanic.com');
                        return;
                    }
                    if (this.student.password && this.student.password.length < 6) {
                        window.MySkanicAlert ? MySkanicAlert.warning('Password akun siswa minimal 6 karakter') : alert('Password akun siswa minimal 6 karakter');
                        return;
                    }
                }
            }

            if (this.step < this.totalSteps) {
                this.step++;
                const modal = document.getElementById('modal-inputsiswa');
                if (modal) modal.scrollTop = 0;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                const modal = document.getElementById('modal-inputsiswa');
                if (modal) modal.scrollTop = 0;
            }
        },

        handleSubmit(event) {
            if (this.isSubmitting) return;
            const form = document.getElementById(this.formId);
            if (!form) return;

            // Strict check: photo is mandatory
            const fileInput = document.getElementById('student_photo');
            const hasPhoto = (fileInput && fileInput.files && fileInput.files.length > 0) || this.hasValidPhoto;
            if (!hasPhoto) {
                this.step = 1;
                if (window.MySkanicAlert) {
                    MySkanicAlert.warning('Foto profil siswa wajib diunggah sebelum data siswa baru dapat disimpan.');
                } else {
                    alert('Foto profil siswa wajib diunggah.');
                }
                const photoBox = document.querySelector('[data-photo-container]');
                if (photoBox) {
                    photoBox.classList.add('ring-2', 'ring-rose-500', 'border-rose-500');
                }
                return;
            }

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            this.ensureEmailDomain();
            this.isSubmitting = true;
            form.submit();
        },

        close() {
            closeModal('modal-inputsiswa');
            setTimeout(() => {
                this.step = 1;
                this.isSubmitting = false;
                if (!this.student.nama || !this.student.nama.trim()) {
                    this.student.username = '';
                    this.student.email = '';
                    this.isEmailManuallyEdited = false;
                }
            }, 300);
        }
    };
}

/**
 * Dedicated Guru Wizard Modal Handler
 */
function guruWizardModal() {
    return {
        step: 1,
        totalSteps: 3,
        modalId: 'modal-inputguru',
        formId: 'frmCreateGuru',
        isSubmitting: false,
        photoPreviewUrl: null,

        teacher: {
            nama: '',
            nip: '',
            nuptk: '',
            email: '',
            jenis_kelamin: '',
            telepon: '',
            password_mode: 'invitation',
            password: '',
            mata_pelajaran_id: '',
            mapel_name: '',
            kelas_ids: [],
            is_homeroom: '0',
            homeroom_kelas_id: '',
            homeroom_kelas_name: '',
            is_kaprog: '0',
            kaprog_jurusan_id: '',
            kaprog_jurusan_name: ''
        },

        init() {
            window.addEventListener('wizard-photo-updated-guru', (e) => {
                this.photoPreviewUrl = e.detail ? e.detail.url : null;
            });
        },

        updateMapelName() {
            const sel = document.getElementById('guru_mapel_select');
            if (sel && sel.selectedIndex > 0) {
                this.teacher.mapel_name = sel.options[sel.selectedIndex].text;
            } else {
                this.teacher.mapel_name = '';
            }
        },

        updateHomeroomName() {
            const sel = document.getElementById('guru_homeroom_select');
            if (sel && sel.selectedIndex > 0) {
                this.teacher.homeroom_kelas_name = sel.options[sel.selectedIndex].text;
            } else {
                this.teacher.homeroom_kelas_name = '';
            }
        },

        updateKaprogName() {
            const sel = document.getElementById('guru_kaprog_select');
            if (sel && sel.selectedIndex > 0) {
                this.teacher.kaprog_jurusan_name = sel.options[sel.selectedIndex].text;
            } else {
                this.teacher.kaprog_jurusan_name = '';
            }
        },

        nextStep() {
            const currentPanel = document.querySelector('#modal-inputguru [data-step="' + this.step + '"]');
            if (currentPanel) {
                const inputs = currentPanel.querySelectorAll('input:not([type="hidden"]), select, textarea');
                let isValid = true;

                for (const input of inputs) {
                    if (input.offsetParent === null) continue;
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('ring-2', 'ring-rose-500');
                        input.reportValidity();
                        break;
                    } else {
                        input.classList.remove('ring-2', 'ring-rose-500');
                    }
                }

                if (!isValid) return;

                if (this.step === 2) {
                    const checkedClasses = currentPanel.querySelectorAll('input[name="kelas_ids[]"]:checked');
                    if (checkedClasses.length === 0) {
                        window.MySkanicAlert ? MySkanicAlert.warning('Pilih minimal 1 rombel/kelas yang diampu.') : alert('Pilih minimal 1 rombel/kelas yang diampu.');
                        return;
                    }
                }
            }

            if (this.step < this.totalSteps) {
                this.step++;
                const modal = document.getElementById('modal-inputguru');
                if (modal) modal.scrollTop = 0;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                const modal = document.getElementById('modal-inputguru');
                if (modal) modal.scrollTop = 0;
            }
        },

        handleSubmit(event) {
            if (this.isSubmitting) return;
            const form = document.getElementById(this.formId);
            if (!form) return;

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            this.isSubmitting = true;
            form.submit();
        },

        close() {
            closeModal('modal-inputguru');
            setTimeout(() => {
                this.step = 1;
                this.isSubmitting = false;
            }, 300);
        }
    };
}

/**
 * Dedicated Jurusan Wizard Modal Handler
 */
function jurusanWizardModal() {
    return {
        step: 1,
        totalSteps: 2,
        modalId: 'modal-inputjurusan',
        formId: 'frmJurusan',
        isSubmitting: false,

        jurusan: {
            kode_jurusan: '',
            nama_jurusan: '',
            deskripsi: '',
            is_active: true
        },

        nextStep() {
            const currentPanel = document.querySelector('#modal-inputjurusan [data-step="' + this.step + '"]');
            if (currentPanel) {
                const inputs = currentPanel.querySelectorAll('input:not([type="hidden"]), textarea');
                let isValid = true;
                for (const input of inputs) {
                    if (input.offsetParent === null) continue;
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('ring-2', 'ring-rose-500');
                        input.reportValidity();
                        break;
                    } else {
                        input.classList.remove('ring-2', 'ring-rose-500');
                    }
                }
                if (!isValid) return;
            }

            if (this.step < this.totalSteps) {
                this.step++;
                const modal = document.getElementById('modal-inputjurusan');
                if (modal) modal.scrollTop = 0;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                const modal = document.getElementById('modal-inputjurusan');
                if (modal) modal.scrollTop = 0;
            }
        },

        handleSubmit(event) {
            if (this.isSubmitting) return;
            const form = document.getElementById(this.formId);
            if (!form) return;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            this.isSubmitting = true;
            form.submit();
        },

        close() {
            closeModal('modal-inputjurusan');
            setTimeout(() => {
                this.step = 1;
                this.isSubmitting = false;
            }, 300);
        }
    };
}

/**
 * Dedicated Kelas / Rombel Wizard Modal Handler
 */
function kelasWizardModal() {
    return {
        step: 1,
        totalSteps: 2,
        modalId: 'modal-inputkelas',
        formId: 'frmKelas',
        isSubmitting: false,

        kelas: {
            jurusan_id: '',
            jurusan_kode: '',
            jurusan_nama: '',
            tingkat: '10',
            nomor_rombel: '1',
            tahun_ajaran: '2026/2027',
            wali_kelas: '',
            jumlah_siswa: '36'
        },

        get previewNama() {
            if (this.kelas.tingkat && this.kelas.jurusan_kode && this.kelas.nomor_rombel) {
                return `${this.kelas.tingkat} ${this.kelas.jurusan_kode} ${this.kelas.nomor_rombel}`;
            }
            return '-';
        },

        onJurusanChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.kelas.jurusan_id = select.value;
            this.kelas.jurusan_kode = opt ? (opt.getAttribute('data-kode') || '') : '';
            this.kelas.jurusan_nama = opt ? (opt.getAttribute('data-nama') || '') : '';
        },

        nextStep() {
            const currentPanel = document.querySelector('#modal-inputkelas [data-step="' + this.step + '"]');
            if (currentPanel) {
                const inputs = currentPanel.querySelectorAll('input:not([type="hidden"]), select');
                let isValid = true;
                for (const input of inputs) {
                    if (input.offsetParent === null) continue;
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('ring-2', 'ring-rose-500');
                        input.reportValidity();
                        break;
                    } else {
                        input.classList.remove('ring-2', 'ring-rose-500');
                    }
                }
                if (!isValid) return;
            }

            if (this.step < this.totalSteps) {
                this.step++;
                const modal = document.getElementById('modal-inputkelas');
                if (modal) modal.scrollTop = 0;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                const modal = document.getElementById('modal-inputkelas');
                if (modal) modal.scrollTop = 0;
            }
        },

        handleSubmit(event) {
            if (this.isSubmitting) return;
            const form = document.getElementById(this.formId);
            if (!form) return;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            this.isSubmitting = true;
            form.submit();
        },

        close() {
            closeModal('modal-inputkelas');
            setTimeout(() => {
                this.step = 1;
                this.isSubmitting = false;
            }, 300);
        }
    };
}

/**
 * Dedicated Room Wizard Modal Handler
 */
function roomWizardModal() {
    return {
        step: 1,
        totalSteps: 2,
        modalId: 'modal-addroom',
        formId: 'frmAddRoom',
        isSubmitting: false,

        room: {
            name: '',
            code: '',
            building: '',
            type: 'Ruang Teori',
            capacity: '36',
            description: '',
            is_active: true
        },

        nextStep() {
            const currentPanel = document.querySelector('#modal-addroom [data-step="' + this.step + '"]');
            if (currentPanel) {
                const inputs = currentPanel.querySelectorAll('input:not([type="hidden"]), select, textarea');
                let isValid = true;
                for (const input of inputs) {
                    if (input.offsetParent === null) continue;
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('ring-2', 'ring-rose-500');
                        input.reportValidity();
                        break;
                    } else {
                        input.classList.remove('ring-2', 'ring-rose-500');
                    }
                }
                if (!isValid) return;
            }

            if (this.step < this.totalSteps) {
                this.step++;
                const modal = document.getElementById('modal-addroom');
                if (modal) modal.scrollTop = 0;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                const modal = document.getElementById('modal-addroom');
                if (modal) modal.scrollTop = 0;
            }
        },

        handleSubmit(event) {
            if (this.isSubmitting) return;
            const form = document.getElementById(this.formId);
            if (!form) return;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            this.isSubmitting = true;
            form.submit();
        },

        close() {
            closeModal('modal-addroom');
            setTimeout(() => {
                this.step = 1;
                this.isSubmitting = false;
            }, 300);
        }
    };
}

/**
 * Dedicated Jadwal Wizard Modal Handler
 */
function jadwalWizardModal() {
    return {
        step: 1,
        totalSteps: 2,
        modalId: 'modal-inputjadwal',
        formId: 'frmJadwal',
        isSubmitting: false,

        jadwal: {
            guru_id: '',
            guru_nama: '',
            mata_pelajaran_id: '',
            mata_pelajaran: '',
            kelas_id: '',
            kelas_nama: '',
            room_id: '',
            room_nama: '',
            hari: 'Senin',
            jam_mulai: '07:30',
            jam_selesai: '09:00',
            tahun_ajaran: '2026/2027'
        },

        onGuruChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.jadwal.guru_id = select.value;
            this.jadwal.guru_nama = opt ? (opt.getAttribute('data-nama') || '') : '';
            const defaultMapel = opt ? (opt.getAttribute('data-mapel') || '') : '';
            if (defaultMapel && !this.jadwal.mata_pelajaran) {
                this.jadwal.mata_pelajaran = defaultMapel;
            }
        },

        onKelasChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.jadwal.kelas_id = select.value;
            this.jadwal.kelas_nama = opt ? (opt.getAttribute('data-nama') || '') : '';
        },

        onRoomChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.jadwal.room_id = select.value;
            this.jadwal.room_nama = opt ? (opt.getAttribute('data-nama') || '') : '';
        },

        onMapelChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.jadwal.mata_pelajaran_id = select.value;
            if (opt && select.value) {
                this.jadwal.mata_pelajaran = opt.getAttribute('data-nama') || '';
            }
        },

        nextStep() {
            const currentPanel = document.querySelector('#modal-inputjadwal [data-step="' + this.step + '"]');
            if (currentPanel) {
                const inputs = currentPanel.querySelectorAll('input:not([type="hidden"]), select');
                let isValid = true;
                for (const input of inputs) {
                    if (input.offsetParent === null) continue;
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('ring-2', 'ring-rose-500');
                        input.reportValidity();
                        break;
                    } else {
                        input.classList.remove('ring-2', 'ring-rose-500');
                    }
                }
                if (!isValid) return;

                if (this.jadwal.jam_mulai && this.jadwal.jam_selesai && this.jadwal.jam_selesai <= this.jadwal.jam_mulai) {
                    window.MySkanicAlert ? MySkanicAlert.warning('Jam selesai harus lebih akhir dari jam mulai!') : alert('Jam selesai harus lebih akhir dari jam mulai!');
                    return;
                }
            }

            if (this.step < this.totalSteps) {
                this.step++;
                const modal = document.getElementById('modal-inputjadwal');
                if (modal) modal.scrollTop = 0;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                const modal = document.getElementById('modal-inputjadwal');
                if (modal) modal.scrollTop = 0;
            }
        },

        handleSubmit(event) {
            if (this.isSubmitting) return;
            const form = document.getElementById(this.formId);
            if (!form) return;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            this.isSubmitting = true;
            form.submit();
        },

        close() {
            closeModal('modal-inputjadwal');
            setTimeout(() => {
                this.step = 1;
                this.isSubmitting = false;
            }, 300);
        }
    };
}

/**
 * Dedicated Room Assignment Wizard Modal Handler
 */
function roomAssignmentWizardModal() {
    return {
        step: 1,
        totalSteps: 2,
        modalId: 'modal-addassignment',
        formId: 'frmAddAssignment',
        isSubmitting: false,

        assignment: {
            room_id: '',
            room_nama: '',
            kelas_id: '',
            kelas_nama: '',
            tahun_ajaran: '2026/2027',
            day_of_week: '',
            starts_on: '',
            ends_on: '',
            start_time: '',
            end_time: ''
        },

        onRoomChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.assignment.room_id = select.value;
            this.assignment.room_nama = opt ? (opt.getAttribute('data-nama') || '') : '';
        },

        onKelasChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.assignment.kelas_id = select.value;
            this.assignment.kelas_nama = opt ? (opt.getAttribute('data-nama') || '') : '';
        },

        nextStep() {
            const currentPanel = document.querySelector('#modal-addassignment [data-step="' + this.step + '"]');
            if (currentPanel) {
                const inputs = currentPanel.querySelectorAll('input:not([type="hidden"]), select');
                let isValid = true;
                for (const input of inputs) {
                    if (input.offsetParent === null) continue;
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('ring-2', 'ring-rose-500');
                        input.reportValidity();
                        break;
                    } else {
                        input.classList.remove('ring-2', 'ring-rose-500');
                    }
                }
                if (!isValid) return;

                if (this.assignment.start_time && this.assignment.end_time && this.assignment.end_time <= this.assignment.start_time) {
                    window.MySkanicAlert ? MySkanicAlert.warning('Jam selesai harus lebih akhir dari jam mulai!') : alert('Jam selesai harus lebih akhir dari jam mulai!');
                    return;
                }
                if (this.assignment.starts_on && this.assignment.ends_on && this.assignment.ends_on < this.assignment.starts_on) {
                    window.MySkanicAlert ? MySkanicAlert.warning('Tanggal selesai harus sama atau setelah tanggal mulai!') : alert('Tanggal selesai harus sama atau setelah tanggal mulai!');
                    return;
                }
            }

            if (this.step < this.totalSteps) {
                this.step++;
                const modal = document.getElementById('modal-addassignment');
                if (modal) modal.scrollTop = 0;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                const modal = document.getElementById('modal-addassignment');
                if (modal) modal.scrollTop = 0;
            }
        },

        handleSubmit(event) {
            if (this.isSubmitting) return;
            const form = document.getElementById(this.formId);
            if (!form) return;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            this.isSubmitting = true;
            form.submit();
        },

        close() {
            closeModal('modal-addassignment');
            setTimeout(() => {
                this.step = 1;
                this.isSubmitting = false;
            }, 300);
        }
    };
}

/**
 * Dedicated Inventory Wizard Modal Handler
 */
function inventoryWizardModal() {
    return {
        step: 1,
        totalSteps: 2,
        modalId: 'modal-addinventory',
        formId: 'frmInventory',
        isSubmitting: false,

        inv: {
            kode_barang: '',
            nama_barang: '',
            jurusan_id: '',
            borrowing_scope: 'jurusan',
            kategori: '',
            lokasi_penyimpanan: '',
            total_unit: 1,
            kondisi: 'baik',
            deskripsi: ''
        },

        nextStep() {
            const currentPanel = document.querySelector('#modal-addinventory [data-step="' + this.step + '"]');
            if (currentPanel) {
                const inputs = currentPanel.querySelectorAll('input:not([type="hidden"]), select, textarea');
                let isValid = true;
                for (const input of inputs) {
                    if (input.offsetParent === null) continue;
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('ring-2', 'ring-rose-500');
                        input.reportValidity();
                        break;
                    } else {
                        input.classList.remove('ring-2', 'ring-rose-500');
                    }
                }
                if (!isValid) return;
            }

            if (this.step < this.totalSteps) {
                this.step++;
                const modal = document.getElementById('modal-addinventory');
                if (modal) modal.scrollTop = 0;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                const modal = document.getElementById('modal-addinventory');
                if (modal) modal.scrollTop = 0;
            }
        },

        handleSubmit(event) {
            if (this.isSubmitting) return;
            const form = document.getElementById(this.formId);
            if (!form) return;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            this.isSubmitting = true;
            form.submit();
        },

        close() {
            closeModal('modal-addinventory');
            setTimeout(() => {
                this.step = 1;
                this.isSubmitting = false;
            }, 300);
        }
    };
}

/**
 * Dedicated Gradebook Assessment Wizard Modal Handler
 */
function gradebookWizardModal() {
    return {
        step: 1,
        totalSteps: 2,
        modalId: 'modal-addassessment',
        formId: 'frmAddAssessment',
        isSubmitting: false,

        asm: {
            kelas_id: '',
            kelas_nama: '',
            assessment_category_id: '',
            category_nama: '',
            mata_pelajaran_id: '',
            nama_mapel: '',
            judul: '',
            deskripsi: '',
            tanggal: new Date().toISOString().split('T')[0],
            tahun_ajaran: '2026/2027',
            semester: 'ganjil',
            skor_maksimal: 100,
            bobot: 1.0,
            status: 'draft'
        },

        onKelasChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.asm.kelas_id = select.value;
            this.asm.kelas_nama = opt ? (opt.text || '') : '';
        },

        onCategoryChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.asm.assessment_category_id = select.value;
            this.asm.category_nama = opt ? (opt.text || '') : '';
        },

        onMapelChange(event) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            this.asm.mata_pelajaran_id = select.value;
            if (opt && select.value) {
                this.asm.nama_mapel = opt.getAttribute('data-nama') || opt.text;
            }
        },

        nextStep() {
            const currentPanel = document.querySelector('#modal-addassessment [data-step="' + this.step + '"]');
            if (currentPanel) {
                const inputs = currentPanel.querySelectorAll('input:not([type="hidden"]), select, textarea');
                let isValid = true;
                for (const input of inputs) {
                    if (input.offsetParent === null) continue;
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('ring-2', 'ring-rose-500');
                        input.reportValidity();
                        break;
                    } else {
                        input.classList.remove('ring-2', 'ring-rose-500');
                    }
                }
                if (!isValid) return;
            }

            if (this.step < this.totalSteps) {
                this.step++;
                const modal = document.getElementById('modal-addassessment');
                if (modal) modal.scrollTop = 0;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                const modal = document.getElementById('modal-addassessment');
                if (modal) modal.scrollTop = 0;
            }
        },

        handleSubmit(event) {
            if (this.isSubmitting) return;
            const form = document.getElementById(this.formId);
            if (!form) return;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            this.isSubmitting = true;
            form.submit();
        },

        close() {
            closeModal('modal-addassessment');
            setTimeout(() => {
                this.step = 1;
                this.isSubmitting = false;
            }, 300);
        }
    };
}

