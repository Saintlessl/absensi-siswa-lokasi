@extends('layouts.app')

@section('title', 'Persetujuan Izin')
@section('page-title', 'Persetujuan Izin')
@section('page-subtitle', 'Tinjau pengajuan izin/sakit siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Persetujuan Izin</li>
@endsection

@section('content')
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-6 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-[#003FA9]">
                    <i class='bx bx-task text-xl'></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Pengajuan Izin</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $pendingCount }} pengajuan menunggu persetujuan</p>
                </div>
            </div>

            <form method="GET" action="{{ route('izin.approval') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="px-3.5 py-2 border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] transition">
                    <option value="pending"  {{ $status === 'pending'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="all"      {{ $status === 'all'      ? 'selected' : '' }}>Semua</option>
                </select>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / NIS"
                    class="px-3.5 py-2 border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] transition">
                <button type="submit" class="px-4 py-2 bg-[#003FA9] hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition flex items-center gap-1.5 shadow-sm">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
            </form>
        </div>

        @if($izins->isEmpty())
            <div class="py-12 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                        <i class='bx bx-task text-2xl text-slate-400'></i>
                    </div>
                    <p class="text-sm font-medium text-slate-600">Tidak ada pengajuan izin</p>
                    <p class="text-xs text-slate-400 mt-1">Tidak ada pengajuan izin pada kriteria filter ini.</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siswa</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jenis</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keterangan</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($izins as $izin)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4">
                                    <p class="font-semibold text-slate-800 leading-tight">{{ $izin->siswa->nama ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $izin->siswa->nis ?? '-' }} &bull; {{ $izin->siswa->kelas->nama_kelas ?? '-' }}</p>
                                </td>
                                <td class="py-4 px-4 text-slate-600 whitespace-nowrap">{{ $izin->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="py-4 px-4">
                                    @if($izin->jenis === 'sakit')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">
                                            Sakit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#003FA9] text-white">
                                            Izin
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-600 max-w-xs">
                                    <p class="truncate" title="{{ $izin->keterangan }}">{{ $izin->keterangan }}</p>
                                    @if($izin->keputusan_catatan)
                                        <p class="text-xs text-slate-400 mt-0.5">Catatan: {{ $izin->keputusan_catatan }}</p>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    @if($izin->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">Menunggu</span>
                                    @elseif($izin->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">Disetujui</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-600 text-white">Ditolak</span>
                                    @endif
                                    @if($izin->decidedBy)
                                        <p class="text-xs text-slate-400 mt-0.5">oleh {{ $izin->decidedBy->name }}</p>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    @if($izin->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <form action="{{ route('izin.approve', $izin->id) }}" method="POST" class="inline"
                                                data-confirm="Setujui pengajuan {{ $izin->jenis }} untuk {{ $izin->siswa->nama ?? '' }}?"
                                                data-confirm-title="Setujui Izin">
                                                @csrf
                                                <input type="hidden" name="catatan" value="Disetujui">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition inline-flex items-center gap-1">
                                                    <i class='bx bx-check text-base'></i> Setujui
                                                </button>
                                            </form>
                                            <form action="{{ route('izin.reject', $izin->id) }}" method="POST" class="inline"
                                                data-confirm="Tolak pengajuan {{ $izin->jenis }} untuk {{ $izin->siswa->nama ?? '' }}?"
                                                data-confirm-title="Tolak Izin"
                                                data-confirm-danger="true">
                                                @csrf
                                                <input type="hidden" name="catatan" value="Ditolak">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition inline-flex items-center gap-1">
                                                    <i class='bx bx-x text-base'></i> Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">{{ $izin->decided_at?->translatedFormat('d M Y H:i') ?? '-' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($izins->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    {{ $izins->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
