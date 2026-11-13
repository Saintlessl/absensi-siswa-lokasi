<form action="{{ route('angkatan.update', $angkatan->id) }}" method="POST" class="space-y-4">
    @csrf

    @if ($angkatan->jurusan)
        <div class="p-3 bg-blue-50 border border-slate-100 rounded-xl flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-dark-500 uppercase">Jurusan / Program Keahlian</span>
                <p class="text-sm font-bold text-[#003EA7]">{{ $angkatan->jurusan->kode_jurusan }} - {{ $angkatan->jurusan->nama_jurusan }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-[#003EA9] text-xs font-bold">{{ $angkatan->jurusan->kode_jurusan }}</span>
        </div>
        <input type="hidden" name="jurusan_id" value="{{ $angkatan->jurusan_id }}">
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-dark-700 mb-1">Nomor Angkatan <span class="text-rose-500">*</span></label>
            <input type="number" name="nomor_angkatan" value="{{ $angkatan->nomor_angkatan }}" required min="1" max="999"
                class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Masuk <span class="text-rose-500">*</span></label>
            <input type="number" name="tahun_masuk" value="{{ $angkatan->tahun_masuk }}" required min="2000" max="2099"
                class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-dark-700 mb-1">Nama Angkatan</label>
            <input type="text" name="nama_angkatan" value="{{ $angkatan->nama_angkatan }}"
                class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-dark-700 mb-1">Kode Angkatan</label>
            <input type="text" name="kode_angkatan" value="{{ $angkatan->kode_angkatan }}"
                class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl uppercase focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Ajaran Masuk</label>
            <input type="text" name="tahun_ajaran_masuk" value="{{ $angkatan->tahun_ajaran_masuk }}"
                class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-dark-700 mb-1">Rencana Tahun Lulus</label>
            <input type="number" name="tahun_lulus_rencana" value="{{ $angkatan->tahun_lulus_rencana }}"
                class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-dark-700 mb-1">Status Siklus</label>
            <select name="status" class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                <option value="aktif" {{ $angkatan->status === 'aktif' ? 'selected' : '' }}>Aktif (Sedang Berjalan)</option>
                <option value="proses_kelulusan" {{ $angkatan->status === 'proses_kelulusan' ? 'selected' : '' }}>Dalam Proses Kelulusan</option>
                <option value="selesai" {{ $angkatan->status === 'selesai' ? 'selected' : '' }}>Selesai (Alumni / Lulus)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-dark-700 mb-1">Tanggal Kelulusan (Jika Selesai)</label>
            <input type="date" name="tanggal_kelulusan" value="{{ optional($angkatan->tanggal_kelulusan)->format('Y-m-d') }}"
                class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Ajaran Kelulusan</label>
        <input type="text" name="tahun_ajaran_kelulusan" value="{{ $angkatan->tahun_ajaran_kelulusan }}" placeholder="Contoh: 2026/2027"
            class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
    </div>

    <div>
        <label class="block text-xs font-semibold text-dark-700 mb-1">Catatan / Keterangan</label>
        <textarea name="keterangan" rows="2" class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">{{ $angkatan->keterangan }}</textarea>
    </div>

    <div class="flex items-center justify-end gap-3 pt-4 border-t border-dark-100">
        <button type="button" onclick="closeModal('modal-editangkatan')"
            class="px-4 py-2 border border-dark-200 rounded-xl text-sm font-medium text-dark-600 hover:bg-dark-50">
            Batal
        </button>
        <button type="submit"
            class="px-5 py-2 bg-gradient-to-r from-amber-500 to-amber-600 text-white text-sm font-semibold rounded-xl shadow hover:from-amber-600 hover:to-amber-700">
            Perbarui Angkatan
        </button>
    </div>
</form>
