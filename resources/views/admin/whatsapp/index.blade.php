@extends('layouts.app')

@section('page-title', 'Monitoring WhatsApp Gateway')
@section('page-subtitle', 'Pantau pengiriman notifikasi otomatis kehadiran siswa ke nomor WhatsApp orang tua')

@section('content')
<div class="space-y-6">

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Pesan --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pesan</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['total']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#003FA9] flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-message-rounded-dots'></i>
            </div>
        </div>

        {{-- Terkirim --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Terkirim</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['sent']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-check-circle'></i>
            </div>
        </div>

        {{-- Gagal --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Gagal</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($stats['failed']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-x-circle'></i>
            </div>
        </div>

        {{-- Dilewati --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Dilewati / Tanpa No HP</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($stats['skipped']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-skip-next-circle'></i>
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <form method="GET" action="{{ route('admin.whatsapp.logs') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            {{-- Status Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status Pengiriman</label>
                <select name="status" class="w-full text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 px-3.5 py-2 transition">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Gagal</option>
                    <option value="skipped" {{ $status === 'skipped' ? 'selected' : '' }}>Dilewati (No HP Belum Ada)</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                </select>
            </div>

            {{-- Date Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="w-full text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 px-3.5 py-2 transition">
            </div>

            {{-- Search Keyword --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Cari Siswa / No WhatsApp</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama siswa atau nomor HP..."
                    class="w-full text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 px-3.5 py-2 transition">
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3.5 rounded-xl bg-[#003FA9] text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm flex items-center justify-center gap-1.5 h-[38px]">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                <a href="{{ route('admin.whatsapp.logs') }}"
                    class="py-2 px-3.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition h-[38px] flex items-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Logs Table Container --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 overflow-hidden">
        <div class="pb-4 mb-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Riwayat Pengiriman Pesan</h3>
            <span class="text-xs text-slate-500">Menampilkan {{ $logs->count() }} dari {{ $logs->total() }} entri</span>
        </div>

        @if($logs->isEmpty())
            <div class="py-12 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                        <i class='bx bx-chat text-2xl text-slate-400'></i>
                    </div>
                    <p class="text-sm font-medium text-slate-600">Belum Ada Riwayat WhatsApp</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Log pengiriman pesan otomatis saat siswa absen masuk/pulang akan tercatat di sini.
                    </p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waktu</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siswa & Kelas</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nomor WhatsApp</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tipe</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Provider</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keterangan / Error</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 whitespace-nowrap text-slate-500 text-xs">
                                    {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-800">{{ $log->siswa->nama ?? 'Siswa Terhapus' }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $log->siswa->kelas->nama_kelas ?? '-' }} (NIS: {{ $log->siswa->nis ?? '-' }})</div>
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-700 text-xs">
                                    {{ $log->nomor_tujuan }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-semibold uppercase {{ $log->tipe === 'masuk' ? 'bg-blue-50 text-[#003FA9]' : 'bg-purple-50 text-purple-700' }}">
                                        {{ $log->tipe }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-500 uppercase text-xs">
                                    {{ $log->provider ?? 'fonnte' }}
                                </td>
                                <td class="py-4 px-4">
                                    @if($log->status === 'sent')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">Terkirim</span>
                                    @elseif($log->status === 'failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-600 text-white">Gagal</span>
                                    @elseif($log->status === 'skipped')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">Dilewati</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">Menunggu</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-600 max-w-xs truncate text-xs" title="{{ $log->error_message ?? $log->pesan }}">
                                    {{ $log->error_message ? $log->error_message : Str::limit($log->pesan, 50) }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    @if($log->status === 'failed')
                                        <form action="{{ route('admin.whatsapp.retry', $log->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition inline-flex items-center gap-1 text-xs font-semibold"
                                                title="Coba kirim ulang pesan ini">
                                                <i class='bx bx-refresh text-base'></i> Coba Lagi
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
