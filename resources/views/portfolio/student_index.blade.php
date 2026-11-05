@extends('layouts.app')

@section('title', 'Portofolio & Karya Saya')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Karya & Portofolio Siswa</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola karya inovasi, aplikasi, dan hasil produksi Teaching Factory Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('portfolio.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                &larr; Lihat Etalase Sekolah
            </a>
            <button type="button" onclick="document.getElementById('modalTambahPortofolio').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Unggah Karya Baru
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($portfolios as $port)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="h-36 bg-slate-100 relative overflow-hidden flex items-center justify-center">
                        @if($port->cover_image_path)
                            <img src="{{ asset('storage/' . $port->cover_image_path) }}" alt="{{ $port->judul }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-slate-400 font-bold text-xs uppercase">{{ $port->kategori }}</div>
                        @endif

                        <span class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold 
                            {{ $port->status === 'approved' ? 'bg-emerald-500 text-white' : ($port->status === 'rejected' ? 'bg-rose-500 text-white' : 'bg-amber-500 text-white') }}">
                            {{ strtoupper($port->status) }}
                        </span>
                    </div>

                    <div class="p-5 space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ str_replace('_', ' ', $port->kategori) }}</span>
                        <h3 class="text-sm font-bold text-slate-800 line-clamp-1">{{ $port->judul }}</h3>
                        <p class="text-xs text-slate-500 line-clamp-2">{{ $port->deskripsi }}</p>

                        @if($port->moderation_notes)
                            <div class="p-2.5 bg-slate-50 rounded-xl text-[11px] text-slate-600 border border-slate-100 mt-2">
                                <span class="font-bold text-slate-700">Catatan Kurator:</span> {{ $port->moderation_notes }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-5 pt-0 border-t border-slate-50 flex items-center justify-between text-xs mt-3">
                    @if($port->status === 'approved')
                        <a href="{{ route('portfolio.show', $port->slug) }}" class="text-blue-600 font-bold hover:underline">
                            Lihat di Etalase &rarr;
                        </a>
                    @else
                        <span class="text-slate-400 text-[11px]">Dalam Antrean Kurasi</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-dashed border-slate-200">
                <p class="text-xs text-slate-400 font-medium">Anda belum mengunggah karya portofolio apapun.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Upload Portofolio -->
<div id="modalTambahPortofolio" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-sm font-black text-slate-800">Unggah Karya / Portofolio Baru</h3>
            <button onclick="document.getElementById('modalTambahPortofolio').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('portfolio.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Judul Karya / Proyek *</label>
                <input type="text" name="judul" required placeholder="Contoh: Aplikasi Sistem Kasir POS Berbasis Flutter" class="w-full text-xs rounded-xl border-slate-200">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Kategori Bidang *</label>
                <select name="kategori" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="web_app">Web Application & Cloud</option>
                    <option value="game">Game Development (2D/3D)</option>
                    <option value="2d_animation">Animasi 2D</option>
                    <option value="3d_animation">Animasi 3D & Pemodelan</option>
                    <option value="multimedia_video">Produksi Video & Broadcasting</option>
                    <option value="iot_hardware">IoT & Embedded System</option>
                    <option value="design_uiux">Desain UI/UX & Grafis</option>
                    <option value="machining_fabrication">Fabrikasi & Pengelasan Logam</option>
                    <option value="otomotif">Teknologi Otomotif & Kendaraan</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Deskripsi Proyek & Teknologi yang Digunakan *</label>
                <textarea name="deskripsi" rows="3" required placeholder="Jelaskan fungsionalitas, teknologi yang digunakan, serta latar belakang proyek..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tautan Live Demo / Video</label>
                    <input type="url" name="demo_url" placeholder="https://..." class="w-full text-xs rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tautan GitHub / Repositori</label>
                    <input type="url" name="repository_url" placeholder="https://github.com/..." class="w-full text-xs rounded-xl border-slate-200">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Gambar Sampul / Screenshot (Maks. 3MB)</label>
                <input type="file" name="cover_image" accept="image/*" class="w-full text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modalTambahPortofolio').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm">Ajukan Karya</button>
            </div>
        </form>
    </div>
</div>
@endsection
