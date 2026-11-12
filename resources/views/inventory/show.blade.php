@extends('layouts.app')

@section('title', 'Detail Kelompok Aset: ' . $item->nama_barang)
@section('page-title', 'Inventaris Alat & Aset Praktik')
@section('page-subtitle', 'Manajemen Unit Fisik, Kondisi Individual & Riwayat Transaksi')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('inventory.index') }}" class="hover:text-[#003FA9] transition">Inventaris Alat</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">{{ $item->nama_barang }}</li>
@endsection

@section('content')
<div x-data="{ activeTab: 'units', monitorSubTab: 'dipinjam', qrModal: { show: false, code: '', token: '', label: '', url: '' } }" class="space-y-6">

    {{-- Main Asset Header Card --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            {{-- Primary Identity --}}
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-2">
                    <span class="px-3 py-1 rounded-xl text-xs font-bold font-mono bg-[#EFF5FF] text-[#003FA9]">
                        {{ $item->kode_barang }}
                    </span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase bg-slate-100 text-slate-700">
                        {{ $item->jurusan ? $item->jurusan->nama_jurusan : 'Semua Jurusan / Umum' }}
                    </span>
                    @php $scopeBadge = $item->borrowing_scope_badge; @endphp
                    <span class="px-3 py-1 rounded-xl text-xs font-bold {{ $scopeBadge['bg'] }} inline-flex items-center gap-1.5">
                        <i class='{{ $scopeBadge['icon'] }} text-sm'></i>
                        <span>{{ $scopeBadge['label'] }}</span>
                    </span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase {{ $item->countAvailableUnits() > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        {{ $item->countAvailableUnits() }} / {{ $activeUnits->count() }} Unit Siap Pinjam
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $item->nama_barang }}</h1>

                <div class="flex items-center flex-wrap gap-x-4 gap-y-1 mt-2 text-xs sm:text-sm text-slate-500 font-medium">
                    <div>Kategori: <span class="font-bold text-slate-800">{{ $item->kategori }}</span></div>
                    <span>•</span>
                    <div>Merk / Model: <span class="font-bold text-slate-800">{{ $item->merk ?: '-' }} {{ $item->tipe_model ? '(' . $item->tipe_model . ')' : '' }}</span></div>
                    <span>•</span>
                    <div>Lokasi Default: <span class="font-bold text-slate-800">{{ $item->lokasi_penyimpanan }}</span></div>
                </div>

                @if($item->deskripsi)
                    <p class="text-xs text-slate-600 mt-3 max-w-3xl leading-relaxed bg-[#EFF5FF]/50 p-3 rounded-xl border border-blue-50">
                        {{ $item->deskripsi }}
                    </p>
                @endif
            </div>

            {{-- Top Actions --}}
            <div class="flex items-center gap-2.5 flex-wrap self-end lg:self-auto shrink-0">
                <a href="{{ route('inventory.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                    <i class='bx bx-arrow-back text-base'></i>
                    <span>Kembali</span>
                </a>

                <a href="{{ route('inventory.print', $item->id) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                    <i class='bx bx-printer text-base text-[#003FA9]'></i>
                    <span>Cetak Label Unit</span>
                </a>

                @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                    <button type="button" onclick="document.getElementById('modalEditGroup').classList.remove('hidden')"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                        <i class='bx bx-edit text-base text-[#003FA9]'></i>
                        <span>Edit Kelompok</span>
                    </button>

                    <button type="button" onclick="document.getElementById('modalAddUnit').classList.remove('hidden')"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold rounded-xl shadow-md shadow-blue-900/10 transition">
                        <i class='bx bx-plus text-base'></i>
                        <span>Tambah Unit Fisik</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Condition & Stock Metrics Bar --}}
        @php
            $breakdown = $item->condition_breakdown;
            $availCount = $item->countAvailableUnits();
            $borrowCount = $item->countBorrowedUnits();
            $activeCount = $activeUnits->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6">
            <div class="p-3.5 rounded-2xl bg-[#EFF5FF] border border-blue-100/60">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Fisik Aktif</p>
                <p class="text-xl font-bold font-mono text-slate-900 mt-0.5">{{ $activeCount }} <span class="text-xs font-sans font-semibold text-slate-500">Unit</span></p>
            </div>
            <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-100">
                <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Tersedia Siap Pakai</p>
                <p class="text-xl font-bold font-mono text-emerald-600 mt-0.5">{{ $availCount }} <span class="text-xs font-sans font-semibold text-emerald-700">Unit (Baik)</span></p>
            </div>
            <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100">
                <p class="text-[10px] font-bold text-[#003FA9] uppercase tracking-wider">Sedang Dipinjam</p>
                <p class="text-xl font-bold font-mono text-[#003FA9] mt-0.5">{{ $borrowCount }} <span class="text-xs font-sans font-semibold text-blue-700">Unit Keluar</span></p>
            </div>
            <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-100">
                <p class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Perlu Cek / Rusak</p>
                <p class="text-xl font-bold font-mono text-amber-600 mt-0.5">{{ $breakdown['rusak_ringan'] + $breakdown['rusak_berat'] + $breakdown['perlu_pemeriksaan'] }} <span class="text-xs font-sans font-semibold text-amber-700">Unit</span></p>
            </div>
        </div>
    </div>

    {{-- Subpage Navigation Tabs --}}
    <div class="flex items-center gap-2.5 overflow-x-auto pb-1">
        <button type="button" @click="activeTab = 'units'"
                :class="activeTab === 'units' ? 'bg-[#003FA9] text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-devices text-lg'></i>
            <span>Daftar Unit Fisik ({{ $activeUnits->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'borrowings'"
                :class="activeTab === 'borrowings' ? 'bg-[#003FA9] text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-history text-lg'></i>
            <span>Riwayat Peminjaman ({{ $item->borrowings->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'request'"
                :class="activeTab === 'request' ? 'bg-[#003FA9] text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-send text-lg'></i>
            <span>Ajukan Peminjaman</span>
        </button>

        @if($archivedUnits->isNotEmpty() && in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
            <button type="button" @click="activeTab = 'archived'"
                    :class="activeTab === 'archived' ? 'bg-[#003FA9] text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80'"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
                <i class='bx bx-archive text-lg'></i>
                <span>Unit Diarsipkan ({{ $archivedUnits->count() }})</span>
            </button>
        @endif
    </div>

    {{-- ==================== TAB 1: DAFTAR UNIT FISIK ==================== --}}
    <div x-show="activeTab === 'units'" class="space-y-4">
        <div class="bg-white rounded-[28px] p-6 shadow-sm border border-slate-100 overflow-hidden">
            <div class="pb-4 mb-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Unit Fisik Individual</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Setiap unit memiliki nomor barcode unik, QR code, dan pelacakan kondisi mandiri.</p>
                </div>
                @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                    <button type="button" onclick="document.getElementById('modalAddUnit').classList.remove('hidden')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#003FA9] text-white text-xs font-bold hover:bg-[#002D7A] transition">
                        <i class='bx bx-plus text-base'></i>
                        <span>Tambah Unit</span>
                    </button>
                @endif
            </div>

            @if($activeUnits->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <i class='bx bx-box text-4xl mb-2 text-slate-300'></i>
                    <p class="text-sm font-semibold text-slate-600">Belum Ada Unit Fisik Terdaftar</p>
                    <p class="text-xs text-slate-400 mt-1">Tambahkan unit fisik untuk mulai mengelola stok dan peminjaman.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kode Unit</th>
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kondisi Fisik</th>
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Ketersediaan</th>
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Lokasi Unit</th>
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Barcode / QR</th>
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($activeUnits as $unit)
                                @php
                                    $badgeKondisi = match($unit->kondisi) {
                                        'baik'              => ['bg' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'label' => 'Baik'],
                                        'rusak_ringan'      => ['bg' => 'bg-amber-50 text-amber-700 border border-amber-200', 'label' => 'Rusak Ringan'],
                                        'rusak_berat'       => ['bg' => 'bg-rose-50 text-rose-700 border border-rose-200', 'label' => 'Rusak Berat'],
                                        'perlu_pemeriksaan' => ['bg' => 'bg-slate-100 text-slate-700 border border-slate-200', 'label' => 'Perlu Pemeriksaan'],
                                    };
                                    $isBorrowed = $unit->status_ketersediaan === 'dipinjam';
                                    $isReserved = $unit->isReserved();
                                    $activeBorrowing = $unit->activeBorrowing();
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    {{-- Kode Unit --}}
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono font-bold text-[#003FA9] text-xs flex items-center gap-1.5">
                                            <span>{{ $unit->kode_unit }}</span>
                                        </div>
                                        @if($unit->barcode_eksternal)
                                            <div class="text-[10px] font-mono text-slate-400 mt-0.5">Barcode: {{ $unit->barcode_eksternal }}</div>
                                        @endif
                                    </td>

                                    {{-- Kondisi Fisik --}}
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $badgeKondisi['bg'] }}">
                                            {{ $badgeKondisi['label'] }}
                                        </span>
                                    </td>

                                    {{-- Status Ketersediaan --}}
                                    <td class="py-3.5 px-4">
                                        @if($isBorrowed)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#003FA9] text-white">
                                                Dipinjam
                                            </span>
                                            @if($activeBorrowing)
                                                <div class="text-[10px] text-slate-500 mt-0.5 font-medium truncate max-w-[150px]" title="Peminjam: {{ $activeBorrowing->user?->name }}">
                                                    Oleh: {{ $activeBorrowing->user?->name }}
                                                </div>
                                            @endif
                                        @elseif($isReserved)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Direservasi
                                            </span>
                                            @if($activeBorrowing)
                                                <div class="text-[10px] text-slate-400 mt-0.5">Untuk: {{ $activeBorrowing->user?->name }}</div>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Tersedia
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Lokasi Unit --}}
                                    <td class="py-3.5 px-4 text-xs font-medium text-slate-600">
                                        <i class='bx bx-map-pin text-[#003FA9]'></i> {{ $unit->effective_lokasi }}
                                    </td>

                                    {{-- QR Code / Barcode Button --}}
                                    <td class="py-3.5 px-4">
                                        <button type="button"
                                            @click="qrModal = { show: true, code: '{{ $unit->kode_unit }}', token: '{{ $unit->qr_token }}', label: '{{ addslashes($item->nama_barang) }}', url: 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($unit->kode_unit) }}' }"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-blue-50 hover:text-[#003FA9] text-slate-700 transition">
                                            <i class='bx bx-qr text-base'></i>
                                            <span>Lihat QR</span>
                                        </button>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- History Audit Log --}}
                                            <button type="button" onclick="showUnitLogs('{{ $unit->id }}')"
                                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition"
                                                title="Riwayat Penggunaan & Audit">
                                                <i class='bx bx-history text-base'></i>
                                            </button>

                                            @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                                                {{-- Edit Unit Condition --}}
                                                <button type="button"
                                                    onclick="editUnit('{{ $unit->id }}', '{{ $unit->kode_unit }}', '{{ $unit->kondisi }}', '{{ addslashes($unit->lokasi_penyimpanan ?: '') }}', '{{ addslashes($unit->barcode_eksternal ?: '') }}', '{{ $unit->tanggal_pengadaan ? $unit->tanggal_pengadaan->format('Y-m-d') : '' }}', '{{ addslashes($unit->catatan ?: '') }}')"
                                                    class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#003FA9] flex items-center justify-center transition"
                                                    title="Edit Kondisi & Informasi Unit">
                                                    <i class='bx bx-edit text-base'></i>
                                                </button>

                                                {{-- Archive Unit --}}
                                                <button type="button"
                                                    onclick="archiveUnitPrompt('{{ $unit->id }}', '{{ $unit->kode_unit }}')"
                                                    class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition"
                                                    title="Arsipkan Unit">
                                                    <i class='bx bx-archive-in text-base'></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== TAB 2: MONITORING PEMINJAMAN KELOMPOK ASET ==================== --}}
    <div x-show="activeTab === 'borrowings'" class="space-y-4" style="display: none;">
        <div class="bg-white rounded-[28px] p-6 sm:p-7 shadow-sm border border-slate-100 space-y-6 overflow-hidden">
            {{-- Section Title & 3 Status Tabs --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Monitoring Peminjaman: {{ $item->nama_barang }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Daftar transaksi peminjaman fisik yang khusus meminjam kelompok alat ini.</p>
                </div>

                {{-- 3 Tab Status Peminjaman (Sedang Dipinjam, Dikembalikan, Belum Dikembalikan) --}}
                <div class="inline-flex p-1 rounded-2xl bg-slate-100 border border-slate-200/60 text-xs font-bold gap-1 self-stretch sm:self-auto">
                    <button type="button" @click="monitorSubTab = 'dipinjam'"
                        :class="monitorSubTab === 'dipinjam' ? 'bg-[#003FA9] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 justify-center">
                        <span>Sedang Dipinjam</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                              :class="monitorSubTab === 'dipinjam' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">
                            {{ $itemCountDipinjam }}
                        </span>
                    </button>

                    <button type="button" @click="monitorSubTab = 'dikembalikan'"
                        :class="monitorSubTab === 'dikembalikan' ? 'bg-[#003FA9] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 justify-center">
                        <span>Dikembalikan</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                              :class="monitorSubTab === 'dikembalikan' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">
                            {{ $itemCountDikembalikan }}
                        </span>
                    </button>

                    <button type="button" @click="monitorSubTab = 'belum_dikembalikan'"
                        :class="monitorSubTab === 'belum_dikembalikan' ? 'bg-[#003FA9] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 justify-center">
                        <span>Belum Dikembalikan</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                              :class="monitorSubTab === 'belum_dikembalikan' ? 'bg-rose-500 text-white' : 'bg-rose-100 text-rose-700'">
                            {{ $itemCountBelumDikembalikan }}
                        </span>
                    </button>
                </div>
            </div>

            {{-- SUBTAB 1: SEDANG DIPINJAM --}}
            <div x-show="monitorSubTab === 'dipinjam'">
                @if($borrowingsDipinjam->isEmpty())
                    <div class="py-12 text-center text-slate-400">
                        <i class='bx bx-check-circle text-4xl mb-2 text-emerald-400'></i>
                        <p class="text-sm font-semibold text-slate-700">Tidak Ada Unit yang Sedang Dipinjam</p>
                        <p class="text-xs text-slate-400 mt-1">Saat ini tidak ada unit alat {{ $item->nama_barang }} yang berstatus aktif dipinjam.</p>
                    </div>
                @else
                    @include('inventory.partials.detail-monitoring-table', ['borrowings' => $borrowingsDipinjam, 'currentTab' => 'dipinjam'])
                @endif
            </div>

            {{-- SUBTAB 2: DIKEMBALIKAN --}}
            <div x-show="monitorSubTab === 'dikembalikan'" style="display: none;">
                @if($borrowingsDikembalikan->isEmpty())
                    <div class="py-12 text-center text-slate-400">
                        <i class='bx bx-history text-4xl mb-2 text-slate-300'></i>
                        <p class="text-sm font-semibold text-slate-700">Belum Ada Riwayat Pengembalian</p>
                        <p class="text-xs text-slate-400 mt-1">Belum ada transaksi pengembalian yang tercatat untuk kelompok alat ini.</p>
                    </div>
                @else
                    @include('inventory.partials.detail-monitoring-table', ['borrowings' => $borrowingsDikembalikan, 'currentTab' => 'dikembalikan'])
                @endif
            </div>

            {{-- SUBTAB 3: BELUM DIKEMBALIKAN (TERLAMBAT) --}}
            <div x-show="monitorSubTab === 'belum_dikembalikan'" style="display: none;">
                @if($borrowingsBelumDikembalikan->isEmpty())
                    <div class="py-12 text-center text-slate-400">
                        <i class='bx bx-shield-quarter text-4xl mb-2 text-emerald-400'></i>
                        <p class="text-sm font-semibold text-slate-700">Tidak Ada Keterlambatan Pengembalian</p>
                        <p class="text-xs text-slate-400 mt-1">Seluruh peminjaman alat {{ $item->nama_barang }} masih berada dalam batas waktu yang ditentukan.</p>
                    </div>
                @else
                    @include('inventory.partials.detail-monitoring-table', ['borrowings' => $borrowingsBelumDikembalikan, 'currentTab' => 'belum_dikembalikan'])
                @endif
            </div>
        </div>
    </div>

    {{-- ==================== TAB 3: AJUKAN PEMINJAMAN ==================== --}}
    <div x-show="activeTab === 'request'" class="space-y-4" style="display: none;">
        <div class="bg-white rounded-[28px] p-7 shadow-sm border border-slate-100 max-w-2xl">
            <div class="pb-4 mb-5 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Form Pengajuan Peminjaman Alat</h3>
                <p class="text-xs text-slate-500 mt-0.5">Ajukan peminjaman kelompok alat ini. Unit fisik akan dialokasikan oleh Kaprog saat persetujuan.</p>
            </div>

            @if($item->countAvailableUnits() > 0)
                <form method="POST" action="{{ route('inventory.borrow.request') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="inventory_item_id" value="{{ $item->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Jumlah Unit <span class="text-rose-500">*</span></label>
                            <input type="number" name="jumlah" value="1" min="1" max="{{ $item->countAvailableUnits() }}" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#EFF5FF] px-4 font-mono font-bold text-slate-800 focus:ring-2 focus:ring-[#003FA9] outline-none">
                            <p class="text-[11px] text-slate-400 mt-1">Stok unit layak tersedia: <span class="font-bold text-emerald-600">{{ $item->countAvailableUnits() }} Unit</span></p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Rencana Pengembalian <span class="text-rose-500">*</span></label>
                            <input type="date" name="rencana_kembali" value="{{ now()->addDays(2)->toDateString() }}" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#EFF5FF] px-4 text-slate-800 focus:ring-2 focus:ring-[#003FA9] outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Keperluan & Tempat Penggunaan <span class="text-rose-500">*</span></label>
                        <textarea name="keperluan" rows="3" required placeholder="Contoh: Praktikum pembuatan materi video profil sekolah di Studio Lab BCF..."
                            class="w-full text-sm rounded-xl border-0 bg-[#EFF5FF] p-4 text-slate-800 focus:ring-2 focus:ring-[#003FA9] outline-none resize-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-xs font-bold hover:bg-[#002D7A] transition shadow-md shadow-blue-900/10">
                            <i class='bx bx-check text-base'></i>
                            <span>Kirim Permohonan Peminjaman</span>
                        </button>
                    </div>
                </form>
            @else
                <div class="p-6 rounded-2xl bg-amber-50 text-amber-900 border border-amber-200 text-center">
                    <i class='bx bx-error-circle text-3xl mb-2 text-amber-600'></i>
                    <h4 class="text-sm font-bold">Stok Unit Fisik Tidak Tersedia</h4>
                    <p class="text-xs text-amber-700 mt-1 max-w-sm mx-auto">
                        Semua unit dari kelompok alat ini sedang dipinjam atau dalam status perbaikan/pemeriksaan fisik.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== TAB 4: UNIT DIARSIPKAN ==================== --}}
    @if($archivedUnits->isNotEmpty())
        <div x-show="activeTab === 'archived'" class="space-y-4" style="display: none;">
            <div class="bg-white rounded-[28px] p-6 shadow-sm border border-slate-100 overflow-hidden">
                <div class="pb-4 mb-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">Unit Fisik yang Diarsipkan / Dinonaktifkan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Unit yang dikeluarkan dari stok aktif karena rusak berat, hilang, atau dihapuskan dari daftar inventaris sekolah.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kode Unit</th>
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Alasan Pengarsipan</th>
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Catatan</th>
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kondisi Terakhir</th>
                                <th class="py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($archivedUnits as $u)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-700 text-xs">{{ $u->kode_unit }}</td>
                                    <td class="py-3 px-4 uppercase text-xs font-bold text-rose-600">{{ str_replace('_', ' ', $u->status_alasan ?: 'Diarsipkan') }}</td>
                                    <td class="py-3 px-4 text-xs text-slate-500">{{ $u->catatan ?: '-' }}</td>
                                    <td class="py-3 px-4 uppercase text-xs font-semibold text-slate-600">{{ str_replace('_', ' ', $u->kondisi) }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <form action="{{ route('inventory.units.restore', $u->id) }}" method="POST" class="inline"
                                            data-confirm="Aktifkan kembali unit ini?"
                                            data-confirm-title="Aktifkan Kembali Unit">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-[#003FA9] hover:bg-blue-100 transition">
                                                Aktifkan Kembali
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== MODAL: LIHAT QR CODE UNIT ==================== --}}
    <div x-show="qrModal.show" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="qrModal.show = false"></div>
        <div class="relative flex items-center justify-center min-h-full p-4">
            <div class="relative w-full max-w-sm bg-white rounded-[28px] p-6 shadow-2xl border border-slate-100 text-center space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-900">Label QR Unit Fisik</h4>
                    <button type="button" @click="qrModal.show = false" class="text-slate-400 hover:text-slate-600">
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>

                <div class="w-48 h-48 mx-auto p-3 bg-white rounded-2xl border border-slate-200 flex items-center justify-center shadow-xs">
                    <img :src="qrModal.url" alt="QR Code Unit" class="w-full h-full object-contain">
                </div>

                <div>
                    <h5 class="text-xs font-bold text-slate-500" x-text="qrModal.label"></h5>
                    <p class="text-sm font-mono font-bold text-[#003FA9] mt-0.5" x-text="qrModal.code"></p>
                    <p class="text-[10px] text-slate-400 font-mono mt-1 truncate" x-text="'Token: ' + qrModal.token"></p>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <button type="button" @click="qrModal.show = false" class="w-full py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('modals')
{{-- ==================== MODAL: TAMBAH UNIT FISIK ==================== --}}
@if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
<div id="modalAddUnit" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalAddUnit').classList.add('hidden')"></div>
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-lg bg-[#EFF5FF] rounded-[28px] p-7 shadow-2xl border border-blue-100/60 text-left">
            <div class="flex items-center justify-between pb-4 border-b border-blue-100/60">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Tambah Unit Fisik</h3>
                    <p class="text-xs font-semibold text-[#003FA9] mt-0.5">{{ $item->nama_barang }} ({{ $item->kode_barang }})</p>
                </div>
                <button type="button" onclick="document.getElementById('modalAddUnit').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <form method="POST" action="{{ route('inventory.units.store', $item->id) }}" class="space-y-4 pt-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Unit <span class="text-rose-500">*</span></label>
                        <input type="number" name="jumlah_unit" value="1" min="1" max="50" required
                            class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 font-mono font-bold focus:ring-2 focus:ring-[#003FA9]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Awal <span class="text-rose-500">*</span></label>
                        <select name="kondisi" required class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-3 focus:ring-2 focus:ring-[#003FA9] font-semibold">
                            <option value="baik">Baik (Siap Pakai)</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                            <option value="perlu_pemeriksaan">Perlu Pemeriksaan</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Unit Manual (Opsional jika 1 unit)</label>
                    <input type="text" name="kode_unit_manual" placeholder="Contoh: {{ $item->kode_barang }}-0009"
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 font-mono uppercase focus:ring-2 focus:ring-[#003FA9]">
                    <p class="text-[10px] text-slate-500 mt-1">Kosongkan agar sistem men-generate kode unik otomatis.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Barcode Eksternal Pabrik (Opsional)</label>
                    <input type="text" name="barcode_eksternal" placeholder="Scan barcode produk jika tersedia..."
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 font-mono focus:ring-2 focus:ring-[#003FA9]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Penyimpanan Spesifik (Opsional)</label>
                    <input type="text" name="lokasi_penyimpanan" value="{{ $item->lokasi_penyimpanan }}"
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengadaan / Pencatatan</label>
                    <input type="date" name="tanggal_pengadaan" value="{{ now()->toDateString() }}"
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                    <input type="text" name="catatan" placeholder="Nomor seri pabrik, garansi, kelengkapan..."
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                </div>

                <div class="pt-4 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalAddUnit').classList.add('hidden')" class="px-5 py-2.5 rounded-full text-xs font-bold text-[#003FA9] hover:bg-blue-50">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-xs font-bold hover:bg-[#002D7A] shadow-md shadow-blue-900/10">
                        Simpan Unit Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== MODAL: EDIT UNIT FISIK ==================== --}}
<div id="modalEditUnit" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalEditUnit').classList.add('hidden')"></div>
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-lg bg-[#EFF5FF] rounded-[28px] p-7 shadow-2xl border border-blue-100/60 text-left">
            <div class="flex items-center justify-between pb-4 border-b border-blue-100/60">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Edit Kondisi & Informasi Unit</h3>
                    <p id="editUnitCodeDisplay" class="text-xs font-mono font-bold text-[#003FA9] mt-0.5"></p>
                </div>
                <button type="button" onclick="document.getElementById('modalEditUnit').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <form id="formEditUnit" method="POST" action="" class="space-y-4 pt-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Fisik Unit <span class="text-rose-500">*</span></label>
                    <select name="kondisi" id="editUnitKondisi" required class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-3 focus:ring-2 focus:ring-[#003FA9] font-bold">
                        <option value="baik">Kondisi Baik (Siap Digunakan)</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                        <option value="perlu_pemeriksaan">Perlu Pemeriksaan Petugas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Penyimpanan Unit</label>
                    <input type="text" name="lokasi_penyimpanan" id="editUnitLokasi"
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Barcode Eksternal</label>
                    <input type="text" name="barcode_eksternal" id="editUnitBarcode"
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 font-mono focus:ring-2 focus:ring-[#003FA9]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengadaan</label>
                    <input type="date" name="tanggal_pengadaan" id="editUnitPengadaan"
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Alasan Perubahan (Dicatat di Log Audit)</label>
                    <textarea name="keterangan_perubahan" rows="2" placeholder="Jelaskan alasan jika kondisi berubah atau unit diperbaiki..."
                        class="w-full text-xs rounded-xl border-0 bg-[#E2EDFF] p-3 text-slate-800 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                </div>

                <div class="pt-4 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalEditUnit').classList.add('hidden')" class="px-5 py-2.5 rounded-full text-xs font-bold text-[#003FA9] hover:bg-blue-50">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-xs font-bold hover:bg-[#002D7A] shadow-md shadow-blue-900/10">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== MODAL: ARSIPKAN UNIT ==================== --}}
<div id="modalArchiveUnit" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalArchiveUnit').classList.add('hidden')"></div>
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-md bg-white rounded-[28px] p-6 shadow-2xl border border-slate-100 text-left">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-bold text-slate-900">Arsipkan Unit Fisik</h4>
                <button type="button" onclick="document.getElementById('modalArchiveUnit').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <form id="formArchiveUnit" method="POST" action="" class="space-y-4 pt-3">
                @csrf
                <p class="text-xs text-slate-600">
                    Unit <span id="archiveUnitCodeDisplay" class="font-bold font-mono text-[#003FA9]"></span> akan dinonaktifkan dari daftar inventaris aktif tanpa menghapus riwayat peminjamannya.
                </p>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pengarsipan <span class="text-rose-500">*</span></label>
                    <select name="status_alasan" required class="w-full h-11 text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 font-semibold">
                        <option value="rusak_berat">Rusak Berat (Tidak Layak Pakai)</option>
                        <option value="hilang">Hilang</option>
                        <option value="dihapuskan">Dihapuskan / Dihibahkan</option>
                        <option value="diarsipkan">Diarsipkan Sementara</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Berita Acara</label>
                    <textarea name="catatan" rows="2" placeholder="Keterangan kondisi kerusakan atau nomor berita acara..."
                        class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 p-3 resize-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalArchiveUnit').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm">
                        Konfirmasi Arsipkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== MODAL: EDIT KELOMPOK BARANG ==================== --}}
<div id="modalEditGroup" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalEditGroup').classList.add('hidden')"></div>
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-xl bg-[#EFF5FF] rounded-[28px] p-7 shadow-2xl border border-blue-100/60 text-left">
            <div class="flex items-center justify-between pb-4 border-b border-blue-100/60">
                <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Edit Informasi Kelompok Aset</h3>
                <button type="button" onclick="document.getElementById('modalEditGroup').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <form method="POST" action="{{ route('inventory.update', $item->id) }}" class="space-y-4 pt-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Peralatan / Model <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_barang" value="{{ $item->nama_barang }}" required
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 font-semibold focus:ring-2 focus:ring-[#003FA9]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                        <input type="text" name="kategori" value="{{ $item->kategori }}" required
                            class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan Pemilik</label>
                        <select name="jurusan_id" class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-3 focus:ring-2 focus:ring-[#003FA9]">
                            <option value="">Semua Jurusan / Umum</option>
                            @foreach($jurusans as $j)
                                <option value="{{ $j->id }}" {{ $item->jurusan_id == $j->id ? 'selected' : '' }}>
                                    {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Merek</label>
                        <input type="text" name="merk" value="{{ $item->merk }}"
                            class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Model / Tipe</label>
                        <input type="text" name="tipe_model" value="{{ $item->tipe_model }}"
                            class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Default <span class="text-rose-500">*</span></label>
                    <input type="text" name="lokasi_penyimpanan" value="{{ $item->lokasi_penyimpanan }}" required
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                </div>

                {{-- Cakupan Akses Peminjaman --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cakupan Akses Peminjaman <span class="text-rose-500">*</span></label>
                    <select name="borrowing_scope" required class="w-full h-11 text-xs rounded-xl border-0 bg-[#E2EDFF] px-3 focus:ring-2 focus:ring-[#003FA9] font-bold">
                        <option value="jurusan" {{ ($item->borrowing_scope ?: 'jurusan') === 'jurusan' ? 'selected' : '' }}>
                            Khusus Jurusan (Hanya Siswa Jurusan Pemilik)
                        </option>
                        <option value="umum" {{ $item->borrowing_scope === 'umum' ? 'selected' : '' }}>
                            Umum (Siswa Semua Jurusan & Guru)
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi & Kelengkapan</label>
                    <textarea name="deskripsi" rows="3" class="w-full text-xs rounded-xl border-0 bg-[#E2EDFF] p-3 focus:ring-2 focus:ring-[#003FA9] resize-none">{{ $item->deskripsi }}</textarea>
                </div>

                <div class="pt-4 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalEditGroup').classList.add('hidden')" class="px-5 py-2.5 rounded-full text-xs font-bold text-[#003FA9] hover:bg-blue-50">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-xs font-bold hover:bg-[#002D7A] shadow-md shadow-blue-900/10">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ==================== MODAL: RIWAYAT AUDIT UNIT ==================== --}}
<div id="modalUnitLogs" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalUnitLogs').classList.add('hidden')"></div>
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-xl bg-white rounded-[28px] p-6 sm:p-7 shadow-2xl border border-slate-100 text-left">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Riwayat Penggunaan & Audit Unit</h3>
                    <p id="logModalUnitInfo" class="text-xs font-mono font-bold text-[#003FA9] mt-0.5">Memuat...</p>
                </div>
                <button type="button" onclick="document.getElementById('modalUnitLogs').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <div id="logTimelineContent" class="py-4 max-h-96 overflow-y-auto space-y-3">
                <div class="text-center py-6 text-slate-400 text-xs">Memuat riwayat log...</div>
            </div>

            <div class="pt-3 border-t border-slate-100 text-right">
                <button type="button" onclick="document.getElementById('modalUnitLogs').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function editUnit(id, code, kondisi, lokasi, barcode, tgl, catatan) {
    document.getElementById('editUnitCodeDisplay').textContent = code;
    document.getElementById('editUnitKondisi').value = kondisi;
    document.getElementById('editUnitLokasi').value = lokasi;
    document.getElementById('editUnitBarcode').value = barcode;
    document.getElementById('editUnitPengadaan').value = tgl;
    document.getElementById('formEditUnit').action = '/inventaris/units/' + id;
    document.getElementById('modalEditUnit').classList.remove('hidden');
}

function archiveUnitPrompt(id, code) {
    document.getElementById('archiveUnitCodeDisplay').textContent = code;
    document.getElementById('formArchiveUnit').action = '/inventaris/units/' + id + '/archive';
    document.getElementById('modalArchiveUnit').classList.remove('hidden');
}

async function showUnitLogs(unitId) {
    document.getElementById('modalUnitLogs').classList.remove('hidden');
    const container = document.getElementById('logTimelineContent');
    const title = document.getElementById('logModalUnitInfo');
    container.innerHTML = '<div class="text-center py-8 text-slate-400 text-xs"><i class="bx bx-loader-alt bx-spin text-2xl block mb-2 text-[#003FA9]"></i>Memuat riwayat...</div>';

    try {
        const res = await fetch('/inventaris/units/' + unitId + '/logs', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        title.textContent = data.unit.kode_unit + ' (' + data.unit.kondisi.toUpperCase() + ') — ' + data.unit.nama_barang;

        if (data.logs.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-slate-400 text-xs">Belum ada riwayat aktivitas untuk unit ini.</div>';
            return;
        }

        let html = '<div class="relative border-l-2 border-blue-100 ml-4 space-y-4 pl-4">';
        data.logs.forEach(log => {
            html += `
                <div class="relative">
                    <div class="absolute -left-[23px] top-1 w-3.5 h-3.5 rounded-full bg-[#003FA9] border-2 border-white shadow-xs"></div>
                    <div class="text-[11px] text-slate-400 font-mono">${log.waktu} • Oleh: <strong class="text-slate-600">${log.user}</strong></div>
                    <div class="text-xs font-bold text-slate-800 mt-0.5">${log.action_label}</div>
                    <div class="text-xs text-slate-600 mt-0.5">${log.keterangan || '-'}</div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    } catch (e) {
        container.innerHTML = '<div class="text-center py-8 text-rose-500 text-xs">Gagal memuat log audit unit.</div>';
    }
}
</script>
@endpush
