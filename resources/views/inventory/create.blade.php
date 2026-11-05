@extends('layouts.app')

@section('page-title', 'Tambah Peralatan Praktik')
@section('page-subtitle', 'Daftarkan alat baru ke inventaris laboratorium atau bengkel jurusan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-blue-50">
        <form method="POST" action="{{ route('inventory.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Kode Barang --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kode Unik Alat / Aset *</label>
                    <input type="text" name="kode_barang" value="{{ old('kode_barang') }}" placeholder="Contoh: BCF-CAM-001, TPFL-WLD-002" required
                        class="w-full text-xs font-mono font-bold uppercase rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Nama Barang --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Peralatan / Perangkat *</label>
                    <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="Contoh: Kamera Sony Alpha a6400 / Mesin Las Inverter" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Jurusan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Jurusan / Pemilik Alat</label>
                    <select name="jurusan_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                        <option value="">Semua Jurusan / Umum</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori Peralatan *</label>
                    <input type="text" name="kategori" value="{{ old('kategori') }}" placeholder="Contoh: Kamera & Video, Mesin & Las, Alat Otomotif, Komputer" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Lokasi Penyimpanan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Lokasi Penyimpanan *</label>
                    <input type="text" name="lokasi_penyimpanan" value="{{ old('lokasi_penyimpanan') }}" placeholder="Contoh: Lemari Kaca Studio BCF / Rak Bengkel Las TPFL" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Total Unit & Kondisi --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Jumlah Unit *</label>
                        <input type="number" name="total_unit" value="{{ old('total_unit', 1) }}" min="1" max="1000" required
                            class="w-full text-xs font-mono font-bold rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kondisi Awal *</label>
                        <select name="kondisi" required class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                            <option value="baik">Kondisi Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi & Kelengkapan (Opsional)</label>
                    <textarea name="deskripsi" rows="3" placeholder="Kelengkapan unit (charger, box, mata las, kabel) atau spesifikasi singkat..."
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 p-3">{{ old('deskripsi') }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('inventory.index') }}" class="px-5 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs flex items-center gap-1.5">
                    <i class='bx bx-check'></i> Simpan Aset Peralatan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
