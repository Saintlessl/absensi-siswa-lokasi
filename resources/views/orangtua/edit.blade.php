<form action="{{ route('orangtua.update', ['id' => $orang_tua->id]) }}" method="POST" id="frmEditOrangTua">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <h4 class="text-sm font-semibold text-dark-600 mb-2 border-b border-dark-200 pb-2">Informasi Pribadi & Kontak
            </h4>
        </div>

        <div>
            <label for="nama_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Nama Lengkap</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-user text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="nama" id="nama_edit" value="{{ $orang_tua->nama }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                    placeholder="Nama Orang Tua" required autocomplete="off">
            </div>
        </div>

        <div>
            <label for="telepon_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Nomor Telepon</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-phone text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="telepon" id="telepon_edit" value="{{ $orang_tua->telepon }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                    placeholder="08xxxxxxxxxx" required autocomplete="off">
            </div>
        </div>

        <div class="md:col-span-2">
            <label for="alamat_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Alamat</label>
            <textarea name="alamat" id="alamat_edit" rows="2"
                class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                placeholder="Alamat Lengkap">{{ $orang_tua->alamat }}</textarea>
        </div>

        <div class="md:col-span-2">
            <label for="siswa_id_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Siswa Terkait</label>
            <select name="siswa_id" id="siswa_id_edit" required
                class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                @foreach ($siswa as $s)
                    <option value="{{ $s->id }}" {{ $orang_tua->siswa_id == $s->id ? 'selected' : '' }}>
                        {{ $s->nama }} - {{ $s->kelas->nama_kelas }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2 mt-4">
            <h4 class="text-sm font-semibold text-dark-600 mb-2 border-b border-dark-200 pb-2">Akun Login Orang Tua</h4>
        </div>

        <div>
            <label for="email_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-envelope text-dark-400 text-lg'></i>
                </div>
                <input type="email" name="email" id="email_edit" value="{{ $orang_tua->user->email ?? '' }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                    placeholder="email@example.com" required autocomplete="off">
            </div>
        </div>

        <div>
            <label for="password_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Password <span
                    class="text-xs text-dark-400">(Kosongkan jika tak diubah)</span></label>
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
        <button type="button" onclick="closeModal('modal-editorangtua')"
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
