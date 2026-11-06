@extends('layouts.app')

@section('title', 'Layanan Bimbingan Konseling (BK)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Layanan Bimbingan & Konseling (BK)</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Sesi bimbingan siswa, pendampingan perilaku, dan penanganan kasus dengan proteksi kerahasiaan konseling.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('discipline.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                &larr; Rekor Disiplin & Prestasi
            </a>
            @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'guru']))
            <button type="button" onclick="document.getElementById('modalTambahKonseling').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 text-white text-xs font-bold hover:bg-purple-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Jadwalkan Sesi Konseling
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Confidentiality Notice Banner -->
    <div class="p-4 bg-purple-50/70 border border-purple-200 rounded-2xl flex items-start gap-3 text-xs text-purple-900">
        <svg class="w-5 h-5 text-purple-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <div>
            <div class="font-bold">Protokol Kerahasiaan Data Konseling (BK Privilege)</div>
            <p class="text-purple-700 mt-0.5">Catatan mendalam permasalahan konseling bersifat rahasia dan hanya dapat diakses oleh Guru BK yang bersangkutan serta Administrator Kesiswaan. Wali Kelas hanya dapat memantau status sesi dan arahan tindak lanjut umum.</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Siswa</th>
                        <th class="py-3 px-4">Konselor BK</th>
                        <th class="py-3 px-4">Topik Umum</th>
                        <th class="py-3 px-4">Detail Permasalahan</th>
                        <th class="py-3 px-4">Tindak Lanjut Umum</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($sessions as $sess)
                        @php
                            $canViewSecret = $sess->canViewConfidentialNotes(auth()->user());
                        @endphp
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-3 px-4 font-bold text-slate-700 whitespace-nowrap">
                                {{ $sess->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $sess->siswa->nama ?? 'Siswa' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $sess->siswa->kelas->nama_kelas ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $sess->guruBk->name ?? 'Konselor' }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $sess->topik_umum }}
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                @if($canViewSecret)
                                    <p class="text-slate-700 line-clamp-2">{{ $sess->permasalahan_rahasia }}</p>
                                    @if($sess->catatan_rahasia)
                                        <span class="text-[10px] text-purple-700 font-bold block mt-0.5">🔒 {{ $sess->catatan_rahasia }}</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-lg">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Rahasia Konselor
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $sess->tindak_lanjut_umum ?? 'Dalam proses' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold 
                                    {{ $sess->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($sess->status === 'in_progress' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                    {{ strtoupper(str_replace('_', ' ', $sess->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada sesi konseling BK yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sessions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('modals')
<!-- Modal Tambah Sesi Konseling -->
<div id="modalTambahKonseling" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalTambahKonseling').classList.add('hidden')"></div>

    {{-- Modal Card --}}
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-2xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">

            {{-- Header --}}
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Catat Sesi Bimbingan & Konseling</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Layanan Bimbingan Konseling & Konsultasi Siswa</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalTambahKonseling').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('counseling.store') }}">
                @csrf
                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Siswa <span class="text-rose-500">*</span></label>
                        <select name="siswa_id" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">-- Pilih Siswa Konseli --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kelas->nama_kelas ?? 'Kelas' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Konseling <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal" value="{{ now()->toDateString() }}" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Penanganan <span class="text-rose-500">*</span></label>
                            <select name="status" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                <option value="in_progress">Sedang Berjalan (In Progress)</option>
                                <option value="completed">Selesai (Completed)</option>
                                <option value="scheduled">Terjadwal (Scheduled)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Topik Umum (Dapat Dilihat Wali Kelas) <span class="text-rose-500">*</span></label>
                        <input type="text" name="topik_umum" required placeholder="Contoh: Konsultasi motivasi belajar / Masalah kehadiran"
                            class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-purple-900 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Permasalahan Mendalam & Rahasia (Strict BK) <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="permasalahan_rahasia" rows="3" required
                            placeholder="Uraian confidential latar belakang masalah keluarga, psikologis, dll..."
                            class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-purple-600 resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tindak Lanjut Umum (Dapat Dilihat Wali Kelas)</label>
                        <input type="text" name="tindak_lanjut_umum"
                            placeholder="Contoh: Pendampingan belajar di kelas dan monitoring presensi mingguan"
                            class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-purple-900 mb-1.5">Catatan Rahasia Konselor (Opsional)</label>
                        <textarea name="catatan_rahasia" rows="2" placeholder="Catatan internal konselor..."
                            class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalTambahKonseling').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        <i class='bx bx-check text-lg'></i>
                        <span>Simpan Sesi Konseling</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
