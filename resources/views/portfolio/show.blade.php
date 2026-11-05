@extends('layouts.app')

@section('title', $portfolio->judul . ' - Etalase Karya')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <div class="flex items-center justify-between">
        <a href="{{ route('portfolio.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-blue-600 transition">
            &larr; Kembali ke Etalase Karya
        </a>
        <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            {{ $portfolio->views_count }} Dilihat
        </div>
    </div>

    <!-- Main Detail Card -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden space-y-6">
        @if($portfolio->cover_image_path)
            <div class="w-full h-80 bg-slate-900 relative">
                <img src="{{ asset('storage/' . $portfolio->cover_image_path) }}" alt="{{ $portfolio->judul }}" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-6 md:p-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 uppercase tracking-wider">
                            {{ $portfolio->jurusan->nama_jurusan }} ({{ $portfolio->jurusan->kode_jurusan }})
                        </span>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 uppercase tracking-wider">
                            {{ str_replace('_', ' ', $portfolio->kategori) }}
                        </span>
                        @if($portfolio->is_tefa_product)
                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-amber-400 text-amber-950 uppercase tracking-wider">
                                🌟 Teaching Factory Product
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black text-slate-800">{{ $portfolio->judul }}</h1>
                    <p class="text-xs text-slate-500 mt-1">Dibuat oleh <strong class="text-slate-700">{{ $portfolio->siswa->nama }}</strong> ({{ $portfolio->siswa->kelas->nama_kelas ?? 'Siswa' }})</p>
                </div>

                <div class="flex items-center gap-2">
                    @if($portfolio->demo_url)
                        <a href="{{ $portfolio->demo_url }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Live Demo / Tautan
                        </a>
                    @endif
                    @if($portfolio->repository_url)
                        <a href="{{ $portfolio->repository_url }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2.5 rounded-xl bg-slate-800 text-white text-xs font-bold hover:bg-slate-900 transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                            Repositori
                        </a>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Deskripsi Karya & Spesifikasi Teknis</h3>
                <div class="prose prose-sm max-w-none text-slate-600 leading-relaxed whitespace-pre-line text-xs">
                    {{ $portfolio->deskripsi }}
                </div>
            </div>

            @if($portfolio->approver)
                <div class="p-4 bg-blue-50/60 rounded-2xl border border-blue-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Telah lolos verifikasi dan dikurasi oleh: <strong class="text-blue-700">{{ $portfolio->approver->name }}</strong></span>
                    <span class="text-[10px] text-slate-400">{{ $portfolio->approved_at ? $portfolio->approved_at->format('d M Y') : '' }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
