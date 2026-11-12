@extends('layouts.app')

@section('title', 'Rekor Kedisiplinan & Prestasi')
@section('page-title', 'Kedisiplinan & Rekor Prestasi')
@section('page-subtitle', 'Pemantauan skor kedisiplinan dan apresiasi penghargaan prestasi siswa di sekolah.')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Kedisiplinan & Rekor Prestasi</h1>
        <p class="text-xs text-slate-500 font-medium mt-0.5">Pemantauan skor kedisiplinan, rincian pelanggaran tata tertib, dan apresiasi penghargaan prestasi siswa.</p>
    </div>

    {{-- Student Score Banner --}}
    <div class="bg-gradient-to-r from-[#003FA9] via-blue-800 to-indigo-900 rounded-3xl p-6 text-white shadow-xl space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold bg-white/20 backdrop-blur-sm tracking-wider uppercase mb-2">
                    Indeks Perilaku Siswa
                </span>
                <h2 class="text-2xl font-black">{{ $siswa->nama }}</h2>
                <p class="text-xs text-blue-200 mt-0.5">NIS: {{ $siswa->nis }} | Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/15 text-center min-w-[160px]">
                <span class="text-[10px] text-blue-200 uppercase font-bold block">Skor Disiplin</span>
                <span class="text-3xl font-black text-white">{{ number_format($skorDisiplin, 0) }}</span>
                <span class="text-[10px] block mt-0.5 {{ $skorDisiplin >= 85 ? 'text-emerald-300' : ($skorDisiplin >= 70 ? 'text-amber-300' : 'text-rose-300') }} font-bold">
                    {{ $skorDisiplin >= 85 ? 'Sangat Baik' : ($skorDisiplin >= 70 ? 'Cukup Baik' : 'Perlu Pembinaan') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-4 border-t border-white/10 text-xs">
            <div>
                <span class="text-[10px] text-blue-200 block">Poin Pengurang (Pelanggaran)</span>
                <span class="font-black text-rose-300">-{{ $pelanggaranPoin }} Poin</span>
            </div>
            <div>
                <span class="text-[10px] text-blue-200 block">Poin Merit (Prestasi)</span>
                <span class="font-black text-emerald-300">+{{ $prestasiPoin }} Poin</span>
            </div>
            <div>
                <span class="text-[10px] text-blue-200 block">Total Catatan BK</span>
                <span class="font-bold text-white">{{ $records->count() }} Peristiwa</span>
            </div>
            <div>
                <span class="text-[10px] text-blue-200 block">Total Item Standar</span>
                <span class="font-bold text-white">{{ $records->sum(fn($r) => $r->items->count()) }} Item</span>
            </div>
        </div>
    </div>

    {{-- History Timeline with Nested Item Details --}}
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-800">Riwayat Kejadian & Penghargaan</h3>
            <span class="text-xs text-slate-400 font-semibold">{{ $records->count() }} Catatan Ditemukan</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($records as $rec)
                <div class="py-5 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($rec->effective_type === 'prestasi')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Prestasi
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                    Pelanggaran
                                </span>
                            @endif

                            <span class="text-xs text-slate-400 font-medium">
                                {{ $rec->tanggal->translatedFormat('l, d F Y') }}
                            </span>

                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                {{ $rec->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : ($rec->status === 'verified' ? 'bg-blue-100 text-[#003FA9]' : 'bg-amber-100 text-amber-800') }}">
                                {{ strtoupper($rec->status) }}
                            </span>
                        </div>

                        <div>
                            @if($rec->poin < 0)
                                <span class="inline-block px-3 py-1 rounded-xl text-xs font-black bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $rec->poin }} Poin
                                </span>
                            @elseif($rec->poin > 0)
                                <span class="inline-block px-3 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    +{{ $rec->poin }} Poin
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 rounded-xl text-xs font-black bg-slate-100 text-slate-600 border border-slate-200">
                                    0 Poin
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-800">{{ $rec->judul }}</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $rec->deskripsi }}</p>
                    </div>

                    {{-- Nested Child Items Breakdown --}}
                    @if($rec->items->count() > 0)
                        <div class="bg-[#F8FAFC] rounded-2xl p-4 border border-slate-100 space-y-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                Rincian Item Tercatat ({{ $rec->items->count() }} Item Standar):
                            </span>
                            <div class="space-y-1.5">
                                @foreach($rec->items as $idx => $it)
                                    <div class="flex items-center justify-between text-xs bg-white p-2.5 rounded-xl border border-slate-100">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] flex items-center justify-center shrink-0">
                                                {{ $idx + 1 }}
                                            </span>
                                            <span class="font-bold text-slate-800">{{ $it->nama_standar }}</span>
                                            @if($it->keterangan)
                                                <span class="text-[11px] text-slate-400 italic">({{ $it->keterangan }})</span>
                                            @endif
                                        </div>
                                        <span class="font-black shrink-0 {{ $it->poin < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $it->formatted_poin }} Poin
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Tindak Lanjut Sekolah --}}
                    @if($rec->tindak_lanjut)
                        <div class="p-3 bg-amber-50/70 rounded-2xl text-xs text-amber-900 border border-amber-200/80">
                            <span class="font-bold text-amber-800">Tindak Lanjut / Arahan Sekolah:</span>
                            <span class="ml-1">{{ $rec->tindak_lanjut }}</span>
                        </div>
                    @endif

                    {{-- Evidence file if available --}}
                    @if($rec->bukti_path)
                        <div>
                            <a href="{{ asset('storage/' . $rec->bukti_path) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-[#003FA9] hover:underline">
                                <i class='bx bx-paperclip text-sm'></i>
                                <span>Lihat Berkas Bukti Lampiran</span>
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-12 text-center text-slate-400">
                    <i class='bx bx-check-shield text-3xl text-emerald-500 mb-2'></i>
                    <p class="text-sm font-semibold text-slate-700">Belum ada catatan kedisiplinan atau prestasi yang terekam.</p>
                    <p class="text-xs text-slate-400 mt-0.5">Pertahankan perilaku positif dan raih prestasi membanggakan di sekolah.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
