@extends('layouts.app')

@section('title', 'Master Tahun Ajaran')
@section('page-title', 'Master Tahun Ajaran')
@section('page-subtitle', 'Pengelolaan Kalender Akademik & Periode Operasional')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Tahun Ajaran</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dark-800">Master Tahun Ajaran & Kalender Akademik</h2>
            <p class="text-sm text-dark-400 mt-1">
                Pengelolaan kalender akademik global sekolah, semester aktif, dan penentuan periode operasional sekolah.
            </p>
        </div>

        @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="openModal('modal-tambahta')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white rounded-xl text-xs font-bold shadow-lg shadow-primary-500/25 hover:shadow-primary-500/35 transition-all duration-200 hover:-translate-y-0.5">
                    <i class='bx bx-plus text-base'></i>
                    <span>Tambah Tahun Ajaran</span>
                </button>
            </div>
        @endif
    </div>
@endsection

@section('content')
    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-emerald-800 text-sm font-semibold">
            <div class="flex items-center gap-2">
                <i class='bx bx-check-circle text-lg text-emerald-600'></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class='bx bx-x text-lg'></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between text-rose-800 text-sm font-semibold">
            <div class="flex items-center gap-2">
                <i class='bx bx-error-circle text-lg text-rose-600'></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i class='bx bx-x text-lg'></i></button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="flex items-center justify-between font-bold mb-1">
                <span class="flex items-center gap-2">
                    <i class='bx bx-error-circle text-lg text-rose-600'></i> Terdapat kesalahan pada input:
                </span>
                <button onclick="this.parentElement.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i class='bx bx-x text-lg'></i></button>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-700 mt-1 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- SECTION: ACTIVE ACADEMIC YEAR SPOTLIGHT & STATS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Spotlight Banner --}}
        <div class="lg:col-span-2 relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#003EA7] via-primary-800 to-blue-900 text-white p-6 sm:p-7 shadow-lg shadow-blue-900/15">
            <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="absolute right-6 top-6 text-white/10 text-8xl font-black select-none pointer-events-none">
                <i class='bx bx-calendar-star'></i>
            </div>

            <div class="relative z-10 flex flex-col justify-between h-full">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-xs text-xs font-bold text-white mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        PERIODE OPERASIONAL AKTIF
                    </div>
                    <div class="flex flex-wrap items-baseline gap-3">
                        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white font-mono">
                            {{ $activeTahunAjaran?->nama ?? '2026/2027' }}
                        </h2>
                        <span class="px-3 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold uppercase tracking-wider">
                            Semester {{ ucfirst($activeTahunAjaran?->semester_aktif ?? 'ganjil') }}
                        </span>
                    </div>
                    <p class="text-xs text-blue-100/90 mt-2 max-w-lg leading-relaxed">
                        Rentang periode: {{ $activeTahunAjaran ? $activeTahunAjaran->tanggal_mulai->translatedFormat('d F Y') : '-' }} s.d {{ $activeTahunAjaran ? $activeTahunAjaran->tanggal_selesai->translatedFormat('d F Y') : '-' }}.
                        Semua presensi harian, jurnal mengajar, dan alokasi ruangan siswa mengacu pada periode ini.
                    </p>
                </div>

                @if(in_array($user->role, ['superadmin', 'admin']) && $activeTahunAjaran)
                    <div class="mt-6 pt-4 border-t border-white/15 flex flex-wrap items-center justify-between gap-3">
                        <div class="text-xs text-blue-100/80">
                            Status semester saat ini: <span class="font-bold text-white">Semester {{ ucfirst($activeTahunAjaran->semester_aktif) }}</span>
                        </div>
                        <form action="{{ route('academic.tahun-ajaran.switch-semester', $activeTahunAjaran->id) }}" method="POST"
                              data-confirm="Apakah Anda yakin ingin mengganti semester aktif menjadi Semester {{ $activeTahunAjaran->semester_aktif === 'ganjil' ? 'Genap' : 'Ganjil' }}? Pergantian semester tidak memicu kenaikan kelas."
                              data-confirm-title="Ganti Semester Aktif">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-bold border border-white/20 transition shadow-xs">
                                <i class='bx bx-refresh text-sm'></i>
                                <span>Ganti ke Semester {{ $activeTahunAjaran->semester_aktif === 'ganjil' ? 'Genap' : 'Ganjil' }}</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Mini Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-dark-100 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    <i class='bx bx-chalkboard'></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-dark-400 block">Total Rombel Aktif</span>
                    <span class="text-2xl font-bold text-dark-800">{{ $stats['total_classes'] }}</span>
                    <span class="text-[11px] text-dark-400 block mt-0.5">Seluruh jenjang X, XI, XII</span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-dark-100 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    <i class='bx bx-user-check'></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-dark-400 block">Siswa Aktif Terdaftar</span>
                    <span class="text-2xl font-bold text-dark-800">{{ $stats['active_students'] }}</span>
                    <span class="text-[11px] text-dark-400 block mt-0.5">Memiliki enrollment aktif</span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-dark-100 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    <i class='bx bx-history'></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-dark-400 block">Total Periode Terdata</span>
                    <span class="text-2xl font-bold text-dark-800">{{ $stats['total_periods'] }}</span>
                    <span class="text-[11px] text-dark-400 block mt-0.5">Historis & Perencanaan</span>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION: SEARCH & FILTER --}}
    <div class="p-4 sm:p-5 rounded-2xl bg-white border border-dark-100 shadow-xs mb-6">
        <form action="{{ route('academic.tahun-ajaran.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <i class='bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-dark-400 text-lg'></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama tahun ajaran, tahun kalender, atau catatan..."
                    class="w-full pl-10 pr-4 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
            </div>

            <div class="w-full md:w-48">
                <select name="status" class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ $statusFilter === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ $statusFilter === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="diarsipkan" {{ $statusFilter === 'diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-[#003EA7] hover:bg-primary-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition">
                    <i class='bx bx-filter-alt'></i>
                    <span>Terapkan</span>
                </button>
                @if($search || $statusFilter)
                    <a href="{{ route('academic.tahun-ajaran.index') }}" class="px-3 py-2 bg-dark-100 hover:bg-dark-200 text-dark-700 rounded-xl text-xs font-semibold transition">
                        Reset
                    </a>
                @endif
                @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                    <button type="button" onclick="openModal('modal-tambahta')"
                        class="sm:hidden ml-auto inline-flex items-center gap-1 px-3 py-2 bg-[#003EA7] text-white rounded-xl text-xs font-semibold shadow-xs">
                        <i class='bx bx-plus'></i> Tambah
                    </button>
                @endif
            </div>
        </form>
    </div>

    {{-- SECTION: TABEL DATA TAHUN AJARAN --}}
    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm overflow-hidden p-6 mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TAHUN AJARAN & PERIODE</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">SEMESTER AKTIF</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">JUMLAH ROMBEL</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">ENROLLMENT SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">STATUS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">CATATAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-36">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($tahunAjarans as $index => $ta)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $ta->is_active ? 'bg-blue-50/30' : '' }}">
                            <td class="py-4 px-4 text-center font-medium text-xs text-slate-400">
                                {{ $tahunAjarans->firstItem() + $index }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl {{ $ta->is_active ? 'bg-[#003FA9] text-white' : 'bg-[#EFF5FF] text-[#003FA9]' }} flex items-center justify-center font-bold text-sm shrink-0">
                                        <i class='bx bx-calendar'></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 text-xs sm:text-sm font-mono block">
                                            {{ $ta->nama }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-normal">
                                            {{ $ta->tanggal_mulai ? $ta->tanggal_mulai->format('d/m/Y') : '-' }} s.d {{ $ta->tanggal_selesai ? $ta->tanggal_selesai->format('d/m/Y') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $ta->semester_aktif === 'ganjil' ? 'bg-[#EFF5FF] text-[#003FA9]' : 'bg-slate-100 text-slate-700' }}">
                                    Semester {{ ucfirst($ta->semester_aktif) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center font-semibold text-slate-800 text-xs sm:text-sm">
                                {{ $ta->kelas_count }} Rombel
                            </td>
                            <td class="py-4 px-4 text-center font-semibold text-slate-800 text-xs sm:text-sm">
                                {{ $ta->enrollments_count }} Siswa
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($ta->is_active)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white">
                                        AKTIF
                                    </span>
                                @elseif($ta->status === 'selesai')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                        Selesai
                                    </span>
                                @elseif($ta->status === 'draft')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white">
                                        Draft
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-600">
                                        Diarsipkan
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center text-xs text-slate-400 max-w-xs truncate" title="{{ $ta->catatan }}">
                                {{ $ta->catatan ?: '-' }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    @if(!$ta->is_active && in_array($user->role, ['superadmin', 'admin']))
                                        <form action="{{ route('academic.tahun-ajaran.activate', $ta->id) }}" method="POST"
                                              data-confirm="Aktifkan Tahun Ajaran {{ $ta->nama }} sebagai periode operasional sekolah? Periode aktif lain akan otomatis dinonaktifkan."
                                              data-confirm-title="Aktifkan Tahun Ajaran" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1 transition shadow-sm"
                                                title="Aktifkan Periode Ini">
                                                <i class='bx bx-check-double text-sm'></i>
                                                <span>Aktifkan</span>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($user->role, ['superadmin', 'admin']))
                                        <button type="button" onclick="editTahunAjaran({{ json_encode($ta) }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                            title="Edit Periode">
                                            <i class='bx bx-edit text-lg'></i>
                                        </button>

                                        @if(!$ta->is_active && $ta->kelas_count === 0 && $ta->enrollments_count === 0)
                                            <form action="{{ route('academic.tahun-ajaran.destroy', $ta->id) }}" method="POST"
                                                  data-confirm="Hapus permanen data Tahun Ajaran {{ $ta->nama }}?"
                                                  data-confirm-title="Hapus Tahun Ajaran"
                                                  data-confirm-danger="true" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                    title="Hapus Periode Kosong">
                                                    <i class='bx bx-trash text-lg'></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                <i class='bx bx-calendar-x text-4xl mb-2 text-slate-300'></i>
                                <h4 class="font-semibold text-slate-600 text-sm">Tidak ada data tahun ajaran</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Data tahun ajaran belum dibuat atau tidak ditemukan sesuai filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tahunAjarans->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{ $tahunAjarans->links() }}
            </div>
        @endif
    </div>
@endsection

@push('modals')
    {{-- MODAL TAMBAH TAHUN AJARAN --}}
    <div id="modal-tambahta" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-dark-900/50 backdrop-blur-xs transition-opacity" onclick="closeModal('modal-tambahta')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-dark-100 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-dark-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-xl font-bold">
                            <i class='bx bx-calendar-plus'></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-dark-800">Tambah Tahun Ajaran Baru</h3>
                            <p class="text-xs text-dark-400">Buat siklus kalender akademik global sekolah</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal('modal-tambahta')" class="text-dark-400 hover:text-dark-600">
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>

                <form action="{{ route('academic.tahun-ajaran.store') }}" method="POST" class="mt-4 space-y-4" id="formTambahTA">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Mulai <span class="text-rose-500">*</span></label>
                            <input type="number" name="tahun_mulai" id="add_tahun_mulai" required min="2000" max="2099" value="{{ date('Y') }}"
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Selesai <span class="text-rose-500">*</span></label>
                            <input type="number" name="tahun_selesai" id="add_tahun_selesai" required min="2000" max="2099" value="{{ date('Y') + 1 }}"
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Nama Tahun Ajaran <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama" id="add_nama" required placeholder="Contoh: 2026/2027"
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono font-bold text-[#003EA7]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Semester Awal <span class="text-rose-500">*</span></label>
                            <select name="semester_aktif" id="add_semester_aktif" required class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                                <option value="ganjil" selected>Semester Ganjil</option>
                                <option value="genap">Semester Genap</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" id="add_tanggal_mulai" required
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_selesai" id="add_tanggal_selesai" required
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Status Awal</label>
                        <select name="status" id="add_status" class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            <option value="draft" selected>Draft (Perencanaan)</option>
                            <option value="aktif">Aktif (Langsung Diberlakukan)</option>
                            <option value="selesai">Selesai</option>
                            <option value="diarsipkan">Diarsipkan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Catatan / Keterangan</label>
                        <textarea name="catatan" id="add_catatan" rows="2" placeholder="Catatan tambahan kalender akademik..."
                            class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="add_is_active" value="1" class="rounded text-[#003EA7] focus:ring-[#003EA7]">
                        <label for="add_is_active" class="text-xs text-dark-700 font-semibold cursor-pointer select-none">
                            Tetapkan sekarang sebagai Tahun Ajaran Aktif Sekolah
                        </label>
                    </div>

                    <div class="pt-4 border-t border-dark-100 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModal('modal-tambahta')"
                            class="px-4 py-2 border border-dark-200 rounded-xl text-xs font-semibold text-dark-600 hover:bg-dark-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 bg-[#003EA7] hover:bg-primary-700 text-white rounded-xl text-xs font-bold shadow-md shadow-primary-500/20 transition flex items-center gap-1.5">
                            <i class='bx bx-save text-base'></i>
                            <span>Simpan Tahun Ajaran</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT TAHUN AJARAN --}}
    <div id="modal-editta" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-dark-900/50 backdrop-blur-xs transition-opacity" onclick="closeModal('modal-editta')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-dark-100 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-dark-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-xl font-bold">
                            <i class='bx bx-edit'></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-dark-800">Edit Tahun Ajaran</h3>
                            <p class="text-xs text-dark-400" id="edit_modal_subtitle">Perbarui data kalender akademik</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal('modal-editta')" class="text-dark-400 hover:text-dark-600">
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>

                <form action="" method="POST" class="mt-4 space-y-4" id="formEditTA">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Mulai <span class="text-rose-500">*</span></label>
                            <input type="number" name="tahun_mulai" id="edit_tahun_mulai" required min="2000" max="2099"
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Selesai <span class="text-rose-500">*</span></label>
                            <input type="number" name="tahun_selesai" id="edit_tahun_selesai" required min="2000" max="2099"
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Nama Tahun Ajaran <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama" id="edit_nama" required
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono font-bold text-[#003EA7]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Semester Aktif <span class="text-rose-500">*</span></label>
                            <select name="semester_aktif" id="edit_semester_aktif" required class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                                <option value="ganjil">Semester Ganjil</option>
                                <option value="genap">Semester Genap</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" required
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" required
                                class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Status Periode</label>
                        <select name="status" id="edit_status" class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            <option value="draft">Draft</option>
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                            <option value="diarsipkan">Diarsipkan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Catatan</label>
                        <textarea name="catatan" id="edit_catatan" rows="2"
                            class="w-full px-3 py-2 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded text-[#003EA7] focus:ring-[#003EA7]">
                        <label for="edit_is_active" class="text-xs text-dark-700 font-semibold cursor-pointer select-none">
                            Jadikan sebagai Tahun Ajaran Aktif Sekolah
                        </label>
                    </div>

                    <div class="pt-4 border-t border-dark-100 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModal('modal-editta')"
                            class="px-4 py-2 border border-dark-200 rounded-xl text-xs font-semibold text-dark-600 hover:bg-dark-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 bg-[#003EA7] hover:bg-primary-700 text-white rounded-xl text-xs font-bold shadow-md shadow-primary-500/20 transition flex items-center gap-1.5">
                            <i class='bx bx-save text-base'></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('modal-tambahta');
            closeModal('modal-editta');
        }
    });

    // Auto-compute years in create modal
    const addMulai = document.getElementById('add_tahun_mulai');
    const addSelesai = document.getElementById('add_tahun_selesai');
    const addNama = document.getElementById('add_nama');
    const addTglMulai = document.getElementById('add_tanggal_mulai');
    const addTglSelesai = document.getElementById('add_tanggal_selesai');

    function syncAddYear() {
        if (!addMulai) return;
        const start = parseInt(addMulai.value, 10);
        if (!isNaN(start)) {
            const end = start + 1;
            if (addSelesai) addSelesai.value = end;
            if (addNama) addNama.value = start + '/' + end;
            if (addTglMulai && !addTglMulai.value) addTglMulai.value = start + '-07-15';
            if (addTglSelesai && !addTglSelesai.value) addTglSelesai.value = end + '-06-30';
        }
    }

    if (addMulai) {
        addMulai.addEventListener('input', syncAddYear);
        syncAddYear();
    }

    // Populate Edit Modal
    function editTahunAjaran(data) {
        const form = document.getElementById('formEditTA');
        if (!form) return;

        form.action = '{{ url('/tahun-ajaran') }}/' + data.id;
        document.getElementById('edit_modal_subtitle').textContent = 'Perbarui data ' + data.nama;
        document.getElementById('edit_tahun_mulai').value = data.tahun_mulai;
        document.getElementById('edit_tahun_selesai').value = data.tahun_selesai;
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_semester_aktif').value = data.semester_aktif || 'ganjil';

        const tglMulai = data.tanggal_mulai ? data.tanggal_mulai.substring(0, 10) : '';
        const tglSelesai = data.tanggal_selesai ? data.tanggal_selesai.substring(0, 10) : '';
        document.getElementById('edit_tanggal_mulai').value = tglMulai;
        document.getElementById('edit_tanggal_selesai').value = tglSelesai;

        document.getElementById('edit_status').value = data.status || 'draft';
        document.getElementById('edit_catatan').value = data.catatan || '';
        document.getElementById('edit_is_active').checked = !!data.is_active;

        openModal('modal-editta');
    }
</script>
@endpush
