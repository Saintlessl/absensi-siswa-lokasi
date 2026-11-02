<form action="{{ route('siswa.update', ['id' => $siswa->id]) }}" method="POST" id="frmEditSiswa">
    @csrf

    {{-- Section A: Identitas Siswa --}}
    <div class="mb-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">1</div>
            <h4 class="text-sm font-semibold text-dark-800">Identitas Siswa</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nisn_edit" class="block text-xs font-medium text-dark-700 mb-1.5">NISN <span class="text-dark-400 font-normal">(10 digit)</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bx-id-card text-dark-400 text-lg'></i>
                    </div>
                    <input type="text" name="nisn" id="nisn_edit" value="{{ $siswa->nisn }}" maxlength="20"
                        class="block w-full pl-10 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all font-mono"
                        placeholder="Contoh: 0051234567" autocomplete="off">
                </div>
            </div>
            <div>
                <label for="nis_edit" class="block text-xs font-medium text-dark-700 mb-1.5">NIS <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bx-badge-check text-dark-400 text-lg'></i>
                    </div>
                    <input type="text" name="nis" id="nis_edit" value="{{ $siswa->nis }}" maxlength="20"
                        class="block w-full pl-10 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all font-mono"
                        placeholder="NIS" required autocomplete="off">
                </div>
            </div>
            <div class="md:col-span-2">
                <label for="nama_edit" class="block text-xs font-medium text-dark-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bx-user text-dark-400 text-lg'></i>
                    </div>
                    <input type="text" name="nama" id="nama_edit" value="{{ $siswa->nama }}"
                        class="block w-full pl-10 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                        placeholder="Nama Lengkap" required autocomplete="off">
                </div>
            </div>

            <div>
                <label for="jenis_kelamin_edit" class="block text-xs font-medium text-dark-700 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                <select name="jenis_kelamin" id="jenis_kelamin_edit" required
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="L" {{ $siswa->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ $siswa->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div>
                <label for="jurusan_edit" class="block text-xs font-medium text-dark-700 mb-1.5">Jurusan</label>
                <select id="jurusan_edit"
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="">-- Semua Jurusan --</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ ($siswa->kelas->jurusan_id ?? '') == $j->id ? 'selected' : '' }}>
                            {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="kelas_id_edit" class="block text-xs font-medium text-dark-700 mb-1.5">Kelas <span class="text-rose-500">*</span></label>
                <select name="kelas_id" id="kelas_id_edit" required
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    @foreach($kelas as $k)
                    <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}" {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kelas }} ({{ $k->jurusan->kode_jurusan ?? '-' }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Section B: Informasi Kontak --}}
    <div class="mb-5 pt-4 border-t border-dark-100">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">2</div>
            <h4 class="text-sm font-semibold text-dark-800">Informasi Kontak & Notifikasi</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="no_telp_siswa_edit" class="block text-xs font-medium text-dark-700 mb-1.5">Nomor Telepon Siswa <span class="text-dark-400 font-normal">(Opsional)</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bx-phone text-dark-400 text-lg'></i>
                    </div>
                    <input type="tel" name="no_telp_siswa" id="no_telp_siswa_edit" value="{{ $siswa->no_telp_siswa }}"
                        class="block w-full pl-10 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all font-mono"
                        placeholder="08123456789" autocomplete="off">
                </div>
            </div>
            <div>
                <label for="no_telp_orangtua_edit" class="block text-xs font-medium text-dark-700 mb-1.5">
                    No. Telepon / WA Orang Tua <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bxl-whatsapp text-emerald-600 text-lg'></i>
                    </div>
                    <input type="tel" name="no_telp_orangtua" id="no_telp_orangtua_edit" value="{{ $siswa->no_telp_orangtua }}" required
                        class="block w-full pl-10 pr-3 py-2 border border-emerald-300 rounded-xl bg-emerald-50/20 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all font-mono"
                        placeholder="081234567890" autocomplete="off">
                </div>
                <p class="text-[11px] text-emerald-700 mt-1 flex items-center gap-1">
                    <i class='bx bx-check-circle text-xs'></i>
                    Digunakan untuk notifikasi WhatsApp presensi otomatis.
                </p>
            </div>
        </div>
    </div>

    {{-- Section C: Akun Siswa --}}
    <div class="mb-5 pt-4 border-t border-dark-100">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">3</div>
            <h4 class="text-sm font-semibold text-dark-800">Akun Login Siswa (@myskanic.com)</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="username_edit" class="block text-xs font-medium text-dark-700 mb-1.5">Username Siswa <span class="text-rose-500">*</span></label>
                <div class="flex rounded-xl overflow-hidden border border-dark-200 focus-within:ring-2 focus-within:ring-primary-500/30 focus-within:border-primary-500 transition-all">
                    <span class="inline-flex items-center px-3 bg-dark-50 text-dark-500 text-sm border-r border-dark-200">
                        <i class='bx bx-user text-base'></i>
                    </span>
                    @php
                        $userEmail = $siswa->user->email ?? '';
                        $currentUsername = str_contains($userEmail, '@') ? explode('@', $userEmail)[0] : $userEmail;
                    @endphp
                    <input type="text" name="username" id="username_edit" value="{{ $currentUsername }}"
                        class="block w-full px-3 py-2 bg-white text-dark-800 text-sm focus:outline-none font-mono lowercase"
                        placeholder="username.siswa" required autocomplete="off">
                    <span class="inline-flex items-center px-3 bg-primary-50 text-primary-700 font-semibold text-xs border-l border-primary-100 select-none">
                        @myskanic.com
                    </span>
                </div>
            </div>

            <div>
                <label for="password_edit" class="block text-xs font-medium text-dark-700 mb-1.5">
                    Password <span class="text-xs text-dark-400 font-normal">(Kosongkan jika tidak diganti)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bx-lock-alt text-dark-400 text-lg'></i>
                    </div>
                    <input type="password" name="password" id="password_edit" minlength="6"
                        class="block w-full pl-10 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                        placeholder="Minimal 6 karakter" autocomplete="off">
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-6 pt-4 border-t border-dark-100 flex items-center justify-end gap-3">
        <button type="button" onclick="closeModal('modal-editsiswa')"
            class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 hover:text-dark-800 text-sm font-medium transition-colors duration-200">
            Batal
        </button>
        <button type="submit"
            class="px-5 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center gap-2">
            <i class='bx bx-save text-lg'></i>
            Perbarui Data
        </button>
    </div>
</form>

<script>
    (function() {
        const jurusanSelect = document.getElementById('jurusan_edit');
        const kelasSelect = document.getElementById('kelas_id_edit');
        if (jurusanSelect && kelasSelect) {
            jurusanSelect.addEventListener('change', function() {
                const selectedJurusan = this.value;
                Array.from(kelasSelect.options).forEach((opt) => {
                    const optJurusan = opt.getAttribute('data-jurusan');
                    if (!selectedJurusan || optJurusan === selectedJurusan) {
                        opt.hidden = false;
                        opt.disabled = false;
                    } else {
                        opt.hidden = true;
                        opt.disabled = true;
                    }
                });
                if (kelasSelect.selectedOptions[0] && kelasSelect.selectedOptions[0].disabled) {
                    const firstVisible = Array.from(kelasSelect.options).find(o => !o.disabled);
                    if (firstVisible) {
                        kelasSelect.value = firstVisible.value;
                    }
                }
            });
        }
    })();
</script>
