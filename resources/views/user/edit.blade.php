<form action="{{ route('user.update', ['id' => $user->id]) }}" method="POST" id="frmEditUser">
    @csrf
    <div class="space-y-4">
        <div>
            <label for="name_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Nama Pengguna</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-user text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="name" id="name_edit" value="{{ $user->name }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                    placeholder="Nama lengkap" required autocomplete="off">
            </div>
        </div>
        
        <div>
            <label for="email_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Email Akses</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-envelope text-dark-400 text-lg'></i>
                </div>
                <input type="email" name="email" id="email_edit" value="{{ $user->email }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                    placeholder="email@sekolah.com" required autocomplete="off">
            </div>
        </div>

        <div>
            <label for="role_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Role Akses</label>
            <select name="role" id="role_edit" required
                class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrator</option>
                <option value="guru" {{ $user->role == 'guru' ? 'selected' : '' }}>Guru</option>
                <option value="siswa" {{ $user->role == 'siswa' ? 'selected' : '' }}>Siswa</option>
                <option value="orangtua" {{ $user->role == 'orangtua' ? 'selected' : '' }}>Orang Tua</option>
            </select>
        </div>

        <div>
            <label for="password_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Password <span class="text-xs text-dark-400">(Kosongkan jika tidak diubah)</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-lock-alt text-dark-400 text-lg'></i>
                </div>
                <input type="password" name="password" id="password_edit"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                    placeholder="Minimal 6 karakter" autocomplete="off">
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-8 flex items-center justify-end gap-3">
        <button type="button" onclick="closeModal('modal-edituser')"
            class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 hover:text-dark-800 font-medium transition-colors duration-200">
            Batal
        </button>
        <button type="submit"
            class="px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center gap-2">
            <i class='bx bx-save text-lg'></i>
            Perbarui Data
        </button>
    </div>
</form>
