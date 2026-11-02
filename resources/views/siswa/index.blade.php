@extends('layouts.app')

@section('title', 'Data Siswa')
@section('page-title', 'Data Siswa')
@section('page-subtitle', 'Kelola data siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Data Siswa</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dark-800">Daftar Siswa</h2>
            <p class="text-sm text-dark-400 mt-1">Kelola data siswa yang ada di sekolah.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('siswa.export.pdf', ['search' => $search ?? '', 'kelas_id' => $kelas_id ?? '']) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/35 transition-all duration-200 hover:-translate-y-0.5"
                target="_blank">
                <i class='bx bxs-file-pdf text-base'></i>
                PDF
            </a>
            <a href="{{ route('siswa.export.excel', ['search' => $search ?? '', 'kelas_id' => $kelas_id ?? '']) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/35 transition-all duration-200 hover:-translate-y-0.5">
                <i class='bx bxs-file text-base'></i>
                Excel
            </a>
            <button onclick="openModal('modal-inputsiswa')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 hover:-translate-y-0.5">
                <i class='bx bx-plus text-lg'></i>
                Tambah Data
            </button>
        </div>
    </div>
@endsection

@section('content')
    @if (session('parent_activation_link'))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-semibold">Tautan aktivasi akun orang tua siap dibagikan melalui kanal yang aman.</p>
            <p class="mt-1">Tautan ini hanya berlaku 7 hari dan tidak memuat password.</p>
            <input class="mt-2 w-full rounded border border-amber-300 bg-white px-2 py-1 font-mono text-xs" readonly value="{{ session('parent_activation_link') }}" onclick="this.select()">
        </div>
    @endif
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ route('siswa') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Cari Siswa</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-dark-400'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            placeholder="Nama / NIS...">
                    </div>
                </div>
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Jurusan</label>
                    <select name="jurusan_id"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusans as $j)
                            <option value="{{ $j->id }}" {{ ($jurusan_id ?? '') == $j->id ? 'selected' : '' }}>
                                {{ $j->kode_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Kelas</label>
                    <select name="kelas_id"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($search || $kelas_id || ($jurusan_id ?? ''))
                    <a href="{{ route('siswa') }}"
                        class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-dark-50/50 border-b border-dark-200/50">
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-14 text-center">No</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Identitas (NIS / NISN)</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Nama Lengkap & Akun</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Kelas & Jurusan</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Kontak Orang Tua (WA)</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($siswa as $s)
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $siswa->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-bold text-dark-800 font-mono">{{ $s->nis }}</div>
                                <div class="text-[11px] text-dark-400">NISN: {{ $s->nisn ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($s->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-dark-900">{{ $s->nama }} <span class="text-xs font-normal text-dark-400">({{ $s->jenis_kelamin }})</span></div>
                                        <div class="text-[11px] text-primary-600 flex items-center gap-1 font-mono">
                                            <i class='bx bx-envelope text-xs'></i> {{ $s->user->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="inline-flex flex-col items-center gap-1">
                                    <span class="font-bold text-dark-800">{{ $s->kelas->nama_kelas ?? '-' }}</span>
                                    @if ($s->kelas && $s->kelas->jurusan)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-dark-100 text-dark-600">
                                            {{ $s->kelas->jurusan->kode_jurusan }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($s->orangtua->first()?->telepon)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">
                                        <i class='bx bxl-whatsapp text-sm text-emerald-600'></i>
                                        <span>{{ $s->orangtua->first()->telepon }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        Belum diisi
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editSiswa('{{ $s->id }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-700 transition-colors duration-200"
                                        title="Edit Data">
                                        <i class='bx bx-edit-alt text-lg'></i>
                                    </button>
                                    <form action="{{ route('siswa.delete', ['id' => $s->id]) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        <button type="button"
                                            class="delete-confirm inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-700 transition-colors duration-200"
                                            title="Hapus Data">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 mb-4 rounded-full bg-dark-100 flex items-center justify-center">
                                        <i class='bx bx-user text-3xl text-dark-400'></i>
                                    </div>
                                    <h3 class="text-sm font-medium text-dark-900">Belum ada data siswa</h3>
                                    <p class="mt-1 text-sm text-dark-500">Silakan tambah data siswa baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($siswa->hasPages())
            <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                {{ $siswa->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        {{-- Modal Tambah Siswa --}}
        <div id="modal-inputsiswa" class="fixed inset-0 z-[60] hidden">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-inputsiswa')"></div>

            {{-- Modal Panel --}}
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl fade-in">
                        {{-- Header --}}
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-user-plus text-primary-600 text-xl'></i>
                                Tambah Data Siswa
                            </h3>
                            <button type="button" onclick="closeModal('modal-inputsiswa')"
                                class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="px-6 py-5">
                            <form action="{{ route('siswa.store') }}" method="POST" id="frmSiswa">
                                @csrf

                                {{-- Section A: Identitas Siswa --}}
                                <div class="mb-5">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">1</div>
                                        <h4 class="text-sm font-semibold text-dark-800">Identitas Siswa</h4>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="nisn" class="block text-xs font-medium text-dark-700 mb-1.5">NISN <span class="text-dark-400 font-normal">(10 digit)</span></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class='bx bx-id-card text-dark-400 text-lg'></i>
                                                </div>
                                                <input type="text" name="nisn" id="nisn" maxlength="20"
                                                    class="block w-full pl-10 pr-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all font-mono"
                                                    placeholder="Contoh: 0051234567" autocomplete="off">
                                            </div>
                                        </div>
                                        <div>
                                            <label for="nis" class="block text-xs font-medium text-dark-700 mb-1.5">NIS <span class="text-rose-500">*</span></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class='bx bx-badge-check text-dark-400 text-lg'></i>
                                                </div>
                                                <input type="text" name="nis" id="nis" maxlength="20"
                                                    class="block w-full pl-10 pr-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all font-mono"
                                                    placeholder="Contoh: 21221001" required autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="nama" class="block text-xs font-medium text-dark-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class='bx bx-user text-dark-400 text-lg'></i>
                                                </div>
                                                <input type="text" name="nama" id="nama"
                                                    class="block w-full pl-10 pr-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                                                    placeholder="Nama Lengkap Siswa" required autocomplete="off">
                                            </div>
                                        </div>
                                        <div>
                                            <label for="jenis_kelamin" class="block text-xs font-medium text-dark-700 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                                            <select name="jenis_kelamin" id="jenis_kelamin" required
                                                class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                                                <option value="">-- Pilih Jenis Kelamin --</option>
                                                <option value="L">Laki-laki</option>
                                                <option value="P">Perempuan</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="modal_filter_jurusan" class="block text-xs font-medium text-dark-700 mb-1.5">Jurusan</label>
                                            <select id="modal_filter_jurusan"
                                                class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                                                <option value="">-- Semua Jurusan --</option>
                                                @foreach($jurusans as $j)
                                                    <option value="{{ $j->id }}">{{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="kelas_id" class="block text-xs font-medium text-dark-700 mb-1.5">Kelas <span class="text-rose-500">*</span></label>
                                            <select name="kelas_id" id="kelas_id" required
                                                class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                                                <option value="">-- Pilih Kelas --</option>
                                                @foreach($kelas as $k)
                                                    <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}">
                                                        {{ $k->nama_kelas }} ({{ $k->jurusan->kode_jurusan ?? '-' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Section B: Informasi Orang Tua/Wali --}}
                                <div class="mb-5 pt-4 border-t border-dark-100">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">2</div>
                                        <h4 class="text-sm font-semibold text-dark-800">Informasi Orang Tua / Wali</h4>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div><label for="parent_name" class="block text-xs font-medium text-dark-700 mb-1.5">Nama Lengkap Orang Tua / Wali <span class="text-rose-500">*</span></label><input type="text" name="parent_name" id="parent_name" required class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30" placeholder="Nama lengkap orang tua/wali" autocomplete="off"></div>
                                        <div><label for="parent_phone" class="block text-xs font-medium text-dark-700 mb-1.5">Nomor WhatsApp <span class="text-rose-500">*</span></label><input type="tel" name="parent_phone" id="parent_phone" required class="block w-full px-3 py-2 border border-emerald-300 rounded-xl bg-emerald-50/20 text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 font-mono" placeholder="081234567890" autocomplete="off"></div>
                                        <div class="md:col-span-2"><label for="parent_address" class="block text-xs font-medium text-dark-700 mb-1.5">Alamat Lengkap <span class="text-rose-500">*</span></label><textarea name="parent_address" id="parent_address" rows="3" required class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30" placeholder="Alamat lengkap orang tua/wali"></textarea><p class="mt-1 text-[11px] text-dark-500">Nomor disimpan dalam format internasional untuk notifikasi presensi.</p></div>
                                        <div class="md:col-span-2 border-t border-dark-100 pt-3"><label for="existing_orangtua_id" class="block text-xs font-medium text-dark-700 mb-1.5">Atau hubungkan profil orang tua yang sudah diverifikasi</label><select name="existing_orangtua_id" id="existing_orangtua_id" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm"><option value="">Buat profil orang tua baru</option>@foreach($orangTuaExisting as $ot)<option value="{{ $ot->id }}">{{ $ot->nama }} — {{ $ot->telepon }} ({{ $ot->siswa->pluck('nama')->join(', ') ?: 'belum terhubung' }})</option>@endforeach</select><label class="mt-2 flex items-start gap-2 text-xs text-dark-600"><input type="checkbox" name="confirm_existing_parent" value="1" class="mt-0.5"> Saya telah memverifikasi identitas orang tua/wali ini dan mengonfirmasi relasi baru.</label></div>
                                    </div>
                                </div>

                                <div class="mb-5 pt-4 border-t border-dark-100 text-sm text-dark-600"><p class="font-semibold text-dark-800">Akun Orang Tua</p><p class="mt-1">Akun dibuat otomatis. Orang tua menetapkan password sendiri lewat tautan aktivasi sekali pakai yang berlaku 7 hari.</p></div>

                                {{-- Section C: Akun Siswa --}}
                                <div class="mb-5 pt-4 border-t border-dark-100">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">3</div>
                                        <h4 class="text-sm font-semibold text-dark-800">Akun Login Siswa (@myskanic.com)</h4>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="username_input" class="block text-xs font-medium text-dark-700 mb-1.5">Username Siswa <span class="text-rose-500">*</span></label>
                                            <div class="flex rounded-xl overflow-hidden border border-dark-200 focus-within:ring-2 focus-within:ring-primary-500/30 focus-within:border-primary-500 transition-all">
                                                <span class="inline-flex items-center px-3 bg-dark-50 text-dark-500 text-sm border-r border-dark-200">
                                                    <i class='bx bx-user text-base'></i>
                                                </span>
                                                <input type="text" name="username" id="username_input"
                                                    class="block w-full px-3 py-2 bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none font-mono lowercase"
                                                    placeholder="budi.santoso" required autocomplete="off">
                                                <span class="inline-flex items-center px-3 bg-primary-50 text-primary-700 font-semibold text-xs border-l border-primary-100 select-none">
                                                    @myskanic.com
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-dark-500 mt-1">Hanya huruf kecil, angka, titik, strip.</p>
                                        </div>
                                        <div>
                                            <label for="password" class="block text-xs font-medium text-dark-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class='bx bx-lock-alt text-dark-400 text-lg'></i>
                                                </div>
                                                <input type="password" name="password" id="password" minlength="6"
                                                    class="block w-full pl-10 pr-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                                                    placeholder="Minimal 6 karakter" required autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="mt-6 pt-4 border-t border-dark-100 flex items-center justify-end gap-3">
                                    <button type="button" onclick="closeModal('modal-inputsiswa')"
                                        class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 hover:text-dark-800 text-sm font-medium transition-colors duration-200">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center gap-2">
                                        <i class='bx bx-save text-lg'></i>
                                        Simpan Siswa
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Edit Siswa --}}
        <div id="modal-editsiswa" class="fixed inset-0 z-[60] hidden">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editsiswa')"></div>

            {{-- Modal Panel --}}
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl fade-in">
                        {{-- Header --}}
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-edit-alt text-primary-600 text-xl'></i>
                                Edit Data Siswa
                            </h3>
                            <button type="button" onclick="closeModal('modal-editsiswa')"
                                class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>

                        {{-- Body (Loaded via AJAX) --}}
                        <div class="px-6 py-5" id="loadeditform">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endpush

@endsection

@push('scripts')
    <script>
        // Modal Functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                // Prevent body scroll
                document.body.style.overflow = 'hidden';

                // Focus first input if it's the add modal
                if (modalId === 'modal-inputsiswa') {
                    setTimeout(() => {
                        document.getElementById('nis').focus();
                    }, 100);
                }
            }
        }

        // Filter Kelas by Jurusan in Add Modal
        document.getElementById('modal_filter_jurusan')?.addEventListener('change', function() {
            const selectedJurusan = this.value;
            const kelasSelect = document.getElementById('kelas_id');
            if (!kelasSelect) return;

            Array.from(kelasSelect.options).forEach((opt, idx) => {
                if (idx === 0) return;
                const optJurusan = opt.getAttribute('data-jurusan');
                if (!selectedJurusan || optJurusan === selectedJurusan) {
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });
            if (kelasSelect.selectedOptions[0] && kelasSelect.selectedOptions[0].disabled) {
                kelasSelect.selectedIndex = 0;
            }
        });

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                // Restore body scroll
                document.body.style.overflow = '';
            }
        }

        // Modal Edit Call
        function editSiswa(id) {
            fetch('/siswa/edit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('loadeditform').innerHTML = html;
                    openModal('modal-editsiswa');

                    // Attach form listener for the newly loaded edit form
                    const formObj = document.getElementById('frmEditSiswa');
                    if (formObj) attachEditFormListener(formObj);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Terjadi kesalahan saat memuat form edit.',
                        customClass: {
                            popup: 'font-inter'
                        }
                    });
                });
        }

        function attachEditFormListener(form) {
            form.addEventListener('submit', function(e) {
                const nis = document.getElementById('nis_edit').value;
                const nama = document.getElementById('nama_edit').value;

                if (!nis || !nama) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Data wajib Harus Diisi!',
                        icon: 'warning',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            popup: 'font-inter rounded-2xl',
                            confirmButton: 'rounded-xl'
                        }
                    });
                }
            });
        }

        // Delete Confirmation
        document.querySelectorAll('.delete-confirm').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Hapus Data Siswa?',
                    text: "Data yang dihapus beserta akun terkait tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'font-inter rounded-2xl',
                        confirmButton: 'rounded-xl',
                        cancelButton: 'rounded-xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Form Validation (Add)
        document.getElementById('frmSiswa').addEventListener('submit', function(e) {
            const nis = document.getElementById('nis').value;
            const nama = document.getElementById('nama').value;

            if (!nis || !nama) {
                e.preventDefault();
                Swal.fire({
                    title: 'Oops!',
                    text: 'Data wajib Harus Diisi!',
                    icon: 'warning',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        popup: 'font-inter rounded-2xl',
                        confirmButton: 'rounded-xl'
                    }
                });
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-inputsiswa');
                closeModal('modal-editsiswa');
            }
        });

        const existingParent = document.getElementById('existing_orangtua_id');
        if (existingParent) {
            existingParent.addEventListener('change', function () {
                const useExisting = !!this.value;
                ['parent_name', 'parent_phone', 'parent_address'].forEach(id => {
                    const field = document.getElementById(id);
                    field.required = !useExisting;
                    field.disabled = useExisting;
                });
            });
        }
    </script>
@endpush
