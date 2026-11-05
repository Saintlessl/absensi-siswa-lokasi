@extends('layouts.app')

@section('page-title', 'Inventaris Alat & Lab Praktik')
@section('page-subtitle', 'Manajemen peralatan bengkel mesin, lab komputer, kamera studio, dan perangkat praktik 5 jurusan')

@section('content')
<div class="space-y-6">

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Alat --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Peralatan</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['total']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0052CC] flex items-center justify-center text-2xl">
                <i class='bx bx-wrench'></i>
            </div>
        </div>

        {{-- Tersedia --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tersedia Siap Pakai</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['tersedia']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                <i class='bx bx-check-shield'></i>
            </div>
        </div>

        {{-- Dipinjam --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sedang Dipinjam</p>
                <h3 class="text-2xl font-bold text-[#0052CC] mt-1">{{ number_format($stats['dipinjam']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0052CC] flex items-center justify-center text-2xl">
                <i class='bx bx-transfer'></i>
            </div>
        </div>

        {{-- Rusak / Maintenance --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Perbaikan / Rusak</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($stats['rusak']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl">
                <i class='bx bx-error-alt'></i>
            </div>
        </div>
    </div>

    {{-- Filter & Actions Bar --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 flex-1">
            {{-- Jurusan --}}
            <div>
                <select name="jurusan_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                            {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kondisi --}}
            <div>
                <select name="kondisi" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                    <option value="">Semua Kondisi</option>
                    <option value="baik" {{ request('kondisi') === 'baik' ? 'selected' : '' }}>Kondisi Baik</option>
                    <option value="rusak_ringan" {{ request('kondisi') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ request('kondisi') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    <option value="maintenance" {{ request('kondisi') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>

            {{-- Keyword --}}
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode/nama alat..."
                    class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-1.5">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['jurusan_id', 'kondisi', 'search']))
                    <a href="{{ route('inventory.index') }}" class="py-2 px-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('inventory.borrowings') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-50 text-[#0052CC] text-xs font-semibold hover:bg-blue-100 transition-colors">
                <i class='bx bx-list-check text-base'></i>
                <span>Transaksi Peminjaman</span>
            </a>
            @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                <a href="{{ route('inventory.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs">
                    <i class='bx bx-plus text-base'></i>
                    <span>Tambah Alat</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Asset Grid --}}
    @if($items->isEmpty())
        <div class="bg-white rounded-3xl p-14 text-center shadow-sm border border-blue-50">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class='bx bx-folder-open'></i>
            </div>
            <h4 class="text-sm font-bold text-slate-800">Tidak Ada Peralatan Ditemukan</h4>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                Silakan ubah filter pencarian atau tambahkan data alat praktik baru.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($items as $item)
                @php
                    $isReady = $item->isAvailable();
                    $badgeKondisi = match($item->kondisi) {
                        'baik'         => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Baik'],
                        'rusak_ringan' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Rusak Ringan'],
                        default        => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Rusak Berat'],
                    };
                @endphp
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-blue-50 hover:border-blue-200 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div>
                        {{-- Top Header --}}
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-blue-50 text-[#0052CC] font-mono">
                                {{ $item->kode_barang }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeKondisi['bg'] }}">
                                {{ $badgeKondisi['label'] }}
                            </span>
                        </div>

                        {{-- Item Name --}}
                        <h4 class="text-sm font-bold text-slate-900 line-clamp-2 mb-1" title="{{ $item->nama_barang }}">
                            {{ $item->nama_barang }}
                        </h4>

                        <p class="text-[11px] text-slate-400 flex items-center gap-1 mb-1">
                            <i class='bx bx-tag'></i> {{ $item->kategori }}
                        </p>
                        <p class="text-[11px] text-slate-500 flex items-center gap-1 mb-3">
                            <i class='bx bx-map-pin text-[#0052CC]'></i> {{ $item->lokasi_penyimpanan }}
                        </p>
                    </div>

                    {{-- Stock & Action --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Tersedia</p>
                            <p class="text-xs font-mono font-bold {{ $item->unit_tersedia > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $item->unit_tersedia }} / {{ $item->total_unit }} Unit
                            </p>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('inventory.show', $item->id) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($items->hasPages())
            <div class="p-4 bg-white rounded-2xl shadow-sm border border-blue-50">
                {{ $items->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
