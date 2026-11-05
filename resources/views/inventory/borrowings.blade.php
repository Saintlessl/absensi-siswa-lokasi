@extends('layouts.app')

@section('page-title', 'Transaksi Peminjaman Alat Praktik')
@section('page-subtitle', 'Persetujuan peminjaman, tracking alat keluar, dan verifikasi fisik saat pengembalian')

@section('content')
<div class="space-y-6">

    {{-- Top Action & Filter Bar --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" action="{{ route('inventory.borrowings') }}" class="flex items-center gap-2 flex-wrap">
            <select name="status" class="text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-1.5 px-3">
                <option value="">Semua Status Transaksi</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>Sedang Dipinjam</option>
                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
            </select>

            <button type="submit" class="py-1.5 px-3 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors">
                Filter
            </button>
            @if(request('status'))
                <a href="{{ route('inventory.borrowings') }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
            @endif
        </form>

        <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#0052CC] hover:underline">
            <i class='bx bx-arrow-back'></i> Kembali ke Katalog Inventaris
        </a>
    </div>

    {{-- Borrowings Table --}}
    <div class="bg-white rounded-3xl shadow-sm border border-blue-50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar Transaksi Peminjaman Alat</h3>
            <span class="text-xs text-slate-400">Total {{ $borrowings->total() }} Transaksi</span>
        </div>

        @if($borrowings->isEmpty())
            <div class="py-14 px-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class='bx bx-check-circle'></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Tidak Ada Transaksi Peminjaman</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Data permohonan pinjam alat praktikum siswa atau guru akan tampil di sini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 uppercase text-[10px] tracking-wider font-semibold">
                            <th class="py-3 px-4">Peminjam</th>
                            <th class="py-3 px-4">Peralatan</th>
                            <th class="py-3 px-4 text-center">Jumlah</th>
                            <th class="py-3 px-4">Jadwal Pinjam & Rencana Kembali</th>
                            <th class="py-3 px-4">Keperluan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($borrowings as $b)
                            @php
                                $badge = match($b->status) {
                                    'returned' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Dikembalikan'],
                                    'borrowed' => ['bg' => 'bg-blue-50 text-[#0052CC] border-blue-200', 'label' => 'Sedang Dipinjam'],
                                    'pending'  => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Menunggu Approve'],
                                    default    => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => $b->status],
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/20 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $b->user->name ?? '-' }}</div>
                                    @if($b->siswa)
                                        <div class="text-[10px] text-slate-400">{{ $b->siswa->kelas->nama_kelas ?? 'Kelas' }} (NIS: {{ $b->siswa->nis }})</div>
                                    @else
                                        <div class="text-[10px] text-slate-400 font-mono">Role: {{ ucfirst($b->user->role ?? '-') }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $b->item->nama_barang }}</div>
                                    <div class="text-[10px] font-mono text-[#0052CC]">{{ $b->item->kode_barang }} | {{ $b->item->lokasi_penyimpanan }}</div>
                                </td>
                                <td class="py-3 px-4 text-center font-bold font-mono text-slate-800">
                                    {{ $b->jumlah }} Unit
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600 whitespace-nowrap">
                                    <div>Pinjam: {{ $b->tanggal_pinjam->translatedFormat('d M Y') }}</div>
                                    <div class="text-[11px] {{ now()->gt($b->rencana_kembali) && $b->status === 'borrowed' ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                        Kembali: {{ $b->rencana_kembali->translatedFormat('d M Y') }}
                                        @if(now()->gt($b->rencana_kembali) && $b->status === 'borrowed')
                                            (Terlambat)
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="{{ $b->keperluan }}">
                                    {{ $b->keperluan }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['bg'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    @if($b->status === 'pending' && in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog', 'guru']))
                                        <form action="{{ route('inventory.borrow.approve', $b->id) }}" method="POST" class="inline" onsubmit="return confirm('Setujui peminjaman dan serahkan alat?')">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold transition-colors shadow-xs">
                                                <i class='bx bx-check'></i> Setujui
                                            </button>
                                        </form>
                                    @elseif($b->status === 'borrowed' && in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog', 'guru']))
                                        {{-- Return Dropdown Form --}}
                                        <div x-data="{ open: false }" class="inline-block relative">
                                            <button @click="open = !open" type="button"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-50 text-[#0052CC] hover:bg-blue-100 text-xs font-semibold transition-colors shadow-xs">
                                                <i class='bx bx-check-circle'></i> Terima Kembali
                                            </button>

                                            <div x-show="open" @click.outside="open = false" x-cloak
                                                class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 p-4 z-50 text-left space-y-3">
                                                <h5 class="text-xs font-bold text-slate-800">Verifikasi Fisik Pengembalian</h5>
                                                <form action="{{ route('inventory.borrow.return', $b->id) }}" method="POST">
                                                    @csrf
                                                    <div class="space-y-2">
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 uppercase">Kondisi Fisik Saat Dikembalikan</label>
                                                            <select name="kondisi_sesudah" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 py-1.5 px-2">
                                                                <option value="baik">Kondisi Baik Lengkap</option>
                                                                <option value="rusak_ringan">Rusak Ringan (Bisa Diperbaiki)</option>
                                                                <option value="rusak_berat">Rusak Berat / Hilang</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 uppercase">Catatan Pengembalian</label>
                                                            <input type="text" name="catatan_pengembalian" placeholder="Kelengkapan, lensa bersih, dll."
                                                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 py-1.5 px-2">
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 flex items-center justify-end gap-1.5">
                                                        <button @click="open = false" type="button" class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-700">Batal</button>
                                                        <button type="submit" class="px-3 py-1 bg-[#0052CC] text-white text-xs font-semibold rounded-xl hover:bg-[#003FA9]">Konfirmasi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($borrowings->hasPages())
                <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $borrowings->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
