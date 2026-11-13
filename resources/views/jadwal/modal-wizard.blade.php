{{-- Multi-Step Wizard Modal for Jadwal Pelajaran --}}
<div id="modal-inputjadwal"
     x-data="jadwalWizardModal()"
     x-cloak
     class="fixed inset-0 z-[60] hidden overflow-y-auto"
     aria-labelledby="modal-title-inputjadwal"
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
                    <h2 id="modal-title-inputjadwal" class="text-2xl font-bold text-[#0F172A] tracking-tight">
                        Tambah Jadwal Pelajaran
                    </h2>
                    <div class="flex items-center flex-wrap text-sm mt-1.5 font-semibold select-none">
                        <span :class="step >= 1 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'">
                            Penugasan & Waktu Mengajar
                        </span>
                        <span class="mx-2 text-[#AAC1EA] font-normal">-</span>
                        <span :class="step >= 2 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'">
                            Konfirmasi Jadwal
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

            <form action="{{ route('jadwal.store') }}"
                  method="POST"
                  id="frmJadwal"
                  @submit.prevent="handleSubmit($event)">
                @csrf

                {{-- Hidden input for backward compatible mapel field --}}
                <input type="hidden" name="mata_pelajaran" :value="jadwal.mata_pelajaran">

                {{-- STEP 1: Penugasan & Waktu Mengajar --}}
                <div x-show="step === 1" data-step="1" class="transition-opacity duration-200">
                    <div class="flex flex-col gap-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="jadwal_guru_id" class="block text-sm font-medium text-[#1E293B] mb-1.5">
                                    Guru Pengajar <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="guru_id"
                                            id="jadwal_guru_id"
                                            required
                                            @change="onGuruChange($event)"
                                            class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                                        <option value="">-- Pilih Guru --</option>
                                        @foreach ($guru as $g)
                                            <option value="{{ $g->id }}"
                                                    data-nama="{{ $g->nama }}"
                                                    data-mapel="{{ $g->mapel }}">
                                                {{ $g->nama }} ({{ $g->nip ?: 'Non-NIP' }})
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
                                <label for="jadwal_mata_pelajaran_id" class="block text-sm font-medium text-[#1E293B] mb-1.5">
                                    Mata Pelajaran <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="mata_pelajaran_id"
                                            id="jadwal_mata_pelajaran_id"
                                            @change="onMapelChange($event)"
                                            class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                                        <option value="">-- Pilih Mata Pelajaran --</option>
                                        @foreach ($mataPelajarans as $mp)
                                            <option value="{{ $mp->id }}" data-nama="{{ $mp->nama_mapel }}">
                                                {{ $mp->kode_mapel }} - {{ $mp->nama_mapel }}
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
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="jadwal_kelas_id" class="block text-sm font-medium text-[#1E293B] mb-1.5">
                                    Rombongan Belajar (Rombel) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="kelas_id"
                                            id="jadwal_kelas_id"
                                            required
                                            @change="onKelasChange($event)"
                                            class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                                        <option value="">-- Pilih Rombel --</option>
                                        @foreach ($kelas as $k)
                                            <option value="{{ $k->id }}" data-nama="{{ $k->nama_kelas }}">
                                                {{ $k->nama_kelas }} ({{ $k->jurusan->kode_jurusan ?? '-' }})
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
                                <label for="jadwal_room_id" class="block text-sm font-medium text-[#1E293B] mb-1.5">
                                    Ruangan Fisik (Opsional)
                                </label>
                                <div class="relative">
                                    <select name="room_id"
                                            id="jadwal_room_id"
                                            @change="onRoomChange($event)"
                                            class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                                        <option value="">-- Ikuti Alokasi Tetap Rombel --</option>
                                        @foreach ($rooms as $r)
                                            <option value="{{ $r->id }}" data-nama="{{ $r->name }} ({{ $r->code }})">
                                                {{ $r->name }} - {{ $r->code }} ({{ $r->building ?: 'Gedung Sekolah' }})
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
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="jadwal_hari" class="block text-sm font-medium text-[#1E293B] mb-1.5">
                                    Hari <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="hari"
                                            id="jadwal_hari"
                                            required
                                            x-model="jadwal.hari"
                                            class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                                        <option value="Senin">Senin</option>
                                        <option value="Selasa">Selasa</option>
                                        <option value="Rabu">Rabu</option>
                                        <option value="Kamis">Kamis</option>
                                        <option value="Jumat">Jumat</option>
                                        <option value="Sabtu">Sabtu</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-[#003FA9]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <x-wizard.input label="Tahun Ajaran"
                                                name="tahun_ajaran"
                                                id="jadwal_tahun_ajaran"
                                                placeholder="2026/2027"
                                                required
                                                model="jadwal.tahun_ajaran"
                                                class="font-mono" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="jadwal_jam_mulai" class="block text-sm font-medium text-[#1E293B] mb-1.5">
                                    Jam Mulai <span class="text-rose-500">*</span>
                                </label>
                                <input type="time"
                                       name="jam_mulai"
                                       id="jadwal_jam_mulai"
                                       required
                                       x-model="jadwal.jam_mulai"
                                       class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition">
                            </div>

                            <div>
                                <label for="jadwal_jam_selesai" class="block text-sm font-medium text-[#1E293B] mb-1.5">
                                    Jam Selesai <span class="text-rose-500">*</span>
                                </label>
                                <input type="time"
                                       name="jam_selesai"
                                       id="jadwal_jam_selesai"
                                       required
                                       x-model="jadwal.jam_selesai"
                                       class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition">
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

                {{-- STEP 2: Konfirmasi Jadwal --}}
                <div x-show="step === 2" data-step="2" class="transition-opacity duration-200">
                    <div class="mb-4">
                        <span class="text-xs font-bold text-[#003FA9] uppercase tracking-wider block mb-1">Pengecekan Otomatis Konflik</span>
                        <h3 class="text-base font-bold text-[#0F172A]">Konfirmasi Jadwal Pelajaran</h3>
                        <p class="text-xs text-[#64748B] mt-0.5">Sistem akan secara instan memvalidasi bentrok jam guru, bentrok kelas, dan bentrok ruangan fisik.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 bg-white/60 p-5 rounded-2xl border border-blue-100">
                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Guru Pengajar</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-900 font-bold text-sm truncate"
                                 x-text="jadwal.guru_nama || '-'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Mata Pelajaran</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-900 font-bold text-sm truncate"
                                 x-text="jadwal.mata_pelajaran || '-'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Rombongan Belajar</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold font-mono"
                                 x-text="jadwal.kelas_nama || '-'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Ruangan Fisik</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold truncate"
                                 x-text="jadwal.room_nama || 'Mengikuti Alokasi Tetap Rombel'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Hari Pelaksanaan</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold"
                                 x-text="jadwal.hari">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Waktu Pelajaran</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold font-mono"
                                 x-text="jadwal.jam_mulai + ' - ' + jadwal.jam_selesai + ' WIB'">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Tahun Ajaran</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold font-mono"
                                 x-text="jadwal.tahun_ajaran">
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
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Jadwal'"></span>
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
