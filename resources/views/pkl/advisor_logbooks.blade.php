@extends('layouts.app')

@section('title', 'Verifikasi Logbook PKL')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Verifikasi Logbook PKL Siswa</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Penilaian dan verifikasi berkala jurnal aktivitas harian siswa bimbingan industri.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Siswa</th>
                        <th class="py-3 px-4">Perusahaan Mitra</th>
                        <th class="py-3 px-4">Aktivitas & Kompetensi</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($logbooks as $log)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-3 px-4 font-bold text-slate-700 whitespace-nowrap">
                                {{ $log->tanggal ? $log->tanggal->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $log->placement->siswa->nama ?? 'Siswa' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $log->placement->siswa->kelas->nama_kelas ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-blue-700 font-semibold block">{{ $log->placement->partner->nama_dudi ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <p class="text-slate-700 line-clamp-2">{{ $log->aktivitas }}</p>
                                @if($log->kompetensi_dipelajari)
                                    <span class="text-[10px] text-blue-600 font-medium">🎯 {{ $log->kompetensi_dipelajari }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $log->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($log->status === 'revision_requested' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ strtoupper(str_replace('_', ' ', $log->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <button type="button" onclick="openReviewModal({{ $log->id }}, '{{ addslashes($log->aktivitas) }}', '{{ $log->status }}', '{{ addslashes($log->catatan_guru ?? '') }}')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg transition shadow-xs">
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Belum ada jurnal logbook yang perlu diverifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logbooks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logbooks->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Review Modal -->
<div id="modalReview" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-sm font-black text-slate-800">Verifikasi Jurnal Logbook</h3>
            <button onclick="document.getElementById('modalReview').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="formReview" method="POST" action="" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Aktivitas Siswa</label>
                <div id="modalAktivitas" class="p-3 bg-slate-50 rounded-xl text-xs text-slate-700 border border-slate-100 max-h-32 overflow-y-auto"></div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Keputusan Pembimbing *</label>
                <select name="status" id="modalStatus" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="approved">Setujui Jurnal (Disetujui)</option>
                    <option value="revision_requested">Minta Perbaikan (Revisi)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan / Evaluasi Pembimbing</label>
                <textarea name="catatan_guru" id="modalCatatan" rows="3" placeholder="Berikan feedback atau poin yang perlu dilengkapi siswa..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modalReview').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm">Simpan Verifikasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReviewModal(id, aktivitas, status, catatan) {
        document.getElementById('formReview').action = '/pkl/logbook/' + id + '/review';
        document.getElementById('modalAktivitas').textContent = aktivitas;
        document.getElementById('modalStatus').value = status === 'revision_requested' ? 'revision_requested' : 'approved';
        document.getElementById('modalCatatan').value = catatan || '';
        document.getElementById('modalReview').classList.remove('hidden');
    }
</script>
@endsection
