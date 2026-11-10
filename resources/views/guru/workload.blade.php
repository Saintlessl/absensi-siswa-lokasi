@extends('layouts.app')

@section('title', 'Beban Kerja Guru')
@section('page-title', 'Beban Kerja Guru')
@section('page-subtitle', 'Pusat pemantauan dan analisis beban mengajar mingguan (JP, basis 45 menit) seluruh guru')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('guru') }}" class="text-slate-500 hover:text-[#003FA9] transition">Data Guru</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Beban Kerja Guru</li>
@endsection

@section('content')
    {{-- Main Container Card --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100">
        {{-- Card Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Manajemen Beban Kerja Mengajar</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Pemantauan kepatuhan standar beban mengajar nasional (Target: 24 - 36 Jam Pelajaran / minggu).</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('guru') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    <i class='bx bx-arrow-back text-sm'></i>
                    <span>Kembali</span>
                </a>
                <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs shadow-sm transition">
                    <i class='bx bx-printer text-sm'></i>
                    <span>Cetak Rekap</span>
                </button>
            </div>
        </div>

        {{-- Summary Statistics Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 mb-6">
            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Guru</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $total_teachers }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">Guru aktif dianalisis</p>
            </div>

            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total JP Sekolah</p>
                <h3 class="text-2xl font-black text-[#003FA9] mt-1">{{ $total_jp_school }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">JP terdistribusi / minggu</p>
            </div>

            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Rata-rata JP</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $average_jp }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">JP per guru / minggu</p>
            </div>

            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Target Terpenuhi</p>
                <h3 class="text-2xl font-black text-emerald-700 mt-1">{{ $optimal_count }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">24 - 36 JP / minggu</p>
            </div>

            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Beban Kurang</p>
                <h3 class="text-2xl font-black text-amber-700 mt-1">{{ $underload_count }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">&lt; 24 JP / minggu</p>
            </div>

            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Jadwal Padat</p>
                <h3 class="text-2xl font-black text-rose-700 mt-1">{{ $heavy_count }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">&gt; 36 JP / minggu</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-[#EFF5FF] rounded-2xl p-4 mb-6">
            <form action="{{ route('guru.workload') }}" method="GET" class="flex flex-col md:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <i class='bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama guru, NIP, atau NUPTK..."
                        class="w-full text-xs rounded-xl border border-transparent pl-10 pr-4 py-2.5 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#003FA9] transition">
                </div>

                <div class="w-full md:w-56">
                    <select name="jurusan_id"
                        class="w-full text-xs rounded-xl border border-transparent py-2.5 px-3 bg-white text-slate-800 focus:outline-none focus:border-[#003FA9] transition cursor-pointer">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}" {{ $jurusan_id == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-44">
                    <input type="text" name="tahun_ajaran" value="{{ $tahun_ajaran }}" placeholder="Tahun Ajaran"
                        class="w-full text-xs rounded-xl border border-transparent py-2.5 px-3 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#003FA9] transition">
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <button type="submit"
                        class="w-full md:w-auto px-5 py-2.5 bg-[#003FA9] hover:bg-[#002D7A] text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition shadow-sm">
                        <i class='bx bx-filter-alt'></i>
                        <span>Filter</span>
                    </button>
                    @if($search || $jurusan_id)
                        <a href="{{ route('guru.workload') }}"
                            class="px-3.5 py-2.5 bg-white hover:bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold transition border border-slate-200" title="Reset Filter">
                            <i class='bx bx-reset text-base'></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Teacher Workload Analysis Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">GURU</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">PENUGASAN & PERAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">MATA PELAJARAN & ROMBEL</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">DURASI KERJA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">BEBAN JAM PELAJARAN (JP)</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">STATUS BEBAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right w-24">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($workloads as $w)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- Teacher Info --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-[#003FA9] text-white flex-shrink-0 flex items-center justify-center text-xs font-extrabold shadow-sm">
                                        @if($w['guru']->photo_path && file_exists(public_path('storage/' . $w['guru']->photo_path)))
                                            <img src="{{ asset('storage/' . $w['guru']->photo_path) }}" alt="{{ $w['guru']->nama }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($w['guru']->nama, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('guru.show', $w['guru']->id) }}" class="font-bold text-slate-900 hover:text-[#003FA9] transition block text-xs sm:text-sm">
                                            {{ $w['guru']->nama }}
                                        </a>
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                            NIP: {{ $w['guru']->nip ?? '-' }} &bull; {{ $w['guru']->status_kepegawaian ?? 'Non-PNS' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Roles & Assignments --}}
                            <td class="py-4 px-4">
                                <div class="flex flex-col gap-1 text-xs">
                                    @if($w['is_homeroom'])
                                        <span class="font-semibold text-emerald-700">
                                            <i class='bx bx-check-shield text-xs mr-1'></i>Wali Kelas {{ $w['homeroom_kelas']?->nama_kelas ?? '' }}
                                        </span>
                                    @endif

                                    @if($w['is_kaprog'])
                                        <span class="font-semibold text-[#003FA9]">
                                            <i class='bx bx-crown text-xs mr-1'></i>KAPROG {{ $w['kaprog_jurusan']?->kode_jurusan ?? '' }}
                                        </span>
                                    @endif

                                    <span class="text-slate-600 font-medium">
                                        {{ $w['total_subjects'] }} Mata Pelajaran
                                    </span>
                                </div>
                            </td>

                            {{-- Subjects & Rombels --}}
                            <td class="py-4 px-4">
                                @if(count($w['subject_list']) > 0)
                                    <div class="text-xs text-slate-800 font-medium max-w-xs space-y-0.5">
                                        @foreach(array_slice($w['subject_list'], 0, 2) as $sName)
                                            <div>&bull; {{ $sName }}</div>
                                        @endforeach
                                        @if(count($w['subject_list']) > 2)
                                            <div class="text-[11px] text-slate-400 font-semibold">+{{ count($w['subject_list']) - 2 }} mapel lainnya</div>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">{{ $w['total_classes'] }} rombel aktif</p>
                                @else
                                    <span class="text-slate-400 italic text-xs">Belum ada jadwal mengajar</span>
                                @endif
                            </td>

                            {{-- Clock Duration --}}
                            <td class="py-4 px-4 text-center">
                                <span class="font-extrabold text-slate-900 text-xs sm:text-sm">{{ $w['total_clock_hours'] }} Jam</span>
                                <p class="text-[11px] text-slate-400">{{ $w['total_sessions'] }} sesi pertemuan</p>
                            </td>

                            {{-- JP Progress Bar --}}
                            <td class="py-4 px-4 min-w-[170px]">
                                <div class="flex items-baseline justify-between mb-1.5">
                                    <span class="text-xs sm:text-sm font-extrabold text-slate-900">{{ $w['total_jp'] }} JP</span>
                                    <span class="text-[11px] font-semibold text-slate-400">Target: 24-36 JP</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    @php
                                        $pct = min(100, ($w['total_jp'] / 36) * 100);
                                        $barColor = $w['status_category'] === 'optimal' ? 'bg-emerald-600' : ($w['status_category'] === 'underload' ? 'bg-amber-500' : 'bg-rose-600');
                                    @endphp
                                    <div class="h-2 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </td>

                            {{-- Workload Status Badge --}}
                            <td class="py-4 px-4">
                                @if($w['status_category'] === 'optimal')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white flex items-center gap-1 w-max">
                                        <i class='bx bx-check-circle'></i> Ideal ({{ $w['total_jp'] }} JP)
                                    </span>
                                @elseif($w['status_category'] === 'underload')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white flex items-center gap-1 w-max">
                                        <i class='bx bx-time'></i> Kurang ({{ $w['total_jp'] }}/24 JP)
                                    </span>
                                @elseif($w['status_category'] === 'heavy')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-600 text-white flex items-center gap-1 w-max">
                                        <i class='bx bx-error-circle'></i> Padat ({{ $w['total_jp'] }} JP)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-400 text-white flex items-center gap-1 w-max">
                                        <i class='bx bx-minus'></i> Belum Ada Jadwal
                                    </span>
                                @endif
                            </td>

                            {{-- Action --}}
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('guru.show', $w['guru']->id) }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition inline-flex items-center gap-1 font-bold text-xs"
                                    title="Kelola Profil & Beban Kerja Guru">
                                    <i class='bx bx-slider text-lg'></i>
                                    <span>Kelola</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class='bx bx-pie-chart-alt-2 text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Tidak ada data beban kerja guru</p>
                                <p class="text-xs text-slate-400 mt-0.5">Silakan sesuaikan kriteria filter atau periode akademik.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
