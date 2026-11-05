@extends('layouts.app')

@section('page-title', 'Buat Komponen Asesmen Baru')
@section('page-subtitle', 'Tentukan rombel, kategori penilaian, dan bobot untuk penilaian tugas atau ujian')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-blue-50">
        <form method="POST" action="{{ route('gradebook.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Kelas --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Rombel / Kelas *</label>
                    <select name="kelas_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                        <option value="">Pilih Rombel Target</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('kelas_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori Penilaian --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori Asesmen *</label>
                    <select name="assessment_category_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('assessment_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nama }} ({{ $cat->kode }}) - Bobot Default {{ $cat->bobot_default }}%
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Mata Pelajaran --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mata Pelajaran (Opsional jika terjadwal)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <select name="mata_pelajaran_id" class="text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                            <option value="">Pilih dari Mapel Diampu</option>
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}" {{ old('mata_pelajaran_id') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->nama_mapel }} ({{ $sub->kode_mapel }})
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="nama_mapel" value="{{ old('nama_mapel') }}" placeholder="Atau ketik nama mapel manual..."
                            class="text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                    </div>
                </div>

                {{-- Judul Asesmen --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Judul / Topik Penilaian *</label>
                    <input type="text" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Praktik Pengelasan Sambungan T / Tugas Membuat Layout CSS" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Deskripsi --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi / Instruksi Tugas (Opsional)</label>
                    <textarea name="deskripsi" rows="2" placeholder="Instruksi pengerjaan tugas atau kriteria rubrik penilaian..."
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 p-3">{{ old('deskripsi') }}</textarea>
                </div>

                {{-- Tanggal Pelaksanaan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Pelaksanaan *</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                {{-- Tahun Ajaran & Semester --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', '2026/2027') }}" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Semester</label>
                        <select name="semester" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                            <option value="ganjil" {{ old('semester') === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="genap" {{ old('semester') === 'genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                    </div>
                </div>

                {{-- Skor Maksimal & Bobot --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Skor Maksimal *</label>
                    <input type="number" step="1" name="skor_maksimal" value="{{ old('skor_maksimal', 100) }}" min="1" max="1000" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Bobot Penilaian (Pengali) *</label>
                    <input type="number" step="0.1" name="bobot" value="{{ old('bobot', 1.0) }}" min="0.1" max="10" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-2 px-3">
                </div>
            </div>

            <input type="hidden" name="status" value="draft">

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('gradebook.index') }}" class="px-5 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs flex items-center gap-1.5">
                    <i class='bx bx-check'></i> Simpan & Lanjut Input Nilai
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
