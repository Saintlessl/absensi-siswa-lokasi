{{-- Sidebar --}}
<aside id="sidebar"
    class="fixed top-0 left-0 z-50 h-screen lg:w-[272px] w-[272px] -translate-x-full lg:translate-x-0 sidebar-transition sidebar-gradient shadow-2xl shadow-dark-900/30 flex flex-col">

    {{-- Logo Section --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-xl bg-white from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/30">
            <img src="{{ asset('img/logo.png') }}" width="100px;" alt="Logo">
        </div>
        <div class="sidebar-logo-text overflow-hidden transition-all duration-300">
            <h1 class="text-white font-bold text-base leading-tight tracking-tight">Absensi Siswa</h1>
            <p class="text-primary-300 text-[11px] font-medium tracking-wider uppercase">SMK Negeri</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 sidebar-scroll overflow-y-auto">

        {{-- Top: Dashboard --}}
        <div class="mb-5">
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('dashboard')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                        <div
                            class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('dashboard')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                            <i class='bx bxs-dashboard text-lg'></i>
                        </div>
                        <span class="sidebar-label transition-all duration-300">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- ============================================================== --}}
        {{-- ADMIN / SUPERADMIN / KAPROG NAVIGATION                           --}}
        {{-- ============================================================== --}}
        @if (in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))

            {{-- 1. MANAJEMEN SEKOLAH --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    MANAJEMEN SEKOLAH
                </p>
                <ul class="space-y-1 stagger-children">
                    {{-- Data Siswa --}}
                    <li>
                        <a href="{{ route('siswa') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('siswa*')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('siswa*')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-group text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Data Siswa</span>
                        </a>
                    </li>

                    @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                        {{-- Data Guru --}}
                        <li>
                            <a href="{{ route('guru') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('guru') || (request()->routeIs('guru.*') && !request()->routeIs('guru.workload'))
                                          ? 'bg-primary-600/20 text-primary-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('guru') || (request()->routeIs('guru.*') && !request()->routeIs('guru.workload'))
                                                ? 'bg-primary-600/30 text-primary-400'
                                                : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-user text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Data Guru</span>
                            </a>
                        </li>

                        {{-- Beban Kerja Guru --}}
                        <li>
                            <a href="{{ route('guru.workload') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('guru.workload')
                                          ? 'bg-primary-600/20 text-primary-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('guru.workload')
                                                ? 'bg-primary-600/30 text-primary-400'
                                                : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-pie-chart-alt-2 text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Beban Kerja Guru</span>
                            </a>
                        </li>

                        {{-- Data Orang Tua --}}
                        <li>
                            <a href="{{ route('orangtua') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('orangtua*')
                                          ? 'bg-primary-600/20 text-primary-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('orangtua*')
                                                ? 'bg-primary-600/30 text-primary-400'
                                                : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-user-pin text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Data Orang Tua</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- 2. AKADEMIK --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    AKADEMIK
                </p>
                <ul class="space-y-1 stagger-children">
                    @php
                        $isAcademicActive = request()->routeIs('academic.*') || request()->routeIs('jurusan*') || request()->routeIs('kelas*');
                    @endphp
                    {{-- Struktur Akademik (Collapsible Dropdown) --}}
                    <li x-data="{ open: {{ $isAcademicActive ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open"
                            class="w-full group flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                            {{ $isAcademicActive
                                ? 'bg-primary-600/20 text-primary-400'
                                : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ $isAcademicActive
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bxs-graduation text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Struktur Akademik</span>
                            </div>
                            <i class='bx bx-chevron-down text-base transition-transform duration-200' :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" x-collapse class="mt-1 space-y-1 pl-11 pr-2">
                            <a href="{{ route('academic.index') }}"
                                class="flex items-center gap-2 py-1.5 px-3 rounded-lg text-xs font-medium transition-colors
                                {{ request()->routeIs('academic.*') ? 'text-primary-400 bg-white/10 font-semibold' : 'text-dark-400 hover:text-white hover:bg-white/5' }}">
                                <i class='bx bx-sitemap text-sm'></i>
                                <span>Overview Hierarki</span>
                            </a>
                            @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                                <a href="{{ route('jurusan') }}"
                                    class="flex items-center gap-2 py-1.5 px-3 rounded-lg text-xs font-medium transition-colors
                                    {{ request()->routeIs('jurusan*') ? 'text-primary-400 bg-white/10 font-semibold' : 'text-dark-400 hover:text-white hover:bg-white/5' }}">
                                    <i class='bx bx-layer text-sm'></i>
                                    <span>Jurusan</span>
                                </a>
                                <a href="{{ route('kelas') }}"
                                    class="flex items-center gap-2 py-1.5 px-3 rounded-lg text-xs font-medium transition-colors
                                    {{ request()->routeIs('kelas*') ? 'text-primary-400 bg-white/10 font-semibold' : 'text-dark-400 hover:text-white hover:bg-white/5' }}">
                                    <i class='bx bx-chalkboard text-sm'></i>
                                    <span>Tingkat & Rombel</span>
                                </a>
                            @endif
                        </div>
                    </li>

                    @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                        {{-- Jadwal Pelajaran --}}
                        <li>
                            <a href="{{ route('jadwal') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('jadwal*')
                                          ? 'bg-primary-600/20 text-primary-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('jadwal*')
                                                ? 'bg-primary-600/30 text-primary-400'
                                                : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-calendar text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Jadwal Pelajaran</span>
                            </a>
                        </li>
                    @endif

                    {{-- Alokasi & Rolling Ruangan --}}
                    <li>
                        <a href="{{ route('rooms.assignments') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('rooms.assignments*')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('rooms.assignments*')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-transfer text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Alokasi Ruangan</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 3. PRESENSI --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    PRESENSI
                </p>
                <ul class="space-y-1 stagger-children">
                    {{-- QR Code Ruangan --}}
                    <li>
                        <a href="{{ route('absensi.qrcodes') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.qrcodes')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('absensi.qrcodes')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-qr-scan text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">QR Code Ruangan</span>
                        </a>
                    </li>

                    {{-- Biometrik Wajah --}}
                    <li>
                        <a href="{{ route('admin.biometrik') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('admin.biometrik*')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('admin.biometrik*')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-scan text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Biometrik Wajah</span>
                        </a>
                    </li>

                    {{-- Rekap Presensi --}}
                    <li>
                        <a href="{{ route('rekapabsensi') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('rekapabsensi')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('rekapabsensi')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-clipboard text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Rekap Presensi</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 4. PENGATURAN --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    PENGATURAN
                </p>
                <ul class="space-y-1 stagger-children">
                    @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                        {{-- Data Ruangan --}}
                        <li>
                            <a href="{{ route('rooms.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('rooms.index*')
                                          ? 'bg-primary-600/20 text-primary-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('rooms.index*')
                                                ? 'bg-primary-600/30 text-primary-400'
                                                : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-building-house text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Data Ruangan</span>
                            </a>
                        </li>

                        {{-- Pengaturan Lokasi Sekolah --}}
                        <li>
                            <a href="{{ route('lokasi_sekolah') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('lokasi_sekolah*')
                                          ? 'bg-primary-600/20 text-primary-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('lokasi_sekolah*')
                                                ? 'bg-primary-600/30 text-primary-400'
                                                : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-map-pin text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Pengaturan Lokasi</span>
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->role === 'superadmin')
                        {{-- Manajemen Pengguna (Superadmin Only) --}}
                        <li>
                            <a href="{{ route('user') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('user*')
                                          ? 'bg-purple-600/20 text-purple-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('user*')
                                                ? 'bg-purple-600/30 text-purple-400'
                                                : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bxs-user-account text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Manajemen Pengguna</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        {{-- ============================================================== --}}
        {{-- GURU ROLE NAVIGATION                                            --}}
        {{-- ============================================================== --}}
        @if (Auth::user()->role === 'guru')
            @php
                $sidebarGuru = \App\Models\Guru::with(['activeHomeroomAssignment.kelas', 'user.jurusan'])->where('user_id', Auth::id())->first();
            @endphp

            {{-- 1. PEMBELAJARAN & JADWAL --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    PEMBELAJARAN
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('teacher.schedule') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('teacher.schedule')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('teacher.schedule') ? 'bg-primary-600/30 text-primary-400' : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-calendar-event text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Jadwal Mengajar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teacher.classes') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('teacher.classes*') || request()->routeIs('teacher.class.*')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('teacher.classes*') || request()->routeIs('teacher.class.*') ? 'bg-primary-600/30 text-primary-400' : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-chalkboard text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Kelas yang Diajar</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 2. WALI KELAS (Jika Guru Ditugaskan Sebagai Wali Kelas) --}}
            @if($sidebarGuru && $sidebarGuru->is_homeroom)
                <div class="mb-5">
                    <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-emerald-400 uppercase tracking-[0.15em] flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> WALI KELAS
                    </p>
                    <ul class="space-y-1 stagger-children">
                        <li>
                            <a href="{{ route('homeroom.monitoring') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('homeroom.monitoring')
                                          ? 'bg-emerald-600/20 text-emerald-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('homeroom.monitoring') ? 'bg-emerald-600/30 text-emerald-400' : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-id-card text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Monitoring Kelas Binaan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

            {{-- 3. KEPALA PROGRAM KEAHLIAN (KAPROG) --}}
            @if($sidebarGuru && $sidebarGuru->is_kaprog)
                <div class="mb-5">
                    <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-purple-400 uppercase tracking-[0.15em] flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> KEPALA PROGRAM
                    </p>
                    <ul class="space-y-1 stagger-children">
                        <li>
                            <a href="{{ route('academic.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('academic.*')
                                          ? 'bg-purple-600/20 text-purple-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('academic.*') ? 'bg-purple-600/30 text-purple-400' : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-sitemap text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Program Keahlian</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('rooms.assignments') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('rooms.assignments*')
                                          ? 'bg-purple-600/20 text-purple-400 menu-active'
                                          : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('rooms.assignments*') ? 'bg-purple-600/30 text-purple-400' : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-transfer text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Rolling Ruangan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

            {{-- 4. PRESENSI & REKAP --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    PRESENSI
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('absensi.harian') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.harian')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-dark-400 group-hover:text-white">
                                <i class='bx bx-fingerprint text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Absen Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rekapabsensi') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('rekapabsensi')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-dark-400 group-hover:text-white">
                                <i class='bx bx-clipboard text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Rekap Presensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.biometrik') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('admin.biometrik*')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-dark-400 group-hover:text-white">
                                <i class='bx bx-scan text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Biometrik Wajah</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        {{-- ============================================================== --}}
        {{-- SISWA ROLE NAVIGATION                                           --}}
        {{-- ============================================================== --}}
        @if (Auth::user()->role === 'siswa')
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    KEHADIRAN & AKUN
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('absensi') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-dark-400 group-hover:text-white">
                                <i class='bx bx-fingerprint text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Absen Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('absensi.face.register.show') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.face.register.show')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-dark-400 group-hover:text-white">
                                <i class='bx bx-scan text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Registrasi Wajah</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('izin') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('izin')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-dark-400 group-hover:text-white">
                                <i class='bx bx-envelope text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Pengajuan Izin</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('riwayatkehadiran') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('riwayatkehadiran')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-dark-400 group-hover:text-white">
                                <i class='bx bx-history text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Riwayat Kehadiran</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('siswa.devices') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('siswa.devices*')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-dark-400 group-hover:text-white">
                                <i class='bx bx-devices text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Perangkat Saya</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        {{-- ============================================================== --}}
        {{-- ORANG TUA ROLE NAVIGATION                                       --}}
        {{-- ============================================================== --}}
        @if (Auth::user()->role === 'orangtua')
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    MONITORING KEHADIRAN
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('riwayatkehadiran') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('riwayatkehadiran')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-dark-400 group-hover:text-white">
                                <i class='bx bx-history text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Riwayat Kehadiran Anak</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </nav>

    {{-- Sidebar Footer: User Info --}}
    <div class="border-t border-white/10 px-4 py-3.5">
        <div class="flex items-center gap-3">
            <div
                class="flex-shrink-0 w-9 h-9 rounded-xl {{ Auth::user()->role === 'superadmin' ? 'bg-gradient-to-br from-purple-500 to-indigo-700' : 'bg-gradient-to-br from-primary-500 to-primary-700' }} flex items-center justify-center shadow-lg text-white text-sm font-bold">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="sidebar-logo-text overflow-hidden flex-1 min-w-0 transition-all duration-300">
                <p class="text-white text-xs font-bold truncate">{{ Auth::user()->name ?? 'User' }}</p>
                <div class="mt-0.5">
                    @if (Auth::user()->role === 'superadmin')
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            SUPERADMIN
                        </span>
                    @elseif (Auth::user()->role === 'admin')
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                            ADMIN
                        </span>
                    @elseif (Auth::user()->role === 'guru')
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            GURU
                        </span>
                    @elseif (Auth::user()->role === 'kaprog')
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-violet-500/20 text-violet-300 border border-violet-500/30">
                            KAPROG ({{ Auth::user()->jurusan->kode_jurusan ?? 'JURUSAN' }})
                        </span>
                    @elseif (Auth::user()->role === 'siswa')
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            SISWA
                        </span>
                    @else
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                            WALI MURID
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('proseslogout') }}"
                class="sidebar-logo-text flex-shrink-0 w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500/20 flex items-center justify-center text-red-400 hover:text-red-300 transition-all duration-200"
                title="Logout">
                <i class='bx bx-log-out text-lg'></i>
            </a>
        </div>
    </div>
</aside>
