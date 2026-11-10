@extends('layouts.app')

@section('title', 'Detail Guru: ' . $guru->nama)
@section('page-title', 'Data Guru')
@section('page-subtitle', 'Kelola data guru')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('guru') }}" class="hover:text-[#003FA9] transition">Data Guru</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">{{ $guru->nama }}</li>
@endsection

@section('content')
<div x-data="{ activeTab: '{{ request('tab', 'penugasan') }}' }" class="space-y-6">

    {{-- Flash Notifications --}}
    @if(session('activation_url'))
        <div class="rounded-2xl bg-blue-50 p-4 border border-blue-200 text-sm text-[#003FA9] shadow-sm">
            <div class="flex items-center justify-between font-bold">
                <span class="flex items-center gap-2">
                    <i class='bx bx-link-external text-base'></i> Tautan Aktivasi Akun Guru Siap Dibagikan
                </span>
                <button type="button" onclick="this.closest('.rounded-2xl').remove()" class="text-blue-400 hover:text-[#003FA9]">
                    <i class='bx bx-x text-lg'></i>
                </button>
            </div>
            <p class="mt-1 text-xs text-blue-700">Salin tautan ini dan kirimkan kepada <strong>{{ $guru->nama }}</strong> untuk membuat password akunnya sendiri secara mandiri:</p>
            <div class="mt-2 flex items-center gap-2">
                <input class="w-full rounded-xl border border-blue-200 bg-white px-3 py-2 font-mono text-xs text-slate-800 select-all" readonly value="{{ session('activation_url') }}" id="activation_url_copy" onclick="this.select()">
                <button type="button" onclick="copyActivationUrl()"
                    class="h-9 px-4 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold whitespace-nowrap inline-flex items-center gap-1.5 transition">
                    <i class='bx bx-copy'></i>
                    <span>Salin</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Teacher Information Header Card (Matching Detail Siswa Reference 2) --}}
    <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            {{-- Left: Avatar & Dominant Teacher Name --}}
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#003FA9] text-white flex items-center justify-center font-extrabold text-2xl flex-shrink-0 shadow-md shadow-blue-900/10 overflow-hidden">
                    @if ($guru->photo_path && file_exists(public_path('storage/' . $guru->photo_path)))
                        <img src="{{ asset('storage/' . $guru->photo_path) }}" alt="{{ $guru->nama }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($guru->nama, 0, 2)) }}
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $guru->nama }}</h1>
                    {{-- Clean Typography Hierarchy --}}
                    <div class="flex items-center flex-wrap gap-x-3 gap-y-1 mt-1 text-xs text-slate-500 font-medium">
                        <div>NIP: <span class="font-bold text-slate-800 font-mono">{{ $guru->nip ?: '-' }}</span></div>
                        <span>•</span>
                        <div>NUPTK: <span class="font-bold text-slate-800 font-mono">{{ $guru->nuptk ?: '-' }}</span></div>
                        <span>•</span>
                        <div>Email: <span class="font-semibold text-[#003FA9]">{{ $guru->user?->email ?: '-' }}</span></div>
                        @if($guru->telepon)
                            <span>•</span>
                            <div>Telp: <span class="font-semibold text-slate-700 font-mono">{{ $guru->telepon }}</span></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Clean Actions (Kembali & Edit) --}}
            <div class="flex items-center gap-3 self-end md:self-auto">
                <a href="{{ route('guru') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                    <i class='bx bx-arrow-back text-base'></i>
                    <span>Kembali</span>
                </a>
                <button type="button" onclick="openModal('modal-editguru-inline')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold shadow-md shadow-blue-900/15 transition">
                    <i class='bx bx-edit text-base'></i>
                    <span>Edit Guru</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Subpage Navigation Tabs (Solid Buttons Matching Detail Siswa) --}}
    <div class="flex items-center gap-2.5 overflow-x-auto pb-1">
        {{-- Tab 1: Penugasan Aktif --}}
        <button type="button"
                @click="activeTab = 'penugasan'"
                :class="activeTab === 'penugasan'
                    ? 'bg-[#003FA9] text-white shadow-sm'
                    : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-id-card text-lg'></i>
            <span>Penugasan Akademik</span>
        </button>

        {{-- Tab 2: Riwayat Penugasan (Timeline) --}}
        <button type="button"
                @click="activeTab = 'timeline'"
                :class="activeTab === 'timeline'
                    ? 'bg-[#003FA9] text-white shadow-sm'
                    : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-git-commit text-lg'></i>
            <span>Riwayat Penugasan (Time Line)</span>
        </button>

        {{-- Tab 3: Beban Kerja & Jadwal --}}
        <button type="button"
                @click="activeTab = 'jadwal'"
                :class="activeTab === 'jadwal'
                    ? 'bg-[#003FA9] text-white shadow-sm'
                    : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-calendar-event text-lg'></i>
            <span>Beban Kerja & Jadwal</span>
        </button>

        {{-- Tab 4: Profil & Biodata --}}
        <button type="button"
                @click="activeTab = 'profil'"
                :class="activeTab === 'profil'
                    ? 'bg-[#003FA9] text-white shadow-sm'
                    : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-user text-lg'></i>
            <span>Informasi & Biodata</span>
        </button>

        {{-- Tab 5: Akun & Keamanan --}}
        <button type="button"
                @click="activeTab = 'akun'"
                :class="activeTab === 'akun'
                    ? 'bg-[#003FA9] text-white shadow-sm'
                    : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap">
            <i class='bx bx-shield-quarter text-lg'></i>
            <span>Akun & Keamanan</span>
        </button>
    </div>

    {{-- ==================== TAB 1: PENUGASAN AKADEMIK ==================== --}}
    <div x-show="activeTab === 'penugasan'" class="space-y-6">

        {{-- Section 1: Ringkasan Penugasan Aktif (Wali Kelas & KAPROG) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Card Wali Kelas --}}
            <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100 space-y-4">
                <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003FA9] flex items-center justify-center text-xl font-bold">
                            <i class='bx bx-id-card'></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">Tugas Wali Kelas</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Penugasan rombongan belajar binaan</p>
                        </div>
                    </div>
                    @if($guru->activeHomeroomAssignment)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#003FA9] text-white">
                            Aktif
                        </span>
                    @endif
                </div>

                @if($guru->activeHomeroomAssignment)
                    <div class="p-5 rounded-2xl bg-[#EFF5FF] space-y-3">
                        <div>
                            <span class="text-slate-400 text-xs block font-medium">Rombongan Belajar Binaan</span>
                            <span class="text-xl font-extrabold text-[#003FA9]">
                                {{ $guru->activeHomeroomAssignment->kelas->nama_kelas }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-600 space-y-1">
                            <div>Jurusan: <strong class="text-slate-900">{{ $guru->activeHomeroomAssignment->kelas->jurusan->nama_jurusan ?? '-' }}</strong></div>
                            <div>Tahun Ajaran: <strong class="text-slate-900 font-mono">{{ $guru->activeHomeroomAssignment->tahun_ajaran }}</strong></div>
                            @if($guru->activeHomeroomAssignment->catatan)
                                <div class="text-slate-500 italic mt-1">{{ $guru->activeHomeroomAssignment->catatan }}</div>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('guru.remove_homeroom_assignment', $guru->activeHomeroomAssignment->id) }}" method="POST"
                          onsubmit="return confirm('Lepaskan penugasan wali kelas dari rombel ini? Riwayat tetap tersimpan.')">
                        @csrf
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-700 font-semibold text-xs transition">
                            Lepaskan Tugas Wali Kelas
                        </button>
                    </form>
                @else
                    <div class="p-6 rounded-2xl bg-slate-50 text-center text-slate-400 text-xs">
                        Saat ini tidak memegang rombel wali kelas.
                    </div>
                    {{-- Form assign homeroom --}}
                    <form action="{{ route('guru.assign_homeroom', $guru->id) }}" method="POST" class="space-y-3 pt-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tugaskan Rombel Baru</label>
                            <div class="relative">
                                <select name="kelas_id" required
                                    class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                                    <option value="">-- Pilih Rombel --</option>
                                    @foreach($kelasList as $k)
                                        <option value="{{ $k->id }}">
                                            {{ $k->nama_kelas }} ({{ $k->jurusan->singkatan ?? $k->jurusan->nama_jurusan }})
                                        </option>
                                    @endforeach
                                </select>
                                <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                            </div>
                        </div>
                        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs transition shadow-sm">
                            Tugaskan Sebagai Wali Kelas
                        </button>
                    </form>
                @endif
            </div>

            {{-- Card KAPROG --}}
            <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100 space-y-4">
                <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003FA9] flex items-center justify-center text-xl font-bold">
                            <i class='bx bx-award'></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">Kepala Program Keahlian</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Penugasan pimpinan manajerial kejuruan</p>
                        </div>
                    </div>
                    @if($guru->is_kaprog)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#003FA9] text-white">
                            Aktif
                        </span>
                    @endif
                </div>

                @if($guru->is_kaprog)
                    <div class="p-5 rounded-2xl bg-[#EFF5FF] space-y-3">
                        <div>
                            <span class="text-slate-400 text-xs block font-medium">Program Keahlian Binaan</span>
                            <span class="text-xl font-extrabold text-[#003FA9]">
                                {{ $guru->kaprog_jurusan->nama_jurusan ?? '-' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600">
                            Memiliki wewenang manajerial data kejuruan, pembagian kelas, dan alokasi ruangan jurusan.
                        </p>
                    </div>
                    <form action="{{ route('guru.remove_kaprog', $guru->id) }}" method="POST"
                          onsubmit="return confirm('Lepaskan jabatan KAPROG dari guru ini?')">
                        @csrf
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-700 font-semibold text-xs transition">
                            Lepaskan Jabatan KAPROG
                        </button>
                    </form>
                @else
                    <div class="p-6 rounded-2xl bg-slate-50 text-center text-slate-400 text-xs">
                        Saat ini tidak memegang jabatan Kepala Program Keahlian.
                    </div>
                    {{-- Form assign kaprog --}}
                    <form action="{{ route('guru.assign_kaprog', $guru->id) }}" method="POST" class="space-y-3 pt-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tetapkan Jurusan KAPROG</label>
                            <div class="relative">
                                <select name="jurusan_id" required
                                    class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach($jurusans as $j)
                                        <option value="{{ $j->id }}">{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                                    @endforeach
                                </select>
                                <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                            </div>
                        </div>
                        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs transition shadow-sm">
                            Tetapkan Sebagai KAPROG
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Section 2: Penugasan Mata Pelajaran Aktif --}}
        <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-5 border-b border-slate-100 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Mata Pelajaran yang Diampu</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Daftar mata pelajaran dan rombongan belajar aktif pada tahun ajaran berjalan.</p>
                </div>
                <button type="button" onclick="openModal('modal-add-subject')"
                    class="h-10 px-4 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center gap-1.5 transition shadow-sm">
                    <i class='bx bx-plus text-base'></i>
                    <span>Tugaskan Mapel Baru</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">MATA PELAJARAN</th>
                            <th class="py-3 px-4">ROMBEL / KELAS</th>
                            <th class="py-3 px-4">TAHUN AJARAN</th>
                            <th class="py-3 px-4">STATUS</th>
                            <th class="py-3 px-4 text-center w-24">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($guru->subjectAssignments as $sa)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $sa->mataPelajaran->nama_mapel ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $sa->mataPelajaran->kode_mapel ?? '' }} • {{ $sa->mataPelajaran->kelompok ?? 'Kejuruan' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800">{{ $sa->kelas->nama_kelas ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $sa->kelas->jurusan->nama_jurusan ?? '' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-medium text-slate-600">
                                    {{ $sa->tahun_ajaran }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 font-bold {{ $sa->is_active ? 'text-emerald-600' : 'text-slate-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $sa->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $sa->is_active ? 'Aktif' : 'Selesai' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form action="{{ route('guru.remove_subject_assignment', $sa->id) }}" method="POST"
                                          onsubmit="return confirm('Lepaskan penugasan mapel {{ $sa->mataPelajaran->nama_mapel ?? '' }} dari guru ini?')">
                                        @csrf
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Lepas Penugasan">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400">
                                    Belum ada penugasan mata pelajaran yang tercatat untuk guru ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== TAB 2: RIWAYAT PENUGASAN (VERTICAL ACCENT BAR TIMELINE) ==================== --}}
    <div x-show="activeTab === 'timeline'" class="space-y-6">

        @php
            // Aggregate all historical assignments chronologically
            $timelineItems = collect();

            foreach ($guru->subjectAssignments as $sa) {
                $timelineItems->push((object)[
                    'type'         => 'mapel',
                    'title'        => $sa->mataPelajaran->nama_mapel ?? 'Mata Pelajaran',
                    'subtitle'     => 'Guru Mata Pelajaran',
                    'tahun_ajaran' => $sa->tahun_ajaran,
                    'kelas'        => $sa->kelas->nama_kelas ?? '-',
                    'jurusan'      => $sa->kelas->jurusan->nama_jurusan ?? '-',
                    'is_active'    => (bool) $sa->is_active,
                    'keterangan'   => ($sa->mataPelajaran->kode_mapel ?? '') . ' • Kelompok ' . ($sa->mataPelajaran->kelompok ?? 'Umum'),
                    'tanggal'      => $sa->created_at ? $sa->created_at->format('d/m/Y') : '-',
                    'detail_id'    => 'SA-' . str_pad($sa->id, 5, '0', STR_PAD_LEFT),
                ]);
            }

            foreach ($guru->homeroomAssignments as $ha) {
                $timelineItems->push((object)[
                    'type'         => 'wali_kelas',
                    'title'        => 'Wali Kelas ' . ($ha->kelas->nama_kelas ?? '-'),
                    'subtitle'     => 'Tugas Tambahan Wali Kelas',
                    'tahun_ajaran' => $ha->tahun_ajaran,
                    'kelas'        => $ha->kelas->nama_kelas ?? '-',
                    'jurusan'      => $ha->kelas->jurusan->nama_jurusan ?? '-',
                    'is_active'    => (bool) $ha->is_active,
                    'keterangan'   => $ha->catatan ?: 'Penugasan wali kelas binaan',
                    'tanggal'      => $ha->created_at ? $ha->created_at->format('d/m/Y') : '-',
                    'detail_id'    => 'HA-' . str_pad($ha->id, 5, '0', STR_PAD_LEFT),
                ]);
            }

            foreach ($guru->kaprogAssignments as $ka) {
                $timelineItems->push((object)[
                    'type'         => 'kaprog',
                    'title'        => 'KAPROG ' . ($ka->jurusan->nama_jurusan ?? '-'),
                    'subtitle'     => 'Kepala Program Keahlian',
                    'tahun_ajaran' => $ka->tahun_ajaran,
                    'kelas'        => 'Semua Kelas Jurusan',
                    'jurusan'      => $ka->jurusan->nama_jurusan ?? '-',
                    'is_active'    => (bool) $ka->is_active,
                    'keterangan'   => $ka->catatan ?: 'Pimpinan program keahlian kejuruan',
                    'tanggal'      => $ka->created_at ? $ka->created_at->format('d/m/Y') : '-',
                    'detail_id'    => 'KA-' . str_pad($ka->id, 5, '0', STR_PAD_LEFT),
                ]);
            }

            // Sort: active items first, then by TA desc
            $timelineItems = $timelineItems->sortByDesc(fn($i) => ($i->is_active ? '1' : '0') . '_' . $i->tahun_ajaran);
        @endphp

        {{-- Vertical Accent Bar Timeline (Matching Detail Siswa Exact Standard) --}}
        <div class="space-y-6 my-6">
            @forelse($timelineItems as $item)
                <div class="flex items-stretch gap-3.5 sm:gap-5" x-data="{ showDetail: false }">
                    {{-- 1. Left: Vertical Accent Bar (Full Height, Independent Per Item) --}}
                    <div class="w-2.5 sm:w-3 shrink-0 rounded-full {{ $item->is_active ? 'bg-[#003FA9]' : 'bg-[#BCD4FF]' }}"></div>

                    {{-- 2. Right: Card Informasi Riwayat Penugasan --}}
                    @if($item->is_active)
                        {{-- Active Assignment Card (SOLID ROYAL BLUE) --}}
                        <div class="flex-1 rounded-2xl p-6 sm:p-7 bg-[#003FA9] text-white shadow-xl transition-all">
                            {{-- Badges row --}}
                            <div class="flex items-center flex-wrap gap-2.5 mb-2">
                                <span class="px-4 py-1 rounded-full text-xs font-bold bg-white text-[#003FA9]">
                                    TA {{ $item->tahun_ajaran }}
                                </span>
                                <span class="px-4 py-1 rounded-full text-xs font-semibold text-white border border-white inline-flex items-center">
                                    Penugasan Aktif Berjalan
                                </span>
                            </div>

                            {{-- Large Title --}}
                            <h3 class="text-2xl font-extrabold text-white mt-2 mb-4 tracking-tight">{{ $item->title }}</h3>

                            {{-- Horizontal Information Columns --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                                <div>
                                    <span class="text-blue-200 block mb-1 font-medium">Peran / Tanggung Jawab</span>
                                    <span class="text-white font-bold text-sm block">{{ $item->subtitle }}</span>
                                </div>
                                <div>
                                    <span class="text-blue-200 block mb-1 font-medium">Rombel / Sasaran</span>
                                    <span class="text-white font-bold text-sm block">{{ $item->kelas }}</span>
                                </div>
                                <div>
                                    <span class="text-blue-200 block mb-1 font-medium">Jurusan Terkait</span>
                                    <span class="text-white font-bold text-sm block">{{ $item->jurusan }}</span>
                                </div>
                                <div class="flex items-end justify-start md:justify-end">
                                    <button type="button" @click="showDetail = !showDetail"
                                            class="text-white hover:text-blue-100 font-bold text-xs inline-flex items-center gap-1 transition">
                                        <span x-text="showDetail ? 'Tutup Detail' : 'Buka Detail'"></span>
                                        <i class='bx text-sm' :class="showDetail ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Expandable Detail Drawer --}}
                            <div x-show="showDetail" x-collapse class="mt-4 pt-4 border-t border-white/20 space-y-2 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <span class="text-blue-200 block">ID Penugasan:</span>
                                        <span class="font-mono text-white font-semibold">{{ $item->detail_id }}</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-200 block">Tanggal Penugasan:</span>
                                        <span class="font-semibold text-white">{{ $item->tanggal }}</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-200 block">Status Operasional:</span>
                                        <span class="font-semibold text-white">Sedang Berjalan (Active)</span>
                                    </div>
                                </div>
                                @if($item->keterangan)
                                    <div class="mt-2 pt-2 border-t border-white/20">
                                        <span class="text-blue-200 font-semibold">Keterangan:</span>
                                        <p class="text-white/90 italic mt-0.5">{{ $item->keterangan }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Completed Past Assignment Card (WHITE CARD) --}}
                        <div class="flex-1 rounded-2xl p-6 sm:p-7 bg-white text-slate-800 shadow-sm border border-slate-100 transition-all">
                            {{-- Badges row --}}
                            <div class="flex items-center flex-wrap gap-2.5 mb-2">
                                <span class="px-4 py-1 rounded-full text-xs font-bold bg-[#003FA9] text-white">
                                    TA {{ $item->tahun_ajaran }}
                                </span>
                            </div>

                            {{-- Large Title --}}
                            <h3 class="text-2xl font-extrabold text-slate-800 mt-2 mb-4 tracking-tight">{{ $item->title }}</h3>

                            {{-- Horizontal Information Columns --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                                <div>
                                    <span class="text-slate-400 block mb-1 font-medium">Peran / Tanggung Jawab</span>
                                    <span class="text-slate-800 font-bold text-sm block">{{ $item->subtitle }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block mb-1 font-medium">Rombel / Sasaran</span>
                                    <span class="text-slate-800 font-bold text-sm block">{{ $item->kelas }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block mb-1 font-medium">Jurusan Terkait</span>
                                    <span class="text-slate-800 font-bold text-sm block">{{ $item->jurusan }}</span>
                                </div>
                                <div class="flex items-end justify-start md:justify-end">
                                    <button type="button" @click="showDetail = !showDetail"
                                            class="text-slate-600 hover:text-[#003FA9] font-bold text-xs inline-flex items-center gap-1 transition">
                                        <span x-text="showDetail ? 'Tutup Detail' : 'Buka Detail'"></span>
                                        <i class='bx text-sm' :class="showDetail ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Expandable Detail Drawer --}}
                            <div x-show="showDetail" x-collapse class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <span class="text-slate-400 block">ID Penugasan:</span>
                                        <span class="font-mono text-slate-800 font-semibold">{{ $item->detail_id }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">Tanggal Penugasan:</span>
                                        <span class="font-semibold text-slate-800">{{ $item->tanggal }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">Status Operasional:</span>
                                        <span class="font-semibold text-slate-800">Selesai / Diarsipkan</span>
                                    </div>
                                </div>
                                @if($item->keterangan)
                                    <div class="mt-2 pt-2 border-t border-slate-100">
                                        <span class="text-slate-400 font-semibold">Keterangan:</span>
                                        <p class="text-slate-700 italic mt-0.5">{{ $item->keterangan }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl p-8 border border-slate-100 text-center text-slate-500 shadow-sm">
                    <i class='bx bx-folder-open text-4xl mb-2 text-slate-300'></i>
                    <p class="font-bold text-slate-700 text-sm">Belum ada riwayat penugasan yang tercatat.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ==================== TAB 3: BEBAN KERJA & JADWAL MENGAJAR ==================== --}}
    <div x-show="activeTab === 'jadwal'" class="space-y-6">
        {{-- Metrics Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-[28px] p-6 shadow-sm border border-slate-100">
                <span class="text-slate-400 text-xs font-semibold block mb-1">Total Jam Pelajaran</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-black text-slate-900">{{ $workload['total_jp'] }}</span>
                    <span class="text-xs text-slate-500 font-bold">JP / minggu</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Standar: 1 JP = 45 menit</p>
            </div>

            <div class="bg-white rounded-[28px] p-6 shadow-sm border border-slate-100">
                <span class="text-slate-400 text-xs font-semibold block mb-1">Durasi Kerja Riil</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-black text-slate-900">{{ $workload['clock_hours'] }}</span>
                    <span class="text-xs text-slate-500 font-bold">Jam / minggu</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Total: {{ $workload['total_minutes'] }} menit mengajar</p>
            </div>

            <div class="bg-white rounded-[28px] p-6 shadow-sm border border-slate-100">
                <span class="text-slate-400 text-xs font-semibold block mb-1">Total Sesi & Rombel</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-900">{{ $workload['total_sessions'] }}</span>
                    <span class="text-xs text-slate-500 font-bold">Sesi • {{ $workload['rombels_taught'] }} Rombel</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">{{ $workload['subjects_taught'] }} Mata Pelajaran Diajar</p>
            </div>

            <div class="bg-white rounded-[28px] p-6 shadow-sm border border-slate-100">
                <span class="text-slate-400 text-xs font-semibold block mb-1">Kepatuhan Beban Kerja</span>
                <div>
                    <span class="text-lg font-extrabold {{ $workload['status'] === 'optimal' ? 'text-emerald-600' : ($workload['status'] === 'underload' ? 'text-amber-600' : 'text-rose-600') }}">
                        {{ $workload['status_label'] }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Target Ideal: 24 - 36 JP / minggu</p>
            </div>
        </div>

        {{-- Weekly Teaching Schedule Table --}}
        <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Jadwal Mengajar Mingguan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Ruangan fisik tersinkronisasi dengan alokasi rolling ruangan aktif.</p>
                </div>
                <a href="{{ route('jadwal') }}"
                    class="h-9 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold inline-flex items-center transition">
                    Kelola di Jadwal Utama
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">HARI</th>
                            <th class="py-3 px-4">WAKTU</th>
                            <th class="py-3 px-4">MATA PELAJARAN</th>
                            <th class="py-3 px-4">ROMBEL (KELAS)</th>
                            <th class="py-3 px-4">RUANGAN FISIK</th>
                            <th class="py-3 px-4 text-center">DURASI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($liveSchedules as $js)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $js->hari }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-600">
                                    {{ \Carbon\Carbon::parse($js->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($js->jam_selesai)->format('H:i') }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    {{ $js->mataPelajaran->nama_mapel ?? $js->mata_pelajaran }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-[#003FA9]">
                                    {{ $js->kelas->nama_kelas ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($js->live_room)
                                        <span class="font-semibold text-slate-900">{{ $js->live_room->name }} ({{ $js->live_room->code }})</span>
                                    @else
                                        <span class="text-slate-400 italic">Belum Dialokasikan</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                    @php
                                        $m = \Carbon\Carbon::parse($js->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($js->jam_selesai));
                                        $jp = round($m / 45, 1);
                                    @endphp
                                    {{ $jp }} JP
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    Belum ada jadwal mengajar yang diatur untuk guru ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== TAB 4: INFORMASI & BIODATA ==================== --}}
    <div x-show="activeTab === 'profil'" class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        <div class="pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 tracking-tight">Biodata Pribadi & Kepegawaian</h3>
            <p class="text-xs text-slate-400 mt-0.5">Perbarui profil dan kontak resmi guru.</p>
        </div>

        <form action="{{ route('guru.update', $guru->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $guru->nama) }}" required
                        class="w-full h-11 px-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Akses Login <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $guru->user?->email) }}" required
                        class="w-full h-11 px-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NIP (Nomor Induk Pegawai)</label>
                    <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}" placeholder="Boleh dikosongkan jika honorer"
                        class="w-full h-11 px-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-mono focus:ring-2 focus:ring-[#003FA9] outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NUPTK</label>
                    <input type="text" name="nuptk" value="{{ old('nuptk', $guru->nuptk) }}" placeholder="Nomor Unik Pendidik"
                        class="w-full h-11 px-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-mono focus:ring-2 focus:ring-[#003FA9] outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="jenis_kelamin" required
                            class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                            <option value="L" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Kepegawaian</label>
                    <div class="relative">
                        <select name="status_kepegawaian"
                            class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                            <option value="">-- Pilih Status --</option>
                            @foreach(['PNS', 'PPPK', 'GTT / Honorer', 'Guru Tetap Yayasan', 'Guru Pengganti'] as $st)
                                <option value="{{ $st }}" {{ old('status_kepegawaian', $guru->status_kepegawaian) === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $guru->telepon) }}" placeholder="081234567890"
                        class="w-full h-11 px-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-mono focus:ring-2 focus:ring-[#003FA9] outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ganti Foto Profil (Opsional)</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#003FA9] file:text-white hover:file:bg-[#002D7A] cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Tempat Tinggal</label>
                <textarea name="alamat" rows="2" placeholder="Alamat domisili lengkap..."
                    class="w-full p-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs focus:ring-2 focus:ring-[#003FA9] outline-none transition">{{ old('alamat', $guru->alamat) }}</textarea>
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button type="submit"
                    class="h-11 px-6 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center gap-2 shadow-sm transition">
                    <i class='bx bx-save text-base'></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ==================== TAB 5: AKUN & KEAMANAN ==================== --}}
    <div x-show="activeTab === 'akun'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Status Akun Control --}}
        <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100 space-y-4">
            <div class="pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900 tracking-tight">Status Akun Login</h3>
                <p class="text-xs text-slate-400 mt-0.5">Kelola hak akses masuk ke aplikasi.</p>
            </div>

            <form action="{{ route('guru.status', $guru->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/60 cursor-pointer transition">
                        <input type="radio" name="status" value="active" {{ $guru->user?->status === 'active' ? 'checked' : '' }}
                            class="text-[#003FA9] focus:ring-[#003FA9]">
                        <div>
                            <p class="text-xs font-bold text-slate-900">Aktif (Active)</p>
                            <p class="text-[11px] text-slate-500">Guru dapat login dan mengakses dashboard secara normal.</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/60 cursor-pointer transition">
                        <input type="radio" name="status" value="pending" {{ $guru->user?->status === 'pending' ? 'checked' : '' }}
                            class="text-[#003FA9] focus:ring-[#003FA9]">
                        <div>
                            <p class="text-xs font-bold text-slate-900">Menunggu Aktivasi (Pending)</p>
                            <p class="text-[11px] text-slate-500">Akun menunggu aktivasi mandiri dari guru.</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/60 cursor-pointer transition">
                        <input type="radio" name="status" value="suspended" {{ $guru->user?->status === 'suspended' ? 'checked' : '' }}
                            class="text-[#003FA9] focus:ring-[#003FA9]">
                        <div>
                            <p class="text-xs font-bold text-slate-900">Ditangguhkan (Suspended)</p>
                            <p class="text-[11px] text-slate-500">Login ditangguhkan sementara.</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/60 cursor-pointer transition">
                        <input type="radio" name="status" value="deactivated" {{ $guru->user?->status === 'deactivated' ? 'checked' : '' }}
                            class="text-[#003FA9] focus:ring-[#003FA9]">
                        <div>
                            <p class="text-xs font-bold text-slate-900">Nonaktif (Deactivated)</p>
                            <p class="text-[11px] text-slate-500">Akun dinonaktifkan secara permanen.</p>
                        </div>
                    </label>
                </div>

                <button type="submit"
                    class="w-full h-11 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold transition shadow-sm">
                    Perbarui Status Akun
                </button>
            </form>
        </div>

        {{-- Set Password & Undangan Aktivasi --}}
        <div class="space-y-6">
            {{-- Set Password Langsung --}}
            <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100 space-y-4">
                <div class="pb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Setel Password Langsung</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Ubah kata sandi akun guru secara manual.</p>
                </div>

                <form action="{{ route('guru.update', $guru->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="nama" value="{{ $guru->nama }}">
                    <input type="hidden" name="email" value="{{ $guru->user?->email }}">
                    <input type="hidden" name="jenis_kelamin" value="{{ $guru->jenis_kelamin }}">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Password Baru (Min. 8 Karakter)</label>
                        <input type="password" name="password" minlength="8" required placeholder="Masukkan password baru..."
                            class="w-full h-11 px-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs focus:ring-2 focus:ring-[#003FA9] outline-none transition">
                    </div>

                    <button type="submit"
                        class="w-full h-11 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold transition shadow-sm">
                        Simpan Password Baru
                    </button>
                </form>
            </div>

            {{-- Buat Tautan Aktivasi Baru --}}
            <div class="bg-white rounded-[28px] p-7 sm:p-8 shadow-sm border border-slate-100 space-y-4">
                <div class="pb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Undangan Aktivasi Mandiri</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Kirimkan tautan agar guru membuat password sendiri.</p>
                </div>

                <form action="{{ route('guru.activation_link', $guru->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full h-11 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-[#003FA9] text-xs font-bold transition">
                        Buat Tautan Aktivasi Baru
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@push('modals')
    {{-- Modal Tambah Penugasan Mapel (Matching Design System) --}}
    <div id="modal-add-subject" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
            onclick="closeModal('modal-add-subject')"></div>
        <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
            <div class="relative w-full max-w-lg rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Tugaskan Mapel Baru</h2>
                        <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                            Atur Penugasan Mengajar Guru
                        </span>
                    </div>
                    <button type="button" onclick="closeModal('modal-add-subject')"
                        class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                <form action="{{ route('guru.assign_subject', $guru->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="mata_pelajaran_id" required
                                class="w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mataPelajarans as $mp)
                                    <option value="{{ $mp->id }}">{{ $mp->nama_mapel }} ({{ $mp->kode_mapel }})</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Rombongan Belajar (Kelas) <span class="text-rose-500">*</span></label>
                            <div class="flex items-center gap-2 text-xs">
                                <button type="button" onclick="document.querySelectorAll('.add-subject-cb').forEach(cb => cb.checked = true)" class="text-[#003FA9] hover:underline font-bold">Pilih Semua</button>
                                <span class="text-slate-400">•</span>
                                <button type="button" onclick="document.querySelectorAll('.add-subject-cb').forEach(cb => cb.checked = false)" class="text-slate-500 hover:underline">Hapus</button>
                            </div>
                        </div>
                        <div class="max-h-40 overflow-y-auto border border-blue-200/60 rounded-xl p-3 bg-white/70 grid grid-cols-2 gap-2 shadow-inner">
                            @foreach($kelasList as $k)
                                <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-blue-50/80 text-xs text-slate-800 cursor-pointer transition">
                                    <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" class="add-subject-cb rounded text-[#003FA9] focus:ring-[#003FA9]">
                                    <span class="font-medium truncate">{{ $k->nama_kelas }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Pilih satu atau beberapa rombel yang akan diajar.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="tahun_ajaran" value="{{ $tahunAjaran }}" required
                            class="w-full px-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition font-medium">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#D8E6FC]">
                        <button type="button" onclick="closeModal('modal-add-subject')"
                            class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] hover:bg-[#d4e4fc] text-[#003FA9] font-bold text-sm transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-sm shadow-md shadow-blue-500/20 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Simpan Penugasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Guru (Inline on Show Page) --}}
    <div id="modal-editguru-inline" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
            onclick="closeModal('modal-editguru-inline')"></div>
        <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
            <div class="relative w-full max-w-2xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Data Guru</h2>
                        <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                            Perbarui Profil, Mapel & Akun Guru
                        </span>
                    </div>
                    <button type="button" onclick="closeModal('modal-editguru-inline')"
                        class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                <div id="loadeditform-inline">
                    @include('guru.edit', ['guru' => $guru])
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function copyActivationUrl() {
        const copyInput = document.getElementById('activation_url_copy');
        if (copyInput) {
            copyInput.select();
            navigator.clipboard.writeText(copyInput.value);
            Swal.fire({
                title: 'Berhasil Disalin!',
                text: 'Tautan aktivasi akun guru siap dibagikan.',
                icon: 'success',
                confirmButtonColor: '#003FA9',
                customClass: {
                    popup: 'font-inter rounded-2xl',
                    confirmButton: 'rounded-xl text-xs px-5 py-2'
                }
            });
        }
    }
</script>
@endpush
@endsection
