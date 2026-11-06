<form action="{{ route('user.update', ['id' => $user->id]) }}" method="POST" id="frmEditUser" class="space-y-4">
    @csrf
    <div class="space-y-4">
        <div>
            <label for="name_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Pengguna <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-user text-slate-400 text-lg'></i>
                </div>
                <input type="text" name="name" id="name_edit" value="{{ $user->name }}"
                    class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Nama lengkap" required autocomplete="off">
            </div>
        </div>
        
        <div>
            <label for="email_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Email Akses <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-envelope text-slate-400 text-lg'></i>
                </div>
                <input type="email" name="email" id="email_edit" value="{{ $user->email }}"
                    class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="email@sekolah.com" required autocomplete="off">
            </div>
        </div>

        <div>
            <label for="jenis_kelamin_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
            <div class="relative">
                <select name="jenis_kelamin" id="jenis_kelamin_edit" required
                    class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                    <option value="L" {{ old('jenis_kelamin', $user->effective_gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin', $user->effective_gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
        </div>

        <div>
            <label for="role_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Role Akses <span class="text-rose-500">*</span></label>
            <div class="relative">
                <select name="role" id="role_edit" required onchange="toggleEditJurusanField(this.value)"
                    class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                    <option value="superadmin" {{ $user->role == 'superadmin' ? 'selected' : '' }}>Super Administrator</option>
                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrator / Staf</option>
                    <option value="kaprog" {{ $user->role == 'kaprog' ? 'selected' : '' }}>Kepala Program Keahlian (KAPROG)</option>
                    <option value="guru" {{ $user->role == 'guru' ? 'selected' : '' }}>Guru</option>
                    <option value="siswa" {{ $user->role == 'siswa' ? 'selected' : '' }}>Siswa</option>
                    <option value="orangtua" {{ $user->role == 'orangtua' ? 'selected' : '' }}>Orang Tua</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
        </div>

        <div id="wrapper_edit_jurusan" class="{{ $user->role == 'kaprog' ? '' : 'hidden' }}">
            <label for="jurusan_id_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Jurusan Binaan (KAPROG) <span class="text-rose-500">*</span></label>
            <div class="relative">
                <select name="jurusan_id" id="jurusan_id_edit"
                    class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                    <option value="">-- Pilih Jurusan --</option>
                    @foreach ($jurusans as $j)
                        <option value="{{ $j->id }}" {{ $user->jurusan_id == $j->id ? 'selected' : '' }}>
                            {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
        </div>

        <div>
            <label for="password_edit" class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-xs font-normal text-slate-500">(Kosongkan jika tidak diubah)</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class='bx bx-lock-alt text-slate-400 text-lg'></i>
                </div>
                <input type="password" name="password" id="password_edit"
                    class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                    placeholder="Minimal 6 karakter" autocomplete="off">
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#D8E6FC]">
        <button type="button" onclick="closeModal('modal-edituser')"
            class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] hover:bg-[#d4e4fc] text-[#003FA9] font-bold text-sm transition">
            Batal
        </button>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#00338d] text-white font-bold text-sm shadow-md shadow-blue-500/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Perbarui Data
        </button>
    </div>
</form>