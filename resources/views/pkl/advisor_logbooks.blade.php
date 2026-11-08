@extends('layouts.app')

@section('title', 'Verifikasi Logbook PKL')
@section('page-title', 'Verifikasi Logbook PKL')
@section('page-subtitle', 'Verifikasi dan review catatan jurnal harian PKL oleh guru pembimbing.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Verifikasi Logbook PKL Siswa</h1>
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

@endsection

@push('modals')
<!-- Review Modal -->
<div id="modalReview" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalReview').classList.add('hidden')"></div>

    {{-- Modal Card --}}
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">

            {{-- Header --}}
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Verifikasi Jurnal Logbook</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Evaluasi & Feedback Pembimbing PKL</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalReview').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <form id="formReview" method="POST" action="">
                @csrf
                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Aktivitas Siswa</label>
                        <div id="modalAktivitas" class="p-4 bg-[#E2EDFF] rounded-xl text-sm text-slate-800 max-h-36 overflow-y-auto leading-relaxed"></div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Keputusan Pembimbing <span class="text-rose-500">*</span></label>
                        <select name="status" id="modalStatus" required
                            class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="approved">Setujui Jurnal (Disetujui)</option>
                            <option value="revision_requested">Minta Perbaikan (Revisi)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan / Evaluasi Pembimbing</label>
                        <textarea name="catatan_guru" id="modalCatatan" rows="3"
                            placeholder="Berikan feedback atau poin yang perlu dilengkapi siswa..."
                            class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalReview').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        <i class='bx bx-check text-lg'></i>
                        <span>Simpan Verifikasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openReviewModal(id, aktivitas, status, catatan) {
        document.getElementById('formReview').action = '{{ url('/pkl/logbook') }}/' + id + '/review';
        document.getElementById('modalAktivitas').textContent = aktivitas;
        document.getElementById('modalStatus').value = status === 'revision_requested' ? 'revision_requested' : 'approved';
        document.getElementById('modalCatatan').value = catatan || '';
        document.getElementById('modalReview').classList.remove('hidden');
    }
</script>
@endpush

