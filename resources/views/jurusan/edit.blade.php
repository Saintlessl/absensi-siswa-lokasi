<form action="{{ route('jurusan.update', ['id' => $jurusan->id]) }}" method="POST" id="frmEditJurusan" enctype="multipart/form-data">
    @csrf
    <div class="space-y-4">
        {{-- Logo Preview & Upload --}}
        <div class="p-4 rounded-2xl bg-white/70 border border-blue-100 flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-[#E2EDFF] border border-blue-200 flex items-center justify-center overflow-hidden flex-shrink-0" id="preview_edit_logo_container">
                @if ($jurusan->logo_url)
                    <img src="{{ $jurusan->logo_url }}" alt="{{ $jurusan->kode_jurusan }}" class="w-full h-full object-cover" id="img_edit_preview">
                @else
                    <svg class="w-8 h-8 text-[#003FA9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                @endif
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-[#1E293B] mb-1">Logo Jurusan (Opsional)</label>
                <input type="file" name="logo" id="logo_edit" accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#003FA9] file:text-white hover:file:bg-[#00338d] cursor-pointer"
                    onchange="previewEditImage(this)">
                <p class="text-[11px] text-[#64748B] mt-1">Format: JPG, PNG, WebP. Maksimal 2MB.</p>
                @if ($jurusan->logo_path)
                    <label class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold text-rose-600 cursor-pointer">
                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                        Hapus logo saat ini
                    </label>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="kode_jurusan_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Kode Jurusan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="kode_jurusan" id="kode_jurusan_edit" value="{{ $jurusan->kode_jurusan }}"
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono font-bold uppercase focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Contoh: PPLG" required>
            </div>

            <div class="sm:col-span-2">
                <label for="nama_jurusan_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Nama Lengkap Jurusan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_jurusan" id="nama_jurusan_edit" value="{{ $jurusan->nama_jurusan }}"
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Contoh: Pengembangan Perangkat Lunak dan Gim" required>
            </div>
        </div>

        <div>
            <label for="deskripsi_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                Deskripsi / Kompetensi Keahlian
            </label>
            <textarea name="deskripsi" id="deskripsi_edit" rows="3"
                class="w-full p-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                placeholder="Deskripsi singkat mengenai fokus keahlian jurusan ini...">{{ $jurusan->deskripsi }}</textarea>
        </div>

        <div class="pt-1">
            <label class="flex items-center gap-3 cursor-pointer select-none bg-white/70 p-3.5 rounded-xl border border-blue-100">
                <input type="checkbox" name="is_active" value="1" {{ $jurusan->is_active ? 'checked' : '' }}
                    class="w-5 h-5 text-[#003FA9] rounded border-blue-300 focus:ring-[#003FA9]">
                <div>
                    <span class="text-sm font-bold text-[#0F172A] block">Jurusan Aktif</span>
                    <span class="text-xs text-[#64748B]">Dapat dipilih untuk struktur rombel dan data siswa</span>
                </div>
            </label>
        </div>
    </div>

    <div class="mt-8 pt-4 border-t border-[#D8E6FC] flex items-center justify-between">
        <button type="button" onclick="closeModal('modal-editjurusan')"
            class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] text-[#003FA9] font-semibold text-sm hover:bg-[#d4e4fc] transition-colors focus:outline-none">
            Batal
        </button>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] text-white font-semibold text-sm hover:bg-[#00338d] shadow-md shadow-blue-500/20 transition-all focus:outline-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            Perbarui Data Jurusan
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
