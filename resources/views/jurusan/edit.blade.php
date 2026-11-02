<form action="{{ route('jurusan.update', ['id' => $jurusan->id]) }}" method="POST" id="frmEditJurusan" enctype="multipart/form-data">
    @csrf
    <div class="space-y-4">
        {{-- Logo Preview & Upload --}}
        <div>
            <label class="block text-sm font-medium text-dark-700 mb-1.5">Logo Jurusan</label>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl border border-dark-200 bg-dark-50 flex items-center justify-center overflow-hidden flex-shrink-0" id="preview_edit_logo_container">
                    @if ($jurusan->logo_url)
                        <img src="{{ $jurusan->logo_url }}" alt="{{ $jurusan->kode_jurusan }}" class="w-full h-full object-cover" id="img_edit_preview">
                    @else
                        <i class='bx bx-image text-2xl text-dark-400' id="icon_edit_preview"></i>
                    @endif
                </div>
                <div class="flex-1">
                    <input type="file" name="logo" id="logo_edit" accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-xs text-dark-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer"
                        onchange="previewEditImage(this)">
                    <p class="text-[11px] text-dark-400 mt-1">Format: JPG, PNG, WebP. Maks 2MB.</p>
                    @if ($jurusan->logo_path)
                        <label class="inline-flex items-center gap-1.5 mt-1.5 text-xs text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-dark-300 text-rose-600 focus:ring-rose-500">
                            Hapus logo saat ini
                        </label>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <label for="kode_jurusan_edit" class="block text-sm font-medium text-dark-700 mb-1.5">
                Kode Jurusan <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-tag text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="kode_jurusan" id="kode_jurusan_edit" value="{{ $jurusan->kode_jurusan }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 uppercase font-mono font-bold"
                    placeholder="Kode Jurusan" required>
            </div>
        </div>

        <div>
            <label for="nama_jurusan_edit" class="block text-sm font-medium text-dark-700 mb-1.5">
                Nama Lengkap Jurusan <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-book-bookmark text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="nama_jurusan" id="nama_jurusan_edit" value="{{ $jurusan->nama_jurusan }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500"
                    placeholder="Nama Lengkap Jurusan" required>
            </div>
        </div>

        <div>
            <label for="deskripsi_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Deskripsi / Kompetensi</label>
            <textarea name="deskripsi" id="deskripsi_edit" rows="3"
                class="block w-full p-3 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 text-sm"
                placeholder="Deskripsi singkat tentang fokus keahlian jurusan ini...">{{ $jurusan->deskripsi }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-dark-700 mb-1.5">Status Jurusan</label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $jurusan->is_active ? 'checked' : '' }} class="rounded border-dark-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm text-dark-700 font-medium">Aktif (Dapat dipilih untuk Rombel & Siswa)</span>
            </label>
        </div>
    </div>

    <div class="mt-8 flex items-center justify-end gap-3">
        <button type="button" onclick="closeModal('modal-editjurusan')"
            class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 font-medium transition-colors duration-200">
            Batal
        </button>
        <button type="submit"
            class="px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg transition-all duration-200 flex items-center gap-2">
            <i class='bx bx-save text-lg'></i>
            Perbarui Jurusan
        </button>
    </div>
</form>

<script>
    function previewEditImage(input) {
        const container = document.getElementById('preview_edit_logo_container');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
