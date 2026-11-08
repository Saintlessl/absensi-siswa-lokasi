@extends('layouts.app')

@section('title', 'Edit Tahun Ajaran')
@section('page-title', 'Edit Tahun Ajaran')
@section('page-subtitle', 'Perbarui Konfigurasi Kalender Akademik & Periode Operasional')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.tahun-ajaran.index') }}" class="hover:text-primary-600">Tahun Ajaran</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Edit {{ $tahunAjaran->nama }}</li>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Flash Notifications --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-emerald-800 text-sm font-semibold">
                <div class="flex items-center gap-2">
                    <i class='bx bx-check-circle text-lg text-emerald-600'></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class='bx bx-x text-lg'></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between text-rose-800 text-sm font-semibold">
                <div class="flex items-center gap-2">
                    <i class='bx bx-error-circle text-lg text-rose-600'></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i class='bx bx-x text-lg'></i></button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <div class="flex items-center justify-between font-bold mb-1">
                    <span class="flex items-center gap-2">
                        <i class='bx bx-error-circle text-lg text-rose-600'></i> Terdapat kesalahan pada input:
                    </span>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i class='bx bx-x text-lg'></i></button>
                </div>
                <ul class="list-disc list-inside text-xs text-rose-700 mt-1 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-dark-100 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-dark-100 bg-dark-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-xl font-bold">
                        <i class='bx bx-calendar-edit'></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-dark-800">Edit Periode Akademik: {{ $tahunAjaran->nama }}</h3>
                        <p class="text-xs text-dark-400">ID Periode: #{{ $tahunAjaran->id }} | Dibuat: {{ $tahunAjaran->created_at ? $tahunAjaran->created_at->format('d/m/Y H:i') : '-' }}</p>
                    </div>
                </div>
                @if($tahunAjaran->is_active)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                        PERIODE AKTIF
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ ucfirst($tahunAjaran->status) }}
                    </span>
                @endif
            </div>

            <form action="{{ route('academic.tahun-ajaran.update', $tahunAjaran->id) }}" method="POST" class="p-6 space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Tahun Mulai <span class="text-rose-500">*</span></label>
                        <input type="number" name="tahun_mulai" id="tahun_mulai" required min="2000" max="2099"
                            value="{{ old('tahun_mulai', $tahunAjaran->tahun_mulai) }}"
                            class="w-full px-3.5 py-2.5 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Tahun Selesai <span class="text-rose-500">*</span></label>
                        <input type="number" name="tahun_selesai" id="tahun_selesai" required min="2000" max="2099"
                            value="{{ old('tahun_selesai', $tahunAjaran->tahun_selesai) }}"
                            class="w-full px-3.5 py-2.5 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Nama Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" id="nama" required
                            value="{{ old('nama', $tahunAjaran->nama) }}"
                            class="w-full px-3.5 py-2.5 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono font-bold text-[#003EA7]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Semester Aktif <span class="text-rose-500">*</span></label>
                        <select name="semester_aktif" id="semester_aktif" required
                            class="w-full px-3.5 py-2.5 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            <option value="ganjil" {{ old('semester_aktif', $tahunAjaran->semester_aktif) === 'ganjil' ? 'selected' : '' }}>Semester Ganjil</option>
                            <option value="genap" {{ old('semester_aktif', $tahunAjaran->semester_aktif) === 'genap' ? 'selected' : '' }}>Semester Genap</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" required
                            value="{{ old('tanggal_mulai', $tahunAjaran->tanggal_mulai ? $tahunAjaran->tanggal_mulai->format('Y-m-d') : '') }}"
                            class="w-full px-3.5 py-2.5 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" required
                            value="{{ old('tanggal_selesai', $tahunAjaran->tanggal_selesai ? $tahunAjaran->tanggal_selesai->format('Y-m-d') : '') }}"
                            class="w-full px-3.5 py-2.5 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1.5">Status Periode</label>
                    <select name="status" id="status"
                        class="w-full px-3.5 py-2.5 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        <option value="draft" {{ old('status', $tahunAjaran->status) === 'draft' ? 'selected' : '' }}>Draft (Perencanaan)</option>
                        <option value="aktif" {{ old('status', $tahunAjaran->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="selesai" {{ old('status', $tahunAjaran->status) === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="diarsipkan" {{ old('status', $tahunAjaran->status) === 'diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1.5">Catatan / Deskripsi Periode</label>
                    <textarea name="catatan" id="catatan" rows="3" placeholder="Tambahkan catatan kalender akademik..."
                        class="w-full px-3.5 py-2.5 text-xs border border-dark-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">{{ old('catatan', $tahunAjaran->catatan) }}</textarea>
                </div>

                <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 flex items-start gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $tahunAjaran->is_active) ? 'checked' : '' }}
                        class="mt-0.5 rounded text-[#003EA7] focus:ring-[#003EA7]">
                    <div>
                        <label for="is_active" class="text-xs text-dark-800 font-bold cursor-pointer select-none block">
                            Tetapkan sebagai Tahun Ajaran Aktif Sekolah
                        </label>
                        <span class="text-[11px] text-dark-500 block mt-0.5">
                            Jika dicentang, sistem akan memastikan ini menjadi satu-satunya periode aktif operasional sekolah.
                        </span>
                    </div>
                </div>

                <div class="pt-5 border-t border-dark-100 flex items-center justify-between">
                    <a href="{{ route('academic.tahun-ajaran.index') }}"
                        class="px-5 py-2.5 border border-dark-200 rounded-xl text-xs font-semibold text-dark-600 hover:bg-dark-50 transition flex items-center gap-1.5">
                        <i class='bx bx-arrow-back'></i>
                        <span>Kembali ke Daftar</span>
                    </a>

                    <button type="submit"
                        class="px-6 py-2.5 bg-[#003EA7] hover:bg-primary-700 text-white rounded-xl text-xs font-bold shadow-md shadow-primary-500/20 transition flex items-center gap-1.5">
                        <i class='bx bx-save text-base'></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
