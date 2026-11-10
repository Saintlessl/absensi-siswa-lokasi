@extends('layouts.app')

@section('title', 'Riwayat Kenaikan Kelas & Kelulusan')
@section('page-title', 'Riwayat Kenaikan Kelas')
@section('page-subtitle', 'Daftar Audit Batch Eksekusi Kenaikan Kelas, Tinggal Kelas, dan Kelulusan Massal')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400 text-sm'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-[#003EA7]">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-slate-400 text-sm'></i></li>
    <li><a href="{{ route('academic.promotion.index') }}" class="hover:text-[#003EA7]">Kenaikan Kelas</a></li>
    <li><i class='bx bx-chevron-right text-slate-400 text-sm'></i></li>
    <li class="text-[#003EA7] font-medium">Riwayat</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Riwayat Proses Kenaikan Kelas & Kelulusan</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Catatan historis seluruh batch perpindahan tahun ajaran, kenaikan kelas, dan penuntasan kelulusan alumni SMK Negeri 1 Ciomas.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('academic.promotion.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#003EA7] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all duration-200 hover:-translate-y-0.5">
                <i class='bx bx-plus-circle text-base'></i>
                <span>Mulai Kenaikan Kelas Baru</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KODE BATCH</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">PERIODE AKADEMIK</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">STATISTIK SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ADMIN PELAKSANA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL EKSEKUSI</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">STATUS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right w-20">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($batches as $index => $b)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4 text-center text-slate-400 text-xs font-medium">
                                {{ $batches->firstItem() + $index }}
                            </td>
                            <td class="py-4 px-4">
                                <a href="{{ route('academic.promotion.show', $b->id) }}" class="font-bold text-[#003FA9] hover:underline font-mono text-xs sm:text-sm block">
                                    {{ $b->batch_code }}
                                </a>
                                @if($b->catatan)
                                    <p class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">{{ $b->catatan }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1.5 font-bold text-slate-900 text-xs sm:text-sm">
                                    <span>{{ $b->tahunAjaranAsal?->nama ?? '-' }}</span>
                                    <i class='bx bx-right-arrow-alt text-slate-400'></i>
                                    <span class="text-[#003FA9]">{{ $b->tahunAjaranTujuan?->nama ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-800">
                                        Total: {{ $b->total_siswa }}
                                    </span>
                                    @if($b->total_naik > 0)
                                        <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-[#EFF5FF] text-[#003FA9]">
                                            Naik: {{ $b->total_naik }}
                                        </span>
                                    @endif
                                    @if($b->total_tinggal > 0)
                                        <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-800">
                                            Tinggal: {{ $b->total_tinggal }}
                                        </span>
                                    @endif
                                    @if($b->total_lulus > 0)
                                        <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800">
                                            Lulus: {{ $b->total_lulus }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $b->creator?->name ?? 'Administrator' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $b->creator?->email ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 font-mono text-xs text-slate-600">
                                {{ $b->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold
                                    {{ $b->status === 'completed' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                    {{ strtoupper($b->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('academic.promotion.show', $b->id) }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition inline-flex items-center gap-1"
                                    title="Lihat Detail Batch">
                                    <i class='bx bx-info-circle text-lg'></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class='bx bx-history text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Belum Ada Riwayat Kenaikan Kelas</p>
                                <p class="text-xs text-slate-400 mt-0.5">Eksekusi kenaikan kelas atau kelulusan pertama Anda melalui wizard.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
