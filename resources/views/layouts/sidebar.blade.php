{{-- Sidebar --}}
<aside id="sidebar"
    class="fixed top-0 left-0 z-50 h-screen lg:w-[272px] w-[272px] -translate-x-full lg:translate-x-0 sidebar-transition bg-[#003FA9] border-r border-blue-900/30 flex flex-col shadow-xl">

    {{-- Logo Section --}}
    <div class="flex items-center gap-3.5 px-6 py-5 border-b border-white/10">
        <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-white/15 border border-white/20 p-1 flex items-center justify-center shadow-sm backdrop-blur-xs">
            <img src="{{ asset('img/school-crest.png') }}" alt="Logo" class="w-full h-full object-contain filter drop-shadow">
        </div>
        <div class="sidebar-logo-text overflow-hidden transition-all duration-300">
            <h1 class="text-white font-extrabold text-lg leading-none tracking-tight">MySkanic</h1>
            <p class="text-blue-200 text-xs italic font-medium tracking-normal mt-1">Online Present</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 sidebar-scroll overflow-y-auto">

        {{-- Top: Dashboard --}}
        <div class="mb-5">
            <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                MENU UTAMA
            </p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('dashboard')
                                  ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                  : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <div
                            class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('dashboard')
                                        ? 'bg-blue-50 text-[#0052CC]'
                                        : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    MANAJEMEN SEKOLAH
                </p>
                <ul class="space-y-1 stagger-children">
                    {{-- Data Siswa --}}
                    <li>
                        <a href="{{ route('siswa') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('siswa*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('siswa*')
                                            ? 'bg-blue-50 text-[#0052CC]'
                                            : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('guru') || (request()->routeIs('guru.*') && !request()->routeIs('guru.workload'))
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('guru.workload')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('orangtua*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-user-pin text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Data Orang Tua</span>
                            </a>
                        </li>

                        {{-- Inventaris Alat Bengkel/Lab --}}
                        <li>
                            <a href="{{ route('inventory.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('inventory.*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('inventory.*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-wrench text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Inventaris Alat</span>
                            </a>
                        </li>

                        {{-- Mitra DU/DI --}}
                        <li>
                            <a href="{{ route('pkl.partners.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('pkl.partners*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('pkl.partners*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-briefcase text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Mitra DU/DI</span>
                            </a>
                        </li>

                        {{-- Penempatan PKL --}}
                        <li>
                            <a href="{{ route('pkl.placements.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('pkl.placements*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('pkl.placements*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-group text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Penempatan PKL</span>
                            </a>
                        </li>

                        {{-- Kedisiplinan & BK --}}
                        <li>
                            <a href="{{ route('discipline.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('discipline.*') || request()->routeIs('counseling.*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('discipline.*') || request()->routeIs('counseling.*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-shield-quarter text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Kedisiplinan & BK</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- 2. AKADEMIK --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
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
                                ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ $isAcademicActive
                                        ? 'bg-blue-50 text-[#0052CC]'
                                        : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('jadwal*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('rooms.assignments*')
                                            ? 'bg-blue-50 text-[#0052CC]'
                                            : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                <i class='bx bx-transfer text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Alokasi Ruangan</span>
                        </a>
                    </li>

                    @if (in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                        {{-- Monitoring Jurnal Guru --}}
                        <li>
                            <a href="{{ route('jurnal.monitoring') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('jurnal.monitoring*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('jurnal.monitoring*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-book-content text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Monitoring Jurnal</span>
                            </a>
                        </li>

                        {{-- Monitoring PKL & Industri --}}
                        <li>
                            <a href="{{ route('pkl.monitoring') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('pkl.monitoring*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('pkl.monitoring*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-line-chart text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Monitoring PKL</span>
                            </a>
                        </li>

                        {{-- Etalase Karya & TeFa --}}
                        <li>
                            <a href="{{ route('portfolio.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('portfolio.*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('portfolio.*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-bulb text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Etalase Karya & TeFa</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- 3. PRESENSI --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    PRESENSI
                </p>
                <ul class="space-y-1 stagger-children">
                    {{-- QR Code Ruangan --}}
                    <li>
                        <a href="{{ route('absensi.qrcodes') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.qrcodes')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('absensi.qrcodes')
                                            ? 'bg-blue-50 text-[#0052CC]'
                                            : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('admin.biometrik*')
                                            ? 'bg-blue-50 text-[#0052CC]'
                                            : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('rekapabsensi')
                                            ? 'bg-blue-50 text-[#0052CC]'
                                            : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                <i class='bx bx-clipboard text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Rekap Presensi</span>
                        </a>
                    </li>

                    @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                        {{-- Log Notifikasi WhatsApp --}}
                        <li>
                            <a href="{{ route('admin.whatsapp.logs') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('admin.whatsapp*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('admin.whatsapp*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bxl-whatsapp text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Gateway WhatsApp</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- 4. PENGATURAN --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    PENGATURAN
                </p>
                <ul class="space-y-1 stagger-children">
                    @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                        {{-- Pengaturan Sekolah --}}
                        <li>
                            <a href="{{ route('settings.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('settings.*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('settings.*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-cog text-lg'></i>
                                </div>
                                <span>Pengaturan Sekolah</span>
                            </a>
                        </li>

                        {{-- Kalender Akademik --}}
                        <li>
                            <a href="{{ route('academic_calendar.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('academic_calendar.*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('academic_calendar.*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-calendar-x text-lg'></i>
                                </div>
                                <span>Kalender Akademik</span>
                            </a>
                        </li>

                        {{-- Persetujuan Izin --}}
                        <li>
                            <a href="{{ route('izin.approval') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('izin.approval')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('izin.approval')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                    <i class='bx bx-task text-lg'></i>
                                </div>
                                <span>Persetujuan Izin</span>
                            </a>
                        </li>

                        {{-- Data Ruangan --}}
                        <li>
                            <a href="{{ route('rooms.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('rooms.index*')
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('rooms.index*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                          ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('lokasi_sekolah*')
                                                ? 'bg-blue-50 text-[#0052CC]'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('user*')
                                                ? 'bg-purple-600/30 text-purple-400'
                                                : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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

            {{-- Persetujuan Izin --}}
            <div class="mb-5">
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('izin.approval') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('izin.approval')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('izin.approval') ? 'bg-blue-50 text-[#0052CC]' : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                <i class='bx bx-task text-lg'></i>
                            </div>
                            <span>Persetujuan Izin</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 1. PEMBELAJARAN & JADWAL --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    PEMBELAJARAN
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('teacher.schedule') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('teacher.schedule')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('teacher.schedule') ? 'bg-blue-50 text-[#0052CC]' : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                <i class='bx bx-calendar-event text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Jadwal Mengajar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teacher.classes') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('teacher.classes*') || request()->routeIs('teacher.class.*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('teacher.classes*') || request()->routeIs('teacher.class.*') ? 'bg-blue-50 text-[#0052CC]' : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                <i class='bx bx-chalkboard text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Kelas yang Diajar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('jurnal.index') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('jurnal.*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('jurnal.*') ? 'bg-blue-50 text-[#0052CC]' : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                <i class='bx bx-book-reader text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Jurnal Mengajar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('gradebook.index') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('gradebook.*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('gradebook.*') ? 'bg-blue-50 text-[#0052CC]' : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                <i class='bx bx-edit text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Buku Nilai</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pkl.advisor.logbooks') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('pkl.advisor*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('pkl.advisor*') ? 'bg-blue-50 text-[#0052CC]' : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
                                <i class='bx bx-check-shield text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Bimbingan PKL</span>
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
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('homeroom.monitoring') ? 'bg-emerald-600/30 text-emerald-400' : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('academic.*') ? 'bg-purple-600/30 text-purple-400' : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                            {{ request()->routeIs('rooms.assignments*') ? 'bg-purple-600/30 text-purple-400' : 'bg-white/10 text-blue-200 group-hover:bg-white/15 group-hover:text-white' }}">
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
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    PRESENSI
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('absensi.harian') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.harian')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-fingerprint text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Absen Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rekapabsensi') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('rekapabsensi')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-clipboard text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Rekap Presensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.biometrik') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('admin.biometrik*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
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
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    KEHADIRAN & AKUN
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('absensi') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-fingerprint text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Absen Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('absensi.face.register.show') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.face.register.show')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-scan text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Registrasi Wajah</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('izin') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('izin')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-envelope text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Pengajuan Izin</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('riwayatkehadiran') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('riwayatkehadiran')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-history text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Riwayat Kehadiran</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('gradebook.parent') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('gradebook.parent*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-award text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Nilai Akademik Anak</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('discipline.student.portal') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('discipline.student*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-shield-quarter text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Kedisiplinan & Prestasi Anak</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('siswa.devices') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('siswa.devices*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-devices text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Perangkat Saya</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('gradebook.student') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('gradebook.student*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-award text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Nilai Akademik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pkl.student.portal') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('pkl.student*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-buildings text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Portal PKL & Magang</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('discipline.student.portal') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('discipline.student*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-shield-quarter text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Kedisiplinan & Prestasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('portfolio.student.index') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('portfolio.student*')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-palette text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Portofolio & TeFa</span>
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
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    MONITORING KEHADIRAN
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('riwayatkehadiran') }}"
                            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('riwayatkehadiran')
                                      ? 'bg-white text-[#0052CC] font-bold rounded-full shadow-sm'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200 group-hover:bg-white/15 group-hover:text-white">
                                <i class='bx bx-history text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Riwayat Kehadiran Anak</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </nav>

    {{-- Sidebar Footer: User Info (Fixed at Bottom) --}}
    <div class="flex-shrink-0 border-t border-white/10 px-4 py-3.5 bg-[#003795]/70 backdrop-blur-sm">
        <div class="flex items-center justify-between gap-2.5">
            <div class="flex items-center gap-3 min-w-0">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-blue-500/80 border border-white/20 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                    @if (Auth::user()->role === 'guru' && Auth::user()->guru && Auth::user()->guru->photo_path && file_exists(public_path('storage/' . Auth::user()->guru->photo_path)))
                        <img src="{{ asset('storage/' . Auth::user()->guru->photo_path) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover rounded-xl">
                    @else
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <div class="sidebar-logo-text overflow-hidden min-w-0 transition-all duration-300">
                    <p class="text-white text-xs font-bold truncate leading-tight">
                        {{ Auth::user()->salutation_name ?? Auth::user()->name ?? 'Admin Kesiswaan' }}
                    </p>
                    <p class="text-blue-200 text-[10px] font-semibold uppercase tracking-wider mt-0.5">
                        {{ strtoupper(Auth::user()->role) }}
                    </p>
                </div>
            </div>

            <a href="{{ route('proseslogout') }}"
                onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();"
                class="flex-shrink-0 w-8 h-8 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 hover:text-red-200 border border-red-500/30 flex items-center justify-center transition-all duration-200"
                title="Keluar">
                <i class='bx bx-log-out text-base'></i>
            </a>
            <form id="sidebar-logout-form" action="{{ route('proseslogout') }}" method="GET" class="hidden"></form>
        </div>
    </div>
</aside>
