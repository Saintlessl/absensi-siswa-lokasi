@extends('layouts.app')

@section('title', 'Mitra DU/DI & Industri')
@section('page-title', 'Mitra DU/DI & Tempat PKL')
@section('page-subtitle', 'Database perusahaan mitra, geofencing lokasi presensi, dan kapasitas penempatan.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Mitra DU/DI & Tempat PKL</h1>
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
                        <span class="text-xs font-bold text-[#003FA9] block mb-1">
                            {{ $p->bidang_industri }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 line-clamp-1">{{ $p->nama_dudi }}</h3>
                        <p class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $p->kota }}
                        </p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold text-white {{ $p->status === 'aktif' ? 'bg-emerald-600' : 'bg-rose-600' }}">
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

                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']))
                <div class="flex items-center gap-2">
                    <button type="button"
                        data-partner="{{ json_encode([
                            'id' => $p->id,
                            'nama_dudi' => $p->nama_dudi,
                            'bidang_industri' => $p->bidang_industri,
                            'alamat' => $p->alamat,
                            'kota' => $p->kota,
                            'kontak_person' => $p->kontak_person,
                            'email' => $p->email,
                            'telepon' => $p->telepon,
                            'pembimbing_dudi' => $p->pembimbing_dudi,
                            'latitude' => $p->latitude,
                            'longitude' => $p->longitude,
                            'radius_meter' => $p->radius_meter,
                            'kuota_siswa' => $p->kuota_siswa,
                            'jurusan_id' => $p->jurusan_id,
                            'status' => $p->status,
                        ]) }}"
                        onclick="openEditDudi(this.dataset.partner)"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-[#003FA9] text-white text-[11px] font-bold hover:bg-[#002D7A] shadow-xs transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9l-2.732-2.732a2 2 0 01-.414-.828l-.828-2.414 2.414-.828a2 2 0 01.828-.414l6.328-6.328z"/></svg>
                        Edit
                    </button>
                    <form method="POST" action="{{ route('pkl.partners.destroy', $p->id) }}" class="flex-1"
                          data-confirm="Hapus mitra '{{ $p->nama_dudi }}'? Tindakan ini tidak dapat dibatalkan. Mitra yang masih dipakai penempatan PKL akan ditolak sistem secara otomatis."
                          data-confirm-title="Hapus Mitra DU/DI"
                          data-confirm-danger="true">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white text-rose-600 text-[11px] font-bold hover:bg-rose-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </form>
                </div>
                @endif
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

@endsection

@push('modals')
<!-- Modal Tambah DU/DI -->
<div id="modalTambahDudi" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalTambahDudi').classList.add('hidden')"></div>

    {{-- Modal Card --}}
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-2xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">

            {{-- Header --}}
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Daftarkan Mitra Industri (DU/DI) Baru</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Data Mitra Industri & Titik Lokasi Presensi</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalTambahDudi').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('pkl.partners.store') }}">
                @csrf
                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Perusahaan / Industri <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_dudi" required placeholder="Contoh: PT Telkom Indonesia, Tbk"
                            class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Bidang Industri <span class="text-rose-500">*</span></label>
                            <input type="text" name="bidang_industri" required placeholder="Contoh: Software Development"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                            <input type="text" name="kota" required placeholder="Contoh: Bandung"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <textarea name="alamat" rows="2" required placeholder="Alamat jalan, gedung, no telp..."
                            class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kontak Person (PIC) <span class="text-rose-500">*</span></label>
                            <input type="text" name="kontak_person" required placeholder="Nama PIC HR / Mentor"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">No. WhatsApp / HP</label>
                            <input type="text" name="telepon" placeholder="08123456789"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Latitude</label>
                            <input type="number" step="any" name="latitude" placeholder="-6.917464"
                                class="w-full h-11 text-sm font-mono rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Longitude</label>
                            <input type="number" step="any" name="longitude" placeholder="107.619123"
                                class="w-full h-11 text-sm font-mono rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Radius (m)</label>
                            <input type="number" name="radius_meter" value="150"
                                class="w-full h-11 text-sm font-mono rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kuota Siswa PKL <span class="text-[10px] font-semibold text-slate-400">(kosongkan = tanpa batas)</span></label>
                        <input type="number" name="kuota_siswa" min="1" max="1000" placeholder="Contoh: 10"
                            class="w-full h-11 text-sm font-mono rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jurusan Terkait</label>
                        <select name="jurusan_id" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">Terbuka untuk Semua Jurusan</option>
                            @foreach($jurusans as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalTambahDudi').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        <i class='bx bx-check text-lg'></i>
                        <span>Simpan Mitra</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit DU/DI --}}
