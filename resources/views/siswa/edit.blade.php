<form action="{{ route('siswa.update', ['id' => $siswa->id]) }}" method="POST" id="frmEditSiswa" enctype="multipart/form-data">
    @csrf

    {{-- Section A: Identitas Siswa --}}
    <div class="mb-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">1</div>
            <h4 class="text-sm font-semibold text-dark-800">Identitas Siswa</h4>
        </div>

        {{-- Foto Profil Siswa --}}
        <div class="mb-4 p-3 rounded-xl bg-dark-50/50 border border-dark-100">
            <label class="block text-xs font-medium text-dark-700 mb-1.5">Foto Profil Siswa (Opsional)</label>
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl border border-dark-200 bg-white flex items-center justify-center overflow-hidden flex-shrink-0" id="preview_edit_photo_container">
                    @if ($siswa->photo_url)
                        <img src="{{ $siswa->photo_url }}" alt="{{ $siswa->nama }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-base font-bold text-dark-400">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="flex-1">
                    <input type="file" name="photo" id="photo_edit" accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-xs text-dark-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer"
                        onchange="previewEditStudentPhoto(this)">
                    <p class="text-[11px] text-dark-400 mt-1">Format: JPG, PNG, WebP. Maks 2MB.</p>
                    @if ($siswa->photo_path)
                        <label class="inline-flex items-center gap-1.5 mt-1.5 text-xs text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_photo" value="1" class="rounded border-dark-300 text-rose-600 focus:ring-rose-500">
                            Hapus foto profil saat ini
                        </label>
                    @endif
                </div>
            </div>
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
                <label for="jurusan_edit" class="block text-xs font-medium text-dark-700 mb-1.5">Jurusan (Program Keahlian) <span class="text-rose-500">*</span></label>
                <select id="jurusan_edit" onchange="filterEditRombel()" required
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="">-- Pilih Jurusan --</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ ($siswa->kelas->jurusan_id ?? '') == $j->id ? 'selected' : '' }}>
                            {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tingkat_edit" class="block text-xs font-medium text-dark-700 mb-1.5">Tingkat Kelas <span class="text-rose-500">*</span></label>
                <select id="tingkat_edit" onchange="filterEditRombel()" required
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="">-- Pilih Tingkat --</option>
                    <option value="10" {{ ($siswa->kelas->tingkat ?? 10) == 10 ? 'selected' : '' }}>Kelas 10</option>
                    <option value="11" {{ ($siswa->kelas->tingkat ?? 0) == 11 ? 'selected' : '' }}>Kelas 11</option>
                    <option value="12" {{ ($siswa->kelas->tingkat ?? 0) == 12 ? 'selected' : '' }}>Kelas 12</option>
                </select>
            </div>

            <div>
                <label for="kelas_id_edit" class="block text-xs font-medium text-dark-700 mb-1.5">Rombongan Belajar (Rombel) <span class="text-rose-500">*</span></label>
                <select name="kelas_id" id="kelas_id_edit" required
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    @foreach($kelas as $k)
                    <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}" data-tingkat="{{ $k->tingkat }}" {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kelas }} (Wali: {{ $k->wali_kelas }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Section B: Informasi Orang Tua/Wali --}}
    <div class="mb-5 pt-4 border-t border-dark-100">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">2</div>
            <h4 class="text-sm font-semibold text-dark-800">Informasi Orang Tua / Wali</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php($parent = $siswa->orangtua->first())
            <div>
                <label class="block text-xs font-medium text-dark-700 mb-1.5">Nama Lengkap Orang Tua / Wali <span class="text-rose-500">*</span></label>
                <input type="text" name="parent_name" value="{{ old('parent_name', $parent?->nama) }}" required class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-dark-700 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                <select name="parent_jenis_kelamin" required class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm">
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="L" {{ old('parent_jenis_kelamin', $parent?->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('parent_jenis_kelamin', $parent?->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-dark-700 mb-1.5">Nomor WhatsApp <span class="text-rose-500">*</span></label>
                <input type="tel" name="parent_phone" value="{{ old('parent_phone', $parent?->telepon) }}" required class="block w-full px-3 py-2 border border-emerald-300 rounded-xl bg-emerald-50/20 text-sm font-mono">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-dark-700 mb-1.5">Alamat Lengkap <span class="text-rose-500">*</span></label>
                <textarea name="parent_address" rows="3" required class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm">{{ old('parent_address', $parent?->alamat) }}</textarea>
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
    function previewEditStudentPhoto(input) {
        const container = document.getElementById('preview_edit_photo_container');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function filterEditRombel() {
        const jurusanId = document.getElementById('jurusan_edit').value;
        const tingkat = document.getElementById('tingkat_edit').value;
        const kelasSelect = document.getElementById('kelas_id_edit');

        Array.from(kelasSelect.options).forEach((opt) => {
            const optJurusan = opt.getAttribute('data-jurusan');
            const optTingkat = opt.getAttribute('data-tingkat');

            const matchJurusan = !jurusanId || optJurusan === jurusanId;
            const matchTingkat = !tingkat || optTingkat === tingkat;

            if (matchJurusan && matchTingkat) {
                opt.hidden = false;
                opt.disabled = false;
            } else {
                opt.hidden = true;
                opt.disabled = true;
            }
        });

        if (kelasSelect.selectedOptions[0] && kelasSelect.selectedOptions[0].disabled) {
            const firstAvailable = Array.from(kelasSelect.options).find(o => !o.disabled);
            if (firstAvailable) {
                kelasSelect.value = firstAvailable.value;
            }
        }
    }
</script>
