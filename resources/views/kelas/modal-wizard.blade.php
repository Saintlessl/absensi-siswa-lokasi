{{-- Multi-Step Wizard Modal for Rombongan Belajar (Kelas) --}}
<div id="modal-inputkelas"
     x-data="kelasWizardModal()"
     x-cloak
     class="fixed inset-0 z-[60] hidden overflow-y-auto"
     aria-labelledby="modal-title-inputkelas"
     role="dialog"
     aria-modal="true">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
        <div class="relative w-full max-w-3xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-slate-100 my-auto transform transition-all text-slate-800"
             @keydown.escape.window="close()">

            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <h2 id="modal-title-inputkelas" class="text-2xl font-bold text-[#0F172A] tracking-tight">
                        Tambah Rombongan Belajar
                    </h2>
                    <div class="flex items-center flex-wrap text-sm mt-1.5 font-semibold select-none">
                        <span :class="step >= 1 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'">
                            Struktur & Identitas Rombel
                        </span>
                        <span class="mx-2 text-[#AAC1EA] font-normal">-</span>
                        <span :class="step >= 2 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'">
                            Konfirmasi
                        </span>
                    </div>
                </div>

                <button type="button"
                        @click="close()"
                        class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

            <form action="{{ route('kelas.store') }}"
                  method="POST"
                  id="frmKelas"
                  @submit.prevent="handleSubmit($event)">
                @csrf

                {{-- STEP 1: Struktur & Identitas Rombel --}}
                <div x-show="step === 1" data-step="1" class="transition-opacity duration-200">
                    <div class="flex flex-col gap-4">
                        {{-- Live Preview Banner --}}
                        <div class="p-3.5 bg-[#E2EDFF] rounded-xl flex items-center justify-between border border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#003FA9]"></span>
                                <span class="text-xs font-semibold text-[#003FA9] uppercase tracking-wider">Format Standar Rombel:</span>
                            </div>
                            <span class="font-bold text-base text-[#003FA9] font-mono tracking-wide" x-text="previewNama">
                                10 PPLG 1
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="kelas_jurusan_id" class="block text-sm font-medium text-[#1E293B] mb-1.5">
                                    Jurusan (Program Keahlian) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="jurusan_id"
                                            id="kelas_jurusan_id"
                                            required
                                            @change="onJurusanChange($event)"
                                            class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                                        <option value="">-- Pilih Jurusan --</option>
                                        @foreach ($jurusans as $j)
                                            <option value="{{ $j->id }}"
                                                    data-kode="{{ $j->kode_jurusan }}"
                                                    data-nama="{{ $j->nama_jurusan }}">
                                                {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-[#003FA9]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="kelas_tingkat" class="block text-sm font-medium text-[#1E293B] mb-1.5">
                                    Tingkat Kelas <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="tingkat"
                                            id="kelas_tingkat"
                                            required
                                            x-model="kelas.tingkat"
                                            class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                                        <option value="10">Kelas 10 (Sepuluh)</option>
                                        <option value="11">Kelas 11 (Sebelas)</option>
                                        <option value="12">Kelas 12 (Dua Belas)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-[#003FA9]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-wizard.input label="Nomor Rombel"
                                                name="nomor_rombel"
                                                id="kelas_nomor_rombel"
                                                type="number"
                                                min="1"
                                                max="99"
                                                placeholder="Contoh: 1, 2, 3"
                                                required
                                                model="kelas.nomor_rombel"
                                                class="font-mono font-bold" />
                            </div>
                            <div>
                                <x-wizard.input label="Tahun Ajaran"
                                                name="tahun_ajaran"
                                                id="kelas_tahun_ajaran"
                                                placeholder="Contoh: 2026/2027"
                                                required
                                                model="kelas.tahun_ajaran"
                                                class="font-mono" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-wizard.input label="Nama Wali Kelas"
                                                name="wali_kelas"
                                                id="kelas_wali_kelas"
                                                placeholder="Contoh: Budi Santoso, S.Kom"
                                                required
                                                model="kelas.wali_kelas" />
                            </div>
                            <div>
                                <x-wizard.input label="Kapasitas Siswa"
                                                name="jumlah_siswa"
                                                id="kelas_jumlah_siswa"
                                                type="number"
                                                min="1"
                                                max="60"
                                                placeholder="36"
                                                required
                                                model="kelas.jumlah_siswa" />
                            </div>
                        </div>
                    </div>

                    {{-- Step 1 Actions --}}
                    <div class="mt-8 flex items-center justify-between">
                        <button type="button"
                                @click="close()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#E2EDFF] text-[#003FA9] font-semibold text-sm hover:bg-[#d4e4fc] transition-colors focus:outline-none">
                            Batal
                        </button>
                        <button type="button"
                                @click="nextStep()"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] text-white font-semibold text-sm hover:bg-[#00338d] shadow-md shadow-blue-500/20 transition-all focus:outline-none">
                            Berikutnya
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- STEP 2: Konfirmasi Data --}}
                <div x-show="step === 2" data-step="2" class="transition-opacity duration-200">
                    <div class="mb-4">
                        <span class="text-xs font-bold text-[#003FA9] uppercase tracking-wider block mb-1">Ringkasan Validasi</span>
                        <h3 class="text-base font-bold text-[#0F172A]">Periksa Kembali Struktur Rombel</h3>
                        <p class="text-xs text-[#64748B] mt-0.5">Pastikan jurusan, nomor urut rombel, dan wali kelas sudah terkonfirmasi dengan benar.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 bg-white/60 p-5 rounded-2xl border border-blue-100">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-[#003FA9] uppercase tracking-wider mb-1">
                                Format Resmi Nama Rombel
                            </label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-900 font-mono font-bold text-base"
                                 x-text="previewNama">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Program Keahlian (Jurusan)</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold truncate"
                                 x-text="kelas.jurusan_kode ? `${kelas.jurusan_kode} - ${kelas.jurusan_nama}` : '-'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Tingkat Kelas</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold"
                                 x-text="'Kelas ' + kelas.tingkat">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Nomor Rombel</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold font-mono"
                                 x-text="kelas.nomor_rombel">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Tahun Ajaran</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold font-mono"
                                 x-text="kelas.tahun_ajaran">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Wali Kelas</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold truncate"
                                 x-text="kelas.wali_kelas || '-'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Kapasitas Maksimal</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold"
                                 x-text="kelas.jumlah_siswa + ' Siswa'">
                            </div>
                        </div>
                    </div>

                    {{-- Step 2 Actions --}}
                    <div class="mt-8 flex items-center justify-between">
                        <button type="button"
                                @click="prevStep()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#E2EDFF] text-[#003FA9] font-semibold text-sm hover:bg-[#d4e4fc] transition-colors focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Kembali
                        </button>

                        <button type="submit"
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] text-white font-semibold text-sm hover:bg-[#00338d] shadow-md shadow-blue-500/20 transition-all focus:outline-none disabled:opacity-50">
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Rombel'"></span>
                            <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
