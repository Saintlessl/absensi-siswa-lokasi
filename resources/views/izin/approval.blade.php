@extends('layouts.app')

@section('title', 'Persetujuan Izin')
@section('page-title', 'Persetujuan Izin')
@section('page-subtitle', 'Tinjau pengajuan izin/sakit siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Persetujuan Izin</li>
@endsection

@section('content')
    <div class="glass-card rounded-2xl border border-dark-200/50 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center text-primary-600">
                    <i class='bx bx-task text-xl'></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-dark-800">Pengajuan Izin</h3>
                    <p class="text-xs text-dark-400">{{ $pendingCount }} pengajuan menunggu persetujuan</p>
                </div>
            </div>

            <form method="GET" action="{{ route('izin.approval') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                    <option value="pending"  {{ $status === 'pending'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="all"      {{ $status === 'all'      ? 'selected' : '' }}>Semua</option>
                </select>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / NIS"
                    class="px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl transition">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
            </form>
        </div>

        @if($izins->isEmpty())
            <p class="text-sm text-dark-400 py-10 text-center">Tidak ada pengajuan pada filter ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-dark-400 border-b border-dark-100">
                            <th class="py-3 pr-4 font-semibold">Siswa</th>
                            <th class="py-3 pr-4 font-semibold">Tanggal</th>
                            <th class="py-3 pr-4 font-semibold">Jenis</th>
                            <th class="py-3 pr-4 font-semibold">Keterangan</th>
                            <th class="py-3 pr-4 font-semibold">Status</th>
                            <th class="py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-50">
                        @foreach($izins as $izin)
                            <tr>
                                <td class="py-3 pr-4">
                                    <p class="font-semibold text-dark-800">{{ $izin->siswa->nama ?? '-' }}</p>
                                    <p class="text-[11px] text-dark-400 font-mono">{{ $izin->siswa->nis ?? '-' }} &bull; {{ $izin->siswa->kelas->nama_kelas ?? '-' }}</p>
                                </td>
                                <td class="py-3 pr-4 font-mono text-dark-600 whitespace-nowrap">{{ $izin->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="py-3 pr-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $izin->jenis === 'sakit' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                        {{ ucfirst($izin->jenis) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-dark-600 max-w-xs">
                                    <p class="truncate" title="{{ $izin->keterangan }}">{{ $izin->keterangan }}</p>
                                    @if($izin->keputusan_catatan)
                                        <p class="text-[11px] text-dark-400 mt-0.5">Catatan: {{ $izin->keputusan_catatan }}</p>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    @if($izin->status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Menunggu</span>
                                    @elseif($izin->status === 'approved')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Disetujui</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Ditolak</span>
                                    @endif
                                    @if($izin->decidedBy)
                                        <p class="text-[10px] text-dark-400 mt-0.5">oleh {{ $izin->decidedBy->name }}</p>
                                    @endif
                                </td>
                                <td class="py-3 text-right whitespace-nowrap">
                                    @if($izin->status === 'pending')
                                        <form action="{{ route('izin.approve', $izin->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm(@js('Setujui pengajuan ' . $izin->jenis . ' untuk ' . ($izin->siswa->nama ?? '') . '?'));">
                                            @csrf
                                            <input type="hidden" name="catatan" value="Disetujui">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition">
                                                <i class='bx bx-check'></i> Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('izin.reject', $izin->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm(@js('Tolak pengajuan ' . $izin->jenis . ' untuk ' . ($izin->siswa->nama ?? '') . '?'));">
                                            @csrf
                                            <input type="hidden" name="catatan" value="Ditolak">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                                                <i class='bx bx-x'></i> Tolak
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-dark-400">{{ $izin->decided_at?->translatedFormat('d M Y H:i') ?? '-' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $izins->links() }}</div>
        @endif
    </div>
@endsection
