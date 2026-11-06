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

        // Student Data Model
        student: {
            nisn: '',
            nis: '',
            nama: '',
            jenis_kelamin: '',
            jurusan_id: '',
            jurusan_name: '',
            tingkat: '',
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

        init() {
            window.addEventListener('wizard-photo-updated', (e) => {
                this.photoPreviewUrl = e.detail ? e.detail.url : null;
            });
        },

        onNisChange() {
            // Auto suggest email if email is empty or starts with NIS
            const raw = (this.student.nis || '').trim().toLowerCase();
            if (raw && (!this.student.email || this.student.email.endsWith('@myskanic.com'))) {
                this.student.email = raw + '@myskanic.com';
            }
            if (!this.student.password) {
                this.student.password = 'password123';
            }
        },

        ensureEmailDomain() {
            let email = (this.student.email || '').trim().toLowerCase();
            if (email && !email.includes('@')) {
                this.student.email = email + '@myskanic.com';
            }
        },

        updateDependentRombel() {
            const jurSelect = document.getElementById('modal_jurusan');
            if (jurSelect && jurSelect.selectedIndex > 0) {
                const parts = jurSelect.options[jurSelect.selectedIndex].text.split(' - ');
                this.student.jurusan_name = parts[0].trim();
            } else {
                this.student.jurusan_name = '';
            }

            const rombelSelect = document.getElementById('kelas_id');
            if (!rombelSelect) return;

            const jId = String(this.student.jurusan_id || '');
            const tVal = String(this.student.tingkat || '');
            const options = rombelSelect.querySelectorAll('option');
            let hasValid = false;

            options.forEach(opt => {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                const optJ = opt.getAttribute('data-jurusan');
                const optT = opt.getAttribute('data-tingkat');
                const match = (!jId || optJ === jId) && (!tVal || optT === tVal);
                opt.hidden = !match;
                if (match && opt.value === String(this.student.kelas_id)) {
                    hasValid = true;
                }
            });

            if (!hasValid) {
                this.student.kelas_id = '';
                this.student.kelas_name = '';
                rombelSelect.value = '';
            } else {
                const opt = rombelSelect.options[rombelSelect.selectedIndex];
                if (opt) this.student.kelas_name = opt.text;
            }
        },

        updateKelasName() {
            const rombelSelect = document.getElementById('kelas_id');
            if (rombelSelect && rombelSelect.selectedIndex > 0) {
                this.student.kelas_name = rombelSelect.options[rombelSelect.selectedIndex].text;
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

                // Step 1 email validation
                if (this.step === 1) {
                    this.ensureEmailDomain();
                    if (!this.student.email.endsWith('@myskanic.com')) {
                        alert('Email akun siswa wajib berakhiran @myskanic.com');
                        return;
                    }
                    if (this.student.password && this.student.password.length < 6) {
                        alert('Password akun siswa minimal 6 karakter');
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
                        alert('Pilih minimal 1 rombel/kelas yang diampu.');
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
                    alert('Jam selesai harus lebih akhir dari jam mulai!');
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
                    alert('Jam selesai harus lebih akhir dari jam mulai!');
                    return;
                }
                if (this.assignment.starts_on && this.assignment.ends_on && this.assignment.ends_on < this.assignment.starts_on) {
                    alert('Tanggal selesai harus sama atau setelah tanggal mulai!');
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

