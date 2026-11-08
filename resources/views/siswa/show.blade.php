@extends('layouts.app')

@section('title', 'Profil & Riwayat Akademik: ' . $siswa->nama)
@section('page-title', 'Riwayat Akademik Siswa')
@section('page-subtitle', 'Jejak rekam kronologis penempatan kelas, tahun ajaran, dan status akademik siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('siswa') }}" class="hover:text-primary-600 transition">Data Siswa</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">{{ $siswa->nama }}</li>
@endsection

@section('content')
<div x-data="{ activeTab: 'timeline' }" class="space-y-6">

    {{-- Top Header Card: Summary & Quick Actions --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-gradient-to-r from-white via-blue-50/20 to-white shadow-sm">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            {{-- Left: Photo & Key Identity --}}
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#003EA7] to-[#002B75] text-white flex items-center justify-center font-bold text-2xl flex-shrink-0 shadow-lg shadow-blue-500/20 overflow-hidden border-2 border-white">
                    @if ($siswa->photo_url)
                        <img src="{{ $siswa->photo_url }}" alt="{{ $siswa->nama }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div class="flex items-center flex-wrap gap-2.5">
                        <h1 class="text-2xl font-bold text-dark-900 tracking-tight">{{ $siswa->nama }}</h1>
                        {{-- Status Badge --}}
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            @if($siswa->status === 'aktif') bg-emerald-50 text-emerald-700 border border-emerald-200
                            @elseif($siswa->status === 'lulus') bg-purple-50 text-purple-700 border border-purple-200
                            @elseif($siswa->status === 'pindah') bg-rose-50 text-rose-700 border border-rose-200
                            @else bg-slate-50 text-slate-700 border border-slate-200 @endif">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                @if($siswa->status === 'aktif') bg-emerald-500
                                @elseif($siswa->status === 'lulus') bg-purple-500
                                @elseif($siswa->status === 'pindah') bg-rose-500
                                @else bg-slate-400 @endif"></span>
                            {{ ucfirst($siswa->status) }}
                        </span>

                        {{-- Gender Badge --}}
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                            {{ $siswa->jenis_kelamin === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                            {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </div>

                    {{-- Sub-details row --}}
                    <div class="flex items-center flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-dark-500 font-mono">
                        <div>NIS: <span class="font-bold text-dark-800">{{ $siswa->nis }}</span></div>
                        <span>•</span>
                        <div>NISN: <span class="font-bold text-dark-800">{{ $siswa->nisn ?? '-' }}</span></div>
                        <span>•</span>
                        <div>Email: <span class="font-semibold text-primary-600">{{ $siswa->user?->email ?? '-' }}</span></div>
                    </div>

                    {{-- Key Academic Anchors --}}
                    <div class="flex items-center flex-wrap gap-2 mt-3">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50/80 text-[#003FA9] border border-blue-200 text-xs font-semibold" title="Tahun Ajaran Masuk Permanen">
                            <i class='bx bx-calendar-check text-sm'></i>
                            Masuk TA: {{ $siswa->tahun_ajaran_masuk_nama }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold" title="Angkatan Resmi Jurusan">
                            <i class='bx bx-group text-sm'></i>
                            {{ $siswa->angkatan?->nama_angkatan ?? ('Angkatan ' . ($siswa->tahun_masuk ?? '-')) }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold" title="Jurusan">
                            <i class='bx bx-book-bookmark text-sm'></i>
                            {{ $siswa->kelas?->jurusan?->nama_jurusan ?? ($siswa->angkatan?->jurusan?->nama_jurusan ?? 'Semua Jurusan') }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold" title="Rombel Saat Ini">
                            <i class='bx bx-door-open text-sm'></i>
                            {{ $siswa->kelas?->nama_kelas ?? 'Belum ada kelas' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-2.5 w-full md:w-auto justify-end">
                <a href="{{ route('siswa') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 text-xs font-semibold transition">
                    <i class='bx bx-arrow-back text-base'></i>
                    Kembali
                </a>
                @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                    <button onclick="editSiswa('{{ $siswa->id }}')"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-xs font-semibold shadow-md shadow-primary-500/20 transition">
                        <i class='bx bx-edit text-base'></i>
                        Edit Siswa
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Tab Navigation Buttons --}}
    <div class="flex items-center gap-2 border-b border-dark-200 pb-1">
        <button type="button"
                @click="activeTab = 'timeline'"
                :class="activeTab === 'timeline'
                    ? 'border-[#003FA9] text-[#003FA9] font-bold bg-blue-50/50'
                    : 'border-transparent text-dark-500 hover:text-dark-800 hover:bg-dark-50'"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-b-2 text-sm font-semibold transition-all">
            <i class='bx bx-git-commit text-lg'></i>
            Riwayat Akademik (Timeline)
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-blue-100 text-[#003FA9]">
                {{ $enrollments->count() }}
            </span>
        </button>

        <button type="button"
                @click="activeTab = 'identity'"
                :class="activeTab === 'identity'
                    ? 'border-[#003FA9] text-[#003FA9] font-bold bg-blue-50/50'
                    : 'border-transparent text-dark-500 hover:text-dark-800 hover:bg-dark-50'"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-b-2 text-sm font-semibold transition-all">
            <i class='bx bx-id-card text-lg'></i>
            Identitas & Riwayat Masuk Permanen
        </button>

        <button type="button"
                @click="activeTab = 'movements'"
                :class="activeTab === 'movements'
                    ? 'border-[#003FA9] text-[#003FA9] font-bold bg-blue-50/50'
                    : 'border-transparent text-dark-500 hover:text-dark-800 hover:bg-dark-50'"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-b-2 text-sm font-semibold transition-all">
            <i class='bx bx-history text-lg'></i>
            Log Perubahan Akademik
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-dark-100 text-dark-700">
                {{ $statusHistories->count() }}
            </span>
        </button>
    </div>

    {{-- ==================== TAB 1: ACADEMIC TIMELINE ==================== --}}
    <div x-show="activeTab === 'timeline'" class="space-y-6">

        {{-- Filter Bar for Timeline --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-dark-50/40">
            <form action="{{ route('siswa.show', $siswa->id) }}" method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="timeline">

                {{-- Filter Tahun Ajaran --}}
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Ajaran</label>
                    <select name="ta" class="w-full h-10 px-3 rounded-xl bg-white border border-dark-200 text-xs font-medium focus:ring-2 focus:ring-[#003FA9]/30 outline-none">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->nama }}" {{ $selectedTa == $ta->nama ? 'selected' : '' }}>
                                {{ $ta->nama }} {{ $ta->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tingkat --}}
                <div class="w-32">
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Tingkat</label>
                    <select name="tingkat" class="w-full h-10 px-3 rounded-xl bg-white border border-dark-200 text-xs font-medium focus:ring-2 focus:ring-[#003FA9]/30 outline-none">
                        <option value="">Semua</option>
                        <option value="10" {{ $selectedTingkat == '10' ? 'selected' : '' }}>Kelas 10</option>
                        <option value="11" {{ $selectedTingkat == '11' ? 'selected' : '' }}>Kelas 11</option>
                        <option value="12" {{ $selectedTingkat == '12' ? 'selected' : '' }}>Kelas 12</option>
                    </select>
                </div>

                {{-- Filter Status Enrollment --}}
                <div class="w-40">
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Status Enrollment</label>
                    <select name="status" class="w-full h-10 px-3 rounded-xl bg-white border border-dark-200 text-xs font-medium focus:ring-2 focus:ring-[#003FA9]/30 outline-none">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ $selectedStatus == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="selesai" {{ $selectedStatus == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="tinggal_kelas" {{ $selectedStatus == 'tinggal_kelas' ? 'selected' : '' }}>Tinggal Kelas</option>
                        <option value="lulus" {{ $selectedStatus == 'lulus' ? 'selected' : '' }}>Lulus</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="h-10 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold transition">
                        Terapkan
                    </button>
                    @if($selectedTa || $selectedTingkat || $selectedStatus)
                        <a href="{{ route('siswa.show', $siswa->id) }}" class="h-10 px-3 rounded-xl border border-dark-200 bg-white hover:bg-dark-50 text-dark-600 text-xs font-semibold flex items-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Chronological Vertical Timeline --}}
        <div class="relative pl-6 sm:pl-8 border-l-2 border-blue-200 ml-4 sm:ml-6 space-y-8 my-4">
            @forelse($enrollments as $enr)
                <div class="relative group">
                    {{-- Node Bullet --}}
                    <div class="absolute -left-[33px] sm:-left-[41px] top-1.5 w-6 h-6 rounded-full border-2 bg-white flex items-center justify-center
                        {{ $enr->is_active ? 'border-emerald-500 text-emerald-600 ring-4 ring-emerald-100 shadow-md' : 'border-blue-400 text-blue-500' }}">
                        @if($enr->is_active)
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        @else
                            <i class='bx bx-check text-sm font-bold'></i>
                        @endif
                    </div>

                    {{-- Timeline Card --}}
                    <div class="glass-card rounded-2xl border p-5 transition-all duration-200
                        {{ $enr->is_active ? 'border-emerald-300 bg-emerald-50/15 shadow-sm ring-1 ring-emerald-200' : 'border-dark-200/70 bg-white hover:border-blue-200 hover:shadow-sm' }}"
                         x-data="{ showDetail: false }">

                        {{-- Card Header --}}
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-dark-100">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase tracking-wider
                                    {{ $enr->is_active ? 'bg-emerald-600 text-white' : 'bg-[#003FA9] text-white' }}">
                                    Tahun Ajaran {{ $enr->tahun_ajaran }}
                                </span>
                                <span class="text-sm font-bold text-dark-800">
                                    Kelas {{ $enr->tingkat }}
                                </span>
                                @if($enr->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mr-1.5"></span>
                                        Enrollment Aktif Berjalan
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $enr->status_badge }}">
                                    {{ $enr->status_label }}
                                </span>
                                <button type="button"
                                        @click="showDetail = !showDetail"
                                        class="text-xs text-primary-600 hover:text-primary-800 font-semibold inline-flex items-center gap-1 transition">
                                    <span x-text="showDetail ? 'Tutup Detail' : 'Buka Detail'"></span>
                                    <i class='bx text-sm' :class="showDetail ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Card Grid Details --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 pt-4 text-xs">
                            <div>
                                <span class="text-dark-400 block mb-0.5 font-medium">Rombongan Belajar (Rombel)</span>
                                <span class="text-dark-900 font-bold text-sm">
                                    {{ $enr->kelas?->nama_kelas ?? '-' }}
                                </span>
                            </div>

                            <div>
                                <span class="text-dark-400 block mb-0.5 font-medium">Wali Kelas Pada Periode Ini</span>
                                <span class="text-dark-900 font-semibold flex items-center gap-1">
                                    <i class='bx bx-user-voice text-primary-600'></i>
                                    {{ $enr->wali_kelas_nama }}
                                </span>
                            </div>

                            <div>
                                <span class="text-dark-400 block mb-0.5 font-medium">Jurusan & Kohort</span>
                                <span class="text-dark-800 font-medium">
                                    {{ $enr->jurusan?->kode_jurusan ?? ($siswa->kelas?->jurusan?->kode_jurusan ?? '-') }}
                                    • {{ $enr->angkatan?->nama_angkatan ?? ($siswa->angkatan?->nama_angkatan ?? '-') }}
                                </span>
                            </div>

                            <div>
                                <span class="text-dark-400 block mb-0.5 font-medium">Rentang Tanggal Penempatan</span>
                                <span class="text-dark-700 font-mono">
                                    {{ $enr->started_at ? $enr->started_at->format('d/m/Y') : '-' }} s.d
                                    {{ $enr->ended_at ? $enr->ended_at->format('d/m/Y') : ($enr->is_active ? 'Sekarang' : '-') }}
                                </span>
                            </div>
                        </div>

                        {{-- Expandable Detail Drawer --}}
                        <div x-show="showDetail" x-collapse class="mt-4 pt-4 border-t border-dark-100 bg-dark-50/50 rounded-xl p-4 space-y-2">
                            <h4 class="text-xs font-bold text-dark-800 uppercase tracking-wider mb-2">Informasi Lengkap Penempatan</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div>
                                    <span class="text-dark-400 block">ID Enrollment:</span>
                                    <span class="font-mono text-dark-700 font-semibold">ENR-{{ str_pad($enr->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div>
                                    <span class="text-dark-400 block">Status Operasional:</span>
                                    <span class="font-semibold text-dark-800">{{ $enr->is_active ? 'Sedang Berjalan (Active)' : 'Selesai / Diarsipkan' }}</span>
                                </div>
                                <div>
                                    <span class="text-dark-400 block">Dicatat Pada:</span>
                                    <span class="text-dark-700">{{ $enr->created_at ? $enr->created_at->translatedFormat('d F Y H:i') : '-' }}</span>
                                </div>
                            </div>
                            @if($enr->catatan)
                                <div class="mt-2 pt-2 border-t border-dark-200/50 text-xs">
                                    <span class="text-dark-400 font-semibold">Catatan Histori:</span>
                                    <p class="text-dark-700 italic mt-0.5">{{ $enr->catatan }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="glass-card rounded-2xl border border-dark-200 p-8 text-center text-dark-500">
                    <i class='bx bx-folder-open text-4xl mb-2 text-dark-400'></i>
                    <p class="font-semibold">Belum ada riwayat enrollment yang sesuai filter.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ==================== TAB 2: IDENTITAS PERMANEN ==================== --}}
    <div x-show="activeTab === 'identity'" class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Card 1: Riwayat Masuk Permanen (Immutable Anchors) --}}
        <div class="glass-card rounded-2xl border border-blue-200/70 p-6 bg-white shadow-sm space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-dark-100">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003FA9] flex items-center justify-center text-xl font-bold">
                    <i class='bx bx-award'></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-dark-900">Identitas & Riwayat Masuk Permanen</h3>
                    <p class="text-xs text-dark-400">Informasi autentik pertama kali siswa diterima di SMK Negeri 1 Ciomas</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                {{-- Tahun Ajaran Masuk Permanen --}}
                <div class="p-3.5 rounded-xl bg-blue-50/60 border border-blue-200 flex items-center justify-between">
                    <div>
                        <span class="text-dark-500 block font-medium">Tahun Ajaran Masuk Permanen</span>
                        <span class="text-base font-extrabold text-[#003FA9] tracking-tight">
                            {{ $siswa->tahun_ajaran_masuk_nama }}
                        </span>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-[#003FA9] text-white font-bold text-xs">
                        Permanen
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-dark-50/60 border border-dark-100">
                        <span class="text-dark-400 block font-medium">Tahun Masuk Kalender</span>
                        <span class="font-bold text-dark-900 text-sm font-mono">{{ $siswa->tahun_masuk }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-dark-50/60 border border-dark-100">
                        <span class="text-dark-400 block font-medium">Jalur Pendaftaran</span>
                        <span class="font-bold text-dark-900 text-sm">
                            {{ $siswa->jalur_masuk === 'pindahan' ? 'Siswa Pindahan / Mutasi' : 'Siswa Baru (Reguler)' }}
                        </span>
                    </div>
                </div>

                @if($siswa->jalur_masuk === 'pindahan')
                    <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-200">
                        <span class="text-amber-700 block font-medium">Asal Sekolah Sebelumnya</span>
                        <span class="font-bold text-dark-900 text-sm">{{ $siswa->asal_sekolah ?: '-' }}</span>
                    </div>
                @endif

                <div class="p-3 rounded-xl bg-dark-50/60 border border-dark-100">
                    <span class="text-dark-400 block font-medium">Jurusan Resmi (Program Keahlian)</span>
                    <span class="font-bold text-dark-900 text-sm">
                        {{ $siswa->kelas?->jurusan?->nama_jurusan ?? ($siswa->angkatan?->jurusan?->nama_jurusan ?? '-') }}
                        ({{ $siswa->kelas?->jurusan?->kode_jurusan ?? ($siswa->angkatan?->jurusan?->kode_jurusan ?? '-') }})
                    </span>
                </div>

                <div class="p-3 rounded-xl bg-dark-50/60 border border-dark-100">
                    <span class="text-dark-400 block font-medium">Angkatan Kohort Resmi</span>
                    <span class="font-bold text-dark-900 text-sm">
                        {{ $siswa->angkatan?->nama_angkatan ?? '-' }}
                        <span class="text-dark-400 font-normal">(Kode: {{ $siswa->angkatan?->kode_angkatan ?? '-' }})</span>
                    </span>
                </div>

                <div class="p-3 rounded-xl bg-dark-50/60 border border-dark-100">
                    <span class="text-dark-400 block font-medium">Catatan Pendaftaran Awal</span>
                    <span class="text-dark-800">{{ $siswa->catatan_status ?: 'Terdaftar resmi sebagai siswa baru.' }}</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Biodata Pribadi & Kontak Wali --}}
        <div class="space-y-6">
            {{-- Biodata Pribadi --}}
            <div class="glass-card rounded-2xl border border-dark-200/70 p-6 bg-white shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-dark-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl font-bold">
                        <i class='bx bx-user'></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-dark-900">Biodata Pribadi Siswa</h3>
                        <p class="text-xs text-dark-400">Identitas kependudukan dan informasi akun</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-dark-400 block font-medium">Tempat, Tanggal Lahir (TTL)</span>
                        <span class="font-bold text-dark-900">{{ $siswa->ttl ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-dark-400 block font-medium">Jenis Kelamin</span>
                        <span class="font-bold text-dark-900">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                    <div>
                        <span class="text-dark-400 block font-medium">Nomor WhatsApp Siswa</span>
                        <span class="font-bold text-dark-900 font-mono">{{ $siswa->no_telp_siswa ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-dark-400 block font-medium">Status Biometrik Wajah</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold
                            {{ $siswa->biometric_status === 'enrolled' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $siswa->biometric_status === 'enrolled' ? 'Terdaftar' : 'Belum Terdaftar' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Informasi Orang Tua / Wali --}}
            @php $parent = $siswa->orangtua->first(); @endphp
            <div class="glass-card rounded-2xl border border-dark-200/70 p-6 bg-white shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-dark-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-xl font-bold">
                        <i class='bx bx-group'></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-dark-900">Data Orang Tua / Wali</h3>
                        <p class="text-xs text-dark-400">Kontak utama monitoring presensi & akademik</p>
                    </div>
                </div>

                @if($parent)
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-dark-400 block font-medium">Nama Lengkap Orang Tua / Wali</span>
                            <span class="font-bold text-dark-900 text-sm">{{ $parent->nama }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-dark-400 block font-medium">Nomor WhatsApp</span>
                                <span class="font-bold text-dark-900 font-mono flex items-center gap-1">
                                    <i class='bx bxl-whatsapp text-emerald-600 text-base'></i>
                                    {{ $parent->telepon ?? '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-dark-400 block font-medium">Hubungan / Gender</span>
                                <span class="font-bold text-dark-900">
                                    {{ $parent->jenis_kelamin === 'L' ? 'Laki-laki (Ayah)' : ($parent->jenis_kelamin === 'P' ? 'Perempuan (Ibu)' : '-') }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-dark-400 block font-medium">Alamat Tempat Tinggal</span>
                            <span class="text-dark-800">{{ $parent->alamat ?? '-' }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-dark-400 italic">Data orang tua belum dikaitkan.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ==================== TAB 3: LOG PERUBAHAN AKADEMIK ==================== --}}
    <div x-show="activeTab === 'movements'" class="space-y-4">

        {{-- Filter Bar for Movements --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-dark-50/40">
            <form action="{{ route('siswa.show', $siswa->id) }}" method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="movements">

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Jenis Perubahan</label>
                    <select name="jenis_perubahan" class="w-full h-10 px-3 rounded-xl bg-white border border-dark-200 text-xs font-medium focus:ring-2 focus:ring-[#003FA9]/30 outline-none">
                        <option value="">Semua Peristiwa Akademik</option>
                        <option value="registrasi_baru" {{ $selectedJenisPerubahan == 'registrasi_baru' ? 'selected' : '' }}>Registrasi Siswa Baru</option>
                        <option value="mutasi_masuk" {{ $selectedJenisPerubahan == 'mutasi_masuk' ? 'selected' : '' }}>Mutasi Masuk (Pindahan)</option>
                        <option value="kenaikan_kelas" {{ $selectedJenisPerubahan == 'kenaikan_kelas' ? 'selected' : '' }}>Kenaikan Kelas</option>
                        <option value="tinggal_kelas" {{ $selectedJenisPerubahan == 'tinggal_kelas' ? 'selected' : '' }}>Tinggal Kelas</option>
                        <option value="pindah_rombel" {{ $selectedJenisPerubahan == 'pindah_rombel' ? 'selected' : '' }}>Perpindahan Rombel</option>
                        <option value="kelulusan" {{ $selectedJenisPerubahan == 'kelulusan' ? 'selected' : '' }}>Kelulusan</option>
                        <option value="mutasi_keluar" {{ $selectedJenisPerubahan == 'mutasi_keluar' ? 'selected' : '' }}>Mutasi Keluar</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="h-10 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold transition">
                        Filter Log
                    </button>
                    @if($selectedJenisPerubahan)
                        <a href="{{ route('siswa.show', $siswa->id) }}" class="h-10 px-3 rounded-xl border border-dark-200 bg-white hover:bg-dark-50 text-dark-600 text-xs font-semibold flex items-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Audit Table --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-dark-100 bg-dark-50/50 text-[11px] font-bold text-dark-500 uppercase tracking-wider">
                            <th class="px-5 py-3.5">Tanggal</th>
                            <th class="px-5 py-3.5">Jenis Peristiwa</th>
                            <th class="px-5 py-3.5">Asal (Kelas & Wali)</th>
                            <th class="px-5 py-3.5">Tujuan (Kelas & Wali)</th>
                            <th class="px-5 py-3.5">Keterangan / Alasan</th>
                            <th class="px-5 py-3.5">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-100 text-xs text-dark-700">
                        @forelse($statusHistories as $hist)
                            <tr class="hover:bg-dark-50/40 transition">
                                <td class="px-5 py-3.5 font-mono text-dark-600 whitespace-nowrap">
                                    {{ $hist->tanggal_perubahan ? $hist->tanggal_perubahan->format('d/m/Y') : ($hist->created_at ? $hist->created_at->format('d/m/Y') : '-') }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold border {{ $hist->jenis_perubahan_badge }}">
                                        {{ $hist->jenis_perubahan_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($hist->kelasAsal || $hist->tahun_ajaran_asal)
                                        <div class="font-semibold text-dark-900">{{ $hist->kelasAsal?->nama_kelas ?? '-' }}</div>
                                        <div class="text-[11px] text-dark-400">TA: {{ $hist->tahun_ajaran_asal ?? '-' }} • Wali: {{ $hist->wali_kelas_asal ?? '-' }}</div>
                                    @else
                                        <span class="text-dark-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($hist->kelasTujuan || $hist->tahun_ajaran_tujuan)
                                        <div class="font-semibold text-dark-900">{{ $hist->kelasTujuan?->nama_kelas ?? '-' }}</div>
                                        <div class="text-[11px] text-dark-400">TA: {{ $hist->tahun_ajaran_tujuan ?? '-' }} • Wali: {{ $hist->wali_kelas_tujuan ?? '-' }}</div>
                                    @elseif($hist->sekolah_tujuan)
                                        <span class="font-semibold text-rose-700">{{ $hist->sekolah_tujuan }}</span>
                                    @else
                                        <span class="text-dark-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="text-dark-800">{{ $hist->alasan ?: '-' }}</div>
                                    @if($hist->catatan)
                                        <div class="text-[11px] text-dark-400 italic">{{ $hist->catatan }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-dark-500 font-medium">
                                    {{ $hist->createdBy?->name ?? 'Sistem' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-dark-400">
                                    Belum ada catatan log perubahan akademik.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
