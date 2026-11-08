@extends('layouts.app')

@section('title', 'Rekor Kedisiplinan & Prestasi')
@section('page-title', 'Kedisiplinan & Rekor Prestasi')
@section('page-subtitle', 'Pemantauan skor kedisiplinan dan apresiasi penghargaan prestasi siswa di sekolah.')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Kedisiplinan & Rekor Prestasi</h1>
        <p class="text-xs text-slate-500 font-medium mt-0.5">Pemantauan skor kedisiplinan dan apresiasi penghargaan prestasi siswa di sekolah.</p>
    </div>

    <!-- Student Score Banner -->
    <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-3xl p-6 text-white shadow-lg space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold bg-white/20 backdrop-blur-sm tracking-wider uppercase mb-2">
                    Indeks Perilaku Siswa
                </span>
                <h2 class="text-2xl font-black">{{ $siswa->nama }}</h2>
                <p class="text-xs text-blue-200 mt-0.5">NIS: {{ $siswa->nis }} | Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/15 text-center">
                <span class="text-[10px] text-blue-200 uppercase font-bold block">Skor Disiplin</span>
                <span class="text-3xl font-black text-white">{{ number_format($skorDisiplin, 0) }}</span>
                <span class="text-[10px] block mt-0.5 {{ $skorDisiplin >= 85 ? 'text-emerald-300' : ($skorDisiplin >= 70 ? 'text-amber-300' : 'text-rose-300') }} font-semibold">
                    {{ $skorDisiplin >= 85 ? 'Sangat Baik' : ($skorDisiplin >= 70 ? 'Cukup Baik' : 'Perlu Pembinaan') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 pt-4 border-t border-white/10 text-xs">
            <div>
                <span class="text-[10px] text-blue-200 block">Poin Pengurang (Pelanggaran)</span>
                <span class="font-bold text-rose-300">-{{ $pelanggaranPoin }} Poin</span>
            </div>
            <div>
                <span class="text-[10px] text-blue-200 block">Poin Merit (Prestasi)</span>
                <span class="font-bold text-emerald-300">+{{ $prestasiPoin }} Poin</span>
            </div>
            <div>
                <span class="text-[10px] text-blue-200 block">Total Rekor Tercatat</span>
                <span class="font-bold text-white">{{ $records->count() }} Peristiwa</span>
            </div>
        </div>
    </div>

    <!-- History Timeline -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-slate-800">Riwayat Kejadian & Penghargaan</h3>
        <div class="divide-y divide-slate-100">
            @forelse($records as $rec)
                <div class="py-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $rec->category->tipe === 'prestasi' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $rec->category->nama ?? $rec->category->tipe }}
                            </span>
                            <span class="text-xs text-slate-400 font-medium">{{ $rec->tanggal->translatedFormat('d F Y') }}</span>
                        </div>
                        <span class="text-xs font-black {{ $rec->category->tipe === 'prestasi' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $rec->category->tipe === 'prestasi' ? '+' : '-' }}{{ $rec->poin }} Poin
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-slate-800">{{ $rec->judul }}</h4>
                        <p class="text-xs text-slate-600 mt-0.5">{{ $rec->deskripsi }}</p>
                    </div>

                    @if($rec->tindak_lanjut)
                        <div class="p-2.5 bg-slate-50 rounded-xl text-[11px] text-slate-600 border border-slate-100">
                            <span class="font-bold text-slate-700">Tindak Lanjut / Arahan Sekolah:</span> {{ $rec->tindak_lanjut }}
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-400 py-6 text-center">Belum ada catatan kedisiplinan atau prestasi yang terekam.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
