<form action="{{ route('kelas.update', ['id' => $kelas->id]) }}" method="POST" id="frmEditKelas">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="nama_kelas_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Nama Kelas</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-home-alt text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="nama_kelas" id="nama_kelas_edit" value="{{ $kelas->nama_kelas }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500"
                    placeholder="Nama Kelas" required>
            </div>
        </div>
        <div>
            <label for="jumlah_siswa_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Jumlah Siswa</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-group text-dark-400 text-lg'></i>
                </div>
                <input type="number" name="jumlah_siswa" id="jumlah_siswa_edit" value="{{ $kelas->jumlah_siswa }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500"
                    placeholder="Jumlah Siswa" required>
            </div>
        </div>
        <div class="md:col-span-2">
            <label for="wali_kelas_edit" class="block text-sm font-medium text-dark-700 mb-1.5">Wali Kelas</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-user-voice text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="wali_kelas" id="wali_kelas_edit" value="{{ $kelas->wali_kelas }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500"
                    placeholder="Nama Wali Kelas" required>
            </div>
        </div>
    </div>

    <div class="mt-8 flex items-center justify-end gap-3">
        <button type="button" onclick="closeModal('modal-editkelas')"
            class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 font-medium transition-colors duration-200">
            Batal
        </button>
        <button type="submit"
            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-medium rounded-xl shadow-lg transition-all duration-200 flex items-center gap-2">
            <i class='bx bx-save text-lg'></i>
            Perbarui Data
        </button>
    </div>
</form>
