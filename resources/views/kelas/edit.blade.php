<form action="{{ route('kelas.update', ['id' => $kelas->id]) }}" method="POST" id="frmEditKelas">
    @csrf
    <div class="space-y-4">
        {{-- Preview Nama Rombel --}}
        <div class="p-3.5 bg-primary-50/50 border border-primary-200/60 rounded-xl flex items-center justify-between">
            <span class="text-xs text-primary-700 font-medium">Format Nama Rombel Otomatis:</span>
            <span id="preview_edit_nama_kelas" class="font-bold text-sm text-primary-900 font-mono">{{ $kelas->nama_kelas }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="jurusan_id_edit" class="block text-xs font-semibold text-dark-700 mb-1.5">
                    Jurusan (Program Keahlian) <span class="text-rose-500">*</span>
                </label>
                <select name="jurusan_id" id="jurusan_id_edit" required onchange="updateEditKelasPreview()"
                    class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 text-sm">
                    <option value="">-- Pilih Jurusan --</option>
                    @foreach ($jurusans as $j)
                        <option value="{{ $j->id }}" data-kode="{{ $j->kode_jurusan }}" {{ $kelas->jurusan_id == $j->id ? 'selected' : '' }}>
                            {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tingkat_edit" class="block text-xs font-semibold text-dark-700 mb-1.5">
                    Tingkat Kelas <span class="text-rose-500">*</span>
                </label>
                <select name="tingkat" id="tingkat_edit" required onchange="updateEditKelasPreview()"
                    class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 text-sm">
                    <option value="10" {{ $kelas->tingkat == 10 ? 'selected' : '' }}>Kelas 10 (Sepuluh)</option>
                    <option value="11" {{ $kelas->tingkat == 11 ? 'selected' : '' }}>Kelas 11 (Sebelas)</option>
                    <option value="12" {{ $kelas->tingkat == 12 ? 'selected' : '' }}>Kelas 12 (Dua Belas)</option>
                </select>
            </div>

            <div>
                <label for="nomor_rombel_edit" class="block text-xs font-semibold text-dark-700 mb-1.5">
                    Nomor Rombel <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="nomor_rombel" id="nomor_rombel_edit" value="{{ $kelas->nomor_rombel ?: 1 }}" min="1" max="99" required oninput="updateEditKelasPreview()"
                    class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 text-sm font-mono"
                    placeholder="1, 2, 3...">
            </div>

            <div>
                <label for="tahun_ajaran_edit" class="block text-xs font-semibold text-dark-700 mb-1.5">
                    Tahun Ajaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran_edit" value="{{ $kelas->tahun_ajaran ?: '2026/2027' }}" required
                    class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 text-sm font-mono"
                    placeholder="Contoh: 2026/2027">
            </div>

            <div>
                <label for="wali_kelas_edit" class="block text-xs font-semibold text-dark-700 mb-1.5">
                    Wali Kelas <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="wali_kelas" id="wali_kelas_edit" value="{{ $kelas->wali_kelas }}" required
                    class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 text-sm"
                    placeholder="Nama Wali Kelas">
            </div>

            <div>
                <label for="jumlah_siswa_edit" class="block text-xs font-semibold text-dark-700 mb-1.5">
                    Kapasitas / Jumlah Siswa <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="jumlah_siswa" id="jumlah_siswa_edit" value="{{ $kelas->jumlah_siswa }}" min="1" required
                    class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 text-sm"
                    placeholder="Contoh: 36">
            </div>
        </div>
    </div>

    <div class="mt-6 pt-4 border-t border-dark-100 flex items-center justify-end gap-3">
        <button type="button" onclick="closeModal('modal-editkelas')"
            class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 font-medium transition-colors duration-200 text-sm">
            Batal
        </button>
        <button type="submit"
            class="px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg transition-all duration-200 flex items-center gap-2 text-sm">
            <i class='bx bx-save text-lg'></i>
            Perbarui Rombel
        </button>
    </div>
</form>

<script>
    function updateEditKelasPreview() {
        const jurusanSelect = document.getElementById('jurusan_id_edit');
        const tingkatSelect = document.getElementById('tingkat_edit');
        const noRombelInput = document.getElementById('nomor_rombel_edit');
        const previewEl = document.getElementById('preview_edit_nama_kelas');

        const opt = jurusanSelect.options[jurusanSelect.selectedIndex];
        const kode = opt ? (opt.getAttribute('data-kode') || '') : '';
        const tingkat = tingkatSelect ? tingkatSelect.value : '';
        const no = noRombelInput ? noRombelInput.value : '';

        if (tingkat && kode && no) {
            previewEl.textContent = `${tingkat} ${kode} ${no}`;
        }
    }
</script>
