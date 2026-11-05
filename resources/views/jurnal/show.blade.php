@extends('layouts.app')

@section('page-title', 'Detail Jurnal Pembelajaran')
@section('page-subtitle', 'Rekap agenda mengajar dan hasil presensi pertemuan mata pelajaran')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Top Action Bar --}}
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('jurnal.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i class='bx bx-left-arrow-alt text-lg'></i> Kembali ke Riwayat Jurnal
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" type="button"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition-colors shadow-xs">
                <i class='bx bx-printer text-base'></i>
                <span>Cetak Jurnal</span>
            </button>
            @if(Auth::user()->role === 'superadmin' || (Auth::user()->role === 'guru' && Auth::user()->guru && Auth::user()->guru->id === $journal->guru_id))
                <form action="{{ route('jurnal.destroy', $journal->id) }}" method="POST" onsubmit="return confirm('Hapus jurnal ini beserta presensi sesinya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100 transition-colors">
                        <i class='bx bx-trash text-base'></i>
                        <span>Hapus</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Journal Header Card --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-blue-50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
            <div class="flex items-center gap-3.5">
                <div class="w-14 h-14 rounded-2xl bg-[#0052CC] text-white flex items-center justify-center text-3xl font-bold shadow-md shadow-blue-500/20">
                    <i class='bx bx-book-open'></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">{{ $journal->nama_mapel }}</h2>
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $journal->status === 'submitted' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $journal->status }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Guru Pengampu: <span class="font-bold text-slate-800">{{ $journal->guru->nama }}</span> (NIP: {{ $journal->guru->nip ?? '-' }})
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 text-right">
                <div>
                    <div class="text-xs font-bold text-slate-800">{{ $journal->tanggal->translatedFormat('l, d F Y') }}</div>
                    <div class="text-[11px] text-slate-400 font-mono">{{ $journal->jam_ke ?? '-' }}</div>
                </div>
                <div class="px-3.5 py-2 rounded-2xl bg-blue-50 text-[#0052CC] font-bold text-sm">
                    {{ $journal->kelas->nama_kelas ?? 'Kelas' }}
                </div>
            </div>
        </div>

        {{-- Lesson Details Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 text-xs">
            {{-- Materi Pokok --}}
            <div class="bg-slate-50/70 p-4 rounded-2xl">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Materi Pokok / Bahasan</p>
                <p class="font-bold text-slate-800 text-sm leading-snug">{{ $journal->materi_pokok }}</p>
            </div>

            {{-- Elemen / Capaian --}}
            <div class="bg-slate-50/70 p-4 rounded-2xl">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Elemen / Capaian Pembelajaran</p>
                <p class="text-slate-700 font-semibold leading-snug">{{ $journal->elemen_capaian ?: 'Kurikulum Merdeka / Reguler' }}</p>
            </div>

            {{-- Aktivitas Pembelajaran --}}
            <div class="md:col-span-2 bg-slate-50/70 p-4 rounded-2xl">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Aktivitas / Kegiatan Pembelajaran</p>
                <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $journal->aktivitas }}</p>
            </div>

            {{-- Tujuan Pembelajaran --}}
            @if($journal->tujuan_pembelajaran)
                <div class="bg-slate-50/70 p-4 rounded-2xl">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tujuan Pembelajaran</p>
                    <p class="text-slate-700 leading-relaxed">{{ $journal->tujuan_pembelajaran }}</p>
                </div>
            @endif

            {{-- Metode & Ruangan --}}
            <div class="bg-slate-50/70 p-4 rounded-2xl">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Metode & Ruangan</p>
                <p class="text-slate-700">Metode: <span class="font-semibold text-slate-900">{{ $journal->metode ?: '-' }}</span></p>
                <p class="text-slate-700 mt-1">Ruang: <span class="font-semibold text-slate-900">{{ $journal->jadwal->room->name ?? ($journal->jadwal->ruangan ?? 'Kelas Reguler') }}</span></p>
            </div>

            {{-- Kendala & Catatan Khusus --}}
            @if($journal->kendala || $journal->catatan)
                <div class="md:col-span-2 bg-amber-50/40 border border-amber-100 p-4 rounded-2xl">
                    <p class="text-[10px] font-bold text-amber-800 uppercase tracking-wider mb-1">Kendala & Catatan Pembelajaran</p>
                    <p class="text-slate-700 leading-relaxed">{{ $journal->kendala ?: $journal->catatan }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Attendance Summary & Roster --}}
    @if($journal->attendances->isNotEmpty())
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-blue-50 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Rekap Presensi Siswa Pertemuan Ini</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Total {{ $stats['total'] }} Siswa Terdaftar</p>
                </div>

                {{-- 5 Quick Badges --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $stats['hadir'] }} Hadir
                    </span>
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-50 text-[#0052CC] border border-blue-200">
                        {{ $stats['terlambat'] }} Terlambat
                    </span>
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        {{ $stats['izin'] }} Izin
                    </span>
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        {{ $stats['sakit'] }} Sakit
                    </span>
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        {{ $stats['alpa'] }} Alpa
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-slate-100">
                            <th class="py-2.5 px-4">No</th>
                            <th class="py-2.5 px-4">NIS</th>
                            <th class="py-2.5 px-4">Nama Siswa</th>
                            <th class="py-2.5 px-4">Status Kehadiran</th>
                            <th class="py-2.5 px-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($journal->attendances as $idx => $att)
                            @php
                                $badge = match($att->status) {
                                    'hadir'     => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'label' => 'Hadir'],
                                    'terlambat' => ['bg' => 'bg-blue-50', 'text' => 'text-[#0052CC]', 'label' => 'Terlambat'],
                                    'izin'      => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'label' => 'Izin'],
                                    'sakit'     => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'label' => 'Sakit'],
                                    default     => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'label' => 'Alpa'],
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-2.5 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-4 text-slate-500 font-mono">{{ $att->siswa->nis ?? '-' }}</td>
                                <td class="py-2.5 px-4 font-semibold text-slate-800">{{ $att->siswa->nama ?? 'Siswa Terhapus' }}</td>
                                <td class="py-2.5 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $badge['bg'] }} {{ $badge['text'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-slate-500">
                                    {{ $att->catatan ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
