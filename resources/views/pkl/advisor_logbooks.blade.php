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
    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">PERUSAHAAN MITRA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">AKTIVITAS & KOMPETENSI</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">STATUS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">AKSI VERIFIKASI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($logbooks as $log)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4 font-mono font-bold text-slate-800 text-xs sm:text-sm whitespace-nowrap">
                                {{ $log->tanggal ? $log->tanggal->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-900 text-xs sm:text-sm block">{{ $log->placement->siswa->nama ?? 'Siswa' }}</span>
                                <span class="text-[11px] text-slate-400 font-normal">{{ $log->placement->siswa->kelas->nama_kelas ?? '' }}</span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-800 text-xs sm:text-sm">
                                {{ $log->placement->partner->nama_dudi ?? '-' }}
                            </td>
                            <td class="py-4 px-4 max-w-xs">
                                <p class="text-slate-800 text-xs sm:text-sm line-clamp-2">{{ $log->aktivitas }}</p>
                                @if($log->kompetensi_dipelajari)
                                    <span class="text-[11px] text-[#003FA9] font-medium block mt-0.5">{{ $log->kompetensi_dipelajari }}</span>
                                @endif
                                @if($u = image_url($log->foto_dokumentasi_path))
                                    <a href="{{ $u }}" target="_blank" rel="noopener" class="inline-block mt-1.5">
                                        <img src="{{ $u }}" alt="Foto dokumentasi PKL" class="h-14 w-14 rounded-xl object-cover border border-slate-200 hover:opacity-80 transition" loading="lazy">
                                    </a>
                                @endif
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold text-white
                                    {{ $log->status === 'approved' ? 'bg-emerald-600' : ($log->status === 'revision_requested' ? 'bg-rose-600' : 'bg-amber-500') }}">
                                    {{ strtoupper(str_replace('_', ' ', $log->status)) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <button type="button" onclick="openReviewModal({{ $log->id }}, '{{ addslashes($log->aktivitas) }}', '{{ $log->status }}', '{{ addslashes($log->catatan_guru ?? '') }}')"
                                    class="px-3.5 py-1.5 bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold rounded-xl transition shadow-sm inline-flex items-center gap-1.5">
                                    <i class='bx bx-check-shield text-sm'></i>
                                    <span>Review</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class='bx bx-book-open text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Belum ada jurnal logbook yang perlu diverifikasi</p>
                                <p class="text-xs text-slate-400 mt-0.5">Jurnal logbook harian siswa PKL akan muncul di sini untuk ditinjau guru pembimbing.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logbooks->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
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
        <div class="relative w-full max-w-xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-slate-100 overflow-hidden">

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

