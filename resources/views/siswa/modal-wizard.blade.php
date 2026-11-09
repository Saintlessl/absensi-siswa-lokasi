{{-- Exact Design Implementation: Multi-Step Modal Form for Siswa --}}
@if ($errors->any())
    @php
        $wizardRecover = collect(old())->only([
            'nisn', 'nis', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
            'tahun_ajaran_id', 'jalur_masuk', 'asal_sekolah', 'jurusan_id', 'angkatan_id',
            'tingkat', 'kelas_id', 'email', 'password', 'parent_name',
            'parent_jenis_kelamin', 'parent_phone', 'parent_address', 'existing_orangtua_id',
            'confirm_parent_verified',
        ]);
    @endphp
    <script>
        // Rehydrate wizard state after a failed submit so the admin does not
        // lose typed data and the modal reopens on the same page load.
        window.__siswaWizardRecover = @json($wizardRecover);
        window.__siswaWizardReopen = true;
    </script>
@endif
<div id="modal-inputsiswa"
     x-data="studentWizardModal()"
     x-cloak
     class="fixed inset-0 z-[60] hidden overflow-y-auto"
     aria-labelledby="modal-title-inputsiswa"
     role="dialog"
     aria-modal="true">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    {{-- Modal Positioning Container --}}
    <div class="flex min-h-full items-center justify-center p-2 sm:p-6 text-left">
        {{-- Modal Card (Exact 1024px desktop, #EFF5FF surface, rounded-3xl) --}}
        <div class="relative w-full max-w-[1024px] rounded-[28px] bg-[#EFF5FF] px-9 pt-8 pb-6 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800"
             @keydown.escape.window="close()">

            {{-- Header (Matching reference images) --}}
            <div class="flex items-start justify-between">
                <div>
                    <h2 id="modal-title-inputsiswa" class="text-[22px] font-bold text-[#0F172A] tracking-tight leading-snug">
                        Tambah Data Siswa
                    </h2>
                    {{-- Breadcrumb Step Indicator --}}
                    <div class="flex items-center flex-wrap text-sm mt-1 font-semibold select-none">
                        <span :class="step >= 1 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'"
                              class="transition-colors duration-200">
                            Akun & Identitas Siswa
                        </span>
                        <span class="mx-2 text-[#AAC1EA] font-normal">-</span>
                        <span :class="step >= 2 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'"
                              class="transition-colors duration-200">
                            Informasi Orang Tua / Wali
                        </span>
                        <span class="mx-2 text-[#AAC1EA] font-normal">-</span>
                        <span :class="step >= 3 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'"
                              class="transition-colors duration-200">
                            Konfirmasi
                        </span>
                    </div>
                </div>

                {{-- Close Button --}}
                <button type="button"
                        @click="close()"
                        class="text-[#94A3B8] hover:text-[#0F172A] p-1 rounded-lg transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Divider Line (at y=118) --}}
            <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

            {{-- Main Form --}}
            <form action="{{ route('siswa.store') }}"
                  method="POST"
                  id="frmSiswa"
                  enctype="multipart/form-data"
                  @submit.prevent="handleSubmit($event)">
                @csrf

                {{-- Hidden fields for auto student account sync --}}
                <input type="hidden" name="username" :value="student.nis">
                <input type="hidden" name="password" :value="student.nis">

                {{-- ==================== STEP 1: Akun & Identitas Siswa ==================== --}}
                <div x-show="step === 1"
                     data-step="1"
                     class="transition-opacity duration-200">
                    <div class="flex flex-row items-stretch gap-[40px]">
                        {{-- Left Column: Photo Uploader Box (342px x 395px, exactly matching Image 1) --}}
                        <div class="w-[342px] shrink-0">
                            <div class="h-full w-full flex flex-col justify-center"
                                 x-data="{
                                    dragOver: false,
                                    previewUrl: null,
                                    handleFileSelect(event) {
                                        const file = event.target.files ? event.target.files[0] : null;
                                        this.processFile(file);
                                    },
                                    handleDrop(event) {
                                        this.dragOver = false;
                                        const file = event.dataTransfer.files ? event.dataTransfer.files[0] : null;
                                        if (file) {
                                            const input = this.$refs.fileInput;
                                            input.files = event.dataTransfer.files;
                                            this.processFile(file);
                                        }
                                    },
                                    processFile(file) {
                                        if (!file) return;
                                        if (file.size > 2 * 1024 * 1024) {
                                            alert('Ukuran file maksimal 2MB');
                                            return;
                                        }
                                        const reader = new FileReader();
                                        reader.onload = (e) => {
                                            this.previewUrl = e.target.result;
                                            window.dispatchEvent(new CustomEvent('wizard-photo-updated', { detail: { url: e.target.result } }));
                                        };
                                        reader.readAsDataURL(file);
                                    },
                                    removePhoto() {
                                        this.previewUrl = null;
                                        this.$refs.fileInput.value = '';
                                        window.dispatchEvent(new CustomEvent('wizard-photo-updated', { detail: { url: null } }));
                                    }
                                 }">
                                {{-- Dashed Container --}}
                                <div class="w-full h-[395px] rounded-[28px] border-2 border-dashed transition-all duration-200 flex flex-col items-center justify-center p-6 text-center relative"
                                     :class="dragOver ? 'border-[#003FA9] bg-blue-100/40' : 'border-[#93C5FD] bg-transparent'"
                                     @dragover.prevent="dragOver = true"
                                     @dragleave.prevent="dragOver = false"
                                     @drop.prevent="handleDrop($event)">

                                    <input type="file"
                                           name="photo"
                                           id="student_photo"
                                           x-ref="fileInput"
                                           accept="image/jpeg,image/png,image/webp"
                                           class="hidden"
                                           @change="handleFileSelect($event)">

                                    {{-- Empty State (No Image) --}}
                                    <div x-show="!previewUrl" class="flex flex-col items-center justify-center">
                                        {{-- Square Rounded Container with Avatar Icon --}}
                                        <div class="w-[84px] h-[84px] rounded-2xl border-2 border-[#003FA9] flex items-center justify-center text-[#003FA9] mb-5">
                                            <svg class="w-11 h-11" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                                <circle cx="12" cy="8" r="4"></circle>
                                                <path stroke-linecap="round" d="M5.5 20c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"></path>
                                            </svg>
                                        </div>

                                        {{-- Blue Upload Button --}}
                                        <button type="button"
                                                @click="$refs.fileInput.click()"
                                                class="h-[40px] px-5 rounded-xl bg-[#003FA9] hover:bg-[#00348C] text-white text-xs font-semibold inline-flex items-center gap-2 shadow-sm transition duration-150">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            <span>Tambahkan Foto Siswa</span>
                                        </button>

                                        {{-- Subtitles --}}
                                        <p class="text-xs font-medium text-[#60A5FA] mt-4">
                                            Atau tarik dan lepas di sini
                                        </p>
                                        <p class="text-[11px] text-[#94A3B8] mt-0.5">
                                            Format: JPG, PNG, WebP. Maksimal 2MB.
                                        </p>
                                    </div>

                                    {{-- Preview State (With Image) --}}
                                    <div x-show="previewUrl" class="flex flex-col items-center justify-center w-full h-full">
                                        <div class="relative group">
                                            <img :src="previewUrl"
                                                 alt="Preview"
                                                 class="w-44 h-44 object-cover rounded-2xl shadow-md border-2 border-[#003FA9]">
                                            <div class="absolute inset-0 bg-slate-900/40 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                                <button type="button"
                                                        @click="$refs.fileInput.click()"
                                                        class="p-2 bg-white text-[#003FA9] rounded-lg shadow text-xs font-semibold">
                                                    Ganti
                                                </button>
                                                <button type="button"
                                                        @click="removePhoto()"
                                                        class="p-2 bg-rose-600 text-white rounded-lg shadow text-xs font-semibold">
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-xs font-medium text-emerald-600 mt-3 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Foto siap diunggah
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Form Inputs (with 5-Level Dependent Dropdown) --}}
                        <div class="flex-1 flex flex-col gap-3.5 max-h-[440px] overflow-y-auto pr-1">
                            {{-- Row 1: NISN (10 digit) | NIS --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.input label="NISN"
                                                hint="10 digit"
                                                name="nisn"
                                                id="input_nisn"
                                                placeholder="Contoh : 0063677346"
                                                maxlength="20"
                                                model="student.nisn" />
                                <x-wizard.input label="NIS"
                                                name="nis"
                                                id="input_nis"
                                                placeholder="Contoh : 212127327"
                                                maxlength="20"
                                                required
                                                model="student.nis"
                                                @input="onNisChange()" />
                            </div>

                            {{-- Row 2: Nama Lengkap --}}
                            <div>
                                <x-wizard.input label="Nama Lengkap"
                                                name="nama"
                                                id="input_nama"
                                                placeholder="Nama Lengkap Siswa"
                                                required
                                                model="student.nama" />
                            </div>

                            {{-- Row 3: Tempat Lahir & Tanggal Lahir (TTL) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.input label="Tempat Lahir"
                                                name="tempat_lahir"
                                                id="input_tempat_lahir"
                                                placeholder="Contoh : Bogor"
                                                maxlength="100"
                                                model="student.tempat_lahir" />

                                <x-wizard.input label="Tanggal Lahir"
                                                name="tanggal_lahir"
                                                id="input_tanggal_lahir"
                                                type="date"
                                                model="student.tanggal_lahir" />
                            </div>

                            {{-- Row 4: Jenis Kelamin | Tahun Ajaran Masuk --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.select label="Jenis Kelamin"
                                                 name="jenis_kelamin"
                                                 id="input_jenis_kelamin"
                                                 placeholder="Pilih Jenis Kelamin"
                                                 required
                                                 model="student.jenis_kelamin">
                                    <option value="L">Laki - Laki</option>
                                    <option value="P">Perempuan</option>
                                </x-wizard.select>

                                <x-wizard.select label="Tahun Ajaran Masuk"
                                                 hint="Tahun kalender pendaftaran"
                                                 name="tahun_ajaran_id"
                                                 id="modal_tahun_ajaran"
                                                 placeholder="Pilih Tahun Ajaran"
                                                 required
                                                 model="student.tahun_ajaran_id"
                                                 @change="onTahunAjaranChange()">
                                    @foreach($tahunAjarans ?? [] as $ta)
                                        <option value="{{ $ta->id }}"
                                                data-tahun-mulai="{{ $ta->tahun_mulai }}"
                                                data-active="{{ $ta->is_active ? '1' : '0' }}"
                                                {{ $ta->is_active ? 'selected' : '' }}>
                                            {{ $ta->nama }} {{ $ta->is_active ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </x-wizard.select>
                            </div>

                            {{-- Row 4: Jalur Pendaftaran & Asal Sekolah (Jika Pindahan) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.select label="Jalur Pendaftaran"
                                                 name="jalur_masuk"
                                                 id="modal_jalur_masuk"
                                                 placeholder="Pilih Jalur"
                                                 required
                                                 model="student.jalur_masuk"
                                                 @change="updateJalurMasuk()">
                                    <option value="baru" selected>Siswa Reguler / Siswa Baru</option>
                                    <option value="pindahan">Siswa Pindahan / Mutasi Masuk</option>
                                </x-wizard.select>

                                <div x-show="student.jalur_masuk === 'pindahan'">
                                    <x-wizard.input label="Asal Sekolah Sebelumnya"
                                                    name="asal_sekolah"
                                                    id="input_asal_sekolah"
                                                    placeholder="Contoh: SMP / SMK Asal"
                                                    model="student.asal_sekolah" />
                                </div>
                            </div>

                            {{-- Row 5: Jurusan (Program Keahlian) | Angkatan Per Jurusan --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.select label="Jurusan (Program Keahlian)"
                                                 id="modal_jurusan"
                                                 name="jurusan_id"
                                                 placeholder="Pilih Jurusan"
                                                 required
                                                 model="student.jurusan_id"
                                                 @change="onJurusanChange()">
                                    @foreach($jurusans as $j)
                                        <option value="{{ $j->id }}">{{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}</option>
                                    @endforeach
                                </x-wizard.select>

                                <x-wizard.select label="Angkatan Per Jurusan"
                                                 id="modal_angkatan"
                                                 name="angkatan_id"
                                                 placeholder="Pilih Angkatan"
                                                 required
                                                 model="student.angkatan_id"
                                                 @change="onAngkatanChange()">
                                    @foreach($angkatans as $a)
                                        <option value="{{ $a->id }}"
                                                data-jurusan="{{ $a->jurusan_id }}"
                                                data-tahun-masuk="{{ $a->tahun_masuk }}"
                                                data-nomor="{{ $a->nomor_angkatan }}">
                                            {{ $a->jurusan?->kode_jurusan ?? '' }} - {{ $a->nama_angkatan }} (Masuk {{ $a->tahun_masuk }})
                                        </option>
                                    @endforeach
                                </x-wizard.select>
                            </div>

                            {{-- Row 6: Tingkat Kelas | Rombel --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.select label="Tingkat Kelas"
                                                 id="modal_tingkat"
                                                 name="tingkat"
                                                 placeholder="Pilih Tingkat"
                                                 required
                                                 model="student.tingkat"
                                                 @change="onTingkatChange()">
                                    <option value="10">Kelas 10</option>
                                    <option value="11">Kelas 11</option>
                                    <option value="12">Kelas 12</option>
                                </x-wizard.select>

                                <x-wizard.select label="Rombongan Belajar (Rombel)"
                                                 name="kelas_id"
                                                 id="kelas_id"
                                                 placeholder="Pilih Rombel"
                                                 required
                                                 model="student.kelas_id"
                                                 @change="updateKelasName()">
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}"
                                                data-jurusan="{{ $k->jurusan_id }}"
                                                data-tingkat="{{ $k->tingkat }}">
                                            {{ $k->nama_kelas }} (Wali: {{ $k->wali_kelas ?: '-' }})
                                        </option>
                                    @endforeach
                                </x-wizard.select>
                            </div>

                            {{-- Row 7: Akun Siswa (Email @myskanic.com & Password) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.input label="Email Akun Siswa"
                                                hint="akhiran @myskanic.com"
                                                name="email"
                                                id="input_student_email"
                                                type="text"
                                                placeholder="contoh : 212127327@myskanic.com"
                                                required
                                                model="student.email"
                                                @blur="ensureEmailDomain()" />
                                <x-wizard.input label="Password Akun"
                                                hint="min. 6 karakter"
                                                name="password"
                                                id="input_student_password"
                                                type="password"
                                                placeholder="Minimal 6 karakter"
                                                required
                                                model="student.password" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==================== STEP 2: Informasi Orang Tua / Wali ==================== --}}
                <div x-show="step === 2"
                     data-step="2"
                     class="transition-opacity duration-200">
                    <div class="flex flex-col gap-4">
                        {{-- Row 1: Nama Lengkap Orang Tua / Wali --}}
                        <div>
                            <x-wizard.input label="Nama Lengkap Orang Tua / Wali"
                                            name="parent_name"
                                            id="input_parent_name"
                                            placeholder="Nama Lengkap Siswa"
                                            required
                                            model="student.parent_name" />
                        </div>

                        {{-- Row 2: Jenis Kelamin | Nomor Telepon Aktif (Whatsapp lebih baik) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-wizard.select label="Jenis Kelamin"
                                             name="parent_jenis_kelamin"
                                             id="input_parent_jk"
                                             placeholder="Pilih Jenis Kelamin"
                                             required
                                             model="student.parent_jenis_kelamin">
                                <option value="L">Laki - Laki</option>
                                <option value="P">Perempuan</option>
                            </x-wizard.select>

                            <x-wizard.input label="Nomor Telepon Aktif (Whatsapp lebih baik)"
                                            name="parent_phone"
                                            id="input_parent_phone"
                                            placeholder="08123456789"
                                            required
                                            model="student.parent_phone" />
                        </div>

                        {{-- Row 3: Alamat Lengkap --}}
                        <div>
                            <x-wizard.textarea label="Alamat Lengkap"
                                               name="parent_address"
                                               id="input_parent_address"
                                               placeholder="Nama Lengkap Siswa"
                                               rows="3"
                                               required
                                               model="student.parent_address" />
                        </div>

                        {{-- Verification Checkbox & Explanation (exact wording from Image 2) --}}
                        <div class="mt-2">
                            <label class="flex items-start gap-3 cursor-pointer select-none">
                                <input type="checkbox"
                                       name="confirm_parent_verified"
                                       id="confirm_parent_verified"
                                       x-model="student.verified"
                                       required
                                       class="mt-0.5 w-4 h-4 rounded border-slate-300 text-[#003FA9] focus:ring-[#003FA9] cursor-pointer">
                                <span class="text-xs sm:text-sm text-slate-700 font-medium leading-relaxed">
                                    Saya telah memverifikasi identitas orang tua/wali ini dan mengonfirmasi relasi baru
                                </span>
                            </label>
                            <p class="text-[11px] sm:text-xs text-slate-400 mt-1 pl-7 leading-relaxed">
                                Akun dibuat otomatis. Orang tua menetapkan password sendiri lewat tautan aktivasi sekali pakai yang berlaku 7 hari.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ==================== STEP 3: Konfirmasi (Image 3 reference) ==================== --}}
                <div x-show="step === 3"
                     data-step="3"
                     class="transition-opacity duration-200">
                    <div class="flex flex-col gap-5">
                        {{-- Top Section: Student Details Review --}}
                        <div class="flex flex-row items-stretch gap-[40px]">
                            {{-- Left: Photo Preview Display Box (342px x 395px, #E2EDFF surface) --}}
                            <div class="w-[342px] h-[395px] shrink-0 rounded-[28px] bg-[#E2EDFF] flex flex-col items-center justify-center p-6 text-center">
                                <div x-show="photoPreviewUrl" class="flex flex-col items-center">
                                    <img :src="photoPreviewUrl"
                                         alt="Student Photo"
                                         class="w-48 h-48 object-cover rounded-2xl shadow-md border-2 border-[#003FA9] mb-3">
                                    <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-3 py-1 rounded-full">
                                        Foto Terverifikasi
                                    </span>
                                </div>
                                <div x-show="!photoPreviewUrl" class="flex flex-col items-center text-[#94A3B8]">
                                    {{-- Upload cloud outline matching Image 3 --}}
                                    <svg class="w-20 h-20 text-[#A0B4D8] mb-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <span class="text-sm font-semibold text-[#64748B]">
                                        Tambahkan Foto Siswa
                                    </span>
                                </div>
                            </div>

                            {{-- Right: Student Form Fields Review --}}
                            <div class="flex-1 flex flex-col gap-3.5 max-h-[440px] overflow-y-auto pr-1">
                                {{-- Row 1: NISN | NIS --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-wizard.review-field label="NISN" bind="student.nisn" />
                                    <x-wizard.review-field label="NIS" bind="student.nis" />
                                </div>

                                {{-- Row 2: Nama Lengkap --}}
                                <div>
                                    <x-wizard.review-field label="Nama Lengkap" bind="student.nama" />
                                </div>

                                {{-- Row 3: Tempat & Tanggal Lahir (TTL) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-wizard.review-field label="Tempat Lahir" bind="student.tempat_lahir || '-'" />
                                    <x-wizard.review-field label="Tanggal Lahir" bind="student.tanggal_lahir || '-'" />
                                </div>

                                {{-- Row 4: Jenis Kelamin | Tahun Ajaran Masuk --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-wizard.review-field label="Jenis Kelamin"
                                                           bind="student.jenis_kelamin === 'L' ? 'Laki - Laki' : (student.jenis_kelamin === 'P' ? 'Perempuan' : '-')" />
                                    <x-wizard.review-field label="Tahun Ajaran Masuk" bind="student.tahun_ajaran_name" />
                                </div>

                                {{-- Row 4: Jalur Pendaftaran | Asal Sekolah --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-wizard.review-field label="Jalur Pendaftaran"
                                                           bind="student.jalur_masuk === 'baru' ? 'Siswa Baru (Tingkat 10)' : 'Siswa Pindahan / Mutasi Masuk'" />
                                    <x-wizard.review-field label="Asal Sekolah"
                                                           bind="student.jalur_masuk === 'pindahan' ? (student.asal_sekolah || '-') : 'Siswa Baru (SMP/Sederajat)'" />
                                </div>

                                {{-- Row 5: Jurusan | Angkatan Per Jurusan --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-wizard.review-field label="Jurusan (Program Keahlian)" bind="student.jurusan_name" />
                                    <x-wizard.review-field label="Angkatan Per Jurusan" bind="student.angkatan_name" />
                                </div>

                                {{-- Row 6: Tingkat Kelas | Rombel --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-wizard.review-field label="Tingkat Kelas" bind="'Kelas ' + (student.tingkat || '10')" />
                                    <x-wizard.review-field label="Rombongan Belajar (Rombel)" bind="student.kelas_name" />
                                </div>

                                {{-- Row 7: Email Akun | Password --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-wizard.review-field label="Email Akun Siswa" bind="student.email" />
                                    <x-wizard.review-field label="Password Akun" bind="student.password ? '••••••••' : '-'" />
                                </div>
                            </div>
                        </div>

                        {{-- Bottom Section: Parent Details Review (Rows 6, 7, 8) --}}
                        <div class="flex flex-col gap-4 pt-2">
                            {{-- Row 6: Nama Lengkap Orang Tua / Wali --}}
                            <div>
                                <x-wizard.review-field label="Nama Lengkap Orang Tua / Wali" bind="student.parent_name" />
                            </div>

                            {{-- Row 7: Jenis Kelamin | Nomor Telepon Aktif --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.review-field label="Jenis Kelamin"
                                                       bind="student.parent_jenis_kelamin === 'L' ? 'Laki - Laki' : (student.parent_jenis_kelamin === 'P' ? 'Perempuan' : '-')" />
                                <x-wizard.review-field label="Nomor Telepon Aktif (Whatsapp lebih baik)" bind="student.parent_phone" />
                            </div>

                            {{-- Row 8: Alamat Lengkap --}}
                            <div>
                                <x-wizard.review-field label="Alamat Lengkap" bind="student.parent_address" isTextarea />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Navigation (matching exact design) --}}
                <div class="flex items-center justify-between mt-6 pt-2">
                    {{-- Left: Kembali Button --}}
                    <div>
                        <button type="button"
                                x-show="step > 1"
                                @click="prevStep()"
                                class="h-[44px] px-6 rounded-xl bg-[#E2EDFF] hover:bg-[#D4E4FC] text-[#003FA9] font-bold text-sm inline-flex items-center gap-2 transition duration-150 shadow-none">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                            </svg>
                            <span>Kembali</span>
                        </button>
                    </div>

                    {{-- Right: Berikutnya / Submit Button --}}
                    <div>
                        {{-- Step 1 and 2: Berikutnya --}}
                        <button type="button"
                                x-show="step < totalSteps"
                                @click="nextStep()"
                                class="h-[44px] px-6 rounded-xl bg-[#003FA9] hover:bg-[#00348C] text-white font-bold text-sm inline-flex items-center gap-2 shadow-sm transition duration-150">
                            <span>Berikutnya</span>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                            </svg>
                        </button>

                        {{-- Step 3: Final Submit (labeled Berikutnya with arrow icon, matching Image 3) --}}
                        <button type="submit"
                                x-show="step === totalSteps"
                                :disabled="isSubmitting"
                                class="h-[44px] px-6 rounded-xl bg-[#003FA9] hover:bg-[#00348C] text-white font-bold text-sm inline-flex items-center gap-2 shadow-sm transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Berikutnya</span>
                            <span x-show="isSubmitting" class="inline-flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                            <svg x-show="!isSubmitting" class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
