@props([
    'name' => 'photo',
    'id' => 'photo_uploader',
    'buttonText' => 'Tambahkan Foto Siswa',
    'accept' => 'image/jpeg,image/png,image/webp',
    'maxSizeMB' => 2,
    'model' => 'photoPreview',
])

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
            if (file.size > {{ $maxSizeMB }} * 1024 * 1024) {
                window.MySkanicAlert ? MySkanicAlert.warning('Ukuran file maksimal {{ $maxSizeMB }}MB') : alert('Ukuran file maksimal {{ $maxSizeMB }}MB');
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewUrl = e.target.result;
                if (typeof window.wizardPhotoPreview !== 'undefined') {
                    window.wizardPhotoPreview = e.target.result;
                }
                window.dispatchEvent(new CustomEvent('wizard-photo-updated', { detail: { url: e.target.result } }));
            };
            reader.readAsDataURL(file);
        },
        removePhoto() {
            this.previewUrl = null;
            this.$refs.fileInput.value = '';
            if (typeof window.wizardPhotoPreview !== 'undefined') {
                window.wizardPhotoPreview = null;
            }
            window.dispatchEvent(new CustomEvent('wizard-photo-updated', { detail: { url: null } }));
        }
     }">

    {{-- Dashed Container --}}
    <div class="w-full h-full min-h-[380px] rounded-[28px] border-2 border-dashed transition-all duration-200 flex flex-col items-center justify-center p-6 text-center relative"
         :class="dragOver ? 'border-[#003FA9] bg-blue-100/40' : 'border-[#93C5FD] bg-transparent'"
         @dragover.prevent="dragOver = true"
         @dragleave.prevent="dragOver = false"
         @drop.prevent="handleDrop($event)">

        {{-- Hidden File Input --}}
        <input type="file"
               name="{{ $name }}"
               id="{{ $id }}"
               x-ref="fileInput"
               accept="{{ $accept }}"
               class="hidden"
               @change="handleFileSelect($event)">

        {{-- Empty State (No Image) --}}
        <template x-if="!previewUrl">
            <div class="flex flex-col items-center justify-center">
                {{-- Square Rounded Container with Avatar Icon --}}
                <div class="w-[84px] h-[84px] rounded-2xl border-2 border-[#003FA9] flex items-center justify-center text-[#003FA9] mb-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path stroke-linecap="round" d="M5.5 20c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"></path>
                    </svg>
                </div>

                {{-- Blue Upload Button --}}
                <button type="button"
                        @click="$refs.fileInput.click()"
                        class="h-10 px-5 rounded-xl bg-[#003FA9] hover:bg-[#00348C] text-white text-xs font-semibold inline-flex items-center gap-2 shadow-sm transition duration-150">
                    {{-- Cloud Upload Icon --}}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span>{{ $buttonText }}</span>
                </button>

                {{-- Subtitles --}}
                <p class="text-xs font-medium text-[#60A5FA] mt-4">
                    Atau tarik dan lepas di sini
                </p>
                <p class="text-[11px] text-[#94A3B8] mt-0.5">
                    Format: JPG, PNG, WebP. Maksimal {{ $maxSizeMB }}MB.
                </p>
            </div>
        </template>

        {{-- Preview State (With Image) --}}
        <template x-if="previewUrl">
            <div class="flex flex-col items-center justify-center w-full h-full">
                <div class="relative group">
                    <img :src="previewUrl"
                         alt="Preview"
                         class="w-44 h-44 object-cover rounded-2xl shadow-md border-2 border-[#003FA9]">
                    <div class="absolute inset-0 bg-slate-900/40 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button type="button"
                                @click="$refs.fileInput.click()"
                                class="p-2 bg-white text-[#003FA9] rounded-lg shadow hover:bg-blue-50 text-xs font-semibold"
                                title="Ganti Foto">
                            Ganti
                        </button>
                        <button type="button"
                                @click="removePhoto()"
                                class="p-2 bg-rose-600 text-white rounded-lg shadow hover:bg-rose-700 text-xs font-semibold"
                                title="Hapus Foto">
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
        </template>

    </div>
</div>
