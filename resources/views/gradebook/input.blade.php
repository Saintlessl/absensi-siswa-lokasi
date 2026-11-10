@extends('layouts.app')

@section('page-title', 'Input Nilai Siswa')
@section('page-subtitle', 'Masukkan nilai tugas, praktik, atau ujian untuk rombel ' . ($assessment->kelas->nama_kelas ?? ''))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Top Action & Info Card --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 uppercase">
                        {{ $assessment->category->nama }} ({{ $assessment->category->kode }})
                    </span>
                    @if($assessment->status === 'published')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">Dipublikasikan</span>
                    @elseif($assessment->status === 'finalized')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#003FA9] text-white">Final (Belum Terbit)</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">Draft</span>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-slate-800">{{ $assessment->judul }}</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Mapel: <span class="font-semibold text-slate-700">{{ $assessment->nama_mapel }}</span> |
                    Rombel: <span class="font-bold text-[#003FA9]">{{ $assessment->kelas->nama_kelas }}</span> |
                    Skor Maksimal: <span class="font-mono font-bold text-slate-800">{{ number_format($assessment->skor_maksimal, 0) }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('gradebook.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition">
                    Kembali
                </a>
            </div>
        </div>

        {{-- Helper Tip --}}
        <div class="mt-4 p-3.5 rounded-xl bg-blue-50/60 border border-blue-100 text-xs text-slate-600 flex items-start gap-2.5">
            <i class='bx bx-info-circle text-lg text-[#003FA9] flex-shrink-0 mt-0.5'></i>
            <div>
                <span class="font-bold text-[#003FA9]">Catatan Sistem Penilaian:</span>
                Kosongkan nilai jika siswa belum mengumpulkan tugas. Nilai yang dikosongkan tidak akan dihitung nol dan tidak merusak rata-rata siswa. Masukkan angka <span class="font-mono font-bold">0</span> hanya bila siswa terbukti memperoleh nilai nol.
            </div>
        </div>
    </div>

    {{-- Score Grid Form --}}
    <form method="POST" action="{{ route('gradebook.save', $assessment->id) }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4 overflow-hidden">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-bold text-slate-800">Daftar Siswa ({{ $scores->count() }} Siswa Terdaftar)</h4>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="fillScorePrompt()"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        <i class='bx bx-magic-wand'></i> Isi Nilai Seragam
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">No</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-28">NIS</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nama Siswa</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-36 text-center">Nilai (0 - {{ number_format($assessment->skor_maksimal, 0) }})</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Catatan / Feedback Guru</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($scores as $idx => $sc)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 text-slate-400 font-mono text-center text-xs">{{ $loop->iteration }}</td>
                                <td class="py-4 px-4 font-mono text-slate-500 text-xs">{{ $sc->siswa->nis }}</td>
                                <td class="py-4 px-4 font-semibold text-slate-800">{{ $sc->siswa->nama }}</td>
                                <td class="py-4 px-4 text-center">
                                    <input type="number" step="0.5" min="0" max="{{ $assessment->skor_maksimal }}"
                                        name="scores[{{ $sc->siswa_id }}][nilai]"
                                        value="{{ !is_null($sc->nilai) ? (float)$sc->nilai : '' }}"
                                        placeholder="Kosong"
                                        class="score-input w-24 text-center text-xs font-bold font-mono rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 py-1.5 px-2">
                                </td>
                                <td class="py-4 px-4">
                                    <input type="text"
                                        name="scores[{{ $sc->siswa_id }}][catatan]"
                                        value="{{ $sc->catatan }}"
                                        placeholder="Feedback untuk siswa (opsional)..."
                                        class="w-full text-xs rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/30 py-1.5 px-3">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('gradebook.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-semibold text-xs hover:bg-slate-200 transition">
                Batal
            </a>

            <div class="flex items-center gap-2.5">
                <button type="submit" name="action" value="save"
                    class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition shadow-sm">
                    Simpan Nilai (Draft)
                </button>
                <button type="submit" name="action" value="publish"
                    class="px-6 py-2.5 rounded-xl bg-[#003FA9] text-white font-semibold text-xs hover:bg-blue-700 transition shadow-sm flex items-center gap-1.5">
                    <i class='bx bx-upload text-base'></i>
                    <span>Simpan & Publikasikan</span>
                </button>
            </div>
        </div>
    </form>

</div>

<script>
    function fillScorePrompt() {
        const val = prompt('Masukkan nilai seragam untuk seluruh siswa yang belum dinilai (0 - {{ $assessment->skor_maksimal }}):');
        if (val !== null && val !== '') {
            document.querySelectorAll('.score-input').forEach(input => {
                if (input.value === '') {
                    input.value = val;
                }
            });
        }
    }
</script>
@endsection
