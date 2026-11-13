{{-- Exact Design Implementation: Multi-Step Modal Form for Guru --}}
<div id="modal-inputguru"
     x-data="guruWizardModal()"
     x-cloak
     class="fixed inset-0 z-[60] hidden overflow-y-auto"
     aria-labelledby="modal-title-inputguru"
     role="dialog"
     aria-modal="true">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    {{-- Modal Positioning Container --}}
    <div class="flex min-h-full items-center justify-center p-2 sm:p-6 text-left">
        {{-- Modal Card (1024px desktop, #EFF5FF surface, rounded-3xl) --}}
        <div class="relative w-full max-w-[1024px] rounded-[28px] bg-[#EFF5FF] px-9 pt-8 pb-6 shadow-2xl border border-slate-100 my-auto transform transition-all text-slate-800"
             @keydown.escape.window="close()">

            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <h2 id="modal-title-inputguru" class="text-[22px] font-bold text-[#0F172A] tracking-tight leading-snug">
                        Tambah Data Guru
                    </h2>
                    {{-- Breadcrumb Step Indicator --}}
                    <div class="flex items-center flex-wrap text-sm mt-1 font-semibold select-none">
                        <span :class="step >= 1 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'"
                              class="transition-colors duration-200">
                            Akun & Identitas Guru
                        </span>
                        <span class="mx-2 text-[#AAC1EA] font-normal">-</span>
                        <span :class="step >= 2 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'"
                              class="transition-colors duration-200">
                            Penugasan Akademik
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

            {{-- Divider Line --}}
            <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

            {{-- Form --}}
            <form action="{{ route('guru.store') }}"
                  method="POST"
                  id="frmCreateGuru"
                  enctype="multipart/form-data"
                  @submit.prevent="handleSubmit($event)">
                @csrf

                {{-- ==================== STEP 1: Akun & Identitas Guru ==================== --}}
                <div x-show="step === 1"
                     data-step="1"
                     class="transition-opacity duration-200">
                    <div class="flex flex-row items-stretch gap-[40px]">
                        {{-- Left Column: Photo Uploader Box --}}
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
                                            window.MySkanicAlert ? MySkanicAlert.warning('Ukuran file maksimal 2MB') : alert('Ukuran file maksimal 2MB');
                                            return;
                                        }
                                        const reader = new FileReader();
                                        reader.onload = (e) => {
                                            this.previewUrl = e.target.result;
                                            window.dispatchEvent(new CustomEvent('wizard-photo-updated-guru', { detail: { url: e.target.result } }));
                                        };
                                        reader.readAsDataURL(file);
                                    },
                                    removePhoto() {
                                        this.previewUrl = null;
                                        this.$refs.fileInput.value = '';
                                        window.dispatchEvent(new CustomEvent('wizard-photo-updated-guru', { detail: { url: null } }));
                                    }
                                 }">
                                {{-- Dashed Container --}}
                                <div class="w-full h-[410px] rounded-[28px] border-2 border-dashed transition-all duration-200 flex flex-col items-center justify-center p-6 text-center relative"
                                     :class="dragOver ? 'border-[#003FA9] bg-blue-100/40' : 'border-[#93C5FD] bg-transparent'"
                                     @dragover.prevent="dragOver = true"
                                     @dragleave.prevent="dragOver = false"
                                     @drop.prevent="handleDrop($event)">

                                    <input type="file"
                                           name="photo"
                                           id="guru_photo"
                                           x-ref="fileInput"
                                           accept="image/jpeg,image/png,image/webp"
                                           class="hidden"
                                           @change="handleFileSelect($event)">

                                    <div x-show="!previewUrl" class="flex flex-col items-center justify-center">
                                        <div class="w-[84px] h-[84px] rounded-2xl border-2 border-[#003FA9] flex items-center justify-center text-[#003FA9] mb-5">
                                            <svg class="w-11 h-11" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                                <circle cx="12" cy="8" r="4"></circle>
                                                <path stroke-linecap="round" d="M5.5 20c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"></path>
                                            </svg>
                                        </div>

                                        <button type="button"
                                                @click="$refs.fileInput.click()"
                                                class="h-[40px] px-5 rounded-xl bg-[#003FA9] hover:bg-[#00348C] text-white text-xs font-semibold inline-flex items-center gap-2 shadow-sm transition duration-150">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            <span>Tambahkan Foto Guru</span>
                                        </button>

                                        <p class="text-xs font-medium text-[#60A5FA] mt-4">
                                            Atau tarik dan lepas di sini
                                        </p>
                                        <p class="text-[11px] text-[#94A3B8] mt-0.5">
                                            Format: JPG, PNG, WebP. Maksimal 2MB.
                                        </p>
                                    </div>

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

                        {{-- Right Column: Teacher Identity Inputs --}}
                        <div class="flex-1 flex flex-col justify-between h-[410px]">
                            {{-- Row 1: NIP | NUPTK --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.input label="NIP"
                                                name="nip"
                                                id="input_guru_nip"
                                                placeholder="Contoh: 198507202010011005"
                                                maxlength="50"
                                                model="teacher.nip" />
                                <x-wizard.input label="NUPTK"
                                                name="nuptk"
                                                id="input_guru_nuptk"
                                                placeholder="Contoh: 1234567890123456"
                                                maxlength="50"
                                                model="teacher.nuptk" />
                            </div>

                            {{-- Row 2: Nama Lengkap --}}
                            <div>
                                <x-wizard.input label="Nama Lengkap & Gelar"
                                                name="nama"
                                                id="input_guru_nama"
                                                placeholder="Contoh: Budi Santoso, S.Pd., M.Kom."
                                                required
                                                model="teacher.nama" />
                            </div>

                            {{-- Row 3: Jenis Kelamin | Kontak WA --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.select label="Jenis Kelamin"
                                                 name="jenis_kelamin"
                                                 id="input_guru_jk"
                                                 placeholder="Pilih Jenis Kelamin"
                                                 required
                                                 model="teacher.jenis_kelamin">
                                    <option value="L">Laki - Laki</option>
                                    <option value="P">Perempuan</option>
                                </x-wizard.select>

                                <x-wizard.input label="Nomor WhatsApp"
                                                name="telepon"
                                                id="input_guru_telepon"
                                                placeholder="08123456789"
                                                model="teacher.telepon" />
                            </div>

                            {{-- Row 4: Email Akun Guru --}}
                            <div>
                                <x-wizard.input label="Email Akun Guru"
                                                type="email"
                                                name="email"
                                                id="input_guru_email"
                                                placeholder="budi.santoso@myskanic.com"
                                                required
                                                model="teacher.email" />
                            </div>

                            {{-- Row 5: Password Setup --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-wizard.select label="Metode Password"
                                                 name="password_mode"
                                                 id="input_guru_pw_mode"
                                                 required
                                                 model="teacher.password_mode">
                                    <option value="invitation">Tautan Aktivasi (Mandiri)</option>
                                    <option value="direct">Buat Password Langsung</option>
                                </x-wizard.select>

                                <div>
                                    <template x-if="teacher.password_mode === 'direct'">
                                        <x-wizard.input label="Password Guru"
                                                        type="password"
                                                        name="password"
                                                        id="input_guru_pw"
                                                        placeholder="Minimal 8 karakter"
                                                        required
                                                        model="teacher.password" />
                                    </template>
                                    <template x-if="teacher.password_mode !== 'direct'">
                                        <div class="h-[44px] px-4 rounded-xl bg-blue-100/50 border border-slate-100 flex items-center text-xs text-[#003FA9] font-medium">
                                            Aktivasi via tautan sekali pakai
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==================== STEP 2: Penugasan Akademik ==================== --}}
                <div x-show="step === 2"
                     data-step="2"
                     class="transition-opacity duration-200">
                    <div class="flex flex-col gap-5">
                        {{-- Row 1: Mata Pelajaran Utama --}}
                        <div>
                            <x-wizard.select label="Mata Pelajaran Utama yang Diampu"
                                             name="mata_pelajaran_id"
                                             id="guru_mapel_select"
                                             placeholder="Pilih Mata Pelajaran"
                                             required
                                             model="teacher.mata_pelajaran_id"
                                             @change="updateMapelName()">
                                @foreach($mataPelajarans as $mp)
                                    <option value="{{ $mp->id }}">{{ $mp->kode_mapel }} - {{ $mp->nama_mapel }}</option>
                                @endforeach
                            </x-wizard.select>
                        </div>

                        {{-- Row 2: Rombel / Kelas yang Diajar --}}
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] mb-2">
                                Rombel / Kelas yang Diajar <span class="text-rose-500 font-normal">*</span>
                            </label>
                            <div class="p-4 rounded-xl bg-[#E2EDFF] max-h-48 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                @foreach($kelasList as $k)
                                    <label class="flex items-center gap-2 p-2 rounded-lg bg-white/70 hover:bg-white text-xs text-[#0F172A] font-medium cursor-pointer transition">
                                        <input type="checkbox"
                                               name="kelas_ids[]"
                                               value="{{ $k->id }}"
                                               class="rounded border-slate-300 text-[#003FA9] focus:ring-[#003FA9]">
                                        <span>{{ $k->nama_kelas }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Row 3: Peran Tambahan (Wali Kelas & Kaprog) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                            {{-- Homeroom Option --}}
                            <div class="p-4 rounded-xl bg-[#E2EDFF]/60 border border-blue-100 flex flex-col justify-between">
                                <label class="flex items-center gap-2 text-xs font-semibold text-[#0F172A] mb-2 cursor-pointer">
                                    <input type="checkbox"
                                           name="is_homeroom"
                                           value="1"
                                           x-model="teacher.is_homeroom"
                                           class="rounded border-slate-300 text-[#003FA9] focus:ring-[#003FA9]">
                                    <span>Tugaskan Sebagai Wali Kelas</span>
                                </label>
                                <div x-show="teacher.is_homeroom == '1' || teacher.is_homeroom === true">
                                    <x-wizard.select label="Pilih Rombel Perwalian"
                                                     name="homeroom_kelas_id"
                                                     id="guru_homeroom_select"
                                                     placeholder="Pilih Rombel"
                                                     model="teacher.homeroom_kelas_id"
                                                     @change="updateHomeroomName()">
                                        @foreach($kelasList as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </x-wizard.select>
                                </div>
                            </div>

                            {{-- Kaprog Option --}}
                            <div class="p-4 rounded-xl bg-[#E2EDFF]/60 border border-blue-100 flex flex-col justify-between">
                                <label class="flex items-center gap-2 text-xs font-semibold text-[#0F172A] mb-2 cursor-pointer">
                                    <input type="checkbox"
                                           name="is_kaprog"
                                           value="1"
                                           x-model="teacher.is_kaprog"
                                           class="rounded border-slate-300 text-[#003FA9] focus:ring-[#003FA9]">
                                    <span>Tugaskan Sebagai Kepala Program Keahlian</span>
                                </label>
                                <div x-show="teacher.is_kaprog == '1' || teacher.is_kaprog === true">
                                    <x-wizard.select label="Pilih Program Keahlian (Jurusan)"
                                                     name="kaprog_jurusan_id"
                                                     id="guru_kaprog_select"
                                                     placeholder="Pilih Jurusan"
                                                     model="teacher.kaprog_jurusan_id"
                                                     @change="updateKaprogName()">
                                        @foreach($jurusans as $j)
                                            <option value="{{ $j->id }}">{{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}</option>
                                        @endforeach
                                    </x-wizard.select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==================== STEP 3: Konfirmasi ==================== --}}
                <div x-show="step === 3"
                     data-step="3"
                     class="transition-opacity duration-200">
                    <div class="flex flex-col gap-5">
                        {{-- Top Section: Teacher Details Review --}}
                        <div class="flex flex-row items-stretch gap-[40px]">
                            {{-- Left: Photo Preview Display Box --}}
                            <div class="w-[342px] h-[395px] shrink-0 rounded-[28px] bg-[#E2EDFF] flex flex-col items-center justify-center p-6 text-center">
                                <div x-show="photoPreviewUrl" class="flex flex-col items-center">
                                    <img :src="photoPreviewUrl"
                                         alt="Teacher Photo"
                                         class="w-48 h-48 object-cover rounded-2xl shadow-md border-2 border-[#003FA9] mb-3">
                                    <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-3 py-1 rounded-full">
                                        Foto Terverifikasi
                                    </span>
                                </div>
                                <div x-show="!photoPreviewUrl" class="flex flex-col items-center text-[#94A3B8]">
                                    <svg class="w-20 h-20 text-[#A0B4D8] mb-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <span class="text-sm font-semibold text-[#64748B]">
                                        Foto Profil Guru
                                    </span>
                                </div>
                            </div>

                            {{-- Right: Teacher Form Review --}}
                            <div class="flex-1 flex flex-col justify-between h-[395px]">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-wizard.review-field label="NIP" bind="teacher.nip" />
                                    <x-wizard.review-field label="NUPTK" bind="teacher.nuptk" />
                                </div>
                                <div>
                                    <x-wizard.review-field label="Nama Lengkap & Gelar" bind="teacher.nama" />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-wizard.review-field label="Jenis Kelamin"
                                                           bind="teacher.jenis_kelamin === 'L' ? 'Laki - Laki' : (teacher.jenis_kelamin === 'P' ? 'Perempuan' : '-')" />
                                    <x-wizard.review-field label="Nomor WhatsApp" bind="teacher.telepon" />
                                </div>
                                <div>
                                    <x-wizard.review-field label="Email Akun Guru" bind="teacher.email" />
                                </div>
                                <div>
                                    <x-wizard.review-field label="Mata Pelajaran Utama" bind="teacher.mapel_name" />
                                </div>
                            </div>
                        </div>

                        {{-- Bottom Section: Additional Roles Review --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <x-wizard.review-field label="Peran Wali Kelas"
                                                   bind="teacher.is_homeroom ? ('Wali Kelas: ' + (teacher.homeroom_kelas_name || '-')) : 'Bukan Wali Kelas'" />
                            <x-wizard.review-field label="Peran KAPROG"
                                                   bind="teacher.is_kaprog ? ('KAPROG: ' + (teacher.kaprog_jurusan_name || '-')) : 'Bukan KAPROG'" />
                        </div>
                    </div>
                </div>

                {{-- Footer Navigation --}}
                <div class="flex items-center justify-between mt-6 pt-2">
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

                    <div>
                        {{-- Next Button --}}
                        <button type="button"
                                x-show="step < totalSteps"
                                @click="nextStep()"
                                class="h-[44px] px-6 rounded-xl bg-[#003FA9] hover:bg-[#00348C] text-white font-bold text-sm inline-flex items-center gap-2 shadow-sm transition duration-150">
                            <span>Berikutnya</span>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                            </svg>
                        </button>

                        {{-- Submit Button --}}
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
