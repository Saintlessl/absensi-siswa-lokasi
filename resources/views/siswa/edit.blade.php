<form action="{{ route('siswa.update', ['id' => $siswa->id]) }}" method="POST" id="frmEditSiswa" enctype="multipart/form-data">
    @csrf

    {{-- Section A: Identitas Siswa --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-6 h-6 rounded-full bg-[#003FA9] text-white flex items-center justify-center text-xs font-bold">1</span>
            <h4 class="text-base font-bold text-[#0F172A]">Identitas Siswa</h4>
        </div>

        {{-- Foto Profil Siswa --}}
        <div class="mb-5 p-4 rounded-2xl bg-white/70 border border-blue-100 flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-[#E2EDFF] border border-blue-200 flex items-center justify-center overflow-hidden flex-shrink-0" id="preview_edit_photo_container">
                @if ($siswa->photo_url)
                    <img src="{{ $siswa->photo_url }}" alt="{{ $siswa->nama }}" class="w-full h-full object-cover">
                @else
                    <span class="text-xl font-bold text-[#003FA9]">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
                @endif
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-[#1E293B] mb-1">Ganti Pasfoto Siswa (Opsional)</label>
                <input type="file" name="photo" id="photo_edit" accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#003FA9] file:text-white hover:file:bg-[#00338d] cursor-pointer"
                    onchange="previewEditStudentPhoto(this)">
                <p class="text-[11px] text-[#64748B] mt-1">Format: JPG, PNG, WebP. Maksimal 2MB.</p>
                @if ($siswa->photo_path)
                    <label class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold text-rose-600 cursor-pointer">
                        <input type="checkbox" name="remove_photo" value="1" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                        Hapus foto profil saat ini
                    </label>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nisn_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    NISN <span class="text-slate-400 font-normal text-xs">(10 digit)</span>
                </label>
                <input type="text" name="nisn" id="nisn_edit" value="{{ $siswa->nisn }}" maxlength="20"
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Contoh: 0051234567" autocomplete="off">
            </div>

            <div>
                <label for="nis_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    NIS <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nis" id="nis_edit" value="{{ $siswa->nis }}" maxlength="20"
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="NIS" required autocomplete="off">
            </div>

            <div class="md:col-span-2">
                <label for="nama_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama" id="nama_edit" value="{{ $siswa->nama }}"
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Nama Lengkap Siswa" required autocomplete="off">
            </div>

            <div>
                <label for="jenis_kelamin_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="jenis_kelamin" id="jenis_kelamin_edit" required
                        class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                        <option value="L" {{ $siswa->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ $siswa->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-[#003FA9]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="jurusan_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Jurusan (Program Keahlian) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select id="jurusan_edit" onchange="filterEditRombel()" required
                        class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}" {{ ($siswa->kelas->jurusan_id ?? '') == $j->id ? 'selected' : '' }}>
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
                    <select id="tingkat_edit" onchange="filterEditRombel()" required
                        class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="10" {{ ($siswa->kelas->tingkat ?? 10) == 10 ? 'selected' : '' }}>Kelas 10</option>
                        <option value="11" {{ ($siswa->kelas->tingkat ?? 0) == 11 ? 'selected' : '' }}>Kelas 11</option>
                        <option value="12" {{ ($siswa->kelas->tingkat ?? 0) == 12 ? 'selected' : '' }}>Kelas 12</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-[#003FA9]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="kelas_id_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Rombongan Belajar (Rombel) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="kelas_id" id="kelas_id_edit" required
                        class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}" data-tingkat="{{ $k->tingkat }}" {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} (Wali: {{ $k->wali_kelas }})
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-[#003FA9]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section B: Informasi Orang Tua/Wali --}}
    <div class="mb-6 pt-5 border-t border-[#D8E6FC]">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-6 h-6 rounded-full bg-[#003FA9] text-white flex items-center justify-center text-xs font-bold">2</span>
            <h4 class="text-base font-bold text-[#0F172A]">Informasi Orang Tua / Wali</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php($parent = $siswa->orangtua->first())
            <div>
                <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Nama Lengkap Orang Tua / Wali <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="parent_name" value="{{ old('parent_name', $parent?->nama) }}" required
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="parent_jenis_kelamin" required
                        class="w-full h-11 px-4 pr-10 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L" {{ old('parent_jenis_kelamin', $parent?->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('parent_jenis_kelamin', $parent?->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-[#003FA9]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Nomor WhatsApp <span class="text-rose-500">*</span>
                </label>
                <input type="tel" name="parent_phone" value="{{ old('parent_phone', $parent?->telepon) }}" required
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Alamat Lengkap <span class="text-rose-500">*</span>
                </label>
                <textarea name="parent_address" rows="3" required
                    class="w-full p-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition">{{ old('parent_address', $parent?->alamat) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Section C: Akun Siswa --}}
    <div class="mb-6 pt-5 border-t border-[#D8E6FC]">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-6 h-6 rounded-full bg-[#003FA9] text-white flex items-center justify-center text-xs font-bold">3</span>
            <h4 class="text-base font-bold text-[#0F172A]">Akun Login Siswa (@myskanic.com)</h4>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="username_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Username / Email Siswa <span class="text-rose-500">*</span>
                </label>
                @php
                    $userEmail = $siswa->user->email ?? '';
                    $currentUsername = str_contains($userEmail, '@') ? explode('@', $userEmail)[0] : $userEmail;
                @endphp
                <div class="flex rounded-xl overflow-hidden bg-[#E2EDFF]">
                    <input type="text" name="username" id="username_edit" value="{{ $currentUsername }}"
                        class="w-full h-11 px-4 bg-transparent border-none text-slate-800 text-sm font-mono focus:outline-none"
                        placeholder="username.siswa" required autocomplete="off">
                    <span class="inline-flex items-center px-3.5 bg-blue-200/50 text-[#003FA9] font-bold text-xs select-none">
                        @myskanic.com
                    </span>
                </div>
            </div>

            <div>
                <label for="password_edit" class="block text-sm font-semibold text-[#1E293B] mb-1.5">
                    Ganti Password <span class="text-xs text-slate-400 font-normal">(Kosongkan jika tetap)</span>
                </label>
                <input type="password" name="password" id="password_edit" minlength="6"
                    class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Minimal 6 karakter" autocomplete="off">
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-8 pt-4 border-t border-[#D8E6FC] flex items-center justify-between">
        <button type="button" onclick="closeModal('modal-editsiswa')"
            class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] text-[#003FA9] font-semibold text-sm hover:bg-[#d4e4fc] transition-colors focus:outline-none">
            Batal
        </button>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] text-white font-semibold text-sm hover:bg-[#00338d] shadow-md shadow-blue-500/20 transition-all focus:outline-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            Perbarui Data Siswa
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
