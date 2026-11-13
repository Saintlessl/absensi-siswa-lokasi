<form action="{{ route('kelas.update', ['id' => $kelas->id]) }}" method="POST" id="frmEditKelas">
    @csrf
    <div class="space-y-4">
        {{-- Preview Banner --}}
        <div class="p-3.5 bg-[#E2EDFF] rounded-xl flex items-center justify-between border border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#003FA9]"></span>
                <span class="text-xs font-semibold text-[#003FA9] uppercase tracking-wider">Format Standar Rombel:</span>
            </div>
            <span id="preview_edit_nama_kelas" class="font-bold text-base text-[#003FA9] font-mono tracking-wide">{{ $kelas->nama_kelas }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="jurusan_id_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Jurusan (Program Keahlian) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="jurusan_id" id="jurusan_id_edit" required onchange="updateEditKelasPreview()"
                        class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach ($jurusans as $j)
                            <option value="{{ $j->id }}" data-kode="{{ $j->kode_jurusan }}" {{ $kelas->jurusan_id == $j->id ? 'selected' : '' }}>
                                {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-[#003FA9]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="tingkat_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Tingkat Kelas <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="tingkat" id="tingkat_edit" required onchange="updateEditKelasPreview()"
                        class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                        <option value="10" {{ $kelas->tingkat == 10 ? 'selected' : '' }}>Kelas 10 (Sepuluh)</option>
                        <option value="11" {{ $kelas->tingkat == 11 ? 'selected' : '' }}>Kelas 11 (Sebelas)</option>
                        <option value="12" {{ $kelas->tingkat == 12 ? 'selected' : '' }}>Kelas 12 (Dua Belas)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-[#003FA9]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="nomor_rombel_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Nomor Rombel <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="nomor_rombel" id="nomor_rombel_edit" value="{{ $kelas->nomor_rombel ?: 1 }}" min="1" max="99" required oninput="updateEditKelasPreview()"
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="1, 2, 3...">
            </div>

            <div>
                <label for="tahun_ajaran_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Tahun Ajaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran_edit" value="{{ $kelas->tahun_ajaran ?: '2026/2027' }}" required
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Contoh: 2026/2027">
            </div>

            <div>
                <label for="wali_kelas_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Wali Kelas <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="wali_kelas" id="wali_kelas_edit" value="{{ $kelas->wali_kelas }}" required
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Nama Wali Kelas">
            </div>

            <div>
                <label for="jumlah_siswa_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Kapasitas Siswa <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="jumlah_siswa" id="jumlah_siswa_edit" value="{{ $kelas->jumlah_siswa }}" min="1" max="60" required
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Contoh: 36">
            </div>
        </div>
    </div>

    <div class="mt-8 pt-4 border-t border-[#D8E6FC] flex items-center justify-between">
        <button type="button" onclick="closeModal('modal-editkelas')"
            class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] text-[#003FA9] font-semibold text-sm hover:bg-[#d4e4fc] transition-colors focus:outline-none">
            Batal
        </button>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] text-white font-semibold text-sm hover:bg-[#00338d] shadow-md shadow-blue-500/20 transition-all focus:outline-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            Perbarui Data Rombel
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
        } else {
            previewEl.textContent = '-';
        }
    }
</script>
