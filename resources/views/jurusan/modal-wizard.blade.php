{{-- Multi-Step Wizard Modal for Jurusan --}}
<div id="modal-inputjurusan"
     x-data="jurusanWizardModal()"
     x-cloak
     class="fixed inset-0 z-[60] hidden overflow-y-auto"
     aria-labelledby="modal-title-inputjurusan"
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
                    <h2 id="modal-title-inputjurusan" class="text-2xl font-bold text-[#0F172A] tracking-tight">
                        Tambah Data Jurusan
                    </h2>
                    <div class="flex items-center flex-wrap text-sm mt-1.5 font-semibold select-none">
                        <span :class="step >= 1 ? 'text-[#003FA9]' : 'text-[#AAC1EA]'">
                            Informasi Jurusan
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

            <form action="{{ route('jurusan.store') }}"
                  method="POST"
                  id="frmJurusan"
                  @submit.prevent="handleSubmit($event)">
                @csrf

                {{-- STEP 1: Informasi Jurusan --}}
                <div x-show="step === 1" data-step="1" class="transition-opacity duration-200">
                    <div class="flex flex-col gap-5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-wizard.input label="Kode Jurusan"
                                                name="kode_jurusan"
                                                id="input_kode_jurusan"
                                                placeholder="Contoh: PPLG"
                                                required
                                                maxlength="10"
                                                model="jurusan.kode_jurusan"
                                                class="uppercase font-mono font-bold" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-wizard.input label="Nama Lengkap Jurusan"
                                                name="nama_jurusan"
                                                id="input_nama_jurusan"
                                                placeholder="Contoh: Pengembangan Perangkat Lunak dan Gim"
                                                required
                                                model="jurusan.nama_jurusan" />
                            </div>
                        </div>

                        <div>
                            <x-wizard.textarea label="Deskripsi / Kompetensi Keahlian"
                                               name="deskripsi"
                                               id="input_deskripsi_jurusan"
                                               placeholder="Deskripsi singkat mengenai fokus kompetensi keahlian jurusan ini..."
                                               rows="3"
                                               model="jurusan.deskripsi" />
                        </div>

                        <div class="mt-1">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox"
                                       name="is_active"
                                       value="1"
                                       x-model="jurusan.is_active"
                                       class="w-4 h-4 rounded border-slate-300 text-[#003FA9] focus:ring-[#003FA9] cursor-pointer">
                                <span class="text-sm text-slate-700 font-medium">
                                    Aktif (Dapat dipilih saat membuat kelas dan data siswa)
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Konfirmasi --}}
                <div x-show="step === 2" x-cloak data-step="2" class="transition-opacity duration-200">
                    <div class="flex flex-col gap-5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-wizard.review-field label="Kode Jurusan" bind="jurusan.kode_jurusan.toUpperCase()" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-wizard.review-field label="Nama Lengkap Jurusan" bind="jurusan.nama_jurusan" />
                            </div>
                        </div>
                        <div>
                            <x-wizard.review-field label="Deskripsi" bind="jurusan.deskripsi" isTextarea />
                        </div>
                        <div>
                            <x-wizard.review-field label="Status Jurusan" bind="jurusan.is_active ? 'Aktif' : 'Nonaktif'" />
                        </div>
                    </div>
                </div>

                {{-- Footer Navigation --}}
                <div class="flex items-center justify-between mt-8 pt-4">
                    <div>
                        <button type="button"
                                x-show="step > 1"
                                x-cloak
                                @click="prevStep()"
                                class="h-[44px] px-6 rounded-xl bg-[#E2EDFF] hover:bg-[#D4E4FC] text-[#003FA9] font-bold text-sm inline-flex items-center gap-2 transition duration-150">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                            </svg>
                            <span>Kembali</span>
                        </button>
                    </div>

                    <div>
                        <button type="button"
                                x-show="step < totalSteps"
                                @click="nextStep()"
                                class="h-[44px] px-6 rounded-xl bg-[#003FA9] hover:bg-[#00348C] text-white font-bold text-sm inline-flex items-center gap-2 shadow-sm transition duration-150">
                            <span>Berikutnya</span>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                            </svg>
                        </button>

                        <button type="submit"
                                x-show="step === totalSteps"
                                x-cloak
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
