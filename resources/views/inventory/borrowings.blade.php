@extends('layouts.app')

@section('page-title', 'Transaksi Peminjaman Alat Praktik')
@section('page-subtitle', 'Persetujuan peminjaman, tracking alat keluar, dan verifikasi fisik saat pengembalian')

@section('content')
<div class="space-y-6">

    {{-- Top Action & Filter Bar --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('inventory.borrowings') }}" class="flex items-center gap-2 flex-wrap">
            <select name="status" class="text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 py-2 px-3 transition">
                <option value="">Semua Status Transaksi</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>Sedang Dipinjam</option>
                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
            </select>

            <select name="sumber" class="text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 py-2 px-3 transition">
                <option value="">Semua Sumber</option>
                <option value="mandiri" {{ request('sumber') === 'mandiri' ? 'selected' : '' }}>Pengajuan Mandiri</option>
                <option value="manual" {{ request('sumber') === 'manual' ? 'selected' : '' }}>Manual oleh Petugas</option>
            </select>

            <input type="date" name="tanggal" value="{{ request('tanggal') }}" title="Filter tanggal pinjam" class="text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 py-2 px-3 transition">
            <input type="text" name="peminjam" value="{{ request('peminjam') }}" placeholder="Nama peminjam..." class="text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 py-2 px-3 w-36 transition">
            <input type="text" name="barang" value="{{ request('barang') }}" placeholder="Nama/kode barang..." class="text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 py-2 px-3 w-40 transition">

            <button type="submit" class="py-2 px-4 rounded-xl bg-[#003FA9] text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                Filter
            </button>
            @if(request('status') || request('sumber') || request('tanggal') || request('peminjam') || request('barang'))
                <a href="{{ route('inventory.borrowings') }}" class="text-xs text-slate-400 hover:text-slate-600 ml-1">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-3">
            @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog', 'guru']))
            <button type="button" onclick="openManualTx()"
                class="inline-flex items-center gap-2 py-2 px-4 rounded-xl bg-[#003FA9] text-white text-xs font-semibold hover:bg-blue-700 transition shadow-sm">
                <i class='bx bx-plus text-base'></i>
                Tambah Transaksi Manual
            </button>
            @endif
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#003FA9] hover:underline">
                <i class='bx bx-arrow-back'></i> Katalog
            </a>
        </div>
    </div>

    {{-- Borrowings Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 overflow-hidden">
        <div class="pb-4 mb-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar Transaksi Peminjaman Alat</h3>
            <span class="text-xs text-slate-400">Total {{ $borrowings->total() }} Transaksi</span>
        </div>

        @if($borrowings->isEmpty())
            <div class="py-12 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                        <i class='bx bx-check-circle text-2xl text-slate-400'></i>
                    </div>
                    <p class="text-sm font-medium text-slate-600">Tidak Ada Transaksi Peminjaman</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Data permohonan pinjam alat praktikum siswa atau guru akan tampil di sini.
                    </p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peminjam</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peralatan</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Jumlah</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jadwal Pinjam & Rencana Kembali</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keperluan</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sumber / Petugas</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Aksi Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($borrowings as $b)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-800">{{ $b->user->name ?? '-' }}</div>
                                    @if($b->siswa)
                                        <div class="text-xs text-slate-400 mt-0.5">{{ $b->siswa->kelas->nama_kelas ?? 'Kelas' }} (NIS: {{ $b->siswa->nis }})</div>
                                    @elseif($b->peminjamGuru)
                                        <div class="text-xs text-slate-400 font-mono mt-0.5">Guru (NIP: {{ $b->peminjamGuru->nip ?? '-' }})</div>
                                    @else
                                        <div class="text-xs text-slate-400 font-mono mt-0.5">Role: {{ ucfirst($b->user->role ?? '-') }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-800">{{ $b->item->nama_barang }}</div>
                                    <div class="text-xs font-mono text-[#003FA9] mt-0.5">{{ $b->item->kode_barang }} | {{ $b->item->lokasi_penyimpanan }}</div>
                                </td>
                                <td class="py-4 px-4 text-center font-bold font-mono text-slate-800">
                                    {{ $b->jumlah }} Unit
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-600 whitespace-nowrap text-xs">
                                    <div>Pinjam: {{ $b->tanggal_pinjam->translatedFormat('d M Y') }}</div>
                                    <div class="mt-0.5 {{ now()->gt($b->rencana_kembali) && $b->status === 'borrowed' ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                        Kembali: {{ $b->rencana_kembali->translatedFormat('d M Y') }}
                                        @if(now()->gt($b->rencana_kembali) && $b->status === 'borrowed')
                                            (Terlambat)
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-slate-600 max-w-xs truncate text-xs" title="{{ $b->keperluan }}">
                                    {{ $b->keperluan }}
                                </td>
                                <td class="py-4 px-4">
                                    @if($b->sumber_transaksi === 'manual_petugas')
                                        <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 uppercase">MANUAL PETUGAS</span>
                                        <div class="text-xs text-slate-400 mt-0.5">Oleh: {{ $b->pencatat->name ?? '-' }}</div>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-500 uppercase">MANDIRI</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    @if($b->status === 'returned')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">Dikembalikan</span>
                                    @elseif($b->status === 'borrowed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#003FA9] text-white">Sedang Dipinjam</span>
                                    @elseif($b->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">Menunggu Approve</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">{{ $b->status }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    @if($b->status === 'pending' && in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog', 'guru']))
                                        <form action="{{ route('inventory.borrow.approve', $b->id) }}" method="POST" class="inline" onsubmit="return confirm('Setujui peminjaman dan serahkan alat?')">
                                            @csrf
                                            <button type="submit"
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition inline-flex items-center gap-1">
                                                <i class='bx bx-check text-base'></i> Setujui
                                            </button>
                                        </form>
                                    @elseif($b->status === 'borrowed' && in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog', 'guru']))
                                        {{-- Return Dropdown Form --}}
                                        <div x-data="{ open: false }" class="inline-block relative">
                                            <button @click="open = !open" type="button"
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-[#003FA9] bg-blue-50 hover:bg-blue-100 transition inline-flex items-center gap-1">
                                                <i class='bx bx-check-circle text-base'></i> {{ $b->sumber_transaksi === 'manual_petugas' ? 'Catat Pengembalian' : 'Terima Kembali' }}
                                            </button>

                                            <div x-show="open" @click.outside="open = false" x-cloak
                                                class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 p-4 z-50 text-left space-y-3">
                                                <h5 class="text-xs font-bold text-slate-800">Verifikasi Fisik Pengembalian</h5>
                                                <form action="{{ $b->sumber_transaksi === 'manual_petugas' ? route('inventory.borrow.return.manual', $b->id) : route('inventory.borrow.return', $b->id) }}" method="POST">
                                                    @csrf
                                                    <div class="space-y-2">
                                                        @if($b->sumber_transaksi === 'manual_petugas')
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 uppercase">Tanggal Kembali Aktual</label>
                                                            <input type="date" name="tanggal_kembali" value="{{ now()->toDateString() }}" required
                                                                class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/50 py-1.5 px-2">
                                                        </div>
                                                        @endif
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 uppercase">Kondisi Fisik Saat Dikembalikan</label>
                                                            <select name="kondisi_sesudah" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/50 py-1.5 px-2">
                                                                <option value="baik">Kondisi Baik Lengkap</option>
                                                                <option value="rusak_ringan">Rusak Ringan (Bisa Diperbaiki)</option>
                                                                <option value="rusak_berat">Rusak Berat / Hilang</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 uppercase">Catatan Pengembalian</label>
                                                            <input type="text" name="catatan_pengembalian" placeholder="Kelengkapan, lensa bersih, dll."
                                                                class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/50 py-1.5 px-2">
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 flex items-center justify-end gap-1.5">
                                                        <button @click="open = false" type="button" class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-700">Batal</button>
                                                        <button type="submit" class="px-3 py-1 bg-[#003FA9] text-white text-xs font-semibold rounded-xl hover:bg-blue-700">Konfirmasi</button>
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
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    {{ $borrowings->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

@push('modals')
{{-- Modal Transaksi Manual Petugas --}}
@if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog', 'guru']))
<div id="modalManualTx" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalManualTx').classList.add('hidden')"></div>
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Transaksi Manual oleh Petugas</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Catat peminjaman walk-in atas nama siswa / guru. Stok langsung berkurang.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalManualTx').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('inventory.borrow.manual') }}">
                @csrf
                <div class="px-8 py-6 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-3 rounded-xl bg-white border border-slate-200 cursor-pointer">
                            <input type="radio" name="jenis_peminjam" value="siswa" checked onchange="mtxJenis(this.value)" class="accent-[#0052CC]">
                            <span class="text-xs font-bold text-slate-700">Siswa</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-xl bg-white border border-slate-200 cursor-pointer">
                            <input type="radio" name="jenis_peminjam" value="guru" onchange="mtxJenis(this.value)" class="accent-[#0052CC]">
                            <span class="text-xs font-bold text-slate-700">Guru</span>
                        </label>
                    </div>
                    <select name="siswa_id" id="mtxSiswa" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]"><option value="">Memuat data...</option></select>
                    <select name="guru_id" id="mtxGuru" class="hidden w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]"><option value="">Memuat data...</option></select>
                    <select name="inventory_item_id" id="mtxItem" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]"><option value="">Memuat barang...</option></select>
                    <p id="mtxStok" class="text-[11px] text-slate-500 font-semibold -mt-2"></p>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Jumlah <span class="text-rose-500">*</span></label>
                            <input type="number" name="jumlah" value="1" min="1" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Pinjam <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_pinjam" value="{{ now()->toDateString() }}" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Rencana Kembali <span class="text-rose-500">*</span></label>
                            <input type="date" name="rencana_kembali" value="{{ now()->addWeek()->toDateString() }}" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Keperluan / Tujuan <span class="text-rose-500">*</span></label>
                        <textarea name="keperluan" rows="2" required placeholder="Praktikum jaringan kelas XII, dll." class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>
                </div>
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalManualTx').classList.add('hidden')" class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50">Batal</button>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] shadow-lg shadow-blue-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let mtxData = null;
async function openManualTx() {
    document.getElementById('modalManualTx').classList.remove('hidden');
    if (mtxData) { mtxRender(); return; }
    const res = await fetch('{{ route("inventory.manual.options") }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
    mtxData = await res.json();
    mtxRender();
}
function mtxFill(sel, items, ph) {
    sel.innerHTML = '';
    const o = document.createElement('option'); o.value = ''; o.textContent = ph; sel.appendChild(o);
    items.forEach(it => { const e = document.createElement('option'); e.value = it.id; e.textContent = it.label + (it.stok !== undefined ? ' — stok: ' + it.stok + ' (' + it.kondisi + ')' : ''); if (it.stok !== undefined && it.stok <= 0) e.disabled = true; sel.appendChild(e); });
}
function mtxRender() {
    if (!mtxData) return;
    mtxFill(document.getElementById('mtxSiswa'), mtxData.siswa, '-- Pilih Siswa --');
    mtxFill(document.getElementById('mtxGuru'), mtxData.guru, '-- Pilih Guru --');
    mtxFill(document.getElementById('mtxItem'), mtxData.items, '-- Pilih Barang --');
    mtxJenis(document.querySelector('input[name="jenis_peminjam"]:checked').value);
    document.getElementById('mtxItem').addEventListener('change', function () {
        const it = (mtxData.items || []).find(x => String(x.id) === this.value);
        document.getElementById('mtxStok').textContent = it ? ('Stok tersedia: ' + it.stok + ' unit — kondisi: ' + it.kondisi) : '';
    });
}
function mtxJenis(v) {
    document.getElementById('mtxSiswa').classList.toggle('hidden', v !== 'siswa');
    document.getElementById('mtxSiswa').required = (v === 'siswa');
    document.getElementById('mtxGuru').classList.toggle('hidden', v !== 'guru');
    document.getElementById('mtxGuru').required = (v === 'guru');
}
</script>
@endif
@endpush
