@extends('layouts.app')

@section('title', 'Manajemen Angkatan & Status Kelulusan')
@section('page-title', 'Manajemen Angkatan')
@section('page-subtitle', 'Kelola siklus angkatan siswa, tahun masuk, masa studi, hingga kelulusan alumni')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600 transition-colors">Akademik</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Manajemen Angkatan</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">Daftar Angkatan & Siklus Siswa</h2>
            <p class="text-sm text-dark-400 mt-1">Master pengelompokan siswa sepanjang masa pendidikan, kenaikan kelas, hingga menjadi alumni.</p>
        </div>
        <button onclick="openModal('modal-inputangkatan')"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-semibold rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-primary-500/35 transition-all duration-200 hover:-translate-y-0.5">
            <i class='bx bx-plus text-lg'></i>
            Tambah Angkatan Baru
        </button>
    </div>
@endsection

@section('content')
    {{-- Summary Stats Row --}}
    @php
        $totalAngkatan = $angkatans->count();
        $totalAktif = $angkatans->where('status', 'aktif')->count();
        $totalSelesai = $angkatans->where('status', 'selesai')->count();
        $totalSiswaGlobal = $angkatans->sum(fn($a) => $a->computed_stats['total_siswa'] ?? 0);
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="glass-card rounded-2xl p-4 border border-dark-200/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class='bx bxs-graduation'></i>
            </div>
            <div>
                <p class="text-xs font-medium text-dark-400">Total Angkatan</p>
                <p class="text-xl font-bold text-dark-800 mt-0.5">{{ $totalAngkatan }}</p>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-dark-200/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class='bx bx-user-check'></i>
            </div>
            <div>
                <p class="text-xs font-medium text-dark-400">Angkatan Aktif</p>
                <p class="text-xl font-bold text-dark-800 mt-0.5">{{ $totalAktif }} <span class="text-xs font-normal text-dark-400">Kohort</span></p>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-dark-200/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class='bx bx-award'></i>
            </div>
            <div>
                <p class="text-xs font-medium text-dark-400">Angkatan Alumni (Lulus)</p>
                <p class="text-xl font-bold text-dark-800 mt-0.5">{{ $totalSelesai }} <span class="text-xs font-normal text-dark-400">Kohort</span></p>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-dark-200/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class='bx bx-group'></i>
            </div>
            <div>
                <p class="text-xs font-medium text-dark-400">Total Siswa Terdata</p>
                <p class="text-xl font-bold text-dark-800 mt-0.5">{{ $totalSiswaGlobal }} <span class="text-xs font-normal text-dark-400">Siswa</span></p>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ route('angkatan.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Cari Angkatan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-dark-400'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            placeholder="Nomor angkatan, kode (misal AKT-PPLG-19), nama, atau tahun...">
                    </div>
                </div>
                <div class="w-full sm:w-56">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Jurusan</label>
                    <select name="jurusan_id" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="semua" {{ ($jurusan_id ?? 'semua') === 'semua' ? 'selected' : '' }}>Semua Jurusan</option>
                        @foreach ($jurusans as $j)
                            <option value="{{ $j->id }}" {{ (string)($jurusan_id ?? '') === (string)$j->id ? 'selected' : '' }}>
                                {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Status Siklus</label>
                    <select name="status" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif (Sedang Berjalan)</option>
                        <option value="proses_kelulusan" {{ $status === 'proses_kelulusan' ? 'selected' : '' }}>Dalam Proses Kelulusan</option>
                        <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai (Alumni / Lulus)</option>
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($search || ($status && $status !== 'semua') || (($jurusan_id ?? 'semua') !== 'semua'))
                    <a href="{{ route('angkatan.index') }}"
                        class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="mx-6 mt-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3 text-emerald-800 text-sm">
                <i class='bx bx-check-circle text-lg text-emerald-600'></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mx-6 mt-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-3 text-rose-800 text-sm">
                <i class='bx bx-error-circle text-lg text-rose-600'></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Angkatan Table --}}
        <div class="overflow-x-auto p-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ANGKATAN & KODE</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TAHUN MASUK</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TARGET / REALISASI KELULUSAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">STATUS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">STATISTIK SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right w-28">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($angkatans as $angkatan)
                        @php
                            $st = $angkatan->computed_stats ?? [
                                'total_siswa' => 0,
                                'siswa_aktif' => 0,
                                'siswa_lulus' => 0,
                                'siswa_proses' => 0,
                                'siswa_tidak_lulus' => 0,
                                'siswa_pindah' => 0,
                                'siswa_keluar' => 0,
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#EFF5FF] text-[#003FA9] flex items-center justify-center font-bold text-base shrink-0">
                                        {{ $angkatan->nomor_angkatan }}
                                    </div>
                                    <div>
                                        <a href="{{ route('angkatan.show', $angkatan->id) }}" class="font-bold text-slate-900 hover:text-[#003FA9] transition block text-xs sm:text-sm">
                                            {{ $angkatan->nama_angkatan }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            @if ($angkatan->jurusan)
                                                <span class="inline-block text-[11px] font-bold text-[#003FA9] font-mono">
                                                    {{ $angkatan->jurusan->kode_jurusan }}
                                                </span>
                                            @endif
                                            <span class="inline-block text-[11px] font-mono text-slate-500 font-medium">
                                                {{ $angkatan->kode_angkatan }}
                                            </span>
                                            @if ($angkatan->keterangan)
                                                <span class="text-[11px] text-slate-400 truncate max-w-[180px]" title="{{ $angkatan->keterangan }}">
                                                    • {{ $angkatan->keterangan }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-bold text-slate-900 text-xs sm:text-sm">{{ $angkatan->tahun_masuk }}</p>
                                <p class="text-[11px] text-slate-400">T.A. {{ $angkatan->tahun_ajaran_masuk }}</p>
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-bold text-slate-900 text-xs sm:text-sm">{{ $angkatan->tahun_lulus_rencana }}</p>
                                <p class="text-[11px] text-slate-400">T.A. {{ $angkatan->tahun_ajaran_kelulusan }}</p>
                                @if ($angkatan->tanggal_kelulusan)
                                    <span class="inline-block text-[11px] text-emerald-700 font-semibold mt-0.5">
                                        Lulus: {{ $angkatan->tanggal_kelulusan->translatedFormat('d M Y') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if ($angkatan->status === 'aktif')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#003FA9] text-white">
                                        Aktif
                                    </span>
                                @elseif ($angkatan->status === 'proses_kelulusan')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white">
                                        Proses Kelulusan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white">
                                        Alumni / Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="text-center px-2.5 py-1 rounded-xl bg-slate-100 min-w-[50px]">
                                        <span class="text-xs font-bold text-slate-800">{{ $st['total_siswa'] }}</span>
                                        <p class="text-[10px] text-slate-400 uppercase">Total</p>
                                    </div>
                                    @if ($angkatan->status === 'selesai')
                                        <div class="text-center px-2.5 py-1 rounded-xl bg-emerald-100 min-w-[50px]">
                                            <span class="text-xs font-bold text-emerald-800">{{ $st['siswa_lulus'] }}</span>
                                            <p class="text-[10px] text-emerald-700 uppercase">Lulus</p>
                                        </div>
                                    @else
                                        <div class="text-center px-2.5 py-1 rounded-xl bg-[#EFF5FF] min-w-[50px]">
                                            <span class="text-xs font-bold text-[#003FA9]">{{ $st['siswa_aktif'] }}</span>
                                            <p class="text-[10px] text-[#003FA9]/70 uppercase">Aktif</p>
                                        </div>
                                    @endif
                                    @if ($st['siswa_pindah'] > 0 || $st['siswa_keluar'] > 0)
                                        <div class="text-center px-2.5 py-1 rounded-xl bg-purple-100 min-w-[50px]">
                                            <span class="text-xs font-bold text-purple-800">{{ $st['siswa_pindah'] + $st['siswa_keluar'] }}</span>
                                            <p class="text-[10px] text-purple-700 uppercase">Mutasi</p>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('angkatan.show', $angkatan->id) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Detail Angkatan">
                                        <i class='bx bx-info-circle text-lg'></i>
                                    </a>
                                    <button type="button" onclick="editAngkatan({{ $angkatan->id }})"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Edit Angkatan">
                                        <i class='bx bx-edit text-lg'></i>
                                    </button>
                                    <form action="{{ route('angkatan.destroy', $angkatan->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="button"
                                            class="delete-confirm p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Hapus Angkatan"
                                            data-name="{{ $angkatan->nama_angkatan }}"
                                            data-students="{{ $st['total_siswa'] }}">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class='bx bxs-graduation text-4xl mb-2 text-slate-300'></i>
                                <h3 class="text-sm font-semibold text-slate-600">Belum Ada Data Angkatan</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Data angkatan belum dibuat atau tidak ditemukan berdasarkan kriteria pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL TAMBAH ANGKATAN --}}
    @push('modals')
        <div id="modal-inputangkatan" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-dark-900/50 backdrop-blur-xs transition-opacity" onclick="closeModal('modal-inputangkatan')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-dark-100 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-dark-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-xl font-bold">
                                <i class='bx bxs-graduation'></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-dark-800">Tambah Angkatan Baru</h3>
                                <p class="text-xs text-dark-400">Buat kohort angkatan siswa baru</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeModal('modal-inputangkatan')" class="text-dark-400 hover:text-dark-600">
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>

                    <form action="{{ route('angkatan.store') }}" method="POST" class="mt-4 space-y-4" id="formTambahAngkatan">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Jurusan / Program Keahlian <span class="text-rose-500">*</span></label>
                            <select name="jurusan_id" id="add_jurusan_id" required class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                                @foreach ($jurusans as $j)
                                    <option value="{{ $j->id }}" data-kode="{{ $j->kode_jurusan }}">{{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-dark-400 mt-1">Setiap jurusan memiliki penomoran angkatan independen sesuai sejarah pendiriannya.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-dark-700 mb-1">Nomor Angkatan <span class="text-rose-500">*</span></label>
                                <input type="number" name="nomor_angkatan" id="add_nomor_angkatan" required min="1" max="999" placeholder="Contoh: 19"
                                    class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Masuk <span class="text-rose-500">*</span></label>
                                <input type="number" name="tahun_masuk" id="add_tahun_masuk" required min="2000" max="2099" value="{{ date('Y') }}"
                                    class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-dark-700 mb-1">Nama Angkatan</label>
                                <input type="text" name="nama_angkatan" id="add_nama_angkatan" placeholder="Otomatis: Angkatan X"
                                    class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-dark-700 mb-1">Kode Angkatan</label>
                                <input type="text" name="kode_angkatan" id="add_kode_angkatan" placeholder="Otomatis: AKT-X"
                                    class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl uppercase focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Ajaran Masuk</label>
                                <input type="text" name="tahun_ajaran_masuk" id="add_tahun_ajaran_masuk" placeholder="Contoh: 2026/2027"
                                    class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-dark-700 mb-1">Rencana Tahun Lulus</label>
                                <input type="number" name="tahun_lulus_rencana" id="add_tahun_lulus_rencana" placeholder="Contoh: 2029"
                                    class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Status Awal</label>
                            <select name="status" class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                                <option value="aktif" selected>Aktif (Sedang Berjalan)</option>
                                <option value="proses_kelulusan">Dalam Proses Kelulusan</option>
                                <option value="selesai">Selesai (Alumni / Lulus)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Catatan / Keterangan (Opsional)</label>
                            <textarea name="keterangan" rows="2" placeholder="Catatan khusus mengenai angkatan ini..."
                                class="w-full px-3 py-2 text-sm border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-dark-100">
                            <button type="button" onclick="closeModal('modal-inputangkatan')"
                                class="px-4 py-2 border border-dark-200 rounded-xl text-sm font-medium text-dark-600 hover:bg-dark-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white text-sm font-semibold rounded-xl shadow hover:from-primary-700 hover:to-primary-800">
                                Simpan Angkatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT ANGKATAN --}}
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

        // Auto suggestion on Add modal
        document.addEventListener('DOMContentLoaded', function() {
            const nomorInput = document.getElementById('add_nomor_angkatan');
            const tahunInput = document.getElementById('add_tahun_masuk');
            const namaInput = document.getElementById('add_nama_angkatan');
            const kodeInput = document.getElementById('add_kode_angkatan');
            const taMasukInput = document.getElementById('add_tahun_ajaran_masuk');
            const taLulusInput = document.getElementById('add_tahun_lulus_rencana');

            function syncAddDefaults() {
                const no = nomorInput.value;
                const yr = parseInt(tahunInput.value, 10);

                if (no && !namaInput.value) {
                    namaInput.placeholder = 'Angkatan ' + no;
                }
                if (no && !kodeInput.value) {
                    kodeInput.placeholder = 'AKT-' + no;
                }
                if (yr && !taMasukInput.value) {
                    taMasukInput.placeholder = yr + '/' + (yr + 1);
                }
                if (yr && !taLulusInput.value) {
                    taLulusInput.placeholder = (yr + 3);
                }
            }

            if (nomorInput && tahunInput) {
                nomorInput.addEventListener('input', syncAddDefaults);
                tahunInput.addEventListener('input', syncAddDefaults);
            }

            // Delete confirmation with student count guard alert
            document.querySelectorAll('.delete-confirm').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    const name = this.getAttribute('data-name');
                    const students = parseInt(this.getAttribute('data-students') || '0', 10);

                    if (students > 0) {
                        Swal.fire({
                            title: 'Tidak Dapat Dihapus!',
                            text: `${name} masih memiliki ${students} siswa terdaftar. Pindahkan atau ubah data siswa terlebih dahulu sebelum menghapus angkatan ini.`,
                            icon: 'error',
                            confirmButtonColor: '#003EA7',
                            confirmButtonText: 'Mengerti',
                            customClass: {
                                popup: 'rounded-2xl',
                                confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold'
                            }
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Hapus Angkatan?',
                        text: `Apakah Anda yakin ingin menghapus ${name}? Tindakan ini tidak dapat dibatalkan.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold',
                            cancelButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });

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
