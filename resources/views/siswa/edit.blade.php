<form action="{{ route('siswa.update', ['id' => $siswa->id]) }}" method="POST" id="frmEditSiswa">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="nis_edit" class="block text-sm font-medium text-dark-700 mb-1.5">NIS</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-id-card text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="nis" id="nis_edit" value="{{ $siswa->nis }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                    placeholder="NIS" required autocomplete="off">
            </div>
        </div>
        <div>
            <label for="nama_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Nama Lengkap</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-user text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="nama" id="nama_edit" value="{{ $siswa->nama }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                    placeholder="Nama Lengkap" required autocomplete="off">
            </div>
        </div>

        <div>
            <label for="jenis_kelamin_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Jenis Kelamin</label>
            <select name="jenis_kelamin" id="jenis_kelamin_edit" required
                class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                <option value="L" {{ $siswa->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $siswa->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>

        <div>
            <label for="kelas_id_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Kelas</label>
            <select name="kelas_id" id="kelas_id_edit" required
                class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                @foreach($kelas as $k)
                <option value="{{ $k->id }}" {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2 mt-2">
            <hr class="border-dark-200 mb-4">
            <h4 class="text-sm font-semibold text-dark-600 mb-4">Akun Login Siswa</h4>
        </div>

        <div>
            <label for="email_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-envelope text-dark-400 text-lg'></i>
                </div>
                <input type="email" name="email" id="email_edit" value="{{ $siswa->user->email ?? '' }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                    placeholder="Email" required autocomplete="off">
            </div>
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
        <button type="button" onclick="closeModal('modal-editsiswa')"
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