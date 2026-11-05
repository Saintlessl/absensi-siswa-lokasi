@extends('layouts.app')

@section('title', 'Kurasi Etalase Karya & TeFa')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Kurasi Portofolio & Teaching Factory</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Verifikasi kualitas karya siswa sebelum dipamerkan secara terbuka di etalase inovasi sekolah.</p>
        </div>
        <a href="{{ route('portfolio.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
            &larr; Lihat Etalase Publik
        </a>
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
                        <th class="py-3 px-4">Karya Proyek</th>
                        <th class="py-3 px-4">Kreator & Kelas</th>
                        <th class="py-3 px-4">Program Keahlian</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Status Saat Ini</th>
                        <th class="py-3 px-4 text-right">Keputusan Kurasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($portfolios as $port)
                        <tr class="hover:bg-blue-50/20 transition">
                            <td class="py-3 px-4 max-w-xs">
                                <span class="font-bold text-slate-800 block">{{ $port->judul }}</span>
                                <p class="text-[11px] text-slate-500 line-clamp-1">{{ $port->deskripsi }}</p>
                                @if($port->demo_url)
                                    <a href="{{ $port->demo_url }}" target="_blank" class="text-[10px] text-blue-600 font-bold hover:underline">Tautan Demo &rarr;</a>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $port->siswa->nama ?? 'Siswa' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $port->siswa->kelas->nama_kelas ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-blue-700">
                                {{ $port->jurusan->kode_jurusan ?? '-' }}
                            </td>
                            <td class="py-3 px-4 uppercase text-[10px] text-slate-600 font-bold">
                                {{ str_replace('_', ' ', $port->kategori) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $port->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($port->status === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ strtoupper($port->status) }}
                                </span>
                                @if($port->is_tefa_product)
                                    <span class="block text-[9px] text-amber-700 font-bold mt-0.5">🌟 TeFa Product</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <button type="button" onclick="openModModal({{ $port->id }}, '{{ addslashes($port->judul) }}', '{{ $port->status }}', {{ $port->is_tefa_product ? 'true' : 'false' }}, '{{ addslashes($port->moderation_notes ?? '') }}')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg transition shadow-xs">
                                    Kurasi
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Tidak ada karya yang menunggu kurasi saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($portfolios->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $portfolios->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Keputusan Kurasi -->
<div id="modalKurasi" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-sm font-black text-slate-800">Keputusan Kurasi Portofolio</h3>
            <button onclick="document.getElementById('modalKurasi').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="formKurasi" method="POST" action="" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Judul Karya</label>
                <div id="modJudul" class="p-2.5 bg-slate-50 rounded-xl text-xs font-bold text-slate-800"></div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Keputusan Kurator *</label>
                <select name="status" id="modStatus" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="approved">Setujui & Publikasikan ke Etalase</option>
                    <option value="rejected">Tolak / Perlu Revisi Siswa</option>
                </select>
            </div>

            <div>
                <label class="flex items-center gap-2 text-xs text-slate-700 font-bold cursor-pointer p-2 bg-amber-50 rounded-xl border border-amber-200">
                    <input type="checkbox" name="is_tefa_product" id="modTefa" value="1" class="rounded text-amber-600">
                    Akreditasi sebagai Produk Teaching Factory (TeFa)
                </label>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Kurasi / Feedback</label>
                <textarea name="moderation_notes" id="modNotes" rows="3" placeholder="Berikan feedback kualitas, saran perbaikan atau ucapan selamat..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modalKurasi').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModModal(id, judul, status, isTefa, notes) {
        document.getElementById('formKurasi').action = '/etalase-karya/' + id + '/kurasi';
        document.getElementById('modJudul').textContent = judul;
        document.getElementById('modStatus').value = status === 'rejected' ? 'rejected' : 'approved';
        document.getElementById('modTefa').checked = Boolean(isTefa);
        document.getElementById('modNotes').value = notes || '';
        document.getElementById('modalKurasi').classList.remove('hidden');
    }
</script>
@endsection
