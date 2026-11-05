@extends('layouts.app')

@section('page-title', 'Tulis Jurnal Pembelajaran')
@section('page-subtitle', 'Isi agenda pembelajaran dan lakukan presensi kehadiran sesi jam pelajaran')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <form method="POST" action="{{ route('jurnal.store') }}" id="journalForm">
        @csrf
        @if($jadwal)
            <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
        @endif
        @if($selectedKelas)
            <input type="hidden" name="kelas_id" value="{{ $selectedKelas->id }}">
        @endif
        @if($mapelId)
            <input type="hidden" name="mata_pelajaran_id" value="{{ $mapelId }}">
        @endif
        <input type="hidden" name="nama_mapel" value="{{ $mapelName }}">

        {{-- Context Card --}}
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-blue-50">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0052CC] flex items-center justify-center text-2xl font-bold">
                        <i class='bx bx-book-reader'></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">{{ $mapelName ?: 'Mata Pelajaran' }}</h3>
                        <p class="text-xs text-slate-400">
                            Pengampu: <span class="font-semibold text-slate-600">{{ $guru->nama }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-blue-100 text-[#0052CC]">
                        Kelas: {{ $selectedKelas ? $selectedKelas->nama_kelas : 'Belum Dipilih' }}
                    </span>
                    @if($jadwal)
                        <span class="px-3 py-1 rounded-xl text-xs font-mono font-semibold bg-slate-100 text-slate-700">
                            {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Form Inputs --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                {{-- Tanggal --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Pertemuan *</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Jam Ke --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Jam Pelajaran / Waktu</label>
                    <input type="text" name="jam_ke" value="{{ old('jam_ke', $jadwal ? substr($jadwal->jam_mulai, 0, 5) . ' - ' . substr($jadwal->jam_selesai, 0, 5) : '') }}" placeholder="Contoh: Jam Ke 1-3 (07:15 - 09:30)"
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Materi Pokok --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Materi Pokok / Bahasan *</label>
                    <input type="text" name="materi_pokok" value="{{ old('materi_pokok') }}" placeholder="Judul materi pembelajaran yang disampaikan hari ini..." required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Elemen CP / KD --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Elemen / Capaian Pembelajaran (Opsional)</label>
                    <input type="text" name="elemen_capaian" value="{{ old('elemen_capaian') }}" placeholder="Contoh: Pemrograman Berorientasi Objek / K3LH"
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Metode Pembelajaran --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Metode Pembelajaran</label>
                    <input type="text" name="metode" value="{{ old('metode', 'Praktik Lab & Diskusi') }}" placeholder="Ceramah, Praktik Bengkel, Diskusi Kelompok, dsb."
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Aktivitas Pembelajaran --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Aktivitas / Kegiatan Pembelajaran *</label>
                    <textarea name="aktivitas" rows="3" required placeholder="Jelaskan alur kegiatan yang berlangsung di kelas/lab/bengkel..."
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 p-3">{{ old('aktivitas') }}</textarea>
                </div>

                {{-- Tujuan Pembelajaran --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tujuan Pembelajaran</label>
                    <textarea name="tujuan_pembelajaran" rows="2" placeholder="Target kompetensi yang diharapkan dicapai siswa..."
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 p-3">{{ old('tujuan_pembelajaran') }}</textarea>
                </div>

                {{-- Kendala / Catatan Khusus --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kendala / Catatan Kelas</label>
                    <textarea name="kendala" rows="2" placeholder="Kendala perangkat, siswa yang membutuhkan bimbingan, dsb..."
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 p-3">{{ old('kendala') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Student Session Attendance Checklist --}}
        @if($selectedKelas && $students->isNotEmpty())
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-blue-50 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Presensi Pertemuan Siswa ({{ $selectedKelas->nama_kelas }})</h4>
                        <p class="text-xs text-slate-400">Tandai status kehadiran siswa khusus untuk sesi mata pelajaran ini</p>
                    </div>

                    <button type="button" onclick="setAllHadir()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 transition-colors">
                        <i class='bx bx-check-double text-base'></i>
                        <span>Tandai Semua Hadir</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] font-semibold tracking-wider">
                                <th class="py-2.5 px-3">No</th>
                                <th class="py-2.5 px-3">NIS</th>
                                <th class="py-2.5 px-3">Nama Siswa</th>
                                <th class="py-2.5 px-3 text-center">Status Kehadiran Sesi</th>
                                <th class="py-2.5 px-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $idx => $st)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="py-2 px-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                    <td class="py-2 px-3 text-slate-500 font-mono">{{ $st->nis }}</td>
                                    <td class="py-2 px-3 font-semibold text-slate-800">{{ $st->nama }}</td>
                                    <td class="py-2 px-3 text-center">
                                        <div class="inline-flex items-center gap-2">
                                            @foreach(['hadir' => 'H', 'terlambat' => 'T', 'izin' => 'I', 'sakit' => 'S', 'alpa' => 'A'] as $statKey => $statLabel)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="attendances[{{ $st->id }}][status]" value="{{ $statKey }}"
                                                        {{ $statKey === 'hadir' ? 'checked' : '' }}
                                                        class="peer sr-only att-radio-{{ $statKey }}">
                                                    <span class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-xs font-bold border border-slate-200 text-slate-600 peer-checked:bg-[#0052CC] peer-checked:text-white peer-checked:border-[#0052CC] transition-colors">
                                                        {{ $statLabel }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="text" name="attendances[{{ $st->id }}][catatan]" placeholder="Keterangan..."
                                            class="w-full text-[11px] rounded-lg border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/30 py-1 px-2">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between gap-3 pt-2">
            <a href="{{ route('jurnal.index') }}" class="px-5 py-2.5 rounded-2xl bg-slate-100 text-slate-600 font-semibold text-xs hover:bg-slate-200 transition-colors">
                Batal
            </a>

            <div class="flex items-center gap-2">
                <button type="submit" name="status" value="draft"
                    class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors shadow-xs">
                    Simpan Sebagai Draft
                </button>
                <button type="submit" name="status" value="submitted"
                    class="px-6 py-2.5 rounded-2xl bg-[#0052CC] text-white font-semibold text-xs hover:bg-[#003FA9] transition-colors shadow-md flex items-center gap-2">
                    <i class='bx bx-check text-base'></i>
                    <span>Kirim Jurnal Selesai</span>
                </button>
            </div>
        </div>
    </form>

</div>

<script>
    function setAllHadir() {
        document.querySelectorAll('.att-radio-hadir').forEach(el => el.checked = true);
    }
</script>
@endsection
