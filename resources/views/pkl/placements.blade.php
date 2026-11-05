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

<!-- Modal Plotting Siswa -->
@if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']))
<div id="modalTambahPenempatan" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-sm font-black text-slate-800">Plotting Siswa ke Mitra DU/DI</h3>
            <button onclick="document.getElementById('modalTambahPenempatan').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('pkl.placements.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Pilih Siswa *</label>
                <select name="siswa_id" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($siswas as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kelas->nama_kelas ?? 'Kelas' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Pilih Mitra DU/DI *</label>
                <select name="pkl_dudi_partner_id" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">-- Pilih Perusahaan Industri --</option>
                    @foreach($partners as $pr)
                        <option value="{{ $pr->id }}">{{ $pr->nama_dudi }} ({{ $pr->kota }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Guru Pembimbing Sekolah</label>
                <select name="guru_pembimbing_id" class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">-- Tanpa Pembimbing / Nanti --</option>
                    @foreach($gurus as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" required class="w-full text-xs rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Selesai *</label>
                    <input type="date" name="tanggal_selesai" required class="w-full text-xs rounded-xl border-slate-200">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Khusus</label>
                <textarea name="catatan" rows="2" placeholder="Catatan shift kerja, divisi magang, dll..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modalTambahPenempatan').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm">Simpan Penempatan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
