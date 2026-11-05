@extends('layouts.app')

@section('title', 'Etalase Karya & Teaching Factory')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Etalase Karya & Teaching Factory (TeFa)</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Showcase produk inovasi, aplikasi, karya multimedia, dan manufaktur siswa 5 kejuruan SMK.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->role === 'siswa')
                <a href="{{ route('portfolio.student.index') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm">
                    Karya Saya
                </a>
            @endif
            @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog', 'guru']))
                <a href="{{ route('portfolio.moderation') }}" class="px-3.5 py-2.5 rounded-xl bg-purple-50 text-purple-700 text-xs font-bold hover:bg-purple-100 transition border border-purple-200">
                    Kurasi Karya
                </a>
            @endif
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-3">
        <form method="GET" action="{{ route('portfolio.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul proyek, nama kreator, atau teknologi..." class="w-full text-xs rounded-xl border-slate-200">
            </div>
            <div>
                <select name="jurusan_id" class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">Semua Program Keahlian</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 text-xs text-slate-700 font-bold cursor-pointer">
                    <input type="checkbox" name="tefa_only" value="1" {{ request('tefa_only') ? 'checked' : '' }} class="rounded text-blue-600">
                    Hanya Produk TeFa
                </label>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition">Cari</button>
            </div>
        </form>
    </div>

    <!-- Portfolio Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($portfolios as $port)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition flex flex-col justify-between group">
                <div>
                    <!-- Thumbnail Container -->
                    <div class="h-44 bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 relative overflow-hidden flex items-center justify-center">
                        @if($port->cover_image_path)
                            <img src="{{ asset('storage/' . $port->cover_image_path) }}" alt="{{ $port->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="text-blue-400 flex flex-col items-center">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-[10px] font-bold mt-1 text-slate-400 uppercase tracking-wider">{{ $port->kategori }}</span>
                            </div>
                        @endif

                        @if($port->is_tefa_product)
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-400 text-amber-950 uppercase tracking-wider shadow-sm flex items-center gap-1">
                                🌟 TeFa Product
                            </span>
                        @endif

                        <span class="absolute bottom-3 left-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/90 backdrop-blur-sm text-blue-700 shadow-xs">
                            {{ $port->jurusan->kode_jurusan ?? 'SMK' }}
                        </span>
                    </div>

                    <div class="p-5 space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ str_replace('_', ' ', $port->kategori) }}</span>
                        <h3 class="text-base font-bold text-slate-800 line-clamp-1 group-hover:text-blue-600 transition">
                            <a href="{{ route('portfolio.show', $port->slug) }}">{{ $port->judul }}</a>
                        </h3>
                        <p class="text-xs text-slate-500 line-clamp-2">{{ $port->deskripsi }}</p>
                    </div>
                </div>

                <div class="px-5 pb-5 pt-2 border-t border-slate-50 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[10px]">
                            {{ substr($port->siswa->nama ?? 'S', 0, 1) }}
                        </div>
                        <span class="text-slate-700 font-semibold text-[11px]">{{ $port->siswa->nama ?? 'Kreator' }}</span>
                    </div>
                    <a href="{{ route('portfolio.show', $port->slug) }}" class="text-blue-600 font-bold hover:underline flex items-center gap-0.5 text-xs">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-dashed border-slate-200">
                <p class="text-xs text-slate-400 font-medium">Belum ada karya terpublikasi yang cocok dengan kriteria pencarian.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $portfolios->links() }}
    </div>
</div>
@endsection
