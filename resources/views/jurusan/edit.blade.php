<form action="{{ route('jurusan.update', ['id' => $jurusan->id]) }}" method="POST" id="frmEditJurusan">
    @csrf
    <div class="space-y-4">
        <div>
            <label for="kode_jurusan_edit" class="block text-sm font-medium text-dark-700 mb-1.5">
                Kode Jurusan <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-tag text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="kode_jurusan" id="kode_jurusan_edit" value="{{ $jurusan->kode_jurusan }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 uppercase"
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
