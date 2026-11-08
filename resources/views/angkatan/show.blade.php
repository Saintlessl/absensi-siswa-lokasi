@extends('layouts.app')

@section('title', $angkatan->nama_angkatan . ' - Detail Angkatan')
@section('page-title', $angkatan->nama_angkatan)
@section('page-subtitle', 'Rincian data kohort, statistik status siswa, dan riwayat kelulusan')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600 transition-colors">Akademik</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('angkatan.index') }}" class="hover:text-primary-600 transition-colors">Manajemen Angkatan</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">{{ $angkatan->nama_angkatan }}</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-[#003EA7] flex items-center justify-center font-extrabold text-xl shadow-xs">
                {{ $angkatan->nomor_angkatan }}
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl font-bold text-dark-800">{{ $angkatan->nama_angkatan }}</h2>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $angkatan->status_badge }} border">
                        {{ $angkatan->status_label }}
                    </span>
                </div>
                <p class="text-xs text-dark-400 mt-1">
                    Kode: <span class="font-mono font-semibold text-dark-600">{{ $angkatan->kode_angkatan }}</span> • Masuk: <span class="font-semibold text-dark-600">{{ $angkatan->tahun_masuk }}</span> (T.A. {{ $angkatan->tahun_ajaran_masuk }}) • Kelulusan: <span class="font-semibold text-dark-600">{{ $angkatan->tahun_lulus_rencana }}</span> (T.A. {{ $angkatan->tahun_ajaran_kelulusan }})
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('angkatan.index') }}"
                class="px-4 py-2 border border-dark-200 text-dark-600 hover:bg-dark-50 text-sm font-medium rounded-xl transition-colors">
                <i class='bx bx-arrow-back mr-1'></i> Kembali
            </a>
            <button onclick="editAngkatan({{ $angkatan->id }})"
                class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">
                <i class='bx bx-edit mr-1'></i> Edit Angkatan
            </button>
        </div>
    </div>
@endsection

