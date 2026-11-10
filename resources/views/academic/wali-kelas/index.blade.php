@extends('layouts.app')

@section('title', 'Manajemen Wali Kelas — MySkanic')
@section('page-title', 'Manajemen Wali Kelas')
@section('page-subtitle', 'Sistem penugasan & histori wali kelas per tahun ajaran.')

@section('content')
<div class="space-y-6" x-data="homeroomManager()">
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#003EA7] via-[#002D7A] to-[#001D4D] p-6 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-blue-200 text-xs font-semibold mb-2">
                    <i class='bx bx-user-pin'></i>
                    <span>Sistem Penugasan & Histori Wali Kelas</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-normal tracking-tight">Manajemen Wali Kelas per Tahun Ajaran</h1>
                <p class="text-blue-100/80 text-sm mt-1 max-w-2xl">
                    Kelola penugasan dan pergantian wali kelas secara atomik. Pergantian guru otomatis mencabut status aktif wali kelas lama tanpa menghapus riwayat historisnya.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('academic.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-sm backdrop-blur transition-all">
                    <i class='bx bx-sitemap text-lg'></i>
                    <span>Overview Hierarki</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-chalkboard'></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Total Rombel (T.A. {{ $selectedTa?->nama }})</span>
                <span class="text-2xl font-black text-slate-800">{{ $totalRombel }} <span class="text-xs font-normal text-slate-500">Rombel</span></span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-check-shield'></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Sudah Ada Wali Kelas</span>
                <span class="text-2xl font-black text-emerald-600">{{ $rombelAssigned }} <span class="text-xs font-normal text-slate-500">Rombel</span></span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold">
                <i class='bx bx-error-circle'></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Belum Ditugaskan</span>
                <span class="text-2xl font-black text-amber-600">{{ $rombelUnassigned }} <span class="text-xs font-normal text-slate-500">Rombel</span></span>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
        <form method="GET" action="{{ route('academic.wali-kelas.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Filter Tahun Ajaran --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran</label>
                <select name="tahun_ajaran_id" onchange="this.form.submit()" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#003EA7] focus:ring focus:ring-blue-100">
                    @foreach($tahunAjarans as $ta)
                        <option value="{{ $ta->id }}" {{ $filters['tahun_ajaran_id'] == $ta->id ? 'selected' : '' }}>
                            {{ $ta->nama }} {{ $ta->is_active ? '(Aktif Berjalan)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Jurusan --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan</label>
                <select name="jurusan_id" onchange="this.form.submit()" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#003EA7] focus:ring focus:ring-blue-100">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ $filters['jurusan_id'] == $j->id ? 'selected' : '' }}>{{ $j->kode_jurusan }} — {{ $j->nama_jurusan }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Tingkat --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Kelas</label>
                <select name="tingkat" onchange="this.form.submit()" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#003EA7] focus:ring focus:ring-blue-100">
                    <option value="">Semua Tingkat (X, XI, XII)</option>
                    <option value="10" {{ $filters['tingkat'] == '10' ? 'selected' : '' }}>Kelas 10 (X)</option>
                    <option value="11" {{ $filters['tingkat'] == '11' ? 'selected' : '' }}>Kelas 11 (XI)</option>
                    <option value="12" {{ $filters['tingkat'] == '12' ? 'selected' : '' }}>Kelas 12 (XII)</option>
                </select>
            </div>

            {{-- Filter Status Assignment --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Status Penugasan</label>
                <select name="status_filter" onchange="this.form.submit()" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#003EA7] focus:ring focus:ring-blue-100">
                    <option value="">Semua Status</option>
                    <option value="assigned" {{ $filters['status_filter'] == 'assigned' ? 'selected' : '' }}>Sudah Ada Wali Kelas</option>
                    <option value="unassigned" {{ $filters['status_filter'] == 'unassigned' ? 'selected' : '' }}>Belum Ada Wali Kelas</option>
                </select>
            </div>

            {{-- Search Input --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Cari Rombel / Guru</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Ketik nama rombel/guru..."
                        class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border-slate-200 focus:border-[#003EA7] focus:ring focus:ring-blue-100">
                    <i class='bx bx-search absolute left-2.5 top-2.5 text-slate-400 text-sm'></i>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Table Matrix --}}
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden p-6">
        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class='bx bx-list-ul text-lg text-[#003FA9]'></i>
                <h3 class="font-bold text-slate-900 text-base tracking-tight">Daftar Rombongan Belajar & Wali Kelas (T.A. {{ $selectedTa?->nama }})</h3>
            </div>
            <span class="text-xs text-slate-400">Total: {{ $kelasList->count() }} rombel ditampilkan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ROMBEL & TINGKAT</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">PROGRAM KEAHLIAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">WALI KELAS AKTIF</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">MULAI BERTUGAS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">STATUS INDIKATOR</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right w-36">AKSI MANAJEMEN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($kelasList as $idx => $k)
                        @php
                            $current = $k->current_homeroom_assignment;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4 text-slate-400 text-xs font-medium text-center">{{ $idx + 1 }}</td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-900 text-xs sm:text-sm block">{{ $k->nama_kelas }}</span>
                                <span class="text-slate-400 text-[11px]">Tingkat {{ $k->tingkat }} • Rombel {{ $k->nomor_rombel }}</span>
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-900 font-mono text-xs sm:text-sm">
                                {{ $k->jurusan?->kode_jurusan ?? '-' }}
                            </td>
                            <td class="py-4 px-4">
                                @if($current && $current->guru)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-[#EFF5FF] text-[#003FA9] flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ substr($current->guru->nama, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 text-xs sm:text-sm block">{{ $current->guru->nama }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono">NIP: {{ $current->guru->nip ?: '-' }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">Belum ada wali kelas aktif</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 font-mono text-xs text-slate-600">
                                {{ $current && $current->starts_on ? $current->starts_on->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($k->has_homeroom)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white">
                                        SUDAH DITUGASKAN
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white">
                                        BELUM DITETAPKAN
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    {{-- Riwayat Wali Kelas Rombel --}}
                                    <button type="button" @click="openClassHistory({{ $k->id }})" title="Lihat Riwayat Wali Kelas Rombel Ini"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition">
                                        <i class='bx bx-history text-lg'></i>
                                    </button>

                                    @if($k->has_homeroom)
                                        {{-- Ganti Wali Kelas --}}
                                        <button type="button" @click="openReplaceModal({{ $k->id }}, '{{ addslashes($k->nama_kelas) }}', '{{ addslashes($current->guru->nama) }}', {{ $current->guru_id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition" title="Ganti Wali Kelas">
                                            <i class='bx bx-transfer-alt text-lg'></i>
                                        </button>

                                        {{-- Nonaktifkan / Berhentikan --}}
                                        <button type="button" @click="openTerminateModal({{ $current->id }}, '{{ addslashes($k->nama_kelas) }}', '{{ addslashes($current->guru->nama) }}')"
                                            class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Akhiri Penugasan">
                                            <i class='bx bx-x-circle text-lg'></i>
                                        </button>
                                    @else
                                        {{-- Tetapkan Wali Kelas Baru --}}
                                        <button type="button" @click="openAssignModal({{ $k->id }}, '{{ addslashes($k->nama_kelas) }}')"
                                            class="px-3 py-1.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs inline-flex items-center gap-1 shadow-sm transition">
                                            <i class='bx bx-user-plus text-sm'></i>
                                            <span>Tetapkan</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <i class='bx bx-group text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Tidak ada rombel yang sesuai</p>
                                <p class="text-xs text-slate-400 mt-0.5">Silakan sesuaikan filter pencarian atau tingkat kelas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL 1: Penugasan Baru --}}
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="showAssignModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#003EA7] flex items-center justify-center text-lg">
                        <i class='bx bx-user-plus'></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm">Penugasan Wali Kelas</h3>
                </div>
                <button type="button" @click="showAssignModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <form method="POST" action="{{ route('academic.wali-kelas.assign') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="kelas_id" :value="assignData.kelas_id">
                <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa?->id }}">

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Rombongan Belajar</label>
                    <input type="text" :value="assignData.nama_kelas" readonly class="w-full text-xs rounded-xl bg-slate-50 border-slate-200 font-bold text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Tahun Ajaran</label>
                    <input type="text" value="{{ $selectedTa?->nama }}" readonly class="w-full text-xs rounded-xl bg-slate-50 border-slate-200 font-bold text-blue-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Guru Wali Kelas <span class="text-rose-500">*</span></label>
                    <select name="guru_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-[#003EA7] focus:ring focus:ring-blue-100">
                        <option value="">-- Pilih Guru Aktif --</option>
                        @foreach($activeGurus as $g)
                            @php
                                $isAlreadyAssigned = in_array($g->id, $assignedTeacherIds);
                                $assignedKelas = $g->homeroomAssignments->first()?->kelas?->nama_kelas;
                            @endphp
                            <option value="{{ $g->id }}" {{ $isAlreadyAssigned ? 'disabled' : '' }}>
                                {{ $g->nama }} {{ $isAlreadyAssigned ? "(Sudah di {$assignedKelas})" : '' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-slate-400 mt-1 block">Guru yang sudah membina rombel lain di tahun ajaran ini ditandai nonaktif sesuai aturan max 1 rombel per guru.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Catatan Penugasan (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan SK penugasan..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showAssignModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#003EA7] hover:bg-[#002D7A] text-white text-xs font-bold shadow">Simpan Penugasan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: Pergantian Wali Kelas (Dengan Konfirmasi Lengkap) --}}
    <div x-show="showReplaceModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="showReplaceModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                        <i class='bx bx-transfer-alt'></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm">Pergantian Wali Kelas</h3>
                </div>
                <button type="button" @click="showReplaceModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            {{-- Warning Alert --}}
            <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-amber-800">
                    <i class='bx bx-shield-quarter text-base'></i>
                    <span>Konfirmasi Pencabutan & Perlindungan Histori:</span>
                </div>
                <p>Penugasan guru lama akan <strong>diakhiri secara otomatis</strong> per tanggal pergantian. Catatan bahwa beliau pernah membina rombel ini <strong>TIDAK AKAN DIHAPUS</strong> dan tetap dapat dilihat di histori.</p>
            </div>

            <form method="POST" action="{{ route('academic.wali-kelas.replace') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="kelas_id" :value="replaceData.kelas_id">
                <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa?->id }}">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Rombongan Belajar</label>
                        <input type="text" :value="replaceData.nama_kelas" readonly class="w-full text-xs rounded-xl bg-slate-50 border-slate-200 font-bold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Tahun Ajaran</label>
                        <input type="text" value="{{ $selectedTa?->nama }}" readonly class="w-full text-xs rounded-xl bg-slate-50 border-slate-200 font-bold text-blue-700">
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-[11px] text-slate-400 uppercase font-bold block">Wali Kelas Sebelumnya (Akan Dinonaktifkan):</span>
                    <span class="text-sm font-bold text-rose-600 block mt-0.5" x-text="replaceData.current_teacher_name"></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Guru Pengganti Baru <span class="text-rose-500">*</span></label>
                    <select name="new_guru_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-[#003EA7] focus:ring focus:ring-blue-100">
                        <option value="">-- Pilih Guru Pengganti --</option>
                        @foreach($activeGurus as $g)
                            @php
                                $isAlreadyAssigned = in_array($g->id, $assignedTeacherIds);
                                $assignedKelas = $g->homeroomAssignments->first()?->kelas?->nama_kelas;
                            @endphp
                            <option value="{{ $g->id }}" {{ $isAlreadyAssigned ? 'disabled' : '' }}>
                                {{ $g->nama }} {{ $isAlreadyAssigned ? "(Sudah di {$assignedKelas})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Efektif Pergantian</label>
                    <input type="date" name="effective_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#003EA7]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pergantian <span class="text-rose-500">*</span></label>
                    <textarea name="alasan_pergantian" required rows="2" placeholder="Contoh: Guru sebelumnya cuti melahirkan / rotasi tugas..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showReplaceModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow">Konfirmasi Pergantian</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: Akhiri Penugasan (Terminate) --}}
    <div x-show="showTerminateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="showTerminateModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                        <i class='bx bx-x-circle'></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm">Akhiri Penugasan Wali Kelas</h3>
                </div>
                <button type="button" @click="showTerminateModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url('/akademik/wali-kelas/terminate') }}/' + terminateData.assignment_id" class="space-y-4">
                @csrf
                <p class="text-xs text-slate-600 leading-relaxed">
                    Anda akan mengakhiri penugasan <strong class="text-slate-800" x-text="terminateData.teacher_name"></strong> sebagai wali kelas untuk rombel <strong class="text-slate-800" x-text="terminateData.nama_kelas"></strong>. Riwayat penugasan tetap tersimpan permanen.
                </p>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penonaktifan <span class="text-rose-500">*</span></label>
                    <textarea name="alasan" required rows="2" placeholder="Contoh: Resign, pensiun, mutasi, penugasan struktural..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showTerminateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow">Akhiri Penugasan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 4: Riwayat Historis Modal (AJAX) --}}
    <div x-show="showHistoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4" @click.away="showHistoryModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#003EA7] flex items-center justify-center text-lg">
                        <i class='bx bx-history'></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm" x-text="historyTitle"></h3>
                </div>
                <button type="button" @click="showHistoryModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <div class="max-h-96 overflow-y-auto space-y-3 pr-1">
                <template x-if="historyLoading">
                    <div class="py-8 text-center text-slate-400">
                        <i class='bx bx-loader-alt bx-spin text-2xl mb-1 block text-[#003EA7]'></i>
                        <span>Memuat riwayat historis...</span>
                    </div>
                </template>

                <template x-if="!historyLoading && historyItems.length === 0">
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada riwayat penugasan untuk entitas ini.
                    </div>
                </template>

                <template x-for="item in historyItems" :key="item.id">
                    <div class="p-3.5 rounded-xl border transition-all"
                        :class="item.is_active ? 'bg-emerald-50/50 border-emerald-200' : 'bg-slate-50 border-slate-200'">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-800" x-text="item.guru_nama || item.nama_kelas"></span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                :class="item.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'"
                                x-text="item.is_active ? 'AKTIF' : 'SELESAI / NONAKTIF'"></span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                            <span>T.A: <strong class="text-slate-700" x-text="item.tahun_ajaran"></strong></span>
                            <span>Periode: <span class="font-mono" x-text="item.starts_on + ' s.d ' + item.ends_on"></span></span>
                            <span x-show="item.replaced_by_nama" class="text-blue-700">Digantikan oleh: <strong x-text="item.replaced_by_nama"></strong></span>
                        </div>
                        <div x-show="item.alasan_pergantian" class="text-xs text-amber-700 bg-amber-50 p-2 rounded-lg mt-2 border border-amber-200">
                            <strong>Alasan:</strong> <span x-text="item.alasan_pergantian"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" @click="showHistoryModal = false" class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function homeroomManager() {
    return {
        showAssignModal: false,
        showReplaceModal: false,
        showTerminateModal: false,
        showHistoryModal: false,

        assignData: { kelas_id: null, nama_kelas: '' },
        replaceData: { kelas_id: null, nama_kelas: '', current_teacher_name: '', current_guru_id: null },
        terminateData: { assignment_id: null, nama_kelas: '', teacher_name: '' },

        historyTitle: '',
        historyLoading: false,
        historyItems: [],

        openAssignModal(kelasId, namaKelas) {
            this.assignData = { kelas_id: kelasId, nama_kelas: namaKelas };
            this.showAssignModal = true;
        },

        openReplaceModal(kelasId, namaKelas, teacherName, guruId) {
            this.replaceData = {
                kelas_id: kelasId,
                nama_kelas: namaKelas,
                current_teacher_name: teacherName,
                current_guru_id: guruId
            };
            this.showReplaceModal = true;
        },

        openTerminateModal(assignmentId, namaKelas, teacherName) {
            this.terminateData = {
                assignment_id: assignmentId,
                nama_kelas: namaKelas,
                teacher_name: teacherName
            };
            this.showTerminateModal = true;
        },

        openClassHistory(kelasId) {
            this.historyTitle = 'Memuat riwayat...';
            this.historyLoading = true;
            this.historyItems = [];
            this.showHistoryModal = true;

            fetch(`{{ route('academic.wali-kelas.history') }}?kelas_id=${kelasId}`)
                .then(res => res.json())
                .then(data => {
                    this.historyLoading = false;
                    if (data.success) {
                        this.historyTitle = data.title;
                        this.historyItems = data.data;
                    }
                })
                .catch(err => {
                    this.historyLoading = false;
                    this.historyTitle = 'Gagal memuat riwayat.';
                });
        }
    };
}
</script>
@endsection
