{{-- Multi-Step Wizard Modal for Inventaris Alat (Exact Design System) --}}
<div id="modal-addinventory"
     x-data="inventoryWizardModal()"
     x-cloak
     class="fixed inset-0 z-[60] hidden overflow-y-auto"
     aria-labelledby="modal-title-addinventory"
     role="dialog"
     aria-modal="true">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    {{-- Modal Positioning Container --}}
    <div class="flex min-h-full items-center justify-center p-2 sm:p-6 text-left">
        {{-- Modal Card --}}
        <div class="relative w-full max-w-3xl rounded-[28px] bg-[#EFF5FF] px-9 pt-8 pb-6 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800"
             @keydown.escape.window="close()">

            {{-- Header --}}
            <div class="flex items-start justify-between pb-4 border-b border-blue-100/60">
                <div>
                    <h2 id="modal-title-addinventory" class="text-[22px] font-bold text-[#0F172A] tracking-tight leading-snug">
                        Tambah Peralatan Praktik
                    </h2>
                    <div class="flex items-center flex-wrap text-sm mt-1 font-semibold select-none">
                        <span :class="step >= 1 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'" class="transition-colors duration-200">
                            Data Peralatan
                        </span>
                        <span class="mx-2 text-[#AAC1EA] font-normal">-</span>
                        <span :class="step >= 2 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'" class="transition-colors duration-200">
                            Konfirmasi
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

            <form id="frmInventory" method="POST" action="{{ route('inventory.store') }}">
                @csrf

                {{-- Step 1: Data Peralatan --}}
                <div x-show="step === 1" data-step="1" class="py-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Kode Barang --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Unik Alat / Aset <span class="text-rose-500">*</span></label>
                            <input type="text" name="kode_barang" x-model="inv.kode_barang" required
                                placeholder="Contoh: BCF-CAM-001, TPFL-WLD-002"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] font-mono font-bold uppercase">
                        </div>

                        {{-- Nama Barang --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Peralatan / Perangkat <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_barang" x-model="inv.nama_barang" required
                                placeholder="Contoh: Kamera Sony Alpha a6400"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>

                        {{-- Jurusan --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Jurusan / Pemilik Alat</label>
                            <select name="jurusan_id" x-model="inv.jurusan_id"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                <option value="">Semua Jurusan / Umum</option>
                                @foreach($jurusans as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Cakupan Akses Peminjaman --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Cakupan Akses Peminjaman <span class="text-rose-500">*</span></label>
                            <select name="borrowing_scope" x-model="inv.borrowing_scope" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none font-semibold">
                                <option value="jurusan">Khusus Jurusan (Hanya Siswa Jurusan Pemilik)</option>
                                <option value="umum">Umum (Siswa Semua Jurusan & Guru)</option>
                            </select>
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori Peralatan <span class="text-rose-500">*</span></label>
                            <input type="text" name="kategori" x-model="inv.kategori" required
                                placeholder="Kamera & Video, Mesin & Las, Laptop & Komputer"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>

                        {{-- Merk & Tipe/Model --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Merk / Brand</label>
                                <input type="text" name="merk" x-model="inv.merk"
                                    placeholder="Contoh: HP, Sony, Asus"
                                    class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Model / Tipe</label>
                                <input type="text" name="tipe_model" x-model="inv.tipe_model"
                                    placeholder="Contoh: OMEN 16, A6400"
                                    class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                            </div>
                        </div>

                        {{-- Lokasi Penyimpanan --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Lokasi Default <span class="text-rose-500">*</span></label>
                            <input type="text" name="lokasi_penyimpanan" x-model="inv.lokasi_penyimpanan" required
                                placeholder="Contoh: Lemari Kaca Studio BCF"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>

                        {{-- Total Unit & Kondisi --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Jumlah Unit <span class="text-rose-500">*</span></label>
                                <input type="number" name="total_unit" x-model="inv.total_unit" min="1" max="1000" required
                                    class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 font-mono font-bold focus:ring-2 focus:ring-[#003FA9]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kondisi <span class="text-rose-500">*</span></label>
                                <select name="kondisi" x-model="inv.kondisi" required
                                    class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                    <option value="baik">Kondisi Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi & Kelengkapan (Opsional)</label>
                            <textarea name="deskripsi" x-model="inv.deskripsi" rows="3"
                                placeholder="Kelengkapan unit (charger, box, mata las, kabel) atau spesifikasi singkat..."
                                class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Konfirmasi --}}
                <div x-show="step === 2" data-step="2" class="py-6 space-y-5" style="display: none;">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Barang</label>
                                <div class="h-11 flex items-center px-4 rounded-xl bg-[#E2EDFF] text-sm font-mono font-bold uppercase text-slate-800" x-text="inv.kode_barang || '-'"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Peralatan</label>
                                <div class="h-11 flex items-center px-4 rounded-xl bg-[#E2EDFF] text-sm font-semibold text-slate-800" x-text="inv.nama_barang || '-'"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                                <div class="h-11 flex items-center px-4 rounded-xl bg-[#E2EDFF] text-sm text-slate-800" x-text="inv.kategori || '-'"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Penyimpanan</label>
                                <div class="h-11 flex items-center px-4 rounded-xl bg-[#E2EDFF] text-sm text-slate-800" x-text="inv.lokasi_penyimpanan || '-'"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Unit</label>
                                <div class="h-11 flex items-center px-4 rounded-xl bg-[#E2EDFF] text-sm font-mono font-bold text-slate-800" x-text="inv.total_unit"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi</label>
                                <div class="h-11 flex items-center px-4 rounded-xl bg-[#E2EDFF] text-sm font-semibold text-slate-800 capitalize" x-text="(inv.kondisi || '').replace('_', ' ')"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Cakupan Akses</label>
                                <div class="h-11 flex items-center px-4 rounded-xl bg-[#E2EDFF] text-sm font-semibold text-slate-800" x-text="inv.borrowing_scope === 'umum' ? 'Umum (Semua Jurusan & Guru)' : 'Khusus Jurusan'"></div>
                            </div>
                        </div>
                        <div x-show="inv.deskripsi">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi</label>
                            <div class="min-h-[44px] flex items-start p-4 rounded-xl bg-[#E2EDFF] text-sm text-slate-800" x-text="inv.deskripsi"></div>
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
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Peralatan'"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