@section('content')
    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 mb-6">
        <div class="glass-card rounded-2xl p-4 border border-dark-200/50">
            <p class="text-[11px] font-semibold text-dark-400 uppercase tracking-wider">Total Siswa</p>
            <p class="text-2xl font-bold text-dark-800 mt-1">{{ $stats['total_siswa'] }}</p>
            <p class="text-[11px] text-dark-400 mt-0.5">{{ $stats['rombel_count'] }} Rombel Terdata</p>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-blue-100 bg-blue-50/20">
            <p class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider">Siswa Aktif</p>
            <p class="text-2xl font-bold text-blue-700 mt-1">{{ $stats['siswa_aktif'] }}</p>
            <p class="text-[11px] text-blue-500 mt-0.5">Bersekolah aktif</p>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-emerald-100 bg-emerald-50/20">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Lulus (Alumni)</p>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ $stats['siswa_lulus'] }}</p>
            <p class="text-[11px] text-emerald-500 mt-0.5">Resmi kelulusan</p>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-amber-100 bg-amber-50/20">
            <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Proses Lulus</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ $stats['siswa_proses'] }}</p>
            <p class="text-[11px] text-amber-500 mt-0.5">Sedang verifikasi</p>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-rose-100 bg-rose-50/20">
            <p class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Tidak Lulus</p>
            <p class="text-2xl font-bold text-rose-700 mt-1">{{ $stats['siswa_tidak_lulus'] }}</p>
            <p class="text-[11px] text-rose-500 mt-0.5">Penetapan dewan</p>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-purple-100 bg-purple-50/20">
            <p class="text-[11px] font-semibold text-purple-600 uppercase tracking-wider">Pindah / Keluar</p>
            <p class="text-2xl font-bold text-purple-700 mt-1">{{ $stats['siswa_pindah'] + $stats['siswa_keluar'] }}</p>
            <p class="text-[11px] text-purple-500 mt-0.5">Mutasi siswa</p>
        </div>
    </div>

    {{-- Siswa Table Card --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ route('angkatan.show', $angkatan->id) }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Cari Siswa di Angkatan Ini</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-dark-400'></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            placeholder="Cari berdasarkan NIS, NISN, atau Nama Siswa...">
                    </div>
                </div>
                <div class="w-full sm:w-56">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Filter Status Siswa</label>
                    <select name="status" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="semua" {{ request('status') === 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Siswa Aktif</option>
                        <option value="dalam_proses_kelulusan" {{ request('status') === 'dalam_proses_kelulusan' ? 'selected' : '' }}>Dalam Proses Kelulusan</option>
                        <option value="lulus" {{ request('status') === 'lulus' ? 'selected' : '' }}>Lulus (Alumni)</option>
                        <option value="tidak_lulus" {{ request('status') === 'tidak_lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                        <option value="pindah" {{ request('status') === 'pindah' ? 'selected' : '' }}>Pindah Sekolah</option>
                        <option value="keluar" {{ request('status') === 'keluar' ? 'selected' : '' }}>Keluar / Mengundurkan Diri</option>
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Terapkan
                </button>
                @if (request('search') || (request('status') && request('status') !== 'semua'))
                    <a href="{{ route('angkatan.show', $angkatan->id) }}"
                        class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-dark-100 bg-dark-50/50 text-[11px] font-bold text-dark-400 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Siswa</th>
                        <th class="px-6 py-3.5">NIS / NISN</th>
                        <th class="px-6 py-3.5">Kelas & Jurusan Terakhir</th>
                        <th class="px-6 py-3.5">Tahun Masuk / Lulus</th>
                        <th class="px-6 py-3.5 text-center">Status Siswa</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-100 text-sm">
                    @forelse ($siswas as $siswa)
                        <tr class="hover:bg-dark-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-600 to-primary-700 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs">
                                        {{ strtoupper(substr($siswa->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-dark-800">{{ $siswa->nama }}</p>
                                        <p class="text-xs text-dark-400">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-mono text-dark-800 font-semibold">{{ $siswa->nis }}</p>
                                <p class="font-mono text-xs text-dark-400">{{ $siswa->nisn ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-dark-800">{{ $siswa->kelas->nama_kelas ?? 'Belum Ditentukan' }}</p>
                                <p class="text-xs text-dark-400">{{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-dark-800">Masuk: <span class="font-semibold">{{ $siswa->tahun_masuk ?? $angkatan->tahun_masuk }}</span></p>
                                @if ($siswa->tahun_lulus)
                                    <p class="text-xs text-emerald-700">Lulus: <span class="font-semibold">{{ $siswa->tahun_lulus }}</span> (T.A. {{ $siswa->tahun_ajaran_lulus }})</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $siswa->status_badge }} border">
                                    {{ $siswa->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('siswa') }}?search={{ urlencode($siswa->nis) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-dark-200 text-xs font-medium text-dark-700 hover:bg-dark-50 transition-colors">
                                    <i class='bx bx-user'></i> Buka di Data Siswa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-dark-400">
                                <i class='bx bx-user-x text-4xl mb-2 text-dark-300'></i>
                                <p class="text-sm font-medium">Tidak ada siswa yang sesuai kriteria pencarian di angkatan ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($siswas->hasPages())
            <div class="px-6 py-4 border-t border-dark-100">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>

    {{-- Edit Modal --}}
    @push('modals')
        <div id="modal-editangkatan" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-dark-900/50 backdrop-blur-xs transition-opacity" onclick="closeModal('modal-editangkatan')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-dark-100 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-dark-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                                <i class='bx bx-edit'></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-dark-800">Edit Data Angkatan</h3>
                                <p class="text-xs text-dark-400">Perbarui identitas kohort dan siklus kelulusan</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeModal('modal-editangkatan')" class="text-dark-400 hover:text-dark-600">
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>
                    <div id="loadeditformangkatan" class="mt-4">
                        <div class="flex items-center justify-center py-12">
                            <i class='bx bx-loader-alt bx-spin text-3xl text-primary-600'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endpush
@endsection

@push('scripts')
    <script>
        function openModal(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeModal(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function editAngkatan(id) {
            const container = document.getElementById('loadeditformangkatan');
            container.innerHTML = `
                <div class="flex items-center justify-center py-12">
                    <i class='bx bx-loader-alt bx-spin text-3xl text-primary-600'></i>
                </div>
            `;
            openModal('modal-editangkatan');

            fetch("{{ route('angkatan.edit') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id: id })
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal memuat formulir edit angkatan.');
                return res.text();
            })
            .then(html => {
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = `
                    <div class="text-center py-8 text-rose-500">
                        <i class='bx bx-error-circle text-4xl mb-2'></i>
                        <p class="text-sm">${err.message}</p>
                    </div>
                `;
            });
        }
    </script>
@endpush
