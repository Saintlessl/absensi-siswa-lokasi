{{-- Multi-Step Wizard Modal for Buku Nilai Asesmen (Exact Design System) --}}
<div id="modal-addassessment"
     x-data="gradebookWizardModal()"
     x-cloak
     class="fixed inset-0 z-[60] hidden overflow-y-auto"
     aria-labelledby="modal-title-addassessment"
     role="dialog"
     aria-modal="true">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    {{-- Modal Positioning Container --}}
    <div class="flex min-h-full items-center justify-center p-2 sm:p-6 text-left">
        {{-- Modal Card --}}
        <div class="relative w-full max-w-3xl rounded-[28px] bg-[#EFF5FF] px-9 pt-8 pb-6 shadow-2xl border border-slate-100 my-auto transform transition-all text-slate-800"
             @keydown.escape.window="close()">

            {{-- Header --}}
            <div class="flex items-start justify-between pb-4 border-b border-blue-100/60">
                <div>
                    <h2 id="modal-title-addassessment" class="text-[22px] font-bold text-[#0F172A] tracking-tight leading-snug">
                        Buat Komponen Asesmen Baru
                    </h2>
                    <div class="flex items-center flex-wrap text-sm mt-1 font-semibold select-none">
                        <span :class="step >= 1 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'" class="transition-colors duration-200">
                            Info Asesmen
                        </span>
                        <span class="mx-2 text-[#AAC1EA] font-normal">-</span>
                        <span :class="step >= 2 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'" class="transition-colors duration-200">
                            Bobot & Konfirmasi
                        </span>
                    </div>
                </div>

                {{-- Close Button --}}
                <button type="button"
                        @click="close()"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="frmAddAssessment" method="POST" action="{{ route('gradebook.store') }}">
                @csrf
                <input type="hidden" name="status" value="draft">

                {{-- Step 1: Info Asesmen --}}
                <div x-show="step === 1" data-step="1" class="py-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Kelas --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Rombel / Kelas <span class="text-rose-500">*</span></label>
                            <select name="kelas_id" x-model="asm.kelas_id" @change="onKelasChange($event)" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                <option value="">Pilih Rombel Target</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kategori Penilaian --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori Asesmen <span class="text-rose-500">*</span></label>
                            <select name="assessment_category_id" x-model="asm.assessment_category_id" @change="onCategoryChange($event)" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nama }} ({{ $cat->kode }}) - Bobot {{ $cat->bobot_default }}%</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Mata Pelajaran --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Mata Pelajaran (Diampu)</label>
                            <select name="mata_pelajaran_id" x-model="asm.mata_pelajaran_id" @change="onMapelChange($event)"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                <option value="">Pilih Mapel Diampu</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}" data-nama="{{ $sub->nama_mapel }}">{{ $sub->nama_mapel }} ({{ $sub->kode_mapel }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Nama Mapel Manual (fallback) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Mapel Alternatif</label>
                            <input type="text" name="nama_mapel" x-model="asm.nama_mapel"
                                placeholder="Atau ketik nama mapel manual..."
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>

                        {{-- Judul Asesmen --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Judul / Topik Penilaian <span class="text-rose-500">*</span></label>
                            <input type="text" name="judul" x-model="asm.judul" required
                                placeholder="Contoh: Praktik Pengelasan Sambungan T / Tugas Membuat Layout CSS"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>

                        {{-- Tanggal Pelaksanaan --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Pelaksanaan <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal" x-model="asm.tanggal" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>
                </div>

                {{-- Step 2: Bobot & Konfirmasi --}}
                <div x-show="step === 2" data-step="2" class="py-6 space-y-5" style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Tahun Ajaran & Semester --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Ajaran <span class="text-rose-500">*</span></label>
                            <input type="text" name="tahun_ajaran" x-model="asm.tahun_ajaran" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Semester <span class="text-rose-500">*</span></label>
                            <select name="semester" x-model="asm.semester" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                <option value="ganjil">Ganjil</option>
                                <option value="genap">Genap</option>
                            </select>
                        </div>

                        {{-- Skor Maksimal & Bobot --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Skor Maksimal <span class="text-rose-500">*</span></label>
                            <input type="number" step="1" name="skor_maksimal" x-model="asm.skor_maksimal" min="1" max="1000" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 font-mono font-bold focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Bobot Penilaian (Pengali) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.1" name="bobot" x-model="asm.bobot" min="0.1" max="10" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 font-mono font-bold focus:ring-2 focus:ring-[#003FA9]">
                        </div>

                        {{-- Deskripsi --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi / Instruksi Tugas (Opsional)</label>
                            <textarea name="deskripsi" x-model="asm.deskripsi" rows="3"
                                placeholder="Instruksi pengerjaan tugas atau kriteria rubrik penilaian..."
                                class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                        </div>
                    </div>

                    {{-- Ringkasan Review --}}
                    <div class="mt-4 p-4 rounded-2xl bg-white/70 border border-blue-100 space-y-2 text-xs">
                        <p class="font-bold text-slate-700">Ringkasan Asesmen:</p>
                        <div class="grid grid-cols-2 gap-2 text-slate-600">
                            <div>Rombel: <span class="font-semibold text-slate-900" x-text="asm.kelas_nama || '-'"></span></div>
                            <div>Kategori: <span class="font-semibold text-slate-900" x-text="asm.category_nama || '-'"></span></div>
                            <div>Mapel: <span class="font-semibold text-slate-900" x-text="asm.nama_mapel || '-'"></span></div>
                            <div>Judul: <span class="font-semibold text-slate-900" x-text="asm.judul || '-'"></span></div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="pt-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" x-show="step > 1" @click="prevStep()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali
                    </button>
                    <div x-show="step <= 1"></div>

                    <button type="button" x-show="step < totalSteps" @click="nextStep()"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        Berikutnya
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <button type="button" x-show="step === totalSteps" @click="handleSubmit($event)" :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25 disabled:opacity-50">
                        <i class='bx bx-check text-lg'></i>
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan & Input Nilai'"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
