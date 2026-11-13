{{-- Multi-Step Wizard Modal for Ruangan Fisik (Room) --}}
<div id="modal-addroom"
     x-data="roomWizardModal()"
     x-cloak
     class="fixed inset-0 z-[60] hidden overflow-y-auto"
     aria-labelledby="modal-title-addroom"
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
                    <h2 id="modal-title-addroom" class="text-2xl font-bold text-[#0F172A] tracking-tight">
                        Tambah Ruangan Baru
                    </h2>
                    <div class="flex items-center flex-wrap text-sm mt-1.5 font-semibold select-none">
                        <span :class="step >= 1 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'">
                            Spesifikasi Ruangan
                        </span>
                        <span class="mx-2 text-[#AAC1EA] font-normal">-</span>
                        <span :class="step >= 2 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'">
                            Konfirmasi Ruangan
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

            <form action="{{ route('rooms.store') }}"
                  method="POST"
                  id="frmAddRoom"
                  @submit.prevent="handleSubmit($event)">
                @csrf

                {{-- STEP 1: Spesifikasi Ruangan --}}
                <div x-show="step === 1" data-step="1" class="transition-opacity duration-200">
                    <div class="flex flex-col gap-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-wizard.input label="Nama Ruangan"
                                                name="name"
                                                id="room_name"
                                                placeholder="Contoh: Lab Komputer 1, Ruang 201"
                                                required
                                                model="room.name" />
                            </div>
                            <div>
                                <x-wizard.input label="Kode Ruangan (Unik)"
                                                name="code"
                                                id="room_code"
                                                placeholder="Contoh: LAB-01, R-201"
                                                required
                                                model="room.code"
                                                class="uppercase font-mono font-bold" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-wizard.input label="Gedung / Lokasi Lantai"
                                                name="building"
                                                id="room_building"
                                                placeholder="Contoh: Gedung A Lantai 2"
                                                model="room.building" />
                            </div>
                            <div>
                                <x-wizard.input label="Jenis / Tipe Ruangan"
                                                name="type"
                                                id="room_type"
                                                placeholder="Contoh: Lab Komputer, Bengkel, Teori"
                                                model="room.type" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                            <div>
                                <x-wizard.input label="Kapasitas Siswa"
                                                name="capacity"
                                                id="room_capacity"
                                                type="number"
                                                min="1"
                                                max="200"
                                                placeholder="36"
                                                model="room.capacity" />
                            </div>
                            <div class="pt-5">
                                <label class="flex items-center gap-3 cursor-pointer select-none bg-[#E2EDFF] p-3 rounded-xl">
                                    <input type="checkbox"
                                           name="is_active"
                                           value="1"
                                           x-model="room.is_active"
                                           class="w-5 h-5 text-[#003FA9] rounded border-blue-300 focus:ring-[#003FA9]">
                                    <div>
                                        <span class="text-sm font-bold text-[#0F172A] block">Ruangan Aktif</span>
                                        <span class="text-xs text-[#64748B]">Dapat digunakan untuk presensi QR code</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <x-wizard.textarea label="Fasilitas & Keterangan (Opsional)"
                                               name="description"
                                               id="room_description"
                                               placeholder="Contoh: Dilengkapi 36 unit PC Core i7, 2 unit AC 2PK, Projektor, Sound System..."
                                               rows="3"
                                               model="room.description" />
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

                {{-- STEP 2: Konfirmasi Ruangan --}}
                <div x-show="step === 2" data-step="2" class="transition-opacity duration-200">
                    <div class="mb-4">
                        <span class="text-xs font-bold text-[#003FA9] uppercase tracking-wider block mb-1">Ringkasan Validasi</span>
                        <h3 class="text-base font-bold text-[#0F172A]">Periksa Spesifikasi Ruangan</h3>
                        <p class="text-xs text-[#64748B] mt-0.5">Sistem akan secara otomatis men-generate token QR Code permanen untuk ruangan ini.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 bg-white/60 p-5 rounded-2xl border border-blue-100">
                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Nama Ruangan</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-900 font-bold text-sm"
                                 x-text="room.name || '-'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Kode Ruangan</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-900 font-bold font-mono text-sm"
                                 x-text="room.code || '-'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Gedung / Lokasi</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold"
                                 x-text="room.building || '-'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Jenis / Tipe</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold"
                                 x-text="room.type || '-'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Kapasitas</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold"
                                 x-text="(room.capacity || '0') + ' Siswa'">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Status Operasional</label>
                            <div class="h-11 px-4 rounded-xl bg-[#E2EDFF] flex items-center text-slate-800 text-sm font-semibold">
                                <span class="inline-flex items-center gap-1.5" :class="room.is_active ? 'text-emerald-700' : 'text-slate-500'">
                                    <span class="w-2 h-2 rounded-full" :class="room.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                    <span x-text="room.is_active ? 'Aktif untuk Presensi' : 'Nonaktif'"></span>
                                </span>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-[#64748B] mb-1">Fasilitas / Catatan</label>
                            <div class="p-3.5 rounded-xl bg-[#E2EDFF] text-slate-800 text-sm min-h-[60px]"
                                 x-text="room.description || 'Tidak ada catatan fasilitas khusus.'">
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
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Ruangan'"></span>
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
