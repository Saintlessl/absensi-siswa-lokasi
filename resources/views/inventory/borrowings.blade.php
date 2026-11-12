@extends('layouts.app')

@section('title', 'Transaksi Peminjaman Alat Praktik')
@section('page-title', 'Inventaris Alat & Aset Praktik')
@section('page-subtitle', 'Persetujuan Permohonan, Reservasi Unit, Penyerahan & Verifikasi Pengembalian')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('inventory.index') }}" class="hover:text-[#003FA9] transition">Inventaris Alat</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Transaksi Peminjaman</li>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Filter & Actions Card --}}
    <div class="bg-white rounded-[28px] p-6 shadow-sm border border-slate-100 space-y-4">
        {{-- Header & Buttons --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Daftar Transaksi Peminjaman</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola seluruh permohonan, penyerahan, dan pengembalian unit fisik alat praktik.</p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap self-end sm:self-auto">
                <a href="{{ route('inventory.scan') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                    <i class='bx bx-qr-scan text-base text-[#003FA9]'></i>
                    <span>Scanner Cepat</span>
                </a>

                @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog', 'guru']))
                    <button type="button" onclick="openManualTx()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold rounded-xl shadow-md shadow-blue-900/10 transition">
                        <i class='bx bx-plus text-base'></i>
                        <span>Transaksi Manual Petugas</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Status Filter Tabs --}}
        @php
            $currentTab = request('tab', '');
        @endphp
        <div class="flex items-center gap-2 overflow-x-auto pt-2 border-t border-slate-100 pb-1">
            <a href="{{ route('inventory.borrowings', request()->except('tab', 'page')) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $currentTab === '' ? 'bg-[#003FA9] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua ({{ $counts['all'] }})
            </a>
            <a href="{{ route('inventory.borrowings', array_merge(request()->except('page'), ['tab' => 'pending'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $currentTab === 'pending' ? 'bg-[#003FA9] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Menunggu Persetujuan ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('inventory.borrowings', array_merge(request()->except('page'), ['tab' => 'approved'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $currentTab === 'approved' ? 'bg-[#003FA9] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Siap Diambil ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('inventory.borrowings', array_merge(request()->except('page'), ['tab' => 'borrowed'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $currentTab === 'borrowed' ? 'bg-[#003FA9] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Sedang Dipinjam ({{ $counts['borrowed'] }})
            </a>
            <a href="{{ route('inventory.borrowings', array_merge(request()->except('page'), ['tab' => 'overdue'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $currentTab === 'overdue' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' }}">
                Belum Dikembalikan / Terlambat ({{ $counts['overdue'] }})
            </a>
            <a href="{{ route('inventory.borrowings', array_merge(request()->except('page'), ['tab' => 'returned'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $currentTab === 'returned' ? 'bg-[#003FA9] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Sudah Dikembalikan ({{ $counts['returned'] }})
            </a>
        </div>

        {{-- Search & Input Filters --}}
        <form method="GET" action="{{ route('inventory.borrowings') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-2">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            {{-- Peminjam --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Peminjam</label>
                <input type="text" name="peminjam" value="{{ request('peminjam') }}" placeholder="Cari peminjam..."
                    class="w-full h-10 px-3 text-xs rounded-xl border-0 bg-[#EFF5FF] text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] font-medium outline-none">
            </div>

            {{-- Barang --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama / Kode Barang</label>
                <input type="text" name="barang" value="{{ request('barang') }}" placeholder="Kode atau nama alat..."
                    class="w-full h-10 px-3 text-xs rounded-xl border-0 bg-[#EFF5FF] text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] font-medium outline-none">
            </div>

            {{-- Sumber Transaksi --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Sumber Transaksi</label>
                <select name="sumber" class="w-full h-10 text-xs rounded-xl border-0 bg-[#EFF5FF] text-slate-800 px-3 focus:ring-2 focus:ring-[#003FA9] font-medium outline-none">
                    <option value="">Semua Sumber</option>
                    <option value="mandiri" {{ request('sumber') === 'mandiri' ? 'selected' : '' }}>Pengajuan Mandiri (Siswa/Guru)</option>
                    <option value="manual" {{ request('sumber') === 'manual' ? 'selected' : '' }}>Manual oleh Petugas</option>
                </select>
            </div>

            {{-- Tanggal --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Pinjam</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                    class="w-full h-10 px-3 text-xs rounded-xl border-0 bg-[#EFF5FF] text-slate-800 focus:ring-2 focus:ring-[#003FA9] outline-none">
            </div>

            {{-- Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 h-10 px-4 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold transition shadow-sm">
                    Filter
                </button>
                @if(request()->anyFilled(['peminjam', 'barang', 'sumber', 'tanggal']))
                    <a href="{{ route('inventory.borrowings', request('tab') ? ['tab' => request('tab')] : []) }}"
                       class="h-10 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold flex items-center justify-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Borrowings Table Card --}}
    <div class="bg-white rounded-[28px] p-6 shadow-sm border border-slate-100 overflow-hidden">
        <div class="pb-4 mb-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Catatan Riwayat & Transaksi Peminjaman</h3>
            <span class="text-xs font-semibold text-slate-500">Total {{ $borrowings->total() }} Data</span>
        </div>

        @if($borrowings->isEmpty())
            <div class="py-16 text-center text-slate-400">
                <i class='bx bx-check-circle text-4xl mb-2 text-slate-300'></i>
                <p class="text-sm font-semibold text-slate-600">Tidak Ada Transaksi Ditemukan</p>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Tidak ada catatan peminjaman alat yang sesuai dengan filter atau kriteria pencarian saat ini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">No.</th>
                            <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peminjam</th>
                            <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peralatan & Unit Fisik</th>
                            <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Jml</th>
                            <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jadwal Pinjam & Rencana Kembali</th>
                            <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tgl Kembali</th>
                            <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Transaksi</th>
                            <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Aksi Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($borrowings as $index => $b)
                            @php
                                $badge = $b->status_badge;
                                $effectiveStatus = $b->effective_status;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- Nomor --}}
                                <td class="py-4 px-4 text-xs font-mono text-slate-400">
                                    {{ $borrowings->firstItem() + $index }}
                                </td>

                                {{-- Identitas Peminjam --}}
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900">{{ $b->user->name ?? '-' }}</div>
                                    @if($b->siswa)
                                        <div class="text-[11px] text-slate-500 mt-0.5 font-medium">
                                            {{ $b->siswa->kelas->nama_kelas ?? 'Kelas' }} • NIS: <span class="font-mono font-bold">{{ $b->siswa->nis }}</span>
                                        </div>
                                    @elseif($b->peminjamGuru)
                                        <div class="text-[11px] text-slate-500 mt-0.5 font-medium">
                                            Guru • NIP: <span class="font-mono">{{ $b->peminjamGuru->nip ?? '-' }}</span>
                                        </div>
                                    @else
                                        <div class="text-[11px] text-slate-400 mt-0.5">Role: {{ ucfirst($b->user->role ?? '-') }}</div>
                                    @endif
                                </td>

                                {{-- Peralatan & Unit Fisik --}}
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900">{{ $b->item->nama_barang }}</div>
                                    <div class="text-[11px] font-mono text-[#003FA9] mt-0.5">
                                        {{ $b->item->kode_barang }} • {{ $b->item->lokasi_penyimpanan }}
                                    </div>
                                    {{-- Allocated Units --}}
                                    @if($b->borrowingUnits->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            @foreach($b->borrowingUnits as $bu)
                                                <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold bg-[#EFF5FF] text-[#003FA9] border border-blue-100"
                                                      title="Status Unit: {{ $bu->status }} • Kondisi: {{ $bu->kondisi_sebelum }}">
                                                    {{ $bu->unit?->kode_unit ?: 'Unit #' . $bu->inventory_unit_id }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($b->status === 'pending')
                                        <div class="text-[10px] text-amber-600 font-medium mt-1">
                                            <i class='bx bx-time'></i> Unit dialokasikan otomatis saat persetujuan
                                        </div>
                                    @endif
                                </td>

                                {{-- Jumlah --}}
                                <td class="py-4 px-4 text-center font-bold font-mono text-slate-900">
                                    {{ $b->jumlah }}
                                </td>

                                {{-- Jadwal Pinjam & Rencana Kembali --}}
                                <td class="py-4 px-4 font-mono text-xs text-slate-600">
                                    <div>Pinjam: {{ $b->tanggal_pinjam->translatedFormat('d M Y') }}</div>
                                    <div class="mt-0.5 {{ $b->is_overdue ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                                        Kembali: {{ $b->rencana_kembali->translatedFormat('d M Y') }}
                                        @if($b->is_overdue)
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] bg-rose-100 text-rose-700 font-bold ml-1">Terlambat</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Tanggal Kembali Aktual --}}
                                <td class="py-4 px-4 font-mono text-xs text-slate-600">
                                    @if($b->tanggal_kembali)
                                        <span class="font-bold text-emerald-600">{{ $b->tanggal_kembali->translatedFormat('d M Y') }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                {{-- Status Transaksi --}}
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $badge['bg'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                    @if($b->sumber_transaksi === 'manual_petugas')
                                        <div class="mt-1">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">MANUAL PETUGAS</span>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Oleh: {{ $b->pencatat->name ?? 'Petugas' }}</div>
                                        </div>
                                    @endif
                                </td>

                                {{-- Aksi Petugas --}}
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    {{-- Actions for Pending Status --}}
                                    @if($b->status === 'pending' && in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                                        <div class="inline-flex items-center gap-1.5">
                                            <form action="{{ route('inventory.borrow.approve', $b->id) }}" method="POST" class="inline"
                                                data-confirm="Setujui peminjaman ini dan reservasi unit?"
                                                data-confirm-title="Setujui Peminjaman">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition inline-flex items-center gap-1">
                                                    <i class='bx bx-check text-base'></i>
                                                    <span>Setujui</span>
                                                </button>
                                            </form>
                                            <form action="{{ route('inventory.borrow.reject', $b->id) }}" method="POST" class="inline"
                                                data-confirm="Tolak permohonan peminjaman ini?"
                                                data-confirm-title="Tolak Peminjaman"
                                                data-confirm-danger="true">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>

                                    {{-- Actions for Approved Status (Ready for Handover) --}}
                                    @elseif($b->status === 'approved' && in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                                        <form action="{{ route('inventory.borrow.handover', $b->id) }}" method="POST" class="inline"
                                            data-confirm="Konfirmasi penyerahan fisik unit ke peminjam?"
                                            data-confirm-title="Penyerahan Barang">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-[#003FA9] hover:bg-[#002D7A] transition inline-flex items-center gap-1 shadow-xs">
                                                <i class='bx bx-send text-base'></i>
                                                <span>Serahkan Barang</span>
                                            </button>
                                        </form>

                                    {{-- Actions for Borrowed Status (Ready for Return Verification) --}}
                                    @elseif($b->status === 'borrowed' && in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog', 'guru']))
                                        <div x-data="{ open: false }" class="inline-block relative">
                                            <button @click="open = !open" type="button"
                                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-[#003FA9] bg-blue-50 hover:bg-blue-100 border border-blue-200 transition inline-flex items-center gap-1">
                                                <i class='bx bx-check-shield text-base'></i>
                                                <span>Terima Kembali</span>
                                            </button>

                                            <div x-show="open" @click.outside="open = false" x-cloak
                                                class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 p-4 z-50 text-left space-y-3">
                                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                                    <h5 class="text-xs font-bold text-slate-900">Verifikasi Fisik Pengembalian</h5>
                                                    <button @click="open = false" class="text-slate-400 hover:text-slate-600"><i class='bx bx-x text-lg'></i></button>
                                                </div>

                                                <form action="{{ $b->sumber_transaksi === 'manual_petugas' ? route('inventory.borrow.return.manual', $b->id) : route('inventory.borrow.return', $b->id) }}" method="POST">
                                                    @csrf
                                                    <div class="space-y-2.5">
                                                        @if($b->sumber_transaksi === 'manual_petugas')
                                                            <div>
                                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Tgl Pengembalian</label>
                                                                <input type="date" name="tanggal_kembali" value="{{ now()->toDateString() }}" required
                                                                    class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 py-1.5 px-2">
                                                            </div>
                                                        @endif

                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Kondisi Fisik Saat Kembali <span class="text-rose-500">*</span></label>
                                                            <select name="kondisi_sesudah" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 py-1.5 px-2 font-semibold">
                                                                <option value="baik">Kondisi Baik Lengkap</option>
                                                                <option value="rusak_ringan">Rusak Ringan (Perlu Servis)</option>
                                                                <option value="rusak_berat">Rusak Berat / Hilang</option>
                                                                <option value="perlu_pemeriksaan">Perlu Pemeriksaan Mendalam</option>
                                                            </select>
                                                        </div>

                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Catatan Pemeriksaan</label>
                                                            <input type="text" name="catatan_pengembalian" placeholder="Kelengkapan, lensa, charger, baut..."
                                                                class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 py-1.5 px-2">
                                                        </div>
                                                    </div>

                                                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                                                        <button @click="open = false" type="button" class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-700">Batal</button>
                                                        <button type="submit" class="px-3.5 py-1.5 bg-[#003FA9] text-white text-xs font-bold rounded-xl hover:bg-[#002D7A] transition shadow-xs">
                                                            Konfirmasi Pengembalian
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    {{-- Borrower Self-Cancellation Option if Still Pending/Approved --}}
                                    @elseif(in_array($b->status, ['pending', 'approved']) && $b->user_id === Auth::id())
                                        <form action="{{ route('inventory.borrow.cancel', $b->id) }}" method="POST" class="inline"
                                            data-confirm="Batalkan permohonan peminjaman ini?"
                                            data-confirm-title="Batalkan Peminjaman"
                                            data-confirm-danger="true">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 text-xs font-mono">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($borrowings->hasPages())
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
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
        <div class="relative w-full max-w-xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden text-left">
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Transaksi Manual oleh Petugas</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-0.5">Catat peminjaman walk-in atas nama siswa / guru. Unit fisik langsung dialokasikan.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalManualTx').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60">
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('inventory.borrow.manual') }}">
                @csrf
                <div class="px-8 py-6 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-3 rounded-xl bg-white border border-slate-200 cursor-pointer">
                            <input type="radio" name="jenis_peminjam" value="siswa" checked onchange="mtxJenis(this.value)" class="accent-[#003FA9]">
                            <span class="text-xs font-bold text-slate-800">Siswa</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-xl bg-white border border-slate-200 cursor-pointer">
                            <input type="radio" name="jenis_peminjam" value="guru" onchange="mtxJenis(this.value)" class="accent-[#003FA9]">
                            <span class="text-xs font-bold text-slate-800">Guru</span>
                        </label>
                    </div>

                    <select name="siswa_id" id="mtxSiswa" class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 font-semibold focus:ring-2 focus:ring-[#003FA9]">
                        <option value="">Memuat data siswa...</option>
                    </select>

                    <select name="guru_id" id="mtxGuru" class="hidden w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 font-semibold focus:ring-2 focus:ring-[#003FA9]">
                        <option value="">Memuat data guru...</option>
                    </select>

                    <select name="inventory_item_id" id="mtxItem" required class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 font-semibold focus:ring-2 focus:ring-[#003FA9]">
                        <option value="">Memuat peralatan...</option>
                    </select>

                    <p id="mtxStok" class="text-[11px] text-slate-500 font-semibold -mt-2"></p>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah <span class="text-rose-500">*</span></label>
                            <input type="number" name="jumlah" value="1" min="1" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 font-mono font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pinjam <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_pinjam" value="{{ now()->toDateString() }}" required class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Rencana Kembali <span class="text-rose-500">*</span></label>
                            <input type="date" name="rencana_kembali" value="{{ now()->addWeek()->toDateString() }}" required class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keperluan / Keterangan <span class="text-rose-500">*</span></label>
                        <textarea name="keperluan" rows="2" required placeholder="Praktikum keahlian kelas XII, dsb." class="w-full text-xs rounded-xl border-0 bg-[#E2EDFF] p-3 text-slate-800 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>
                </div>

                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalManualTx').classList.add('hidden')" class="px-5 py-2.5 rounded-full text-xs font-bold text-[#003FA9] hover:bg-blue-50">Batal</button>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-xs font-bold hover:bg-[#002D7A] shadow-md shadow-blue-900/10">
                        <i class='bx bx-check text-base'></i>
                        <span>Simpan & Serahkan Unit</span>
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
    items.forEach(it => {
        const e = document.createElement('option');
        e.value = it.id;
        e.textContent = it.label + (it.stok !== undefined ? ' — stok: ' + it.stok + ' (' + it.kondisi + ')' : '');
        if (it.stok !== undefined && it.stok <= 0) e.disabled = true;
        sel.appendChild(e);
    });
}
function mtxRender() {
    if (!mtxData) return;
    mtxFill(document.getElementById('mtxSiswa'), mtxData.siswa, '-- Pilih Siswa --');
    mtxFill(document.getElementById('mtxGuru'), mtxData.guru, '-- Pilih Guru --');
    mtxFill(document.getElementById('mtxItem'), mtxData.items, '-- Pilih Peralatan --');
    mtxJenis(document.querySelector('input[name="jenis_peminjam"]:checked').value);
    document.getElementById('mtxItem').addEventListener('change', function () {
        const it = (mtxData.items || []).find(x => String(x.id) === this.value);
        document.getElementById('mtxStok').textContent = it ? ('Stok unit layak tersedia: ' + it.stok + ' unit') : '';
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
