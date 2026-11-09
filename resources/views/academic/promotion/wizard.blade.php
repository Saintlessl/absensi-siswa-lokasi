@extends('layouts.app')

@section('title', 'Kenaikan Kelas & Kelulusan Massal')
@section('page-title', 'Kenaikan Kelas & Kelulusan')
@section('page-subtitle', 'Wizard 6-Langkah Perpindahan Tahun Ajaran, Kenaikan Kelas, dan Kelulusan Siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400 text-sm'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-[#003EA7]">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-slate-400 text-sm'></i></li>
    <li class="text-[#003EA7] font-medium">Kenaikan Kelas</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Wizard Kenaikan Kelas, Tinggal Kelas & Kelulusan</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Proses terstruktur multi-langkah perpindahan siswa antar tahun ajaran dengan menjaga keutuhan identitas kohort permanen dan riwayat wali kelas.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('academic.promotion.history') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold shadow-sm transition-all duration-200 hover:-translate-y-0.5">
                <i class='bx bx-history text-base text-[#003EA7]'></i>
                <span>Riwayat Proses Kenaikan</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
<div x-data="promotionWizard()" x-init="init()" class="space-y-6">

    {{-- STEP PROGRESS INDICATOR --}}
    <div class="bg-white rounded-2xl p-4 sm:p-6 border border-slate-100 shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-2 sm:gap-4 relative">
            <template x-for="(s, index) in steps" :key="s.number">
                <div class="flex items-center gap-3 p-2.5 rounded-xl transition-all cursor-pointer"
                    :class="{
                        'bg-blue-50/80 border border-blue-200 text-[#003EA7] font-bold shadow-sm': step === s.number,
                        'text-emerald-700 bg-emerald-50/50 border border-emerald-100': step > s.number,
                        'text-slate-400 opacity-60 cursor-not-allowed': step < s.number
                    }"
                    @click="goToStep(s.number)">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0 transition-all"
                        :class="{
                            'bg-[#003EA7] text-white shadow-md shadow-blue-500/20': step === s.number,
                            'bg-emerald-600 text-white': step > s.number,
                            'bg-slate-200 text-slate-500': step < s.number
                        }">
                        <template x-if="step > s.number">
                            <i class='bx bx-check text-base'></i>
                        </template>
                        <template x-if="step <= s.number">
                            <span x-text="s.number"></span>
                        </template>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider opacity-75" x-text="'Langkah ' + s.number"></p>
                        <p class="text-xs font-semibold truncate" x-text="s.title"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- =========================================================================
         LANGKAH 1: PILIH TAHUN AJARAN
         ========================================================================= --}}
    <div x-show="step === 1" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class='bx bx-calendar-star text-primary-600 text-lg'></i>
                <span>Langkah 1: Tentukan Periode Operasional Asal & Tujuan</span>
            </h3>
            <p class="text-xs text-slate-500 mt-1">
                Pilih Tahun Ajaran yang sedang dievaluasi (Asal) dan Tahun Ajaran baru yang menjadi target penempatan siswa (Tujuan).
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Tahun Ajaran Asal --}}
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i class='bx bx-history text-slate-500'></i>
                        <span>Tahun Ajaran Asal</span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-semibold">Tahun Aktif/Berjalan</span>
                </div>

                <select x-model="originTaId" @change="onOriginTaChange()"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-[#003EA7] focus:border-transparent transition-all">
                    <option value="">-- Pilih Tahun Ajaran Asal --</option>
                    @foreach($academicYears as $ta)
                        <option value="{{ $ta->id }}" {{ $ta->is_active ? 'selected' : '' }}>
                            {{ $ta->nama }} (Semester {{ ucfirst($ta->semester_aktif) }}) - Status: {{ ucfirst($ta->status) }} {{ $ta->is_active ? '★ AKTIF' : '' }}
                        </option>
                    @endforeach
                </select>

                <div class="text-xs text-slate-500 bg-white p-3 rounded-xl border border-slate-100 space-y-1">
                    <div class="flex justify-between">
                        <span>Rombel Tersedia:</span>
                        <strong class="text-slate-800" x-text="originClasses.length + ' Rombel'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Siswa Aktif Terdaftar:</span>
                        <strong class="text-emerald-700" x-text="totalActiveStudentsInOrigin + ' Siswa'"></strong>
                    </div>
                </div>
            </div>

            {{-- Tahun Ajaran Tujuan --}}
            <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-200/70 space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-[#003EA7] uppercase tracking-wider flex items-center gap-2">
                        <i class='bx bx-right-arrow-alt text-[#003EA7]'></i>
                        <span>Tahun Ajaran Tujuan (Baru)</span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-100 text-[#003EA7] font-semibold">Tahun Baru</span>
                </div>

                <select x-model="destTaId" @change="onDestTaChange()"
                    class="w-full px-4 py-3 rounded-xl border border-blue-200 bg-white text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-[#003EA7] focus:border-transparent transition-all">
                    <option value="">-- Pilih Tahun Ajaran Tujuan --</option>
                    @foreach($academicYears as $ta)
                        <option value="{{ $ta->id }}">
                            {{ $ta->nama }} (Tahun Kalender: {{ $ta->tahun_mulai }}/{{ $ta->tahun_selesai }}) - {{ ucfirst($ta->status) }}
                        </option>
                    @endforeach
                </select>

                <div class="text-xs text-slate-500 bg-white p-3 rounded-xl border border-blue-100 space-y-1">
                    <div class="flex justify-between">
                        <span>Rombel Tujuan Terdaftar:</span>
                        <strong class="text-[#003EA7]" x-text="destClasses.length + ' Rombel'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Pemeriksaan Enrollment Ganda:</span>
                        <span class="font-semibold text-emerald-600">Aktif & Tervalidasi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notice on Destination Academic Year --}}
        <div x-show="destTaId && destClasses.length === 0" x-cloak
            class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-amber-900 text-xs">
            <div class="flex items-center gap-3">
                <i class='bx bx-info-circle text-2xl text-amber-600 flex-shrink-0'></i>
                <div>
                    <p class="font-bold">Tahun Ajaran Tujuan belum memiliki struktur rombel!</p>
                    <p class="text-amber-700 mt-0.5">Anda dapat menyalin struktur rombel kelas dari Tahun Ajaran Asal secara instan ke Tahun Ajaran Tujuan.</p>
                </div>
            </div>
            <button type="button" @click="replicateStructure()" :disabled="isLoading"
                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5 flex-shrink-0">
                <i class='bx bx-copy'></i>
                <span>Salin Struktur Rombel Sekarang</span>
            </button>
        </div>

        {{-- Warning if no active students in selected origin TA --}}
        <div x-show="originTaId && originClasses.length > 0 && totalActiveStudentsInOrigin === 0" x-cloak
            class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5">
            <i class='bx bx-info-circle text-amber-600 text-lg flex-shrink-0 mt-0.5'></i>
            <div>
                <p class="font-bold text-amber-900">Perhatian: Tidak Ada Siswa Aktif pada Tahun Ajaran Ini</p>
                <p class="mt-0.5 text-amber-700">Tahun ajaran ini memiliki struktur rombel namun belum memiliki data siswa aktif. Kenaikan kelas hanya dapat diproses untuk tahun ajaran yang memiliki data siswa (misalnya: <strong>{{ $activeTa?->nama }}</strong>).</p>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="button" @click="advanceToStep2()"
                :disabled="!originTaId || !destTaId || originTaId === destTaId || originClasses.length === 0"
                class="px-6 py-3 bg-[#003EA7] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                <span>Lanjut ke Langkah 2: Pilih Rombel Asal</span>
                <i class='bx bx-chevron-right text-base'></i>
            </button>
        </div>
    </div>

    {{-- =========================================================================
         LANGKAH 2: PILIH ROMBEL ASAL
         ========================================================================= --}}
    <div x-show="step === 2" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-chalkboard text-primary-600 text-lg'></i>
                    <span>Langkah 2: Pilih Rombongan Belajar (Rombel) Asal</span>
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Centang rombongan belajar yang ingin dievaluasi status kenaikan kelas atau kelulusannya.
                </p>
            </div>
            <div class="flex items-center gap-2" x-show="originClasses.length > 0">
                <button type="button" @click="toggleSelectAllClasses()"
                    class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-all cursor-pointer shadow-2xs">
                    <span x-text="selectedKelasIds.length > 0 ? 'Batal Pilih Semua (' + selectedKelasIds.length + ')' : 'Pilih Semua Rombel Aktif'"></span>
                </button>
            </div>
        </div>

        {{-- Filter & Count Summary --}}
        <div x-show="originClasses.length > 0" class="flex flex-wrap items-center justify-between gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-slate-400 font-bold text-[11px] uppercase mr-1">Filter:</span>
                <button type="button" @click="filterTingkat = ''"
                    class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all cursor-pointer"
                    :class="filterTingkat === '' ? 'bg-[#003EA7] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    Semua (<span x-text="originClasses.length"></span>)
                </button>
                <button type="button" @click="filterTingkat = 'with_students'"
                    class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all cursor-pointer"
                    :class="filterTingkat === 'with_students' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    Ada Siswa (<span x-text="originClassesWithStudents.length"></span>)
                </button>
                <button type="button" @click="filterTingkat = 10"
                    class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all cursor-pointer"
                    :class="filterTingkat === 10 ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    Kelas 10
                </button>
                <button type="button" @click="filterTingkat = 11"
                    class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all cursor-pointer"
                    :class="filterTingkat === 11 ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    Kelas 11
                </button>
                <button type="button" @click="filterTingkat = 12"
                    class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all cursor-pointer"
                    :class="filterTingkat === 12 ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    Kelas 12
                </button>
            </div>
            <div class="text-[11px] text-slate-500 font-medium">
                Terpilih: <strong class="text-[#003EA7]" x-text="selectedKelasIds.length"></strong> dari <strong x-text="originClasses.length"></strong> rombel
                (<strong class="text-emerald-700" x-text="selectedActiveStudentCount"></strong> siswa aktif)
            </div>
        </div>

        {{-- Grouped by Jurusan & Tingkat (Ringkas & Terorganisir) --}}
        <div x-show="groupedOriginClassesByJurusan.length > 0" class="space-y-4">
            <template x-for="group in groupedOriginClassesByJurusan" :key="group.jurusan_id">
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden transition-all hover:border-slate-300">
                    {{-- Header Jurusan --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 sm:px-5 py-3 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black tracking-wider bg-[#003EA7] text-white shadow-xs"
                                x-text="group.jurusan_kode"></span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 leading-tight" x-text="group.jurusan_nama"></h4>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-500 mt-0.5">
                                    <span><strong class="text-slate-700" x-text="group.classes.length"></strong> Rombel</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span :class="getClassesActiveStudentCount(group.classes) > 0 ? 'text-emerald-700 font-bold' : 'text-slate-500'">
                                        <strong x-text="getClassesActiveStudentCount(group.classes)"></strong> Siswa Terdaftar
                                    </span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span>Terpilih: <strong class="text-[#003EA7]" x-text="getSelectedCountInGroup(group.classes)"></strong>/<span x-text="group.classes.length"></span> rombel</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-start sm:self-center flex-shrink-0">
                            <button type="button" @click="toggleJurusanClasses(group.classes)"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                                :class="isJurusanFullySelected(group.classes)
                                    ? 'bg-blue-100 text-[#003EA7] hover:bg-blue-200 ring-1 ring-blue-300'
                                    : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-slate-300 shadow-2xs'">
                                <i class='bx' :class="isJurusanFullySelected(group.classes) ? 'bx-check-double text-base text-[#003EA7]' : 'bx-select-multiple text-base text-slate-500'"></i>
                                <span x-text="isJurusanFullySelected(group.classes) ? 'Batal Pilih Jurusan' : 'Pilih Semua Jurusan Ini'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Body Tingkat Rows --}}
                    <div class="p-3 sm:p-4 space-y-2.5">
                        <template x-for="lvl in group.levels" :key="lvl.tingkat">
                            <div class="flex flex-col md:flex-row md:items-center gap-2 p-2 sm:p-2.5 rounded-xl bg-slate-50/70 border border-slate-100 hover:border-slate-200/90 transition-colors">
                                {{-- Tingkat Label & Quick Selector --}}
                                <div class="flex items-center justify-between md:w-36 flex-shrink-0">
                                    <button type="button" @click="toggleLevelClasses(lvl.classes)"
                                        class="group/lvl inline-flex items-center gap-1.5 text-left cursor-pointer hover:opacity-85 transition-opacity"
                                        :title="'Klik untuk memilih/membatalkan semua rombel Kelas ' + lvl.tingkat + ' di jurusan ini'">
                                        <span class="w-2.5 h-2.5 rounded-full" :class="{
                                            'bg-indigo-500 ring-2 ring-indigo-200': lvl.tingkat === 10,
                                            'bg-sky-500 ring-2 ring-sky-200': lvl.tingkat === 11,
                                            'bg-amber-500 ring-2 ring-amber-200': lvl.tingkat === 12
                                        }"></span>
                                        <span class="text-xs font-bold text-slate-700 group-hover/lvl:text-[#003EA7]" x-text="'Kelas ' + lvl.tingkat"></span>
                                    </button>
                                    <span class="text-[10px] text-slate-400 font-semibold md:mr-2">
                                        (<span x-text="getSelectedCountInGroup(lvl.classes)"></span>/<span x-text="lvl.classes.length"></span>)
                                    </span>
                                </div>

                                {{-- Class Chips / Pills --}}
                                <div class="flex flex-wrap items-center gap-2 flex-1">
                                    <template x-for="c in lvl.classes" :key="c.id">
                                        <button type="button" @click="toggleClass(c.id)"
                                            :title="c.wali_kelas ? (c.nama_kelas + ' — Wali: ' + c.wali_kelas) : c.nama_kelas"
                                            class="group relative inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs border transition-all duration-150 select-none cursor-pointer text-left"
                                            :class="isClassSelected(c.id)
                                                ? 'bg-blue-50/95 border-blue-400 text-blue-950 font-bold ring-2 ring-blue-500/25 shadow-xs'
                                                : (c.total_siswa_aktif > 0
                                                    ? 'bg-white border-slate-200 text-slate-700 hover:border-blue-300 hover:bg-slate-50 shadow-2xs'
                                                    : 'bg-white/80 border-slate-200/70 text-slate-500 hover:border-slate-300 hover:text-slate-700')">
                                            
                                            <i class='bx text-sm transition-transform group-hover:scale-110 flex-shrink-0'
                                                :class="isClassSelected(c.id) ? 'bxs-check-circle text-[#003EA7]' : 'bx-circle text-slate-300'"></i>

                                            <span class="text-xs tracking-tight" x-text="c.nama_kelas"></span>

                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md flex-shrink-0"
                                                :class="c.total_siswa_aktif > 0
                                                    ? (isClassSelected(c.id) ? 'bg-emerald-600 text-white' : 'bg-emerald-100 text-emerald-800')
                                                    : 'bg-slate-100 text-slate-400'"
                                                x-text="c.total_siswa_aktif + ' Siswa'">
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty state when no classes found in filtered view --}}
        <div x-show="groupedOriginClassesByJurusan.length === 0 && originClasses.length > 0" x-cloak
            class="p-8 text-center text-slate-400 bg-slate-50 rounded-xl border border-slate-100">
            <p class="text-xs">Tidak ada rombel yang cocok dengan filter yang dipilih.</p>
        </div>

        {{-- Empty state when no classes found in origin TA --}}
        <div x-show="originClasses.length === 0 && !isLoading" x-cloak
            class="p-12 text-center text-slate-400 bg-slate-50/50 rounded-2xl border border-slate-200 space-y-3">
            <div class="w-16 h-16 mx-auto rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-3xl">
                <i class='bx bx-folder-open'></i>
            </div>
            <h4 class="text-sm font-bold text-slate-800">Tidak Ada Rombel pada Tahun Ajaran yang Dipilih</h4>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Tahun Ajaran Asal yang Anda pilih belum memiliki struktur rombel kelas. Silakan kembali ke Langkah 1 dan pilih Tahun Ajaran yang memiliki data rombel aktif (misalnya: <strong>{{ $activeTa?->nama }}</strong>).
            </p>
            <button type="button" @click="step = 1"
                class="px-4 py-2 bg-[#003EA7] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                &larr; Kembali ke Langkah 1
            </button>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <button type="button" @click="step = 1"
                class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                <i class='bx bx-chevron-left text-base align-middle'></i>
                <span>Kembali ke Langkah 1</span>
            </button>

            <button type="button" @click="advanceToStep3()"
                :disabled="selectedKelasIds.length === 0 || isLoading"
                class="px-6 py-3 bg-[#003EA7] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                <span x-show="!isLoading">Muat Siswa & Tentukan Status</span>
                <span x-show="isLoading">Memuat Data Siswa...</span>
                <i class='bx bx-chevron-right text-base'></i>
            </button>
        </div>
    </div>

    {{-- =========================================================================
         LANGKAH 3: TENTUKAN STATUS SISWA
         ========================================================================= --}}
    <div x-show="step === 3" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-user-check text-primary-600 text-lg'></i>
                    <span>Langkah 3: Tentukan Keputusan Akademik Setiap Siswa</span>
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Sistem telah memberikan default cerdas (Kelas 10 &rarr; Naik 11, Kelas 11 &rarr; Naik 12, Kelas 12 &rarr; Kelulusan). Admin dapat mengubah status secara bebas.
                </p>
            </div>

            {{-- Summary Badges --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    Naik: <strong x-text="countAction('naik_kelas')"></strong>
                </span>
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Tinggal: <strong x-text="countAction('tinggal_kelas')"></strong>
                </span>
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Lulus: <strong x-text="countAction('lulus')"></strong>
                </span>
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                    Pindah: <strong x-text="countAction('pindah')"></strong>
                </span>
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    Keluar/DO: <strong x-text="countAction('keluar')"></strong>
                </span>
            </div>
        </div>

        {{-- Controls and Search --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-50 p-3 rounded-xl">
            <div class="flex items-center gap-2 flex-1">
                <div class="relative flex-1 max-w-sm">
                    <i class='bx bx-search absolute left-3 top-2.5 text-slate-400 text-base'></i>
                    <input type="text" x-model="searchStudent" placeholder="Cari NIS, NISN, atau Nama Siswa..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:ring-1 focus:ring-[#003EA7]">
                </div>

                <select x-model="filterKelasAsal" class="px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white">
                    <option value="">Semua Rombel Asal</option>
                    <template x-for="c in selectedClassesInfo" :key="c.id">
                        <option :value="c.id" x-text="c.nama_kelas"></option>
                    </template>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="bulkSetAction('naik_kelas')"
                    class="px-2.5 py-1.5 bg-blue-100 hover:bg-blue-200 text-[#003EA7] font-bold text-[11px] rounded-lg transition-all">
                    Tandai Semua Naik
                </button>
                <button type="button" @click="bulkSetAction('lulus', 12)"
                    class="px-2.5 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold text-[11px] rounded-lg transition-all">
                    Tandai Kls 12 Lulus
                </button>
                <button type="button" @click="resetToDefaults()"
                    class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-[11px] rounded-lg transition-all">
                    Reset
                </button>
            </div>
        </div>

        {{-- Students Table --}}
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="p-3 w-12 text-center">No</th>
                        <th class="p-3">Siswa & Kohort Permanen</th>
                        <th class="p-3">Rombel Asal</th>
                        <th class="p-3 w-48">Keputusan Status</th>
                        <th class="p-3">Keterangan / Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(s, idx) in filteredStudents" :key="s.siswa_id">
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="p-3 text-center text-slate-400 font-semibold" x-text="idx + 1"></td>
                            <td class="p-3">
                                <div class="font-bold text-slate-800" x-text="s.nama"></div>
                                <div class="text-[11px] text-slate-500 font-mono" x-text="'NIS: ' + s.nis + ' | NISN: ' + (s.nisn || '-')"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.2 bg-slate-100 rounded text-slate-600 font-semibold" x-text="s.angkatan_nama"></span>
                                    <span>Masuk: <strong x-text="s.tahun_ajaran_masuk || s.tahun_masuk"></strong></span>
                                </div>
                            </td>
                            <td class="p-3">
                                <span class="font-semibold text-slate-800" x-text="s.kelas_asal_nama"></span>
                                <div class="text-[10px] text-slate-400" x-text="'Tingkat ' + s.tingkat_asal + ' • ' + s.jurusan_kode"></div>
                            </td>
                            <td class="p-3">
                                <select x-model="s.action" @change="onStudentActionChange(s)"
                                    class="w-full px-3 py-1.5 text-xs rounded-lg border font-bold transition-all"
                                    :class="{
                                        'bg-blue-50 text-blue-700 border-blue-200': s.action === 'naik_kelas',
                                        'bg-amber-50 text-amber-800 border-amber-200': s.action === 'tinggal_kelas',
                                        'bg-emerald-50 text-emerald-800 border-emerald-200': s.action === 'lulus',
                                        'bg-purple-50 text-purple-800 border-purple-200': s.action === 'pindah',
                                        'bg-rose-50 text-rose-800 border-rose-200': s.action === 'keluar'
                                    }">
                                    <option value="naik_kelas">Naik Kelas</option>
                                    <option value="tinggal_kelas">Tinggal Kelas</option>
                                    <option value="lulus">Lulus (Alumni)</option>
                                    <option value="pindah">Pindah Sekolah (Mutasi)</option>
                                    <option value="keluar">Keluar / Drop Out</option>
                                </select>
                            </td>
                            <td class="p-3">
                                <input type="text" x-model="s.catatan" placeholder="Catatan opsional..."
                                    class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <button type="button" @click="step = 2"
                class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                <i class='bx bx-chevron-left text-base align-middle'></i>
                <span>Kembali ke Pilih Rombel</span>
            </button>

            <button type="button" @click="advanceToStep4()"
                class="px-6 py-3 bg-[#003EA7] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center gap-2">
                <span>Lanjut ke Langkah 4: Alokasi Rombel Tujuan</span>
                <i class='bx bx-chevron-right text-base'></i>
            </button>
        </div>
    </div>

    {{-- =========================================================================
         LANGKAH 4: ALOKASI ROMBEL TUJUAN
         ========================================================================= --}}
    <div x-show="step === 4" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-git-branch text-primary-600 text-lg'></i>
                    <span>Langkah 4: Alokasi Rombel Tujuan Tahun Ajaran Baru</span>
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Hanya siswa yang berstatus <strong class="text-blue-700">Naik Kelas</strong> atau <strong class="text-amber-700">Tinggal Kelas</strong> yang dialokasikan ke rombel tujuan. Siswa lulus/pindah/keluar tidak membutuhkan alokasi rombel.
                </p>
            </div>

            <button type="button" @click="autoMapDestinationClasses(true)"
                class="px-3.5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5 flex-shrink-0">
                <i class='bx bx-magic-wand'></i>
                <span>Auto-Map Otomatis</span>
            </button>
        </div>

        {{-- Destination Class Capacity Monitor --}}
        <div>
            <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5">Kapasitas & Alokasi Rombel Tujuan:</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5">
                <template x-for="dc in destClasses" :key="dc.id">
                    <div class="p-2.5 rounded-xl border bg-slate-50/50"
                        :class="getAllocatedCountForClass(dc.id) > dc.kapasitas ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200'">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800 truncate" x-text="dc.nama_kelas"></span>
                            <span class="text-[10px] font-bold"
                                :class="getAllocatedCountForClass(dc.id) > dc.kapasitas ? 'text-rose-600' : 'text-slate-500'"
                                x-text="getAllocatedCountForClass(dc.id) + '/' + dc.kapasitas"></span>
                        </div>
                        <div class="w-full bg-slate-200 h-1.5 rounded-full mt-1.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all"
                                :class="getAllocatedCountForClass(dc.id) > dc.kapasitas ? 'bg-rose-500' : 'bg-primary-600'"
                                :style="'width: ' + Math.min(100, (getAllocatedCountForClass(dc.id) / dc.kapasitas) * 100) + '%'"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Student Allocation Table --}}
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="p-3 w-12 text-center">No</th>
                        <th class="p-3">Siswa</th>
                        <th class="p-3">Rombel Asal</th>
                        <th class="p-3">Status Keputusan</th>
                        <th class="p-3 w-64">Rombel Tujuan (T.A. Baru)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(s, idx) in activePromotingStudents" :key="s.siswa_id">
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="p-3 text-center text-slate-400 font-semibold" x-text="idx + 1"></td>
                            <td class="p-3">
                                <div class="font-bold text-slate-800" x-text="s.nama"></div>
                                <div class="text-[11px] text-slate-500 font-mono" x-text="'NIS: ' + s.nis"></div>
                            </td>
                            <td class="p-3">
                                <span class="font-semibold text-slate-800" x-text="s.kelas_asal_nama"></span>
                                <div class="text-[10px] text-slate-400" x-text="'Tingkat ' + s.tingkat_asal"></div>
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold"
                                    :class="s.action === 'naik_kelas' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'"
                                    x-text="s.action === 'naik_kelas' ? 'Naik Kelas' : 'Tinggal Kelas'"></span>
                            </td>
                            <td class="p-3">
                                <select x-model="s.kelas_tujuan_id"
                                    class="w-full px-3 py-1.5 text-xs rounded-lg border font-semibold"
                                    :class="!s.kelas_tujuan_id ? 'border-rose-300 bg-rose-50/30 text-rose-700' : 'border-slate-200 bg-white text-slate-800'">
                                    <option value="">-- Pilih Rombel Tujuan --</option>
                                    <template x-for="dc in destClasses" :key="dc.id">
                                        <option :value="dc.id"
                                            x-text="dc.nama_kelas + ' (' + dc.jurusan_kode + ' - Tk ' + dc.tingkat + ')'"></option>
                                    </template>
                                </select>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <button type="button" @click="step = 3"
                class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                <i class='bx bx-chevron-left text-base align-middle'></i>
                <span>Kembali ke Status Siswa</span>
            </button>

            <button type="button" @click="advanceToStep5()"
                class="px-6 py-3 bg-[#003EA7] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center gap-2">
                <span>Lanjut ke Langkah 5: Penetapan Wali Kelas</span>
                <i class='bx bx-chevron-right text-base'></i>
            </button>
        </div>
    </div>

    {{-- =========================================================================
         LANGKAH 5: PENETAPAN WALI KELAS
         ========================================================================= --}}
    <div x-show="step === 5" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class='bx bx-user-pin text-primary-600 text-lg'></i>
                <span>Langkah 5: Penetapan Wali Kelas Tahun Ajaran Baru</span>
            </h3>
            <p class="text-xs text-slate-500 mt-1">
                Tugaskan wali kelas baru untuk rombel tujuan yang menerima siswa. Riwayat penugasan tahun ajaran lalu tidak akan terhapus.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template x-for="dc in allocatedDestClasses" :key="dc.id">
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800 text-sm" x-text="dc.nama_kelas"></span>
                        <span class="text-[11px] px-2 py-0.5 rounded bg-blue-100 text-[#003EA7] font-bold"
                            x-text="getAllocatedCountForClass(dc.id) + ' Siswa Dialokasikan'"></span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                            Pilih Wali Kelas Baru:
                        </label>
                        <select x-model="homeroomAssignments[dc.id]"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white font-medium focus:ring-1 focus:ring-[#003EA7]">
                            <option value="">-- Tetap Gunakan / Tanpa Pergantian --</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->nama }} (NIP: {{ $g->nip ?: '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-[11px] text-slate-400 flex items-center justify-between">
                        <span>Wali Kelas Saat Ini:</span>
                        <strong class="text-slate-600" x-text="dc.wali_kelas_nama || 'Belum Ditetapkan'"></strong>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <button type="button" @click="step = 4"
                class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                <i class='bx bx-chevron-left text-base align-middle'></i>
                <span>Kembali ke Alokasi Rombel</span>
            </button>

            <button type="button" @click="advanceToStep6()"
                class="px-6 py-3 bg-[#003EA7] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center gap-2">
                <span>Lanjut ke Langkah 6: Review & Konfirmasi Final</span>
                <i class='bx bx-chevron-right text-base'></i>
            </button>
        </div>
    </div>

    {{-- =========================================================================
         LANGKAH 6: REVIEW DAN EKSEKUSI
         ========================================================================= --}}
    <div x-show="step === 6" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class='bx bx-check-shield text-emerald-600 text-lg'></i>
                <span>Langkah 6: Review Akhir & Eksekusi Batch Atomik</span>
            </h3>
            <p class="text-xs text-slate-500 mt-1">
                Seluruh data akan diproses dalam satu transaksi database atomik (<code class="font-mono text-primary-600">DB::transaction</code>) dengan perlindungan konkurensi data.
            </p>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Siswa</span>
                <p class="text-lg font-bold text-slate-800 mt-0.5" x-text="students.length"></p>
            </div>
            <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200">
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Naik Kelas</span>
                <p class="text-lg font-bold text-blue-700 mt-0.5" x-text="countAction('naik_kelas')"></p>
            </div>
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200">
                <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Tinggal Kelas</span>
                <p class="text-lg font-bold text-amber-700 mt-0.5" x-text="countAction('tinggal_kelas')"></p>
            </div>
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200">
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Kelulusan</span>
                <p class="text-lg font-bold text-emerald-700 mt-0.5" x-text="countAction('lulus')"></p>
            </div>
            <div class="p-3.5 rounded-xl bg-purple-50 border border-purple-200">
                <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider">Mutasi Pindah</span>
                <p class="text-lg font-bold text-purple-700 mt-0.5" x-text="countAction('pindah')"></p>
            </div>
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200">
                <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Keluar / DO</span>
                <p class="text-lg font-bold text-rose-700 mt-0.5" x-text="countAction('keluar')"></p>
            </div>
        </div>

        {{-- Execution Notes --}}
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Catatan Berita Acara Eksekusi (Opsional):
            </label>
            <textarea x-model="batchCatatan" rows="2" placeholder="Tuliskan catatan atau nomor SK rapat pleno kenaikan kelas..."
                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-1 focus:ring-[#003EA7]"></textarea>
        </div>

        {{-- Final Confirmation Checkbox --}}
        <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200 flex items-start gap-3">
            <input type="checkbox" id="confirmCheckbox" x-model="isConfirmed"
                class="mt-1 rounded text-[#003EA7] focus:ring-[#003EA7]">
            <label for="confirmCheckbox" class="text-xs text-slate-700 font-medium cursor-pointer">
                <strong>Saya mengonfirmasi bahwa seluruh keputusan akademik siswa di atas telah diverifikasi.</strong>
                Saya memahami bahwa proses ini akan menutup enrollment tahun ajaran asal, membuat enrollment baru di tahun ajaran tujuan, membekukan riwayat snapshot wali kelas, dan mencatat mutasi status secara permanen di basis data.
            </label>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <button type="button" @click="step = 5"
                class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                <i class='bx bx-chevron-left text-base align-middle'></i>
                <span>Kembali ke Wali Kelas</span>
            </button>

            <button type="button" @click="submitPromotionBatch()"
                :disabled="!isConfirmed || isSubmitting"
                class="px-8 py-3.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/25 transition-all flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                <i class='bx bx-check-double text-lg' x-show="!isSubmitting"></i>
                <i class='bx bx-loader-alt bx-spin text-lg' x-show="isSubmitting"></i>
                <span x-text="isSubmitting ? 'Mengeksekusi Transaksi Atomik...' : 'Eksekusi Kenaikan Kelas & Kelulusan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- SCRIPT ALPINE.JS WIZARD LOGIC --}}
<script>
function promotionWizard() {
    return {
        step: 1,
        isLoading: false,
        isSubmitting: false,
        isConfirmed: false,
        originTaId: '{{ $activeTa?->id }}',
        destTaId: '',
        academicYearsData: @json($academicYears->keyBy('id')->map(fn($ta) => ['id' => $ta->id, 'tahun_mulai' => (int) $ta->tahun_mulai, 'nama' => $ta->nama])),
        originClasses: [],
        selectedKelasIds: [],
        students: [],
        destClasses: [],
        homeroomAssignments: {},
        batchCatatan: '',
        searchStudent: '',
        filterKelasAsal: '',
        filterTingkat: '',

        steps: [
            { number: 1, title: 'Tahun Ajaran' },
            { number: 2, title: 'Pilih Rombel' },
            { number: 3, title: 'Status Siswa' },
            { number: 4, title: 'Rombel Tujuan' },
            { number: 5, title: 'Wali Kelas' },
            { number: 6, title: 'Review & Eksekusi' }
        ],

        init() {
            if (this.originTaId) {
                this.loadOriginClasses();
            }
        },

        goToStep(s) {
            if (s < this.step) {
                this.step = s;
            }
        },

        async onOriginTaChange() {
            if (this.originTaId) {
                await this.loadOriginClasses();
            } else {
                this.originClasses = [];
                this.selectedKelasIds = [];
            }
        },

        async onDestTaChange() {
            if (this.destTaId) {
                await this.loadDestClasses();
            } else {
                this.destClasses = [];
            }
        },

        async loadOriginClasses() {
            this.isLoading = true;
            try {
                const res = await fetch(`{{ route('academic.promotion.origin_classes') }}?origin_ta_id=${this.originTaId}`);
                const data = await res.json();
                if (data.success) {
                    this.originClasses = data.classes;
                    const withStudents = this.originClasses.filter(c => c.total_siswa_aktif > 0);
                    if (withStudents.length > 0) {
                        this.selectedKelasIds = withStudents.map(c => c.id);
                    } else {
                        this.selectedKelasIds = this.originClasses.map(c => c.id);
                    }
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.isLoading = false;
            }
        },

        async loadDestClasses() {
            this.isLoading = true;
            try {
                const res = await fetch(`{{ route('academic.promotion.destination_classes') }}?dest_ta_id=${this.destTaId}`);
                const data = await res.json();
                if (data.success) {
                    this.destClasses = data.classes;
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.isLoading = false;
            }
        },

        async replicateStructure() {
            const confirmResult = await Swal.fire({
                title: 'Salin Struktur Rombel?',
                text: 'Struktur rombel kelas dari Tahun Ajaran Asal akan disalin secara otomatis ke Tahun Ajaran Tujuan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#003EA7',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Salin Struktur',
                cancelButtonText: 'Batal'
            });

            if (!confirmResult.isConfirmed) return;

            this.isLoading = true;
            try {
                const res = await fetch(`{{ route('academic.promotion.replicate_classes') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        origin_ta_id: this.originTaId,
                        dest_ta_id: this.destTaId
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.destClasses = data.classes;
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        confirmButtonColor: '#003EA7'
                    });
                } else {
                    let errMsg = data.message || 'Gagal menyalin struktur rombel.';
                    if (data.errors) {
                        const errList = Object.values(data.errors).flat();
                        if (errList.length > 0) errMsg = errList[0];
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyalin Rombel',
                        text: errMsg,
                        confirmButtonColor: '#003EA7'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Terjadi kesalahan sistem atau jaringan saat menyalin rombel.',
                    confirmButtonColor: '#003EA7'
                });
            } finally {
                this.isLoading = false;
            }
        },

        advanceToStep2() {
            if (!this.originTaId || !this.destTaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Periode Belum Dipilih',
                    text: 'Silakan tentukan Tahun Ajaran Asal dan Tahun Ajaran Tujuan.',
                    confirmButtonColor: '#003EA7'
                });
                return;
            }

            if (this.originTaId === this.destTaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Periode Tidak Boleh Sama',
                    text: 'Tahun Ajaran Tujuan tidak boleh sama dengan Tahun Ajaran Asal.',
                    confirmButtonColor: '#003EA7'
                });
                return;
            }

            const origTa = this.academicYearsData[this.originTaId];
            const destTa = this.academicYearsData[this.destTaId];
            if (origTa && destTa && destTa.tahun_mulai <= origTa.tahun_mulai) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Urutan Periode Tidak Valid',
                    text: `Tahun ajaran tujuan (${destTa.nama}) harus secara kronologis lebih baru daripada tahun ajaran asal (${origTa.nama}).`,
                    confirmButtonColor: '#003EA7'
                });
                return;
            }

            if (this.originClasses.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Ada Rombel',
                    text: `Tahun Ajaran Asal (${origTa?.nama || ''}) belum memiliki struktur rombel kelas. Silakan pilih tahun ajaran yang memiliki data rombel.`,
                    confirmButtonColor: '#003EA7'
                });
                return;
            }

            if (this.destClasses.length === 0) {
                this.loadDestClasses();
            }
            this.step = 2;
        },

        toggleSelectAllClasses() {
            const targets = this.filteredOriginClasses.length > 0 ? this.filteredOriginClasses : this.originClasses;
            const targetIds = targets.map(c => Number(c.id));
            const allSelected = targetIds.length > 0 && targetIds.every(id => this.isClassSelected(id));

            if (allSelected) {
                const targetSet = new Set(targetIds);
                this.selectedKelasIds = this.selectedKelasIds.filter(id => !targetSet.has(Number(id)));
            } else {
                const newSet = new Set(this.selectedKelasIds.map(Number));
                targetIds.forEach(id => newSet.add(id));
                this.selectedKelasIds = Array.from(newSet);
            }
        },

        isClassSelected(id) {
            const numId = Number(id);
            return this.selectedKelasIds.some(x => Number(x) === numId);
        },

        toggleClass(id) {
            const numId = Number(id);
            const idx = this.selectedKelasIds.findIndex(x => Number(x) === numId);
            if (idx > -1) {
                this.selectedKelasIds.splice(idx, 1);
            } else {
                this.selectedKelasIds.push(numId);
            }
        },

        isJurusanFullySelected(classes) {
            if (!classes || classes.length === 0) return false;
            return classes.every(c => this.isClassSelected(c.id));
        },

        toggleJurusanClasses(classes) {
            if (!classes || classes.length === 0) return;
            if (this.isJurusanFullySelected(classes)) {
                const idsToRemove = new Set(classes.map(c => Number(c.id)));
                this.selectedKelasIds = this.selectedKelasIds.filter(id => !idsToRemove.has(Number(id)));
            } else {
                const currentSet = new Set(this.selectedKelasIds.map(Number));
                classes.forEach(c => currentSet.add(Number(c.id)));
                this.selectedKelasIds = Array.from(currentSet);
            }
        },

        isLevelFullySelected(classes) {
            if (!classes || classes.length === 0) return false;
            return classes.every(c => this.isClassSelected(c.id));
        },

        toggleLevelClasses(classes) {
            if (!classes || classes.length === 0) return;
            if (this.isLevelFullySelected(classes)) {
                const idsToRemove = new Set(classes.map(c => Number(c.id)));
                this.selectedKelasIds = this.selectedKelasIds.filter(id => !idsToRemove.has(Number(id)));
            } else {
                const currentSet = new Set(this.selectedKelasIds.map(Number));
                classes.forEach(c => currentSet.add(Number(c.id)));
                this.selectedKelasIds = Array.from(currentSet);
            }
        },

        getClassesActiveStudentCount(classes) {
            if (!classes) return 0;
            return classes.reduce((sum, c) => sum + (c.total_siswa_aktif || 0), 0);
        },

        getSelectedCountInGroup(classes) {
            if (!classes) return 0;
            return classes.filter(c => this.isClassSelected(c.id)).length;
        },

        get selectedActiveStudentCount() {
            return this.originClasses
                .filter(c => this.isClassSelected(c.id))
                .reduce((sum, c) => sum + (c.total_siswa_aktif || 0), 0);
        },

        get groupedOriginClassesByJurusan() {
            const list = this.filteredOriginClasses;
            const map = new Map();

            list.forEach(c => {
                const jId = c.jurusan_id || 0;
                if (!map.has(jId)) {
                    map.set(jId, {
                        jurusan_id: jId,
                        jurusan_kode: c.jurusan_kode || 'LAIN',
                        jurusan_nama: c.jurusan_nama || 'Lainnya',
                        classes: [],
                        levelsMap: {}
                    });
                }
                const grp = map.get(jId);
                grp.classes.push(c);
                const t = Number(c.tingkat) || 10;
                if (!grp.levelsMap[t]) {
                    grp.levelsMap[t] = [];
                }
                grp.levelsMap[t].push(c);
            });

            return Array.from(map.values()).map(grp => {
                const sortedLevels = Object.keys(grp.levelsMap)
                    .map(Number)
                    .sort((a, b) => a - b)
                    .map(lvl => ({
                        tingkat: lvl,
                        classes: grp.levelsMap[lvl].sort((a, b) => a.nama_kelas.localeCompare(b.nama_kelas, undefined, { numeric: true }))
                    }));
                return {
                    ...grp,
                    levels: sortedLevels
                };
            });
        },

        async advanceToStep3() {
            if (this.selectedKelasIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Rombel Belum Dipilih',
                    text: 'Pilih minimal satu rombongan belajar asal untuk diproses.',
                    confirmButtonColor: '#003EA7'
                });
                return;
            }

            this.isLoading = true;
            try {
                const res = await fetch(`{{ route('academic.promotion.load_students') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        origin_ta_id: this.originTaId,
                        kelas_ids: this.selectedKelasIds
                    })
                });
                const data = await res.json();
                if (data.success) {
                    if (!data.students || data.students.length === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Tidak Ada Siswa Aktif',
                            text: 'Tidak ada siswa dengan status aktif yang ditemukan pada rombel yang dipilih.',
                            confirmButtonColor: '#003EA7'
                        });
                        return;
                    }

                    this.students = data.students.map(s => ({
                        ...s,
                        action: s.default_action,
                        kelas_tujuan_id: null,
                        catatan: ''
                    }));
                    this.step = 3;
                } else {
                    let errMsg = data.message || 'Gagal memuat siswa.';
                    if (data.errors) {
                        const errList = Object.values(data.errors).flat();
                        if (errList.length > 0) errMsg = errList[0];
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat Data',
                        text: errMsg,
                        confirmButtonColor: '#003EA7'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Gagal memuat data siswa dari server.',
                    confirmButtonColor: '#003EA7'
                });
            } finally {
                this.isLoading = false;
            }
        },

        get totalActiveStudentsInOrigin() {
            return this.originClasses.reduce((sum, c) => sum + (c.total_siswa_aktif || 0), 0);
        },

        get originClassesWithStudents() {
            return this.originClasses.filter(c => c.total_siswa_aktif > 0);
        },

        get filteredOriginClasses() {
            if (!this.filterTingkat) return this.originClasses;
            if (this.filterTingkat === 'with_students') {
                return this.originClasses.filter(c => c.total_siswa_aktif > 0);
            }
            return this.originClasses.filter(c => c.tingkat == this.filterTingkat);
        },

        get selectedClassesInfo() {
            return this.originClasses.filter(c => this.isClassSelected(c.id));
        },

        get filteredStudents() {
            return this.students.filter(s => {
                const matchSearch = !this.searchStudent ||
                    s.nama.toLowerCase().includes(this.searchStudent.toLowerCase()) ||
                    s.nis.includes(this.searchStudent);
                const matchKelas = !this.filterKelasAsal || s.kelas_asal_id == this.filterKelasAsal;
                return matchSearch && matchKelas;
            });
        },

        get activePromotingStudents() {
            return this.students.filter(s => s.action === 'naik_kelas' || s.action === 'tinggal_kelas');
        },

        get allocatedDestClasses() {
            const usedIds = new Set(this.activePromotingStudents.map(s => s.kelas_tujuan_id).filter(Boolean));
            return this.destClasses.filter(dc => usedIds.has(dc.id));
        },

        countAction(act) {
            return this.students.filter(s => s.action === act).length;
        },

        onStudentActionChange(s) {
            if (s.action !== 'naik_kelas' && s.action !== 'tinggal_kelas') {
                s.kelas_tujuan_id = null;
            }
        },

        bulkSetAction(act, filterTingkat = null) {
            this.students.forEach(s => {
                if (filterTingkat && s.tingkat_asal !== filterTingkat) return;

                if (act === 'naik_kelas') {
                    if (s.tingkat_asal >= 12) {
                        s.action = 'lulus';
                    } else {
                        s.action = 'naik_kelas';
                    }
                } else {
                    s.action = act;
                }
            });
        },

        resetToDefaults() {
            this.students.forEach(s => {
                s.action = s.default_action;
                s.kelas_tujuan_id = null;
            });
        },

        autoMapDestinationClasses(force = false) {
            this.students.forEach(s => {
                if (!force && s.kelas_tujuan_id) return;

                if (s.action === 'naik_kelas') {
                    const targetTingkat = s.tingkat_asal + 1;
                    const matched = this.destClasses.find(dc =>
                        dc.tingkat === targetTingkat &&
                        (dc.jurusan_id === s.jurusan_id || dc.jurusan_kode === s.jurusan_kode) &&
                        (dc.nama_kelas.replace(/\b(X|XI|XII|10|11|12)\b/i, '').trim() === s.kelas_asal_nama.replace(/\b(X|XI|XII|10|11|12)\b/i, '').trim())
                    ) || this.destClasses.find(dc => dc.tingkat === targetTingkat && dc.jurusan_id === s.jurusan_id);

                    if (matched) {
                        s.kelas_tujuan_id = matched.id;
                    }
                } else if (s.action === 'tinggal_kelas') {
                    const targetTingkat = s.tingkat_asal;
                    const matched = this.destClasses.find(dc =>
                        dc.tingkat === targetTingkat &&
                        dc.nama_kelas === s.kelas_asal_nama
                    ) || this.destClasses.find(dc => dc.tingkat === targetTingkat && dc.jurusan_id === s.jurusan_id);

                    if (matched) {
                        s.kelas_tujuan_id = matched.id;
                    }
                }
            });
        },

        getAllocatedCountForClass(clsId) {
            return this.activePromotingStudents.filter(s => s.kelas_tujuan_id == clsId).length;
        },

        advanceToStep4() {
            this.autoMapDestinationClasses(false);
            this.step = 4;
        },

        advanceToStep5() {
            // Check if all active promoting students have destination class
            const unallocated = this.activePromotingStudents.filter(s => !s.kelas_tujuan_id);
            if (unallocated.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Alokasi Belum Lengkap',
                    text: `Terdapat ${unallocated.length} siswa berstatus naik/tinggal kelas yang belum dialokasikan ke rombel tujuan.`,
                    confirmButtonColor: '#003EA7'
                });
                return;
            }
            this.step = 5;
        },

        advanceToStep6() {
            // Check for duplicate homeroom assignments
            const assignedGurus = Object.values(this.homeroomAssignments).filter(Boolean);
            const duplicates = assignedGurus.filter((item, index) => assignedGurus.indexOf(item) !== index);
            if (duplicates.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Wali Kelas Ganda',
                    text: 'Satu guru tidak boleh ditugaskan sebagai wali kelas pada lebih dari satu rombel tujuan.',
                    confirmButtonColor: '#003EA7'
                });
                return;
            }
            this.step = 6;
        },

        async submitPromotionBatch() {
            if (!this.isConfirmed) return;
            this.isSubmitting = true;

            const payload = {
                tahun_ajaran_asal_id: this.originTaId,
                tahun_ajaran_tujuan_id: this.destTaId,
                catatan: this.batchCatatan,
                students: this.students.map(s => ({
                    siswa_id: s.siswa_id,
                    action: s.action,
                    kelas_tujuan_id: s.kelas_tujuan_id,
                    catatan: s.catatan
                })),
                homeroom_assignments: Object.entries(this.homeroomAssignments)
                    .filter(([k, g]) => g)
                    .map(([k, g]) => ({ kelas_id: parseInt(k), guru_id: parseInt(g) }))
            };

            try {
                const res = await fetch(`{{ route('academic.promotion.execute') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Dieksekusi!',
                        text: data.message,
                        confirmButtonColor: '#003EA7'
                    });
                    window.location.href = data.redirect_url;
                } else {
                    let errMsg = data.message || 'Terjadi kegagalan saat mengeksekusi proses kenaikan kelas.';
                    if (data.errors) {
                        const errList = Object.values(data.errors).flat();
                        if (errList.length > 0) errMsg = errList[0];
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Eksekusi Gagal',
                        text: errMsg,
                        confirmButtonColor: '#003EA7'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Terjadi kesalahan sistem atau koneksi saat mengeksekusi data kenaikan kelas.',
                    confirmButtonColor: '#003EA7'
                });
            } finally {
                this.isSubmitting = false;
            }
        }
    };
}
</script>
@endsection