<div id="modalEditDudi" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalEditDudi').classList.add('hidden')"></div>
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-2xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Mitra Industri (DU/DI)</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Perbarui data mitra & titik lokasi presensi</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalEditDudi').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <form method="POST" id="formEditDudi" action="">
                @csrf @method('PUT')
                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Perusahaan / Industri <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_dudi" id="edit_nama_dudi" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Bidang Industri <span class="text-rose-500">*</span></label>
                            <input type="text" name="bidang_industri" id="edit_bidang_industri" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                            <input type="text" name="kota" id="edit_kota" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <textarea name="alamat" id="edit_alamat" rows="2" required class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kontak Person (PIC) <span class="text-rose-500">*</span></label>
                            <input type="text" name="kontak_person" id="edit_kontak_person" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">No. WhatsApp / HP</label>
                            <input type="text" name="telepon" id="edit_telepon" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Email</label>
                            <input type="email" name="email" id="edit_email" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Pembimbing Industri</label>
                            <input type="text" name="pembimbing_dudi" id="edit_pembimbing_dudi" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Latitude</label>
                            <input type="number" step="any" name="latitude" id="edit_latitude" class="w-full h-11 text-sm font-mono rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Longitude</label>
                            <input type="number" step="any" name="longitude" id="edit_longitude" class="w-full h-11 text-sm font-mono rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Radius (m)</label>
                            <input type="number" name="radius_meter" id="edit_radius_meter" class="w-full h-11 text-sm font-mono rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kuota Siswa PKL <span class="text-[10px] font-semibold text-slate-400">(kosongkan = tanpa batas)</span></label>
                            <input type="number" name="kuota_siswa" id="edit_kuota_siswa" min="1" max="1000" class="w-full h-11 text-sm font-mono rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Mitra <span class="text-rose-500">*</span></label>
                            <select name="status" id="edit_status" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                <option value="aktif">AKTIF</option>
                                <option value="nonaktif">NONAKTIF</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Jurusan Terkait</label>
                            <select name="jurusan_id" id="edit_jurusan_id" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                <option value="">Terbuka untuk Semua Jurusan</option>
                                @foreach($jurusans as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalEditDudi').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">Batal</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        <i class='bx bx-check text-lg'></i><span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function openEditDudi(raw) {
    const p = typeof raw === 'string' ? JSON.parse(raw) : raw;
    document.getElementById('formEditDudi').action = '/pkl/mitra-industri/' + p.id;
    const set = (id, v) => { document.getElementById(id).value = (v === null || v === undefined) ? '' : v; };
    set('edit_nama_dudi', p.nama_dudi);
    set('edit_bidang_industri', p.bidang_industri);
    set('edit_kota', p.kota);
    set('edit_alamat', p.alamat);
    set('edit_kontak_person', p.kontak_person);
    set('edit_telepon', p.telepon);
    set('edit_email', p.email);
    set('edit_pembimbing_dudi', p.pembimbing_dudi);
    set('edit_latitude', p.latitude);
    set('edit_longitude', p.longitude);
    set('edit_radius_meter', p.radius_meter);
    set('edit_kuota_siswa', p.kuota_siswa);
    set('edit_status', p.status);
    const sel = document.getElementById('edit_jurusan_id');
    sel.value = p.jurusan_id || '';
    @if(auth()->user()->role === 'kaprog')
    // Kaprog: jurusan terkunci ke jurusannya (server tetap verifikasi ulang)
    const managed = {{ $kaprogManagedJurusanId ?? 'null' }};
    if (managed) { sel.value = String(managed); sel.disabled = true; }
    @endif
    document.getElementById('modalEditDudi').classList.remove('hidden');
}
</script>
@endpush
