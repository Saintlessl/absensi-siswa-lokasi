@extends('layouts.app')

@section('title', 'Penempatan Siswa PKL')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Penempatan Siswa PKL</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Distribusi siswa ke dunia usaha/industri mitra beserta penugasan guru pembimbing sekolah.</p>
        </div>
        @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']))
        <button type="button" onclick="document.getElementById('modalTambahPenempatan').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Plotting Siswa PKL
        </button>
        @endif
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Siswa</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Perusahaan Mitra</th>
                        <th class="py-3 px-4">Guru Pembimbing</th>
                        <th class="py-3 px-4">Periode</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($placements as $pl)
                        <tr class="hover:bg-blue-50/40 transition">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $pl->siswa->nama ?? 'Siswa' }}</span>
                                <span class="text-[10px] text-slate-400">NIS: {{ $pl->siswa->nis ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-600">
                                {{ $pl->siswa->kelas->nama_kelas ?? '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-blue-700 block">{{ $pl->partner->nama_dudi ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $pl->partner->kota ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-slate-700 font-medium">{{ $pl->guruPembimbing->name ?? 'Belum Ditugaskan' }}</span>
                            </td>
                            <td class="py-3 px-4 text-[11px] text-slate-500">
                                {{ $pl->tanggal_mulai ? $pl->tanggal_mulai->format('d M Y') : '' }} s/d {{ $pl->tanggal_selesai ? $pl->tanggal_selesai->format('d M Y') : '' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $pl->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ strtoupper($pl->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Belum ada penempatan siswa PKL.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($placements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $placements->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('modals')
<!-- Modal Plotting Siswa -->
@if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']))
<div id="modalTambahPenempatan" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalTambahPenempatan').classList.add('hidden')"></div>

    {{-- Modal Card --}}
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-2xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">

            {{-- Header --}}
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Plotting Siswa ke Mitra DU/DI</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Penempatan Siswa Praktik Kerja Lapangan (PKL)</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalTambahPenempatan').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('pkl.placements.store') }}">
                @csrf
                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Siswa <span class="text-rose-500">*</span></label>
                        <select name="siswa_id" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kelas->nama_kelas ?? 'Kelas' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Mitra DU/DI <span class="text-rose-500">*</span></label>
                        <select name="pkl_dudi_partner_id" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">-- Pilih Perusahaan Industri --</option>
                            @foreach($partners as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->nama_dudi }} ({{ $pr->kota }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Guru Pembimbing Sekolah</label>
                        <select name="guru_pembimbing_id" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">-- Tanpa Pembimbing / Nanti --</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_selesai" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Khusus</label>
                        <textarea name="catatan" rows="2" placeholder="Catatan shift kerja, divisi magang, dll..."
                            class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalTambahPenempatan').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        <i class='bx bx-check text-lg'></i>
                        <span>Simpan Penempatan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endpush
