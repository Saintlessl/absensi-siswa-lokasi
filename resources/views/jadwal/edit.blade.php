<form action="{{ route('jadwal.update', ['id' => $jadwal->id]) }}" method="POST" id="frmEditJadwal" class="space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Mata Pelajaran (Read Only / Auto dari Guru) --}}
        <div class="md:col-span-2">
            <label for="mata_pelajaran_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Mata Pelajaran</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-book-open text-slate-400 text-lg'></i>
                </div>
                <input type="text" name="mata_pelajaran" id="mata_pelajaran_edit"
                    value="{{ $jadwal->mata_pelajaran }}"
                    class="block w-full pl-10 pr-4 h-11 rounded-xl bg-blue-100/50 border-none text-slate-600 text-sm focus:outline-none cursor-not-allowed font-medium"
                    readonly>
            </div>
        </div>

        {{-- Guru --}}
        <div class="md:col-span-2">
            <label for="guru_id_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Guru Pengajar <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-user text-slate-400 text-lg'></i>
                </div>
                <select name="guru_id" id="guru_id_edit" onchange="updateMapelEdit()"
                    class="block w-full pl-10 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer"
                    required>
                    <option value="">Pilih Guru</option>
                    @foreach ($guru as $g)
                        <option value="{{ $g->id }}" data-mapel="{{ $g->mapel }}"
                            {{ $jadwal->guru_id == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
        </div>

        {{-- Kelas --}}
        <div>
            <label for="kelas_id_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Kelas / Rombel <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-home-alt text-slate-400 text-lg'></i>
                </div>
                <select name="kelas_id" id="kelas_id_edit"
                    class="block w-full pl-10 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer"
                    required>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" {{ $jadwal->kelas_id == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
        </div>

        {{-- Hari --}}
        <div>
            <label for="hari_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Hari <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-calendar text-slate-400 text-lg'></i>
                </div>
                <select name="hari" id="hari_edit"
                    class="block w-full pl-10 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer"
                    required>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                        <option value="{{ $h }}" {{ $jadwal->hari == $h ? 'selected' : '' }}>
                            {{ $h }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
        </div>

        {{-- Jam Mulai --}}
        <div>
            <label for="jam_mulai_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Jam Mulai <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-time text-slate-400 text-lg'></i>
                </div>
                <input type="text" name="jam_mulai" id="jam_mulai_edit" value="{{ $jadwal->jam_mulai }}"
                    class="timepicker block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#003FA9]"
                    placeholder="00:00" required>
            </div>
        </div>

        {{-- Jam Selesai --}}
        <div>
            <label for="jam_selesai_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Jam Selesai <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-time-five text-slate-400 text-lg'></i>
                </div>
                <input type="text" name="jam_selesai" id="jam_selesai_edit" value="{{ $jadwal->jam_selesai }}"
                    class="timepicker block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#003FA9]"
                    placeholder="00:00" required>
            </div>
        </div>
    </div>

    {{-- Footer Buttons --}}
    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#D8E6FC]">
        <button type="button" onclick="closeModal('modal-editjadwal')"
            class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] hover:bg-[#d4e4fc] text-[#003FA9] font-bold text-sm transition">
            Batal
        </button>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#00338d] text-white font-bold text-sm shadow-md shadow-blue-500/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
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