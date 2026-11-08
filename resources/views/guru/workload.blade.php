@extends('layouts.app')

@section('title', 'Beban Kerja Guru')
@section('page-title', 'Beban Kerja Guru')
@section('page-subtitle', 'Pusat pemantauan dan analisis beban mengajar mingguan (JP, basis 45 menit) seluruh guru')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('guru') }}" class="text-dark-500 hover:text-primary-600 transition">Data Guru</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Beban Kerja Guru</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">Manajemen Beban Kerja Mengajar</h2>
            <p class="text-xs text-dark-400 mt-0.5">Pemantauan kepatuhan standar beban mengajar nasional (Target: 24 - 36 Jam Pelajaran / minggu).</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 text-xs font-semibold rounded-xl shadow-sm transition">
                <i class='bx bx-arrow-back text-base'></i>
                <span>Kembali ke Data Guru</span>
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                <i class='bx bx-printer text-base'></i>
                <span>Cetak Rekap Beban Kerja</span>
            </button>
        </div>
    </div>
@endsection

@section('content')
    {{-- Summary Statistics Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 mb-6">
        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white">
            <p class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Total Guru</p>
            <h3 class="text-2xl font-black text-dark-900 mt-1">{{ $total_teachers }}</h3>
            <p class="text-[10px] text-dark-400 mt-0.5">Guru aktif dianalisis</p>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white">
            <p class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Total JP Sekolah</p>
            <h3 class="text-2xl font-black text-primary-700 mt-1">{{ $total_jp_school }}</h3>
            <p class="text-[10px] text-dark-400 mt-0.5">JP terdistribusi / minggu</p>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white">
            <p class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Rata-rata JP</p>
            <h3 class="text-2xl font-black text-indigo-700 mt-1">{{ $average_jp }}</h3>
            <p class="text-[10px] text-dark-400 mt-0.5">JP per guru / minggu</p>
        </div>

        <div class="glass-card rounded-2xl border border-emerald-100 p-4 bg-emerald-50/40">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Target Terpenuhi</p>
            <h3 class="text-2xl font-black text-emerald-800 mt-1">{{ $optimal_count }}</h3>
            <p class="text-[10px] text-emerald-600 mt-0.5">24 - 36 JP / minggu</p>
        </div>

        <div class="glass-card rounded-2xl border border-amber-100 p-4 bg-amber-50/40">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Beban Kurang</p>
            <h3 class="text-2xl font-black text-amber-800 mt-1">{{ $underload_count }}</h3>
            <p class="text-[10px] text-amber-600 mt-0.5">&lt; 24 JP / minggu</p>
        </div>

        <div class="glass-card rounded-2xl border border-rose-100 p-4 bg-rose-50/40">
            <p class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Jadwal Padat</p>
            <h3 class="text-2xl font-black text-rose-800 mt-1">{{ $heavy_count }}</h3>
            <p class="text-[10px] text-rose-600 mt-0.5">&gt; 36 JP / minggu</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white mb-6 shadow-sm">
        <form action="{{ route('guru.workload') }}" method="GET" class="flex flex-col md:flex-row items-center gap-3">
            <div class="flex-1 w-full relative">
                <i class='bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-dark-400 text-lg'></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama guru, NIP, atau NUPTK..."
                    class="w-full text-xs rounded-xl border-dark-200 pl-10 pr-4 py-2.5 bg-dark-50/30 text-dark-800 focus:bg-white focus:border-primary-500 focus:ring-primary-500 transition">
            </div>

            <div class="w-full md:w-56">
                <select name="jurusan_id"
                    class="w-full text-xs rounded-xl border-dark-200 py-2.5 px-3 bg-dark-50/30 text-dark-800 focus:bg-white focus:border-primary-500 focus:ring-primary-500 transition">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ $jurusan_id == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-44">
                <input type="text" name="tahun_ajaran" value="{{ $tahun_ajaran }}" placeholder="Tahun Ajaran"
                    class="w-full text-xs rounded-xl border-dark-200 py-2.5 px-3 bg-dark-50/30 text-dark-800 focus:bg-white focus:border-primary-500 focus:ring-primary-500 transition">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit"
                    class="w-full md:w-auto px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition shadow-sm">
                    <i class='bx bx-filter-alt'></i>
                    <span>Filter</span>
                </button>
                @if($search || $jurusan_id)
                    <a href="{{ route('guru.workload') }}"
                        class="px-3 py-2.5 border border-dark-200 hover:bg-dark-50 text-dark-600 rounded-xl text-xs font-semibold transition" title="Reset Filter">
                        <i class='bx bx-reset text-base'></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Teacher Workload Analysis Table --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-dark-50/70 border-b border-dark-100 text-[11px] font-bold text-dark-500 uppercase tracking-wider">
                        <th class="px-5 py-4">Guru</th>
                        <th class="px-5 py-4">Penugasan & Peran</th>
                        <th class="px-5 py-4">Mata Pelajaran & Rombel</th>
                        <th class="px-5 py-4 text-center">Durasi Kerja</th>
                        <th class="px-5 py-4">Beban Jam Pelajaran (JP)</th>
                        <th class="px-5 py-4">Status Beban</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-100/60">
                    @forelse($workloads as $w)
                        <tr class="hover:bg-dark-50/40 transition">
                            {{-- Teacher Info --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-primary-100 flex-shrink-0 flex items-center justify-center text-xs font-bold text-primary-700">
                                        @if($w['guru']->photo_path && file_exists(public_path('storage/' . $w['guru']->photo_path)))
                                            <img src="{{ asset('storage/' . $w['guru']->photo_path) }}" alt="{{ $w['guru']->nama }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($w['guru']->nama, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('guru.show', $w['guru']->id) }}" class="font-bold text-dark-900 hover:text-primary-600 transition block">
                                            {{ $w['guru']->nama }}
                                        </a>
                                        <p class="text-[10px] text-dark-400">NIP: {{ $w['guru']->nip ?? '-' }} &bull; {{ $w['guru']->status_kepegawaian ?? 'Non-PNS' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Roles & Assignments --}}
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($w['is_homeroom'])
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Wali: {{ $w['homeroom_kelas']?->nama_kelas ?? 'Kelas' }}
                                        </span>
                                    @endif

                                    @if($w['is_kaprog'])
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            KAPROG {{ $w['kaprog_jurusan']?->kode_jurusan ?? '' }}
                                        </span>
                                    @endif

                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $w['total_subjects'] }} Mapel
                                    </span>
                                </div>
                            </td>

                            {{-- Subjects & Rombels --}}
                            <td class="px-5 py-4">
                                @if(count($w['subject_list']) > 0)
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach(array_slice($w['subject_list'], 0, 2) as $sName)
                                            <span class="px-2 py-0.5 bg-dark-100 text-dark-700 rounded-md text-[10px] font-medium">
                                                {{ $sName }}
                                            </span>
                                        @endforeach
                                        @if(count($w['subject_list']) > 2)
                                            <span class="px-1.5 py-0.5 bg-dark-200 text-dark-600 rounded-md text-[10px] font-bold">
                                                +{{ count($w['subject_list']) - 2 }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-dark-400 mt-1">{{ $w['total_classes'] }} rombongan belajar diajar</p>
                                @else
                                    <span class="text-dark-400 italic text-[11px]">Belum ada mapel di jadwal</span>
                                @endif
                            </td>

                            {{-- Clock Duration --}}
                            <td class="px-5 py-4 text-center">
                                <span class="font-bold text-dark-800 text-xs">{{ $w['total_clock_hours'] }} Jam</span>
                                <p class="text-[10px] text-dark-400">{{ $w['total_sessions'] }} sesi pertemuan</p>
                            </td>

                            {{-- JP Progress Bar --}}
                            <td class="px-5 py-4 min-w-[170px]">
                                <div class="flex items-baseline justify-between mb-1">
                                    <span class="text-sm font-extrabold text-dark-900">{{ $w['total_jp'] }} JP</span>
                                    <span class="text-[10px] font-semibold text-dark-400">Target: 24-36 JP</span>
                                </div>
                                <div class="w-full bg-dark-100 rounded-full h-2 overflow-hidden">
                                    @php
                                        $pct = min(100, ($w['total_jp'] / 36) * 100);
                                        $barColor = $w['status_category'] === 'optimal' ? 'bg-emerald-500' : ($w['status_category'] === 'underload' ? 'bg-amber-500' : 'bg-rose-500');
                                    @endphp
                                    <div class="h-2 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </td>

                            {{-- Workload Status Badge --}}
                            <td class="px-5 py-4">
                                @if($w['status_category'] === 'optimal')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 w-max">
                                        <i class='bx bx-check-circle'></i> Ideal ({{ $w['total_jp'] }} JP)
                                    </span>
                                @elseif($w['status_category'] === 'underload')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1 w-max">
                                        <i class='bx bx-time'></i> Kurang ({{ $w['total_jp'] }}/24 JP)
                                    </span>
                                @elseif($w['status_category'] === 'heavy')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 flex items-center gap-1 w-max">
                                        <i class='bx bx-error-circle'></i> Padat ({{ $w['total_jp'] }} JP)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-dark-100 text-dark-500 flex items-center gap-1 w-max">
                                        <i class='bx bx-minus'></i> Belum Ada Jadwal
                                    </span>
                                @endif
                            </td>

                            {{-- Action --}}
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('guru.show', $w['guru']->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-dark-50 hover:bg-primary-50 text-dark-700 hover:text-primary-700 rounded-xl text-xs font-semibold border border-dark-200 hover:border-primary-200 transition">
                                    <i class='bx bx-slider'></i>
                                    <span>Kelola</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-dark-400">
                                <i class='bx bx-pie-chart-alt-2 text-4xl mb-2 text-dark-300'></i>
                                <p class="text-sm font-medium">Tidak ada data guru yang sesuai dengan kriteria filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
