@extends('layouts.app')

@section('title', 'Detail Riwayat Kenaikan Kelas - ' . $batch->batch_code)
@section('page-title', 'Detail Eksekusi Batch')
@section('page-subtitle', 'Laporan Audit Kenaikan Kelas, Tinggal Kelas, dan Kelulusan Siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400 text-sm'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-[#003EA7]">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-slate-400 text-sm'></i></li>
    <li><a href="{{ route('academic.promotion.index') }}" class="hover:text-[#003EA7]">Kenaikan Kelas</a></li>
    <li><i class='bx bx-chevron-right text-slate-400 text-sm'></i></li>
    <li><a href="{{ route('academic.promotion.history') }}" class="hover:text-[#003EA7]">Riwayat</a></li>
    <li><i class='bx bx-chevron-right text-slate-400 text-sm'></i></li>
    <li class="text-[#003EA7] font-medium">{{ $batch->batch_code }}</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-2.5 py-1 rounded-md bg-blue-100 text-[#003EA7] font-mono text-xs font-bold">{{ $batch->batch_code }}</span>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                    {{ $batch->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    {{ $batch->status }}
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight mt-1">Laporan Audit Perpindahan Tahun Ajaran</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Eksekusi dari <strong class="text-slate-700">{{ $batch->tahunAjaranAsal?->nama ?? '-' }}</strong> menuju <strong class="text-[#003EA7]">{{ $batch->tahunAjaranTujuan?->nama ?? '-' }}</strong>.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('academic.promotion.history') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold shadow-sm transition-all">
                <i class='bx bx-arrow-back text-base'></i>
                <span>Kembali ke Riwayat</span>
            </a>
            <a href="{{ route('academic.promotion.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#003EA7] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all">
                <i class='bx bx-plus text-base'></i>
                <span>Wizard Baru</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa</span>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $batch->total_siswa }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Naik Kelas</span>
            <p class="text-2xl font-bold text-blue-700 mt-1">{{ $batch->total_naik }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Tinggal Kelas</span>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ $batch->total_tinggal }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Kelulusan</span>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ $batch->total_lulus }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider">Mutasi Pindah</span>
            <p class="text-2xl font-bold text-purple-700 mt-1">{{ $batch->total_pindah }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Keluar / DO</span>
            <p class="text-2xl font-bold text-rose-700 mt-1">{{ $batch->total_keluar }}</p>
        </div>
    </div>

    {{-- METADATA CARD --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
        <div>
            <span class="text-slate-400 font-semibold">Admin Pelaksana:</span>
            <p class="font-bold text-slate-800 mt-0.5">{{ $batch->creator?->name ?? 'Administrator' }}</p>
            <p class="text-[11px] text-slate-500">{{ $batch->creator?->email ?? '-' }}</p>
        </div>
        <div>
            <span class="text-slate-400 font-semibold">Waktu Eksekusi:</span>
            <p class="font-bold text-slate-800 mt-0.5">{{ $batch->created_at->translatedFormat('l, d F Y - H:i:s') }}</p>
            <p class="text-[11px] text-slate-500 font-mono">{{ $batch->created_at->diffForHumans() }}</p>
        </div>
        <div>
            <span class="text-slate-400 font-semibold">Catatan Berita Acara:</span>
            <p class="font-medium text-slate-700 mt-0.5">{{ $batch->catatan ?: 'Tidak ada catatan berita acara khusus.' }}</p>
        </div>
    </div>

    {{-- PROCESSED STUDENTS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" x-data="{ search: '', filterStatus: '' }">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class='bx bx-list-check text-primary-600 text-lg'></i>
                <span>Daftar Siswa yang Diproses ({{ $batch->items->count() }} Data)</span>
            </h3>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <i class='bx bx-search absolute left-3 top-2.5 text-slate-400 text-xs'></i>
                    <input type="text" x-model="search" placeholder="Cari nama, NIS, rombel..."
                        class="pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white focus:ring-1 focus:ring-[#003EA7] w-48 sm:w-56">
                </div>
                <select x-model="filterStatus" class="px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 bg-white">
                    <option value="">Semua Status</option>
                    <option value="naik_kelas">Naik Kelas</option>
                    <option value="tinggal_kelas">Tinggal Kelas</option>
                    <option value="lulus">Lulus</option>
                    <option value="pindah">Mutasi Pindah</option>
                    <option value="keluar">Keluar / DO</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">IDENTITAS SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KOHORT PERMANEN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KEPUTUSAN STATUS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ROMBEL ASAL</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ROMBEL TUJUAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">PERUBAHAN TINGKAT</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">HASIL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($batch->items as $idx => $item)
                        <tr x-show="(!search || '{{ strtolower(addslashes($item->siswa?->nama ?? '')) }} {{ $item->siswa?->nis ?? '' }} {{ strtolower(addslashes($item->kelasAsal?->nama_kelas ?? '')) }} {{ strtolower(addslashes($item->kelasTujuan?->nama_kelas ?? '')) }}'.includes(search.toLowerCase())) && (!filterStatus || '{{ $item->action }}' === filterStatus)"
                            class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4 text-center text-slate-400 text-xs font-medium">{{ $idx + 1 }}</td>
                            <td class="py-4 px-4">
                                <a href="{{ route('siswa.show', $item->siswa_id) }}" class="font-bold text-slate-900 hover:text-[#003FA9] transition block text-xs sm:text-sm">
                                    {{ $item->siswa?->nama ?? '-' }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    NIS: {{ $item->siswa?->nis ?? '-' }} | NISN: {{ $item->siswa?->nisn ?? '-' }}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700">
                                    {{ $item->siswa?->angkatan?->nama_angkatan ?? ($item->siswa?->tahun_masuk ? 'Angkatan ' . $item->siswa?->tahun_masuk : '-') }}
                                </span>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    Masuk: {{ $item->siswa?->tahun_ajaran_masuk ?? $item->siswa?->tahun_masuk ?? '-' }}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @if($item->action === 'naik_kelas')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#003FA9] text-white">
                                        Naik Kelas
                                    </span>
                                @elseif($item->action === 'tinggal_kelas')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white">
                                        Tinggal Kelas
                                    </span>
                                @elseif($item->action === 'lulus')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white">
                                        Lulus (Alumni)
                                    </span>
                                @elseif($item->action === 'pindah')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-600 text-white">
                                        Mutasi Pindah
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-600 text-white">
                                        Keluar / DO
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-semibold text-slate-800 text-xs sm:text-sm">{{ $item->kelasAsal?->nama_kelas ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                @if($item->kelasTujuan)
                                    <span class="font-bold text-[#003FA9] text-xs sm:text-sm font-mono">{{ $item->kelasTujuan->nama_kelas }}</span>
                                @else
                                    <span class="text-slate-400 text-xs italic">- (Tidak dialokasikan)</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 font-mono text-xs">
                                @if($item->tingkat_tujuan)
                                    <span class="text-slate-600">Tingkat {{ $item->tingkat_asal }}</span>
                                    <i class='bx bx-right-arrow-alt text-slate-400 align-middle'></i>
                                    <strong class="text-emerald-700">Tingkat {{ $item->tingkat_tujuan }}</strong>
                                @else
                                    <span class="text-slate-500">Tingkat {{ $item->tingkat_asal }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                    <i class='bx bx-check-circle text-base'></i>
                                    <span>Berhasil</span>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class='bx bx-folder-open text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Tidak Ada Item Siswa</p>
                                <p class="text-xs text-slate-400 mt-0.5">Tidak ada item siswa tercatat dalam batch ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
