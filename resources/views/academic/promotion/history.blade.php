@extends('layouts.app')

@section('title', 'Riwayat Kenaikan Kelas & Kelulusan')
@section('page-title', 'Riwayat Kenaikan Kelas')
@section('page-subtitle', 'Daftar Audit Batch Eksekusi Kenaikan Kelas, Tinggal Kelas, dan Kelulusan Massal')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.promotion.index') }}" class="hover:text-primary-600">Kenaikan Kelas</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Riwayat</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dark-800">Riwayat Proses Kenaikan Kelas & Kelulusan</h2>
            <p class="text-sm text-dark-400 mt-1">
                Catatan historis seluruh batch perpindahan tahun ajaran, kenaikan kelas, dan penuntasan kelulusan alumni SMK Negeri 1 Ciomas.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('academic.promotion.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white rounded-xl text-xs font-bold shadow-lg shadow-primary-500/25 transition-all duration-200 hover:-translate-y-0.5">
                <i class='bx bx-plus-circle text-base'></i>
                <span>Mulai Kenaikan Kelas Baru</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Kode Batch</th>
                        <th class="p-4">Periode Akademik</th>
                        <th class="p-4">Statistik Siswa</th>
                        <th class="p-4">Admin Pelaksana</th>
                        <th class="p-4">Tanggal Eksekusi</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $index => $b)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="p-4 text-center text-slate-400 font-semibold">
                                {{ $batches->firstItem() + $index }}
                            </td>
                            <td class="p-4">
                                <a href="{{ route('academic.promotion.show', $b->id) }}" class="font-bold text-[#003EA7] hover:underline font-mono">
                                    {{ $b->batch_code }}
                                </a>
                                @if($b->catatan)
                                    <p class="text-[11px] text-slate-500 truncate max-w-xs mt-0.5">{{ $b->catatan }}</p>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                    <span>{{ $b->tahunAjaranAsal?->nama ?? '-' }}</span>
                                    <i class='bx bx-right-arrow-alt text-slate-400'></i>
                                    <span class="text-[#003EA7]">{{ $b->tahunAjaranTujuan?->nama ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        Total: {{ $b->total_siswa }}
                                    </span>
                                    @if($b->total_naik > 0)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">
                                            Naik: {{ $b->total_naik }}
                                        </span>
                                    @endif
                                    @if($b->total_tinggal > 0)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800">
                                            Tinggal: {{ $b->total_tinggal }}
                                        </span>
                                    @endif
                                    @if($b->total_lulus > 0)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800">
                                            Lulus: {{ $b->total_lulus }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-800">{{ $b->creator?->name ?? 'Administrator' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $b->creator?->email ?? '-' }}</div>
                            </td>
                            <td class="p-4 font-mono text-[11px] text-slate-600">
                                {{ $b->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    {{ $b->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $b->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('academic.promotion.show', $b->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-[#003EA7] hover:text-white text-slate-700 rounded-lg text-xs font-bold transition-all">
                                    <span>Detail</span>
                                    <i class='bx bx-chevron-right'></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-3xl">
                                    <i class='bx bx-folder-open'></i>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Belum Ada Riwayat Kenaikan Kelas</p>
                                <p class="text-xs text-slate-400 mt-1">Eksekusi kenaikan kelas atau kelulusan pertama Anda melalui wizard.</p>
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
