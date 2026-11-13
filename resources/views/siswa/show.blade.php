@extends('layouts.app')

@section('title', 'Detail Siswa: ' . $siswa->nama)
@section('page-title', 'Data Siswa')
@section('page-subtitle', 'Kelola data siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('siswa') }}" class="hover:text-[#003FA9] transition">Data Siswa</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">{{ $siswa->nama }}</li>
@endsection

@section('content')
<div x-data="{ activeTab: '{{ request('tab', 'timeline') }}' }" class="space-y-6">

    {{-- Student Information Header Card (Matching Reference 2 & Section 6.B) --}}
    <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            {{-- Left: Student Primary Visual Identity (Photo + Details) --}}
            <div class="flex items-center gap-5 sm:gap-6">
                {{-- Larger Student Photo Header --}}
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-[#EFF5FF] border-2 border-blue-100 shrink-0 flex items-center justify-center shadow-sm">
                    @if ($siswa->photo_url)
                        <img src="{{ $siswa->photo_url }}" alt="{{ $siswa->nama }}" class="w-full h-full object-cover">
                    @else
                        <div class="flex flex-col items-center justify-center text-[#003FA9]">
                            <span class="text-2xl sm:text-3xl font-extrabold">{{ strtoupper(substr($siswa->nama, 0, 1)) }}</span>
                            <span class="text-[9px] font-semibold text-slate-400 mt-0.5">Belum Ada Foto</span>
                        </div>
                    @endif
                </div>

                {{-- Name & Administrative Details --}}
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $siswa->nama }}</h1>
                    <div class="flex items-center flex-wrap gap-x-3 gap-y-1.5 mt-2 text-xs sm:text-sm text-slate-500 font-medium">
                        <div>NIS: <span class="font-bold text-slate-800 font-mono">{{ $siswa->nis }}</span></div>
                        <span>•</span>
                        <div>NISN: <span class="font-bold text-slate-800 font-mono">{{ $siswa->nisn ?? '-' }}</span></div>
                        <span>•</span>
                        <div>Kelas: <span class="font-bold text-slate-800">{{ $siswa->kelas->nama_kelas ?? '-' }}</span></div>
                        <span>•</span>
                        <div>Jurusan: <span class="font-bold text-slate-800">{{ $siswa->kelas->jurusan->nama_jurusan ?? ($siswa->jurusan->nama_jurusan ?? '-') }}</span></div>
                        <span>•</span>
                        <div>Masuk TA: {{ $siswa->tahun_ajaran_masuk_nama }}</div>
                        @if($siswa->user?->email)
                            <span>•</span>
                            <div>Email: <a href="mailto:{{ $siswa->user->email }}" class="font-semibold text-[#003FA9] hover:underline">{{ $siswa->user->email }}</a></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Actions (Kembali & Edit Siswa) --}}
            <div class="flex items-center gap-3 w-full md:w-auto justify-end shrink-0">
                <a href="{{ route('siswa') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                    <i class='bx bx-arrow-back text-base'></i>
                    <span>Kembali</span>
                </a>
                @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                    <button type="button" onclick="editSiswa('{{ $siswa->id }}')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold shadow-md shadow-blue-900/15 transition">
                        <i class='bx bx-edit text-base'></i>
                        <span>Edit Siswa</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Subpage Navigation Tabs (Matching Reference 2: Solid button style, no translucent underline) --}}
    <div class="flex items-center gap-2.5 overflow-x-auto pb-1">
        {{-- Tab 1: Riwayat Akademik --}}
        <button type="button"
                @click="activeTab = 'timeline'"
                :class="activeTab === 'timeline'
                    ? 'bg-[#003FA9] text-white shadow-sm'
                    : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-git-commit text-lg'></i>
            <span>Riwayat Akademik (Time Line)</span>
            <span class="sr-only">Riwayat Akademik (Timeline)</span>
        </button>

        {{-- Tab 2: Identitas & Riwayat Masuk Permanen --}}
        <button type="button"
                @click="activeTab = 'identity'"
                :class="activeTab === 'identity'
                    ? 'bg-[#003FA9] text-white shadow-sm'
                    : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-id-card text-lg'></i>
            <span>Identitas & Riwayat Masuk Permanen</span>
        </button>

        {{-- Tab 3: Log --}}
        <button type="button"
                @click="activeTab = 'movements'"
                :class="activeTab === 'movements'
                    ? 'bg-[#003FA9] text-white shadow-sm'
                    : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-history text-lg'></i>
            <span>Log</span>
        </button>
    </div>

    {{-- ==================== TAB 1: RIWAYAT AKADEMIK (TIME LINE) ==================== --}}
    <div x-show="activeTab === 'timeline'" class="space-y-6">

        {{-- Filter Bar for Timeline (Matching Reference 2) --}}
        <form action="{{ route('siswa.show', $siswa->id) }}" method="GET">
            <input type="hidden" name="tab" value="timeline">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                {{-- Filter Tahun Ajaran --}}
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Tahun Ajaran</label>
                    <div class="relative">
                        <select name="ta" class="w-full h-11 px-4 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                            <option value="">Semua Tahun Ajaran</option>
                            @foreach($tahunAjarans as $ta)
                                <option value="{{ $ta->nama }}" {{ $selectedTa == $ta->nama ? 'selected' : '' }}>
                                    {{ $ta->nama }} {{ $ta->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                {{-- Filter Tingkat --}}
                <div class="w-full sm:w-36">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Tingkat</label>
                    <div class="relative">
                        <select name="tingkat" class="w-full h-11 px-4 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                            <option value="">Semua</option>
                            <option value="10" {{ $selectedTingkat == '10' ? 'selected' : '' }}>Kelas 10</option>
                            <option value="11" {{ $selectedTingkat == '11' ? 'selected' : '' }}>Kelas 11</option>
                            <option value="12" {{ $selectedTingkat == '12' ? 'selected' : '' }}>Kelas 12</option>
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                {{-- Filter Status Enrollment --}}
                <div class="w-full sm:w-44">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Status Enrollment</label>
                    <div class="relative">
                        <select name="status" class="w-full h-11 px-4 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ $selectedStatus == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="selesai" {{ $selectedStatus == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="tinggal_kelas" {{ $selectedStatus == 'tinggal_kelas' ? 'selected' : '' }}>Tinggal Kelas</option>
                            <option value="lulus" {{ $selectedStatus == 'lulus' ? 'selected' : '' }}>Lulus</option>
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                {{-- Filter Actions --}}
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-11 px-6 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold transition shadow-sm">
                        Terapkan
                    </button>
                    @if($selectedTa || $selectedTingkat || $selectedStatus)
                        <a href="{{ route('siswa.show', $siswa->id) }}" class="h-11 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold flex items-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Vertical Accent Bar Timeline (Matching Reference 1 & 2) --}}
        <div class="space-y-6 my-6">
            @forelse($enrollments as $enr)
                <div class="flex items-stretch gap-3.5 sm:gap-5" x-data="{ showDetail: false }">
                    {{-- 1. Left: Vertical Accent Bar (Full Height, Independent Per Item) --}}
                    <div class="w-2.5 sm:w-3 shrink-0 rounded-full {{ $enr->is_active ? 'bg-[#003FA9]' : 'bg-[#BCD4FF]' }}"></div>

                    {{-- 2. Right: Card Informasi Riwayat Akademik --}}
                    @if($enr->is_active)
                        {{-- Active Current Enrollment Card (SOLID ROYAL BLUE) --}}
                        <div class="flex-1 rounded-2xl p-6 sm:p-7 bg-[#003FA9] text-white shadow-xl transition-all">
                            {{-- Badges row (Matching Reference 2) --}}
                            <div class="flex items-center flex-wrap gap-2.5 mb-2">
                                <span class="px-4 py-1 rounded-full text-xs font-bold bg-white text-[#003FA9]">
                                    TA {{ $enr->tahun_ajaran }}
                                </span>
                                <span class="sr-only">Tahun Ajaran {{ $enr->tahun_ajaran }}</span>
                                <span class="px-4 py-1 rounded-full text-xs font-semibold text-white border border-white inline-flex items-center">
                                    Enrollment Aktif Berjalan
                                </span>
                            </div>

                            {{-- Large Title --}}
                            <h3 class="text-2xl font-extrabold text-white mt-2 mb-4 tracking-tight">Kelas {{ $enr->tingkat }}</h3>

                            {{-- Horizontal Information Columns --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                                <div>
                                    <span class="text-blue-200 block mb-1 font-medium">Rombongan Belajar</span>
                                    <span class="text-white font-bold text-sm block">{{ $enr->kelas?->nama_kelas ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-blue-200 block mb-1 font-medium">Wali kelas</span>
                                    <span class="text-white font-bold text-sm block">{{ $enr->wali_kelas_nama ?: '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-blue-200 block mb-1 font-medium">Rentang Tanggal Penempatan</span>
                                    <span class="text-white font-bold text-sm block">
                                        {{ $enr->started_at ? $enr->started_at->format('d/m/Y') : '-' }} s.d Sekarang
                                    </span>
                                </div>
                                <div class="flex items-end justify-start md:justify-end">
                                    <button type="button" @click="showDetail = !showDetail"
                                            class="text-white hover:text-blue-100 font-bold text-xs inline-flex items-center gap-1 transition">
                                        <span x-text="showDetail ? 'Tutup Detail' : 'Buka Detail'"></span>
                                        <i class='bx text-sm' :class="showDetail ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Expandable Detail Drawer --}}
                            <div x-show="showDetail" x-collapse class="mt-4 pt-4 border-t border-white/20 space-y-2 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <span class="text-blue-200 block">ID Enrollment:</span>
                                        <span class="font-mono text-white font-semibold">ENR-{{ str_pad($enr->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-200 block">Jurusan & Kohort:</span>
                                        <span class="font-semibold text-white">
                                            {{ $enr->jurusan?->kode_jurusan ?? ($siswa->kelas?->jurusan?->kode_jurusan ?? '-') }}
                                            • {{ $enr->angkatan?->nama_angkatan ?? ($siswa->angkatan?->nama_angkatan ?? '-') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-blue-200 block">Status Operasional:</span>
                                        <span class="font-semibold text-white">Sedang Berjalan (Active)</span>
                                    </div>
                                </div>
                                @if($enr->catatan)
                                    <div class="mt-2 pt-2 border-t border-white/20">
                                        <span class="text-blue-200 font-semibold">Catatan Histori:</span>
                                        <p class="text-white/90 italic mt-0.5">{{ $enr->catatan }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Completed Past Enrollment Card --}}
                        <div class="flex-1 rounded-2xl p-6 sm:p-7 bg-white text-slate-800 shadow-sm border border-slate-100 transition-all">
                            {{-- Badges row (Matching Reference 2) --}}
                            <div class="flex items-center flex-wrap gap-2.5 mb-2">
                                <span class="px-4 py-1 rounded-full text-xs font-bold bg-[#003FA9] text-white">
                                    TA {{ $enr->tahun_ajaran }}
                                </span>
                                <span class="sr-only">Tahun Ajaran {{ $enr->tahun_ajaran }}</span>
                            </div>

                            {{-- Large Title --}}
                            <h3 class="text-2xl font-extrabold text-slate-800 mt-2 mb-4 tracking-tight">Kelas {{ $enr->tingkat }}</h3>

                            {{-- Horizontal Information Columns --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                                <div>
                                    <span class="text-slate-400 block mb-1 font-medium">Rombongan Belajar</span>
                                    <span class="text-slate-800 font-bold text-sm block">{{ $enr->kelas?->nama_kelas ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block mb-1 font-medium">Wali kelas</span>
                                    <span class="text-slate-800 font-bold text-sm block">{{ $enr->wali_kelas_nama ?: '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block mb-1 font-medium">Rentang Tanggal Penempatan</span>
                                    <span class="text-slate-800 font-bold text-sm block">
                                        {{ $enr->started_at ? $enr->started_at->format('d/m/Y') : '-' }} s.d {{ $enr->ended_at ? $enr->ended_at->format('d/m/Y') : 'Selesai' }}
                                    </span>
                                </div>
                                <div class="flex items-end justify-start md:justify-end">
                                    <button type="button" @click="showDetail = !showDetail"
                                            class="text-slate-600 hover:text-[#003FA9] font-bold text-xs inline-flex items-center gap-1 transition">
                                        <span x-text="showDetail ? 'Tutup Detail' : 'Buka Detail'"></span>
                                        <i class='bx text-sm' :class="showDetail ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Expandable Detail Drawer --}}
                            <div x-show="showDetail" x-collapse class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <span class="text-slate-400 block">ID Enrollment:</span>
                                        <span class="font-mono text-slate-800 font-semibold">ENR-{{ str_pad($enr->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">Jurusan & Kohort:</span>
                                        <span class="font-semibold text-slate-800">
                                            {{ $enr->jurusan?->kode_jurusan ?? ($siswa->kelas?->jurusan?->kode_jurusan ?? '-') }}
                                            • {{ $enr->angkatan?->nama_angkatan ?? ($siswa->angkatan?->nama_angkatan ?? '-') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">Status Operasional:</span>
                                        <span class="font-semibold text-slate-800">Selesai / Diarsipkan</span>
                                    </div>
                                </div>
                                @if($enr->catatan)
                                    <div class="mt-2 pt-2 border-t border-slate-100">
                                        <span class="text-slate-400 font-semibold">Catatan Histori:</span>
                                        <p class="text-slate-700 italic mt-0.5">{{ $enr->catatan }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl p-8 border border-slate-100 text-center text-slate-500 shadow-sm">
                    <i class='bx bx-folder-open text-4xl mb-2 text-slate-300'></i>
                    <p class="font-bold text-slate-700 text-sm">Belum ada riwayat enrollment yang sesuai filter.</p>
                    <p class="text-xs text-slate-400 mt-1">Siswa ini belum memiliki catatan mutasi atau promosi kelas.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ==================== TAB 2: IDENTITAS & RIWAYAT MASUK PERMANEN ==================== --}}
    <div x-show="activeTab === 'identity'" class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Card 1: Riwayat Masuk Permanen (Immutable Anchors) --}}
        <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100 space-y-6">
            <div class="pb-4 border-b border-slate-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003FA9] flex items-center justify-center text-xl font-bold">
                    <i class='bx bx-award'></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Riwayat Pendaftaran & Masuk Permanen</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Informasi autentik pertama kali siswa diterima di sekolah</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <div class="p-4 rounded-2xl bg-[#EFF5FF] flex items-center justify-between">
                    <div>
                        <span class="text-slate-500 block font-medium">Tahun Ajaran Masuk Permanen</span>
                        <span class="text-lg font-extrabold text-[#003FA9] tracking-tight">
                            {{ $siswa->tahun_ajaran_masuk_nama }}
                        </span>
                    </div>
                    <span class="px-3 py-1 rounded-lg bg-[#003FA9] text-white font-bold text-xs">
                        Permanen
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block font-medium">Tahun Masuk Kalender</span>
                        <span class="font-bold text-slate-900 text-sm font-mono">{{ $siswa->tahun_masuk }}</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block font-medium">Jalur Pendaftaran</span>
                        <span class="font-bold text-slate-900 text-sm">
                            {{ $siswa->jalur_masuk === 'pindahan' ? 'Siswa Pindahan / Mutasi' : 'Siswa Baru (Reguler)' }}
                        </span>
                    </div>
                </div>

                @if($siswa->jalur_masuk === 'pindahan')
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200">
                        <span class="text-amber-800 block font-medium">Asal Sekolah Sebelumnya</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $siswa->asal_sekolah ?: '-' }}</span>
                    </div>
                @endif

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block font-medium">Jurusan Resmi (Program Keahlian)</span>
                    <span class="font-bold text-slate-900 text-sm">
                        {{ $siswa->kelas?->jurusan?->nama_jurusan ?? ($siswa->angkatan?->jurusan?->nama_jurusan ?? '-') }}
                        ({{ $siswa->kelas?->jurusan?->kode_jurusan ?? ($siswa->angkatan?->jurusan?->kode_jurusan ?? '-') }})
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block font-medium">Angkatan Kohort Resmi</span>
                    <span class="font-bold text-slate-900 text-sm">
                        {{ $siswa->angkatan?->nama_angkatan ?? '-' }}
                        <span class="text-slate-400 font-normal">(Kode: {{ $siswa->angkatan?->kode_angkatan ?? '-' }})</span>
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block font-medium">Catatan Pendaftaran Awal</span>
                    <span class="text-slate-800 font-medium">{{ $siswa->catatan_status ?: 'Terdaftar resmi sebagai siswa baru.' }}</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Biodata Diri Siswa & Data Kontak Wali --}}
        <div class="space-y-6">
            {{-- Biodata Pribadi --}}
            <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100 space-y-4">
                <div class="pb-4 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003FA9] flex items-center justify-center text-xl font-bold">
                        <i class='bx bx-user'></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Biodata Pribadi Siswa</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Identitas kependudukan dan status siswa</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Tempat, Tanggal Lahir (TTL)</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $siswa->ttl ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Jenis Kelamin</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Nomor WhatsApp Siswa</span>
                        <span class="font-bold text-slate-900 font-mono text-sm">{{ $siswa->no_telp_siswa ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Status Akademik</span>
                        <span class="font-bold text-sm {{ $siswa->status === 'aktif' ? 'text-emerald-600' : 'text-slate-800' }}">
                            {{ ucfirst($siswa->status) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Biometrik Wajah</span>
                        <span class="font-bold text-sm {{ $siswa->biometric_status === 'enrolled' ? 'text-emerald-600' : 'text-slate-600' }}">
                            {{ $siswa->biometric_status === 'enrolled' ? 'Terdaftar' : 'Belum Terdaftar' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Pengecualian Wajah</span>
                        <span class="font-bold text-sm {{ $siswa->is_biometric_exempt ? 'text-[#003FA9]' : 'text-slate-600' }}">
                            {{ $siswa->is_biometric_exempt ? 'Ya (Bebas)' : 'Tidak (Wajib)' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Informasi Orang Tua / Wali --}}
            @php $parent = $siswa->orangtua->first(); @endphp
            <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100 space-y-4">
                <div class="pb-4 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003FA9] flex items-center justify-center text-xl font-bold">
                        <i class='bx bx-group'></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Data Orang Tua / Wali</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Kontak utama monitoring presensi & akademik</p>
                    </div>
                </div>

                @if($parent)
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 block font-medium">Nama Lengkap Orang Tua / Wali</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $parent->nama }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-slate-400 block font-medium">Nomor WhatsApp</span>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $parent->telepon) }}" target="_blank"
                                   class="font-bold text-[#003FA9] hover:underline font-mono text-sm block">
                                    {{ $parent->telepon }}
                                </a>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-medium">Hubungan</span>
                                <span class="font-bold text-slate-900 text-sm">{{ $parent->hubungan ?: 'Orang Tua' }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Alamat Tempat Tinggal</span>
                            <span class="font-medium text-slate-800 text-sm">{{ $parent->alamat ?: '-' }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-slate-50 text-slate-400 text-xs text-center font-medium">
                        Belum ada data orang tua / wali yang ditautkan ke siswa ini.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ==================== TAB 3: LOG PERUBAHAN AKADEMIK ==================== --}}
    <div x-show="activeTab === 'movements'" class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100">
        <div class="pb-4 border-b border-slate-100 mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight">Log Aktivitas & Perubahan Akademik</h3>
                <p class="text-xs text-slate-400 mt-0.5">Jejak audit kronologis mutasi, kenaikan kelas, dan perubahan status akademik</p>
            </div>
            <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                Total: {{ $statusHistories->count() }} Catatan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">WAKTU</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">JENIS PERUBAHAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ROMBEL ASAL</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ROMBEL TUJUAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ALASAN / CATATAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">AKTOR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($statusHistories as $hist)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4 font-mono text-slate-500 whitespace-nowrap text-xs">
                                {{ $hist->tanggal_perubahan ? $hist->tanggal_perubahan->format('d/m/Y') : ($hist->created_at ? $hist->created_at->format('d/m/Y') : '-') }}
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap font-bold text-slate-800">
                                {{ $hist->jenis_perubahan_label }}
                            </td>
                            <td class="py-4 px-4">
                                @if($hist->kelasAsal || $hist->tahun_ajaran_asal)
                                    <div class="font-semibold text-slate-800">{{ $hist->kelasAsal?->nama_kelas ?? '-' }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">TA: {{ $hist->tahun_ajaran_asal ?? '-' }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($hist->kelasTujuan || $hist->tahun_ajaran_tujuan)
                                    <div class="font-semibold text-slate-800">{{ $hist->kelasTujuan?->nama_kelas ?? '-' }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">TA: {{ $hist->tahun_ajaran_tujuan ?? '-' }}</div>
                                @elseif($hist->sekolah_tujuan)
                                    <span class="font-bold text-rose-600">{{ $hist->sekolah_tujuan }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-slate-700 font-medium">{{ $hist->alasan ?: '-' }}</div>
                                @if($hist->catatan)
                                    <div class="text-xs text-slate-400 italic mt-0.5">{{ $hist->catatan }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap text-slate-600 font-medium text-xs">
                                {{ $hist->createdBy?->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <i class='bx bx-history text-2xl text-slate-400'></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600">Belum ada catatan log</p>
                                    <p class="text-xs text-slate-400 mt-1">Belum ada catatan log perubahan akademik siswa ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('modals')
    {{-- Modal Edit Siswa (Design System) --}}
    <div id="modal-editsiswa" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
            onclick="closeModal('modal-editsiswa')"></div>

        {{-- Modal Positioning Container --}}
        <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
            <div class="relative w-full max-w-3xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-slate-100 my-auto transform transition-all text-slate-800">
                {{-- Header --}}
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Data Siswa</h2>
                        <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                            Perbarui Profil, Akun & Relasi Orang Tua
                        </span>
                    </div>
                    <button type="button" onclick="closeModal('modal-editsiswa')"
                        class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                {{-- Body (Loaded via AJAX) --}}
                <div id="loadeditform"></div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function editSiswa(id) {
        fetch('/siswa/edit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('loadeditform').innerHTML = html;
            openModal('modal-editsiswa');

            const formObj = document.getElementById('frmEditSiswa');
            if (formObj) attachEditFormListener(formObj);
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: 'Terjadi kesalahan saat memuat form edit.',
                customClass: {
                    popup: 'font-inter'
                }
            });
        });
    }

    function attachEditFormListener(form) {
        form.addEventListener('submit', function(e) {
            const nis = document.getElementById('nis_edit')?.value;
            const nama = document.getElementById('nama_edit')?.value;

            if (!nis || !nama) {
                e.preventDefault();
                Swal.fire({
                    title: 'Oops!',
                    text: 'Data wajib Harus Diisi!',
                    icon: 'warning',
                    confirmButtonColor: '#003FA9',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        popup: 'font-inter rounded-2xl',
                        confirmButton: 'rounded-xl'
                    }
                });
            }
        });
    }

    function previewEditStudentPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById('preview_edit_photo_container');
                if (container) {
                    container.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">`;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
