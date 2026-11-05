@extends('layouts.app')

@section('page-title', 'Monitoring WhatsApp Gateway')
@section('page-subtitle', 'Pantau pengiriman notifikasi otomatis kehadiran siswa ke nomor WhatsApp orang tua')

@section('content')
<div class="space-y-6">

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Pesan --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pesan</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['total']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0052CC] flex items-center justify-center text-2xl">
                <i class='bx bx-message-rounded-dots'></i>
            </div>
        </div>

        {{-- Terkirim --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Terkirim</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['sent']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                <i class='bx bx-check-circle'></i>
            </div>
        </div>

        {{-- Gagal --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gagal</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($stats['failed']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl">
                <i class='bx bx-x-circle'></i>
            </div>
        </div>

        {{-- Dilewati --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dilewati / Tanpa No HP</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($stats['skipped']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                <i class='bx bx-skip-next-circle'></i>
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50">
        <form method="GET" action="{{ route('admin.whatsapp.logs') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            {{-- Status Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Pengiriman</label>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Gagal</option>
                    <option value="skipped" {{ $status === 'skipped' ? 'selected' : '' }}>Dilewati (No HP Belum Ada)</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                </select>
            </div>

            {{-- Date Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50">
            </div>

            {{-- Search Keyword --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Siswa / No WhatsApp</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama siswa atau nomor HP..."
                    class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50">
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs flex items-center justify-center gap-1.5">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                <a href="{{ route('admin.whatsapp.logs') }}"
                    class="py-2 px-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Logs Table Container --}}
    <div class="bg-white rounded-3xl shadow-sm border border-blue-50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Riwayat Pengiriman Pesan</h3>
            <span class="text-xs text-slate-500">Menampilkan {{ $logs->count() }} dari {{ $logs->total() }} entri</span>
        </div>

        @if($logs->isEmpty())
            <div class="py-14 px-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-3">
                    <i class='bx bx-chat text-2xl'></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Belum Ada Riwayat WhatsApp</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Log pengiriman pesan otomatis saat siswa absen masuk/pulang akan tercatat di sini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 uppercase text-[10px] tracking-wider font-semibold">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Siswa & Kelas</th>
                            <th class="py-3 px-4">Nomor WhatsApp</th>
                            <th class="py-3 px-4">Tipe</th>
                            <th class="py-3 px-4">Provider</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Keterangan / Error</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($logs as $log)
                            @php
                                $statusBadge = match($log->status) {
                                    'sent'    => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'label' => 'Terkirim'],
                                    'failed'  => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'label' => 'Gagal'],
                                    'skipped' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'label' => 'Dilewati'],
                                    default   => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'label' => 'Menunggu'],
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/20 transition-colors">
                                <td class="py-3 px-4 whitespace-nowrap text-slate-500">
                                    {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800">{{ $log->siswa->nama ?? 'Siswa Terhapus' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $log->siswa->kelas->nama_kelas ?? '-' }} (NIS: {{ $log->siswa->nis ?? '-' }})</div>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-700">
                                    {{ $log->nomor_tujuan }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $log->tipe === 'masuk' ? 'bg-blue-50 text-[#0052CC]' : 'bg-purple-50 text-purple-700' }}">
                                        {{ $log->tipe }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-500 uppercase text-[11px]">
                                    {{ $log->provider ?? 'fonnte' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $log->status === 'sent' ? 'bg-emerald-500' : ($log->status === 'failed' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="{{ $log->error_message ?? $log->pesan }}">
                                    {{ $log->error_message ? $log->error_message : Str::limit($log->pesan, 50) }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($log->status === 'failed')
                                        <form action="{{ route('admin.whatsapp.retry', $log->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors shadow-xs"
                                                title="Coba kirim ulang pesan ini">
                                                <i class='bx bx-refresh text-sm'></i> Coba Lagi
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
                <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
