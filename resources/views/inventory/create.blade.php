@extends('layouts.app')

@section('title', 'Tambah Kelompok Aset Praktik')
@section('page-title', 'Inventaris Alat & Aset Praktik')
@section('page-subtitle', 'Daftarkan kelompok aset baru ke inventaris laboratorium atau bengkel jurusan')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('inventory.index') }}" class="hover:text-[#003FA9] transition">Inventaris Alat</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Tambah Aset</li>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100">
        <div class="pb-5 mb-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Formulir Tambah Kelompok Aset</h2>
                <p class="text-xs text-slate-500 mt-0.5">Sistem akan secara otomatis membuat unit fisik individual sesuai jumlah yang dimasukkan.</p>
            </div>
            <a href="{{ route('inventory.index') }}" class="text-slate-400 hover:text-slate-600">
                <i class='bx bx-x text-2xl'></i>
            </a>
        </div>

        <form method="POST" action="{{ route('inventory.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Kode Barang (Kode Kelompok) --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Kelompok Aset <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_barang" value="{{ old('kode_barang') }}" placeholder="Contoh: PPLG-LTP-OMEN16, BCF-CAM-001" required
                        class="w-full h-11 text-xs font-mono font-bold uppercase rounded-xl border-0 bg-[#EFF5FF] px-4 text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none">
                </div>

                {{-- Nama Barang --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Peralatan / Model <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="Contoh: OMEN 16 / Kamera Sony Alpha a6400" required
                        class="w-full h-11 text-xs font-semibold rounded-xl border-0 bg-[#EFF5FF] px-4 text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none">
                </div>

                {{-- Jurusan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan Pemilik</label>
                    <select name="jurusan_id" class="w-full h-11 text-xs rounded-xl border-0 bg-[#EFF5FF] px-3 text-slate-800 focus:ring-2 focus:ring-[#003FA9] outline-none font-medium">
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Peralatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="kategori" value="{{ old('kategori') }}" placeholder="Laptop, Kamera, Mesin Las, dsb." required
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#EFF5FF] px-4 text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none font-medium">
                </div>

                {{-- Merk & Tipe/Model --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Merek / Brand</label>
                    <input type="text" name="merk" value="{{ old('merk') }}" placeholder="HP, Sony, Asus, dsb."
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#EFF5FF] px-4 text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Model / Tipe</label>
                    <input type="text" name="tipe_model" value="{{ old('tipe_model') }}" placeholder="OMEN 16, A6400, dsb."
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#EFF5FF] px-4 text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none">
                </div>

                {{-- Lokasi Penyimpanan --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Penyimpanan Default <span class="text-rose-500">*</span></label>
                    <input type="text" name="lokasi_penyimpanan" value="{{ old('lokasi_penyimpanan') }}" placeholder="Contoh: Kantor PPLG, Lemari Kaca Studio BCF, dsb." required
                        class="w-full h-11 text-xs rounded-xl border-0 bg-[#EFF5FF] px-4 text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none font-medium">
                </div>

                {{-- Cakupan Akses Peminjaman --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Cakupan Akses Peminjaman <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ scope: '{{ old('borrowing_scope', 'jurusan') }}' }">
                        <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition"
                               :class="scope === 'jurusan' ? 'border-[#003FA9] bg-[#EFF5FF]' : 'border-slate-200 bg-white'">
                            <input type="radio" name="borrowing_scope" value="jurusan" x-model="scope" class="mt-0.5 text-[#003FA9] focus:ring-[#003FA9]">
                            <div>
                                <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <i class='bx bx-lock-alt text-amber-600'></i>
                                    <span>Khusus Jurusan</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">Hanya siswa dari jurusan pemilik barang yang dapat meminjam.</div>
                            </div>
                        </label>
                        <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition"
                               :class="scope === 'umum' ? 'border-[#003FA9] bg-[#EFF5FF]' : 'border-slate-200 bg-white'">
                            <input type="radio" name="borrowing_scope" value="umum" x-model="scope" class="mt-0.5 text-[#003FA9] focus:ring-[#003FA9]">
                            <div>
                                <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <i class='bx bx-globe text-[#003FA9]'></i>
                                    <span>Umum (Semua Jurusan & Guru)</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">Dapat dipinjam oleh siswa dari seluruh jurusan dan guru SMK.</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Total Unit & Kondisi Awal --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Unit Awal <span class="text-rose-500">*</span></label>
                    <input type="number" name="total_unit" value="{{ old('total_unit', 1) }}" min="1" max="1000" required
                        class="w-full h-11 text-xs font-mono font-bold rounded-xl border-0 bg-[#EFF5FF] px-4 text-slate-800 focus:ring-2 focus:ring-[#003FA9] outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">Sistem akan men-generate {{ old('total_unit', 1) }} unit fisik individual.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Awal Unit <span class="text-rose-500">*</span></label>
                    <select name="kondisi" required class="w-full h-11 text-xs rounded-xl border-0 bg-[#EFF5FF] px-3 text-slate-800 focus:ring-2 focus:ring-[#003FA9] outline-none font-semibold">
                        <option value="baik">Kondisi Baik (Siap Digunakan)</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                        <option value="perlu_pemeriksaan">Perlu Pemeriksaan</option>
                    </select>
                </div>

                {{-- Deskripsi --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi & Kelengkapan (Opsional)</label>
                    <textarea name="deskripsi" rows="3" placeholder="Kelengkapan unit (charger, box, mata las, kabel) atau spesifikasi singkat..."
                        class="w-full text-xs rounded-xl border-0 bg-[#EFF5FF] p-3.5 text-slate-800 focus:ring-2 focus:ring-[#003FA9] outline-none resize-none">{{ old('deskripsi') }}</textarea>
                </div>
            </div>

            <div class="pt-5 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 rounded-full text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold transition shadow-md shadow-blue-900/10">
                    <i class='bx bx-check text-base'></i>
                    <span>Simpan Kelompok Aset & Buat Unit</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
