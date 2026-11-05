@extends('layouts.app')

@section('title', 'Mitra DU/DI & Industri')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Mitra DU/DI & Tempat PKL</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Database perusahaan mitra, geofencing lokasi presensi, dan kapasitas penempatan.</p>
        </div>
        <button type="button" onclick="document.getElementById('modalTambahDudi').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Mitra Industri
        </button>
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-4 border border-blue-50 shadow-sm">
        <form method="GET" action="{{ route('pkl.partners.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama perusahaan, kota, bidang usaha..." class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="jurusan_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Jurusan Terkait</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->kode_jurusan ?? $j->nama_jurusan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition">Filter</button>
                <a href="{{ route('pkl.partners.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($partners as $p)
            <div class="bg-white rounded-2xl p-5 border border-slate-100 hover:border-blue-200 shadow-sm transition space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 mb-1.5">
                            {{ $p->bidang_industri }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 line-clamp-1">{{ $p->nama_dudi }}</h3>
                        <p class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $p->kota }}
                        </p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ strtoupper($p->status) }}
                    </span>
                </div>

                <div class="text-[11px] text-slate-600 space-y-1 bg-slate-50 p-2.5 rounded-xl">
                    <div><span class="font-semibold text-slate-700">PIC:</span> {{ $p->kontak_person }} {{ $p->telepon ? '('.$p->telepon.')' : '' }}</div>
                    <div><span class="font-semibold text-slate-700">Pembimbing Lapangan:</span> {{ $p->pembimbing_dudi ?? '-' }}</div>
                    <div class="flex items-center gap-1">
                        <span class="font-semibold text-slate-700">Geofence:</span>
                        @if($p->latitude && $p->longitude)
                            <span class="text-emerald-700 font-bold">Aktif (Radius {{ $p->radius_meter }}m)</span>
                        @else
                            <span class="text-amber-700 font-bold">Belum diset</span>
                        @endif
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500 font-medium">Siswa Aktif: <strong class="text-blue-700">{{ $p->placements->where('status', 'active')->count() }}</strong></span>
                    <span class="text-xs text-blue-600 font-bold">{{ $p->jurusan->kode_jurusan ?? 'Semua Jurusan' }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-dashed border-slate-200">
                <p class="text-xs text-slate-500 font-medium">Belum ada mitra DU/DI yang terdaftar.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $partners->links() }}
    </div>
</div>

<!-- Modal Tambah DU/DI -->
<div id="modalTambahDudi" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-sm font-black text-slate-800">Daftarkan Mitra Industri (DU/DI) Baru</h3>
            <button onclick="document.getElementById('modalTambahDudi').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('pkl.partners.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Perusahaan / Industri *</label>
                <input type="text" name="nama_dudi" required placeholder="Contoh: PT Telkom Indonesia, Tbk" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Bidang Industri *</label>
                    <input type="text" name="bidang_industri" required placeholder="Contoh: Software Development" class="w-full text-xs rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kota / Kabupaten *</label>
                    <input type="text" name="kota" required placeholder="Contoh: Bandung" class="w-full text-xs rounded-xl border-slate-200">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Alamat Lengkap *</label>
                <textarea name="alamat" rows="2" required placeholder="Alamat jalan, gedung, no telp..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kontak Person (PIC) *</label>
                    <input type="text" name="kontak_person" required placeholder="Nama PIC HR / Mentor" class="w-full text-xs rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="telepon" placeholder="08123456789" class="w-full text-xs rounded-xl border-slate-200">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">Latitude</label>
                    <input type="number" step="any" name="latitude" placeholder="-6.917464" class="w-full text-xs rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">Longitude</label>
                    <input type="number" step="any" name="longitude" placeholder="107.619123" class="w-full text-xs rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1">Radius (m)</label>
                    <input type="number" name="radius_meter" value="150" class="w-full text-xs rounded-xl border-slate-200">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Jurusan Terkait</label>
                <select name="jurusan_id" class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">Terbuka untuk Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}">{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modalTambahDudi').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm">Simpan Mitra</button>
            </div>
        </form>
    </div>
</div>
@endsection
