<form action="{{ route('jadwal.update', ['id' => $jadwal->id]) }}" method="POST" id="frmEditJadwal">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Mata Pelajaran (Read Only / Auto dari Guru) --}}
        <div class="md:col-span-2">
            <label for="mata_pelajaran_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Mata Pelajaran</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-book-open text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="mata_pelajaran" id="mata_pelajaran_edit"
                    value="{{ $jadwal->mata_pelajaran }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50 text-dark-500 focus:outline-none cursor-not-allowed"
                    readonly>
            </div>
        </div>

        {{-- Guru --}}
        <div class="md:col-span-2">
            <label for="guru_id_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Guru Pengajar</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-user text-dark-400 text-lg'></i>
                </div>
                <select name="guru_id" id="guru_id_edit" onchange="updateMapelEdit()"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all appearance-none"
                    required>
                    <option value="">Pilih Guru</option>
                    @foreach ($guru as $g)
                        <option value="{{ $g->id }}" data-mapel="{{ $g->mapel }}"
                            {{ $jadwal->guru_id == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Kelas --}}
        <div>
            <label for="kelas_id_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Kelas</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-home-alt text-dark-400 text-lg'></i>
                </div>
                <select name="kelas_id" id="kelas_id_edit"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all appearance-none"
                    required>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" {{ $jadwal->kelas_id == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Hari --}}
        <div>
            <label for="hari_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Hari</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-calendar text-dark-400 text-lg'></i>
                </div>
                <select name="hari" id="hari_edit"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all appearance-none"
                    required>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                        <option value="{{ $h }}" {{ $jadwal->hari == $h ? 'selected' : '' }}>
                            {{ $h }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Jam Mulai --}}
        <div>
            <label for="jam_mulai_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Jam Mulai</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-time text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="jam_mulai" id="jam_mulai_edit" value="{{ $jadwal->jam_mulai }}"
                    class="timepicker block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500"
                    placeholder="00:00" required>
            </div>
        </div>

        {{-- Jam Selesai --}}
        <div>
            <label for="jam_selesai_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Jam Selesai</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-time-five text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="jam_selesai" id="jam_selesai_edit" value="{{ $jadwal->jam_selesai }}"
                    class="timepicker block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500"
                    placeholder="00:00" required>
            </div>
        </div>
    </div>

    {{-- Footer Buttons --}}
    <div class="mt-8 flex items-center justify-end gap-3">
        <button type="button" onclick="closeModal('modal-editjadwal')"
            class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 font-medium transition-colors duration-200">
            Batal
        </button>
        <button type="submit"
            class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-medium rounded-xl shadow-lg transition-all duration-200 flex items-center gap-2">
            <i class='bx bx-save text-lg'></i>
            Perbarui Jadwal
        </button>
    </div>
</form>

<script>
    // Fungsi untuk update otomatis mata pelajaran saat guru dipilih (di Modal Edit)
    function updateMapelEdit() {
        const selectGuru = document.getElementById('guru_id_edit');
        const inputMapel = document.getElementById('mata_pelajaran_edit');
        const selectedOption = selectGuru.options[selectGuru.selectedIndex];
        const mapel = selectedOption.getAttribute('data-mapel');
        inputMapel.value = mapel || '';
    }

    // Inisialisasi Flatpickr kembali karena konten ini dimuat via AJAX
    flatpickr(".timepicker", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true
    });
</script>
