@extends('layouts.app')

@section('title', 'Inventaris Alat & Aset Praktik')
@section('page-title', 'Inventaris Alat & Aset Praktik')
@section('page-subtitle', 'Sistem Manajemen Aset Sekolah, Pelacakan Unit Fisik & Peminjaman Alat Praktik')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Inventaris Alat</li>
@endsection

@section('content')
<div class="space-y-6">

    {{-- 4 Stat Cards (MySkanic Clean Minimalist Design) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Kelompok & Unit --}}
        <div class="bg-white rounded-[24px] p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Aset & Unit</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_unit']) }}</h3>
                    <span class="text-xs font-semibold text-slate-500">Unit ({{ number_format($stats['total_kelompok']) }} Kelompok)</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#EFF5FF] text-[#003FA9] flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-package'></i>
            </div>
        </div>

        {{-- Unit Siap Pakai --}}
        <div class="bg-white rounded-[24px] p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tersedia Siap Pinjam</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl font-bold text-emerald-600">{{ number_format($stats['tersedia']) }}</h3>
                    <span class="text-xs font-semibold text-emerald-700">Unit Layak</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-check-shield'></i>
            </div>
        </div>

        {{-- Sedang Dipinjam --}}
        <div class="bg-white rounded-[24px] p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sedang Dipinjam</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl font-bold text-[#003FA9]">{{ number_format($stats['dipinjam']) }}</h3>
                    <span class="text-xs font-semibold text-blue-700">Unit Aktif Keluar</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#003FA9] flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-transfer'></i>
            </div>
        </div>

        {{-- Perbaikan / Rusak --}}
        <div class="bg-white rounded-[24px] p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Perbaikan / Pemeriksaan</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl font-bold text-rose-600">{{ number_format($stats['rusak']) }}</h3>
                    <span class="text-xs font-semibold text-rose-700">Unit Non-Aktif</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-wrench'></i>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- B. BAGIAN MONITORING PEMINJAMAN GLOBAL (Bagian Utama)                     --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        {{-- Card Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Monitoring Peminjaman Alat</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if(Auth::user()->role === 'kaprog' && Auth::user()->jurusan)
                        Memantau seluruh transaksi peminjaman alat praktik program keahlian <strong class="text-[#003FA9]">{{ Auth::user()->jurusan->nama_jurusan }}</strong>.
                    @else
                        Memantau seluruh transaksi peminjaman peralatan praktik dari seluruh program keahlian.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap self-end sm:self-auto">
                <a href="{{ route('inventory.scan') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                    <i class='bx bx-qr-scan text-base text-[#003FA9]'></i>
                    <span>Scan Barcode / QR</span>
                </a>

                <a href="{{ route('inventory.borrowings') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                    <i class='bx bx-clipboard text-base text-[#003FA9]'></i>
                    <span>Permohonan & Persetujuan</span>
                </a>
            </div>
        </div>

        {{-- C. TIGA TAB FILTER STATUS PEMINJAMAN --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <div class="inline-flex p-1 rounded-2xl bg-[#EFF5FF] border border-blue-100/60 text-xs font-bold gap-1 flex-wrap">
                {{-- 1. Sedang Dipinjam --}}
                <a href="{{ route('inventory.index', array_merge(request()->except(['monitoring_page', 'page']), ['monitoring_tab' => 'dipinjam'])) }}"
                   class="px-4 py-2.5 rounded-xl transition inline-flex items-center gap-2 {{ $monitoringTab === 'dipinjam' ? 'bg-[#003FA9] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                    <i class='bx bx-time text-base'></i>
                    <span>Sedang Dipinjam</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] {{ $monitoringTab === 'dipinjam' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ number_format($countDipinjam) }}
                    </span>
                </a>

                {{-- 2. Dikembalikan --}}
                <a href="{{ route('inventory.index', array_merge(request()->except(['monitoring_page', 'page']), ['monitoring_tab' => 'dikembalikan'])) }}"
                   class="px-4 py-2.5 rounded-xl transition inline-flex items-center gap-2 {{ $monitoringTab === 'dikembalikan' ? 'bg-[#003FA9] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                    <i class='bx bx-check-circle text-base'></i>
                    <span>Dikembalikan</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] {{ $monitoringTab === 'dikembalikan' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ number_format($countDikembalikan) }}
                    </span>
                </a>

                {{-- 3. Belum Dikembalikan --}}
                <a href="{{ route('inventory.index', array_merge(request()->except(['monitoring_page', 'page']), ['monitoring_tab' => 'belum_dikembalikan'])) }}"
                   class="px-4 py-2.5 rounded-xl transition inline-flex items-center gap-2 {{ $monitoringTab === 'belum_dikembalikan' ? 'bg-[#003FA9] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                    <i class='bx bx-error-circle text-base'></i>
                    <span>Belum Dikembalikan</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] {{ $monitoringTab === 'belum_dikembalikan' ? 'bg-rose-500 text-white' : 'bg-rose-100 text-rose-700' }}">
                        {{ number_format($countBelumDikembalikan) }}
                    </span>
                </a>
            </div>

            {{-- Tab explanation note --}}
            <div class="text-xs font-medium self-center">
                @if($monitoringTab === 'dipinjam')
                    <span class="inline-flex items-center gap-1.5 text-blue-700"><i class='bx bx-info-circle'></i> Unit sudah diserahkan dan masih dalam batas waktu peminjaman.</span>
                @elseif($monitoringTab === 'dikembalikan')
                    <span class="inline-flex items-center gap-1.5 text-emerald-700"><i class='bx bx-check-shield'></i> Unit telah dikembalikan dan diverifikasi kondisinya oleh petugas.</span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-rose-700 font-bold"><i class='bx bx-alarm-exclamation'></i> Unit telah melewati batas waktu pengembalian (terlambat).</span>
                @endif
            </div>
        </div>

        {{-- Search & Filter Controls for Global Monitoring --}}
        <form method="GET" action="{{ route('inventory.index') }}" class="p-4 rounded-2xl bg-[#EFF5FF]/60 border border-blue-100/60">
            <input type="hidden" name="monitoring_tab" value="{{ $monitoringTab }}">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-end gap-3">
                {{-- Search peminjam, nama barang, kode unit --}}
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Cari Transaksi Peminjaman</label>
                    <div class="relative">
                        <i class='bx bx-search absolute left-3.5 top-3.5 text-slate-400 text-base'></i>
                        <input type="text" name="monitoring_search" value="{{ $monitoringSearch }}"
                               placeholder="Nama peminjam, NISN/NIP, nama barang, atau kode unit barcode..."
                               class="w-full h-11 pl-10 pr-4 text-xs rounded-xl border-0 bg-white text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] font-medium outline-none shadow-2xs">
                    </div>
                </div>

                {{-- Filter Jurusan for Super Admin / Admin --}}
                @if(Auth::user()->role !== 'kaprog')
                    <div class="w-full sm:w-56">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Filter Jurusan</label>
                        <select name="monitoring_jurusan_id" class="w-full h-11 text-xs rounded-xl border-0 bg-white text-slate-800 px-3.5 focus:ring-2 focus:ring-[#003FA9] font-semibold outline-none shadow-2xs appearance-none">
                            <option value="">Semua Jurusan</option>
                            @foreach($jurusans as $j)
                                <option value="{{ $j->id }}" {{ $monitoringJurusanId == $j->id ? 'selected' : '' }}>
                                    {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Action buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-11 px-5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm transition">
                        <i class='bx bx-filter-alt text-sm'></i>
                        <span>Filter Monitoring</span>
                    </button>
                    @if($monitoringSearch || $monitoringJurusanId)
                        <a href="{{ route('inventory.index', ['monitoring_tab' => $monitoringTab]) }}"
                           class="h-11 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold inline-flex items-center transition shadow-2xs">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- D. TABEL SELURUH TRANSAKSI PEMINJAMAN (Style matching Data Siswa) --}}
        @if($monitoringBorrowings->isEmpty())
            <div class="py-14 text-center text-slate-400 border border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
                <i class='bx bx-folder-open text-4xl mb-2 text-slate-300'></i>
                <h4 class="text-sm font-bold text-slate-700">Tidak Ada Transaksi di Tab Ini</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    @if($monitoringTab === 'dipinjam')
                        Saat ini tidak ada peralatan yang sedang dalam status dipinjam.
                    @elseif($monitoringTab === 'dikembalikan')
                        Belum ada riwayat pengembalian yang tercatat.
                    @else
                        Tidak ada transaksi peminjaman yang terlambat dikembalikan.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">IDENTITAS PEMINJAM</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NAMA LENGKAP</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ROLE</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">JURUSAN</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NAMA BARANG</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KODE UNIT</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">JUMLAH</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TGL PINJAM</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">BATAS KEMBALI</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TGL KEMBALI AKTUAL</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">STATUS</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-28">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($monitoringBorrowings as $b)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- 1. No --}}
                                <td class="py-4 px-4 text-xs font-semibold text-slate-400 text-center">
                                    {{ $monitoringBorrowings->firstItem() + $loop->index }}
                                </td>

                                {{-- 2. Identitas Peminjam (NIS/NIP) --}}
                                <td class="py-4 px-4">
                                    @if($b->siswa)
                                        <div class="font-bold text-slate-900 font-mono text-xs">{{ $b->siswa->nis }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">NISN: {{ $b->siswa->nisn ?: '-' }}</div>
                                    @elseif($b->peminjamGuru)
                                        <div class="font-bold text-slate-900 font-mono text-xs">{{ $b->peminjamGuru->nip ?: '-' }}</div>
                                        <div class="text-[10px] text-slate-400">NIP Guru</div>
                                    @else
                                        <div class="font-bold text-slate-900 font-mono text-xs">#{{ $b->user_id }}</div>
                                        <div class="text-[10px] text-slate-400">User ID</div>
                                    @endif
                                </td>

                                {{-- 3. Nama Lengkap --}}
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">
                                        {{ $b->user?->name ?? ($b->siswa?->nama ?? ($b->peminjamGuru?->nama ?? '-')) }}
                                    </div>
                                    @if($b->siswa?->kelas)
                                        <div class="text-[11px] text-slate-400">{{ $b->siswa->kelas->nama_kelas }}</div>
                                    @endif
                                </td>

                                {{-- 4. Role --}}
                                <td class="py-4 px-4">
                                    @if($b->siswa)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            Siswa
                                        </span>
                                    @elseif($b->peminjamGuru)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-100">
                                            Guru
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600">
                                            Petugas
                                        </span>
                                    @endif
                                </td>

                                {{-- 5. Jurusan --}}
                                <td class="py-4 px-4">
                                    <span class="text-xs font-semibold text-slate-700">
                                        {{ $b->siswa?->kelas?->jurusan?->nama_jurusan ?? ($b->item?->jurusan?->nama_jurusan ?? 'Umum') }}
                                    </span>
                                </td>

                                {{-- 6. Nama Barang --}}
                                <td class="py-4 px-4">
                                    <a href="{{ route('inventory.show', $b->inventory_item_id) }}"
                                       class="font-bold text-slate-900 hover:text-[#003FA9] transition block text-xs sm:text-sm line-clamp-1"
                                       title="{{ $b->item?->nama_barang }}">
                                        {{ $b->item?->nama_barang ?? '-' }}
                                    </a>
                                    <div class="text-[10px] font-mono text-slate-400">{{ $b->item?->kode_barang }}</div>
                                    @if($b->keperluan)
                                        <div class="text-[11px] text-slate-500 italic mt-0.5 line-clamp-1" title="{{ $b->keperluan }}">
                                            {{ $b->keperluan }}
                                        </div>
                                    @endif
                                </td>

                                {{-- 7. Kode Unit --}}
                                <td class="py-4 px-4">
                                    @if($b->borrowingUnits->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach($b->borrowingUnits as $bu)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#EFF5FF] text-[#003FA9] font-mono font-bold text-[11px] border border-blue-100">
                                                    {{ $bu->unit?->kode_unit ?: 'Unit #' . $bu->inventory_unit_id }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-500 font-mono">{{ $b->jumlah }} Unit</span>
                                    @endif
                                </td>

                                {{-- 8. Jumlah Unit --}}
                                <td class="py-4 px-4 text-center font-mono font-bold text-slate-800 text-xs">
                                    {{ $b->jumlah }}
                                </td>

                                {{-- 9. Tgl Pinjam --}}
                                <td class="py-4 px-4 text-xs font-mono font-medium text-slate-600">
                                    {{ $b->tanggal_pinjam ? $b->tanggal_pinjam->format('d/m/Y') : '-' }}
                                </td>

                                {{-- 10. Batas Pengembalian --}}
                                <td class="py-4 px-4 text-xs font-mono font-medium {{ $b->is_overdue ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                    {{ $b->rencana_kembali ? $b->rencana_kembali->format('d/m/Y') : '-' }}
                                </td>

                                {{-- 11. Tgl Pengembalian Aktual --}}
                                <td class="py-4 px-4 text-xs font-mono font-medium text-slate-600">
                                    {{ $b->tanggal_kembali ? $b->tanggal_kembali->format('d/m/Y') : '-' }}
                                </td>

                                {{-- 12. Status --}}
                                <td class="py-4 px-4">
                                    @php $badge = $b->status_badge; @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badge['bg'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                {{-- 13. Aksi --}}
                                <td class="py-4 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']) && in_array($b->status, ['borrowed', 'approved']))
                                            <a href="{{ route('inventory.borrowings', ['search' => $b->id]) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-[#EFF5FF] text-[#003FA9] hover:bg-blue-100 transition shadow-2xs"
                                               title="Proses Pengembalian">
                                                <i class='bx bx-check-shield text-sm'></i>
                                                <span>Kembalikan</span>
                                            </a>
                                        @else
                                            <a href="{{ route('inventory.borrowings', ['search' => $b->id]) }}"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-[#003FA9] hover:bg-[#EFF5FF] transition"
                                               title="Lihat Detail Transaksi">
                                                <i class='bx bx-show text-lg'></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($monitoringBorrowings->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $monitoringBorrowings->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- E. BAGIAN DAFTAR KELOMPOK BARANG BERBENTUK CARD                           --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-[28px] p-6 shadow-sm border border-slate-100 space-y-4">
        {{-- Top Title & Action Buttons --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Katalog Kelompok Aset</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar kelompok model barang praktik dengan pelacakan fisik individual.</p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap self-end sm:self-auto">
                <a href="{{ route('inventory.scan') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                    <i class='bx bx-qr-scan text-base text-[#003FA9]'></i>
                    <span>Scan Barcode / QR</span>
                </a>

                <a href="{{ route('inventory.borrowings') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                    <i class='bx bx-history text-base text-[#003FA9]'></i>
                    <span>Riwayat Transaksi</span>
                </a>

                @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                    <button type="button" onclick="openModal('modal-addinventory')"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold rounded-xl shadow-md shadow-blue-900/10 transition">
                        <i class='bx bx-plus text-base'></i>
                        <span>Tambah Kelompok Aset</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Filter Controls --}}
        <form method="GET" action="{{ route('inventory.index') }}" class="pt-2 border-t border-slate-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {{-- Search --}}
                <div class="lg:col-span-1">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Cari Aset / Unit</label>
                    <div class="relative">
                        <i class='bx bx-search absolute left-3 top-3 text-slate-400 text-base'></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Nama, kode, atau unit..."
                               class="w-full h-10 pl-9 pr-3 text-xs rounded-xl border-0 bg-[#EFF5FF] text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] font-medium outline-none transition">
                    </div>
                </div>

                {{-- Jurusan --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Jurusan Pemilik</label>
                    <select name="jurusan_id" class="w-full h-10 text-xs rounded-xl border-0 bg-[#EFF5FF] text-slate-800 px-3 focus:ring-2 focus:ring-[#003FA9] font-medium outline-none transition">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Kategori</label>
                    <select name="kategori" class="w-full h-10 text-xs rounded-xl border-0 bg-[#EFF5FF] text-slate-800 px-3 focus:ring-2 focus:ring-[#003FA9] font-medium outline-none transition">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Ketersediaan --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Ketersediaan Unit</label>
                    <select name="ketersediaan" class="w-full h-10 text-xs rounded-xl border-0 bg-[#EFF5FF] text-slate-800 px-3 focus:ring-2 focus:ring-[#003FA9] font-medium outline-none transition">
                        <option value="">Semua Status Stok</option>
                        <option value="tersedia" {{ request('ketersediaan') === 'tersedia' ? 'selected' : '' }}>Tersedia Siap Pinjam</option>
                        <option value="habis" {{ request('ketersediaan') === 'habis' ? 'selected' : '' }}>Habis / Sedang Dipinjam</option>
                    </select>
                </div>

                {{-- Filter & Reset Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 h-10 px-4 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold transition shadow-sm">
                        Filter
                    </button>
                    @if(request()->anyFilled(['jurusan_id', 'kategori', 'kondisi', 'ketersediaan', 'status', 'search']))
                        <a href="{{ route('inventory.index') }}" class="h-10 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold flex items-center justify-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Asset Groups Grid --}}
    @if($items->isEmpty())
        <div class="bg-white rounded-[28px] p-16 text-center shadow-sm border border-slate-100">
            <div class="w-16 h-16 rounded-2xl bg-[#EFF5FF] text-[#003FA9] flex items-center justify-center mx-auto mb-4 text-3xl font-bold">
                <i class='bx bx-search-alt'></i>
            </div>
            <h4 class="text-base font-bold text-slate-900">Tidak Ada Kelompok Aset Ditemukan</h4>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto leading-relaxed">
                Tidak ada data peralatan yang cocok dengan filter yang dipilih. Silakan atur ulang filter atau daftarkan kelompok peralatan baru.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($items as $item)
                @php
                    $breakdown = $item->condition_breakdown;
                    $available = $item->countAvailableUnits();
                    $borrowed = $item->countBorrowedUnits();
                    $total = $item->units->where('is_active', true)->count();
                    if ($total === 0) {
                        $total = (int) $item->total_unit;
                        $available = (int) $item->unit_tersedia;
                    }
                @endphp
                <div class="bg-white rounded-[24px] p-5 shadow-sm border border-slate-100 hover:border-blue-200 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div>
                        {{-- Top Header: Department, Scope & Group Code --}}
                        <div class="flex items-center justify-between gap-1.5 mb-3 flex-wrap">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono uppercase bg-[#EFF5FF] text-[#003FA9]">
                                {{ $item->kode_barang }}
                            </span>
                            <div class="flex items-center gap-1">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ $item->jurusan ? $item->jurusan->kode_jurusan : 'UMUM' }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $item->borrowing_scope === 'umum' ? 'bg-blue-50 text-[#003FA9] border border-blue-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}"
                                      title="{{ $item->borrowing_scope === 'umum' ? 'Dapat dipinjam semua jurusan & guru' : 'Hanya untuk siswa jurusan pemilik' }}">
                                    {{ $item->borrowing_scope === 'umum' ? 'Umum' : 'Khusus' }}
                                </span>
                            </div>
                        </div>

                        {{-- Item Name --}}
                        <h4 class="text-sm font-bold text-slate-900 line-clamp-2 leading-snug mb-1" title="{{ $item->nama_barang }}">
                            {{ $item->nama_barang }}
                        </h4>

                        {{-- Category, Brand & Location --}}
                        <div class="space-y-1 my-2.5 text-xs">
                            <div class="text-slate-500 flex items-center gap-1.5 truncate">
                                <i class='bx bx-tag text-slate-400'></i>
                                <span>{{ $item->kategori }}{{ $item->merk ? ' • ' . $item->merk : '' }}</span>
                            </div>
                            <div class="text-slate-600 flex items-center gap-1.5 truncate">
                                <i class='bx bx-map-pin text-[#003FA9]'></i>
                                <span>{{ $item->lokasi_penyimpanan }}</span>
                            </div>
                        </div>

                        {{-- Physical Condition Summary Pills --}}
                        <div class="pt-2.5 pb-3 border-t border-slate-100">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Ringkasan Fisik</p>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if($breakdown['baik'] > 0)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $breakdown['baik'] }} Baik
                                    </span>
                                @endif
                                @if($breakdown['rusak_ringan'] > 0)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ $breakdown['rusak_ringan'] }} Rusak Ringan
                                    </span>
                                @endif
                                @if($breakdown['rusak_berat'] > 0)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $breakdown['rusak_berat'] }} Rusak Berat
                                    </span>
                                @endif
                                @if($breakdown['perlu_pemeriksaan'] > 0)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $breakdown['perlu_pemeriksaan'] }} Verifikasi
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Stock Counts & Action Footer --}}
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Tersedia Siap Pinjam</p>
                                <p class="text-xs font-mono font-bold {{ $available > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $available }} / {{ $total }} Unit
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Dipinjam</p>
                                <p class="text-xs font-mono font-bold text-[#003FA9]">
                                    {{ $borrowed }} Unit
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('inventory.show', $item->id) }}"
                               class="flex-1 py-2 px-3 rounded-xl text-center text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                Detail & Kelola Unit
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($items->hasPages())
            <div class="p-4 bg-white rounded-[24px] shadow-sm border border-slate-100">
                {{ $items->links() }}
            </div>
        @endif
    @endif

</div>
@endsection

@push('modals')
    @include('inventory.modal-wizard')
@endpush
