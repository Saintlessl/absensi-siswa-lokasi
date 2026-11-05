@extends('layouts.app')

@section('page-title', 'Detail Peralatan & Aset')
@section('page-subtitle', 'Informasi spesifikasi aset, barcode label, dan riwayat peminjaman')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Top Action Bar --}}
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i class='bx bx-left-arrow-alt text-lg'></i> Kembali ke Daftar Alat
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" type="button"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition-colors">
                <i class='bx bx-printer text-base'></i>
                <span>Cetak Label QR</span>
            </button>
            @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus aset alat ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100 transition-colors">
                        <i class='bx bx-trash'></i>
                        <span>Hapus</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Main Asset Card --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-blue-50">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Left: Specs --}}
            <div class="md:col-span-2 space-y-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-xl text-xs font-bold font-mono bg-blue-100 text-[#0052CC]">
                        {{ $item->kode_barang }}
                    </span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase bg-slate-100 text-slate-700">
                        {{ $item->jurusan ? $item->jurusan->nama_jurusan : 'Semua Jurusan' }}
                    </span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase {{ $item->unit_tersedia > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        {{ $item->unit_tersedia }} / {{ $item->total_unit }} Tersedia
                    </span>
                </div>

                <h2 class="text-xl font-bold text-slate-900">{{ $item->nama_barang }}</h2>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-slate-50/70 rounded-2xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Kategori</p>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $item->kategori }}</p>
                    </div>
                    <div class="p-3 bg-slate-50/70 rounded-2xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Lokasi Penyimpanan</p>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $item->lokasi_penyimpanan }}</p>
                    </div>
                    <div class="p-3 bg-slate-50/70 rounded-2xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Kondisi Fisik</p>
                        <p class="font-bold uppercase text-xs mt-0.5 {{ $item->kondisi === 'baik' ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ str_replace('_', ' ', $item->kondisi) }}
                        </p>
                    </div>
                    <div class="p-3 bg-slate-50/70 rounded-2xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Status Penggunaan</p>
                        <p class="font-bold uppercase text-xs mt-0.5 {{ $item->status === 'tersedia' ? 'text-emerald-600' : 'text-blue-600' }}">
                            {{ $item->status }}
                        </p>
                    </div>
                </div>

                @if($item->deskripsi)
                    <div class="p-3 bg-slate-50/70 rounded-2xl text-xs">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Deskripsi & Kelengkapan</p>
                        <p class="text-slate-600 leading-relaxed">{{ $item->deskripsi }}</p>
                    </div>
                @endif
            </div>

            {{-- Right: Barcode & Borrow Form --}}
            <div class="space-y-4">
                {{-- Barcode / QR Box --}}
                <div class="bg-slate-50 p-5 rounded-2xl text-center border border-slate-200/80">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">QR Code Label Aset</p>
                    <div class="w-32 h-32 mx-auto bg-white p-2 rounded-xl border border-slate-200 flex items-center justify-center shadow-xs">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('inventory.show', $item->id)) }}"
                             alt="QR Code Aset" class="w-full h-full object-contain">
                    </div>
                    <p class="text-xs font-mono font-bold text-slate-800 mt-2">{{ $item->kode_barang }}</p>
                    <p class="text-[10px] text-slate-400 font-mono truncate max-w-xs mx-auto">Token: {{ substr($item->qr_token, 0, 16) }}...</p>
                </div>

                {{-- Borrow Action Box --}}
                @if($item->isAvailable())
                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                        <h4 class="text-xs font-bold text-[#0052CC] uppercase tracking-wider mb-2">Pinjam Peralatan Ini</h4>
                        <form method="POST" action="{{ route('inventory.borrow.request') }}" class="space-y-2.5">
                            @csrf
                            <input type="hidden" name="inventory_item_id" value="{{ $item->id }}">

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Jumlah Unit</label>
                                <input type="number" name="jumlah" value="1" min="1" max="{{ $item->unit_tersedia }}" required
                                    class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-white py-1.5 px-3 font-mono font-bold">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Rencana Tanggal Pengembalian</label>
                                <input type="date" name="rencana_kembali" value="{{ date('Y-m-d') }}" required
                                    class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-white py-1.5 px-3">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Keperluan Praktik</label>
                                <textarea name="keperluan" rows="2" placeholder="Contoh: Praktik Pembuatan Video Iklan..." required
                                    class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-white p-2"></textarea>
                            </div>

                            <button type="submit"
                                class="w-full py-2 px-3 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs flex items-center justify-center gap-1.5">
                                <i class='bx bx-check'></i> Ajukan Peminjaman
                            </button>
                        </form>
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-amber-50 text-amber-800 text-xs text-center border border-amber-200">
                        <i class='bx bx-error-circle text-2xl block mb-1'></i>
                        <span class="font-bold">Unit Tidak Tersedia</span>
                        <p class="text-[11px] mt-0.5">Peralatan sedang dipinjam atau dalam perbaikan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Borrowing History Table --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-blue-50 space-y-4">
        <h3 class="text-sm font-bold text-slate-800">Riwayat Peminjaman Alat</h3>

        @if($item->borrowings->isEmpty())
            <div class="py-8 text-center text-xs text-slate-400">
                Belum ada riwayat transaksi peminjaman untuk peralatan ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] font-semibold tracking-wider">
                            <th class="py-2.5 px-3">Peminjam</th>
                            <th class="py-2.5 px-3">Tgl Pinjam</th>
                            <th class="py-2.5 px-3">Rencana Kembali</th>
                            <th class="py-2.5 px-3">Tgl Kembali</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Kondisi Fisik</th>
                            <th class="py-2.5 px-3">Keperluan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($item->borrowings as $b)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-2 px-3 font-semibold text-slate-800">
                                    {{ $b->user->name ?? '-' }}
                                    @if($b->siswa)
                                        <div class="text-[10px] text-slate-400 font-mono">Siswa (NIS: {{ $b->siswa->nis }})</div>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-slate-600 font-mono">{{ $b->tanggal_pinjam->translatedFormat('d M Y') }}</td>
                                <td class="py-2 px-3 text-slate-600 font-mono">{{ $b->rencana_kembali->translatedFormat('d M Y') }}</td>
                                <td class="py-2 px-3 text-slate-600 font-mono">{{ $b->tanggal_kembali ? $b->tanggal_kembali->translatedFormat('d M Y') : '-' }}</td>
                                <td class="py-2 px-3">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $b->status === 'returned' ? 'bg-emerald-50 text-emerald-700' : ($b->status === 'borrowed' ? 'bg-blue-50 text-[#0052CC]' : 'bg-amber-50 text-amber-700') }}">
                                        {{ $b->status }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 uppercase text-[10px] font-semibold text-slate-600">
                                    {{ $b->kondisi_sesudah ?: $b->kondisi_sebelum }}
                                </td>
                                <td class="py-2 px-3 text-slate-500 max-w-xs truncate" title="{{ $b->keperluan }}">
                                    {{ $b->keperluan }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
