@extends('layouts.app')

@section('title', 'Kalender Akademik')
@section('page-title', 'Kalender Akademik')
@section('page-subtitle', 'Hari non-efektif — libur nasional & kegiatan sekolah')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Kalender Akademik</li>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-1 glass-card rounded-2xl border border-dark-200/50 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center text-primary-600">
                    <i class='bx bx-calendar-plus text-xl'></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-dark-800">Tambah Hari Non-Efektif</h3>
                    <p class="text-xs text-dark-400">Siswa tanpa rekap absen pada hari ini TIDAK dihitung alpa</p>
                </div>
            </div>

            <form action="{{ route('academic_calendar.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-dark-700 mb-1.5">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" required value="{{ old('tanggal') }}"
                        class="block w-full px-3.5 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                    @error('tanggal')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="nama_kegiatan" class="block text-sm font-medium text-dark-700 mb-1.5">Keterangan</label>
                    <input type="text" name="nama_kegiatan" id="nama_kegiatan" required maxlength="255" placeholder="cth: Libur Nasional / Class Meeting"
                        class="block w-full px-3.5 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                    @error('nama_kegiatan')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit"
                    class="w-full px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class='bx bx-plus'></i> Tambah
                </button>
            </form>

            <form method="GET" class="mt-6 pt-6 border-t border-dark-100">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1">Bulan</label>
                        <select name="bulan" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                            @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-500 mb-1">Tahun</label>
                        <select name="tahun" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                            @for($y=(int)$tahun-1; $y<=(int)$tahun+1; $y++)
                                <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-3 w-full px-4 py-2 border border-dark-200 rounded-xl text-sm font-semibold text-dark-600 hover:bg-dark-50 transition">
                    <i class='bx bx-filter-alt'></i> Tampilkan
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-card rounded-2xl border border-dark-200/50 p-6">
            <h3 class="text-lg font-bold text-dark-800 mb-2">Daftar Hari Non-Efektif</h3>
            @if($days->isEmpty())
                <p class="text-sm text-dark-400 py-8 text-center">Belum ada hari non-efektif pada bulan ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-dark-400 border-b border-dark-100">
                                <th class="py-3 pr-4 font-semibold">Tanggal</th>
                                <th class="py-3 pr-4 font-semibold">Keterangan</th>
                                <th class="py-3 pr-4 font-semibold">Status</th>
                                <th class="py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-50">
                            @foreach($days as $day)
                                <tr>
                                    <td class="py-3 pr-4 font-medium font-mono text-dark-700 whitespace-nowrap">{{ $day->tanggal->translatedFormat('d F Y') }}</td>
                                    <td class="py-3 pr-4 text-dark-600">{{ $day->nama_kegiatan }}</td>
                                    <td class="py-3 pr-4">
                                        @if($day->is_active)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-dark-100 text-dark-500 border border-dark-200">Non-Aktif</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right whitespace-nowrap">
                                        <form action="{{ route('academic_calendar.toggle', $day->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="{{ $day->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                class="p-2 rounded-lg {{ $day->is_active ? 'text-amber-600 bg-amber-50 hover:bg-amber-100' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' }} transition-colors">
                                                <i class='bx {{ $day->is_active ? 'bx-pause-circle' : 'bx-play-circle' }} text-base'></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('academic_calendar.destroy', $day->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Hapus hari non-efektif ini?');">
                                            @csrf
                                            <button type="submit" class="p-2 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 transition-colors">
                                                <i class='bx bx-trash text-base'></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
