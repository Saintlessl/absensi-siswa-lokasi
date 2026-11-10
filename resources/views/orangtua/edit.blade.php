<form action="{{ route('orangtua.update', ['id' => $orang_tua->id]) }}" method="POST" id="frmEditOrangTua" class="space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2 pb-1 border-b border-slate-100">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Informasi Pribadi & Kontak
            </h4>
        </div>

        {{-- Nama Lengkap --}}
        <div>
            <label for="nama_edit" class="block text-xs font-bold text-slate-700 mb-1.5">
                Nama Lengkap <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-user text-slate-400 text-lg'></i>
                </div>
                <input type="text" name="nama" id="nama_edit" value="{{ $orang_tua->nama }}"
                    class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Nama Orang Tua" required autocomplete="off">
            </div>
        </div>

        {{-- Jenis Kelamin --}}
        <div>
            <label for="jenis_kelamin_edit" class="block text-xs font-bold text-slate-700 mb-1.5">
                Jenis Kelamin <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <select name="jenis_kelamin" id="jenis_kelamin_edit" required
                    class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="L" {{ old('jenis_kelamin', $orang_tua->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin', $orang_tua->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
        </div>

        {{-- Nomor Telepon --}}
        <div>
            <label for="telepon_edit" class="block text-xs font-bold text-slate-700 mb-1.5">
                Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-phone text-slate-400 text-lg'></i>
                </div>
                <input type="text" name="telepon" id="telepon_edit" value="{{ $orang_tua->telepon }}"
                    class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="08xxxxxxxxxx" required autocomplete="off">
            </div>
        </div>

        {{-- Siswa Terkait (Read Only) --}}
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Siswa Terkait</label>
            <div class="h-11 px-4 flex items-center rounded-xl bg-[#EFF5FF] text-slate-800 text-xs font-semibold">
                {{ $orang_tua->siswa()->pluck('nama')->join(', ') ?: 'Belum terhubung' }}
            </div>
        </div>

        {{-- Alamat Lengkap --}}
        <div class="md:col-span-2">
            <label for="alamat_edit" class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap</label>
            <textarea name="alamat" id="alamat_edit" rows="2"
                class="block w-full p-3.5 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition resize-none"
                placeholder="Alamat Lengkap">{{ $orang_tua->alamat }}</textarea>
        </div>

        {{-- Section Divider: Akun Akses Portal --}}
        <div class="md:col-span-2 pt-3 pb-1 border-b border-slate-100">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Akun Akses Portal Orang Tua
            </h4>
        </div>

        {{-- Email Akun --}}
        <div>
            <label for="email_edit" class="block text-xs font-bold text-slate-700 mb-1.5">
                Email Akun <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-envelope text-slate-400 text-lg'></i>
                </div>
                <input type="email" name="email" id="email_edit" value="{{ $orang_tua->user->email ?? '' }}"
                    class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="email@example.com" required autocomplete="off">
            </div>
        </div>

        {{-- Password Baru --}}
        <div>
            <label for="password_edit" class="block text-xs font-bold text-slate-700 mb-1.5">
                Password Baru <span class="text-[11px] font-normal text-slate-400">(Kosongkan jika tak diubah)</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-lock-alt text-slate-400 text-lg'></i>
                </div>
                <input type="password" name="password" id="password_edit"
                    class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Minimal 6 karakter" autocomplete="off">
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="pt-5 flex items-center justify-end gap-3 border-t border-slate-100">
        <button type="button" onclick="closeModal('modal-editorangtua')"
            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm transition">
            Batal
        </button>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs sm:text-sm shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Perbarui Data
        </button>
    </div>
</form>
