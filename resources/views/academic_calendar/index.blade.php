@extends('settings.layout')

@php
    $settingsTitle = 'Kalender Akademik — MySkanic';
    $settingsPageTitle = 'Kalender Akademik';
    $settingsPageSubtitle = 'Hari non-efektif — libur nasional & kegiatan sekolah (siswa tidak dihitung alpa)';
@endphp

@section('settings-content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-1 bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <div class="flex items-center gap-3 mb-5 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                    <i class='bx bx-calendar-plus'></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Tambah Hari Libur</h3>
                    <p class="text-xs text-slate-400">Hari libur nasional / non-efektif</p>
                </div>
            </div>

            <form action="{{ route('academic_calendar.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="tanggal" class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" required value="{{ old('tanggal') }}"
                        class="block w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7] transition">
                    @error('tanggal')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="nama_kegiatan" class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kegiatan / Keterangan</label>
                    <input type="text" name="nama_kegiatan" id="nama_kegiatan" required maxlength="255" placeholder="cth: Libur Nasional / Class Meeting"
                        class="block w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7] transition">
                    @error('nama_kegiatan')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit"
                    class="w-full px-5 py-2.5 bg-[#003EA7] hover:bg-blue-800 text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center justify-center gap-2">
                    <i class='bx bx-plus'></i> Tambah ke Kalender
                </button>
            </form>

            <form method="GET" class="mt-6 pt-5 border-t border-slate-100">
                <span class="block text-xs font-bold text-slate-700 mb-2">Filter Periode Bulan</span>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Bulan</label>
                        <select name="bulan" class="block w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50/50 text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20">
                            @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Tahun</label>
                        <select name="tahun" class="block w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50/50 text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20">
                            @for($y=(int)$tahun-1; $y<=(int)$tahun+1; $y++)
                                <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-3 w-full px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <i class='bx bx-filter-alt'></i> Tampilkan Bulan Ini
                </button>
            </form>
        </div>

        <div class="xl:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-base font-bold text-slate-800 mb-3">Daftar Hari Non-Efektif</h3>
            @if($days->isEmpty())
                <div class="p-8 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    Belum ada hari non-efektif pada bulan yang dipilih.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 text-slate-400 uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4 font-bold">TANGGAL</th>
                                <th class="py-3 px-4 font-bold">KETERANGAN</th>
                                <th class="py-3 px-4 font-bold">STATUS</th>
                                <th class="py-3 px-4 font-bold text-right w-24">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($days as $day)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-slate-900 font-mono text-xs whitespace-nowrap">{{ $day->tanggal->translatedFormat('d F Y') }}</td>
                                    <td class="py-3.5 px-4 text-slate-800 font-medium text-xs">{{ $day->nama_kegiatan }}</td>
                                    <td class="py-3.5 px-4">
                                        @if($day->is_active)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Non-Aktif</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <form action="{{ route('academic_calendar.toggle', $day->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="{{ $day->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-[#003EA7] hover:bg-blue-50 transition">
                                                    <i class='bx {{ $day->is_active ? 'bx-pause-circle' : 'bx-play-circle' }} text-lg'></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('academic_calendar.destroy', $day->id) }}" method="POST" class="inline"
                                                data-confirm="Hapus hari non-efektif ini?"
                                                data-confirm-title="Hapus Hari Non-Efektif"
                                                data-confirm-danger="true">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus">
                                                    <i class='bx bx-trash text-lg'></i>
                                                </button>
                                            </form>
                                        </div>
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
