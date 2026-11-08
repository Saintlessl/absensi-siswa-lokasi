{{-- Sidebar --}}
<aside id="sidebar"
    class="fixed top-0 left-0 z-50 h-screen lg:w-[272px] w-[272px] -translate-x-full lg:translate-x-0 sidebar-transition bg-[#003EA7] border-r border-blue-900/30 flex flex-col shadow-none select-none">

    {{-- Logo Section --}}
    <div class="sidebar-logo-container flex items-center gap-3.5 px-6 pt-6 pb-4 transition-all duration-300">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 group">
            <img src="{{ asset('img/school-crest.png') }}"
                 alt="Logo MySkanic"
                 class="sidebar-logo-img w-10 h-10 object-contain transition-transform duration-200 group-hover:scale-105 flex-shrink-0">
            <div class="sidebar-logo-text overflow-hidden transition-all duration-300">
                <h1 class="text-white font-extrabold text-lg leading-none tracking-tight">MySkanic</h1>
                <p class="text-blue-200 text-xs italic font-medium tracking-normal mt-1">Online Present</p>
            </div>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 pt-2 pb-6 sidebar-scroll overflow-y-auto">

        {{-- Top: Dashboard --}}
        <div class="mb-5">
            <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                MENU UTAMA
            </p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('dashboard')
                                  ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                  : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                        data-tooltip="Dashboard"
                        title="Dashboard">
                        <i class='bx bxs-dashboard text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('dashboard') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                        <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('dashboard') ? 'font-bold' : '' }}">Dashboard</span>
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
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('siswa*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Data Siswa"
                            title="Data Siswa">
                            <i class='bx bx-group text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('siswa*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('siswa*') ? 'font-bold' : '' }}">Data Siswa</span>
                        </a>
                    </li>

                    @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                        {{-- Data Guru --}}
                        <li>
                            <a href="{{ route('guru') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('guru') || (request()->routeIs('guru.*') && !request()->routeIs('guru.workload'))
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Data Guru"
                                title="Data Guru">
                                <i class='bx bx-user text-[22px] flex-shrink-0 transition-colors {{ (request()->routeIs('guru') || (request()->routeIs('guru.*') && !request()->routeIs('guru.workload'))) ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ (request()->routeIs('guru') || (request()->routeIs('guru.*') && !request()->routeIs('guru.workload'))) ? 'font-bold' : '' }}">Data Guru</span>
                            </a>
                        </li>

                        {{-- Beban Kerja Guru --}}
                        <li>
                            <a href="{{ route('guru.workload') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('guru.workload')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Beban Kerja Guru"
                                title="Beban Kerja Guru">
                                <i class='bx bx-pie-chart-alt-2 text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('guru.workload') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('guru.workload') ? 'font-bold' : '' }}">Beban Kerja Guru</span>
                            </a>
                        </li>

                        {{-- Data Orang Tua --}}
                        <li>
                            <a href="{{ route('orangtua') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('orangtua*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Data Orang Tua"
                                title="Data Orang Tua">
                                <i class='bx bx-user-pin text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('orangtua*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('orangtua*') ? 'font-bold' : '' }}">Data Orang Tua</span>
                            </a>
                        </li>

                        {{-- Inventaris Alat Bengkel/Lab --}}
                        <li>
                            <a href="{{ route('inventory.index') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('inventory.*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Inventaris Alat"
                                title="Inventaris Alat">
                                <i class='bx bx-wrench text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('inventory.*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('inventory.*') ? 'font-bold' : '' }}">Inventaris Alat</span>
                            </a>
                        </li>

                        {{-- Mitra DU/DI --}}
                        <li>
                            <a href="{{ route('pkl.partners.index') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('pkl.partners*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Mitra DU/DI"
                                title="Mitra DU/DI">
                                <i class='bx bx-briefcase text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('pkl.partners*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('pkl.partners*') ? 'font-bold' : '' }}">Mitra DU/DI</span>
                            </a>
                        </li>

                        {{-- Penempatan PKL --}}
                        <li>
                            <a href="{{ route('pkl.placements.index') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('pkl.placements*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Penempatan PKL"
                                title="Penempatan PKL">
                                <i class='bx bx-group text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('pkl.placements*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('pkl.placements*') ? 'font-bold' : '' }}">Penempatan PKL</span>
                            </a>
                        </li>

                        {{-- Kedisiplinan & BK --}}
                        <li>
                            <a href="{{ route('discipline.index') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('discipline.*') || request()->routeIs('counseling.*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Kedisiplinan & BK"
                                title="Kedisiplinan & BK">
                                <i class='bx bx-shield-quarter text-[22px] flex-shrink-0 transition-colors {{ (request()->routeIs('discipline.*') || request()->routeIs('counseling.*')) ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ (request()->routeIs('discipline.*') || request()->routeIs('counseling.*')) ? 'font-bold' : '' }}">Kedisiplinan & BK</span>
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
                        $isAcademicActive = request()->routeIs('academic.*') || request()->routeIs('jurusan*') || request()->routeIs('kelas*') || request()->routeIs('angkatan*');
                    @endphp
                    {{-- Struktur Akademik (Collapsible Dropdown) --}}
                    <li x-data="{ open: {{ $isAcademicActive ? 'true' : 'false' }} }">
                        <button type="button"
                            @click="if (document.getElementById('sidebar').classList.contains('sidebar-collapsed')) { window.location.href = '{{ route('academic.index') }}'; } else { open = !open; }"
                            class="sidebar-nav-link group relative w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                            {{ $isAcademicActive
                                ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Struktur Akademik"
                            title="Struktur Akademik">
                            <div class="flex items-center gap-3.5">
                                <i class='bx bxs-graduation text-[22px] flex-shrink-0 transition-colors {{ $isAcademicActive ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ $isAcademicActive ? 'font-bold' : '' }}">Struktur Akademik</span>
                            </div>
                            <i class='bx bx-chevron-down sidebar-chevron text-base transition-transform duration-200' :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" x-collapse class="sidebar-submenu mt-1 space-y-1 pl-11 pr-2">
                            <a href="{{ route('academic.index') }}"
                                class="flex items-center gap-2 py-1.5 px-3 rounded-lg text-xs font-medium transition-colors
                                {{ request()->routeIs('academic.*') ? 'text-primary-400 bg-white/10 font-semibold' : 'text-dark-400 hover:text-white hover:bg-white/5' }}">
                                <i class='bx bx-sitemap text-sm'></i>
                                <span>Overview Hierarki</span>
                            </a>
                            @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                                <a href="{{ route('angkatan.index') }}"
                                class="flex items-center gap-2 py-1.5 px-3 rounded-lg text-xs font-medium transition-colors
                                {{ request()->routeIs('angkatan*') ? 'text-primary-400 bg-white/10 font-semibold' : 'text-dark-400 hover:text-white hover:bg-white/5' }}">
                                <i class='bx bxs-graduation text-sm'></i>
                                <span>Angkatan & Kelulusan</span>
                            </a>
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
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('jadwal*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Jadwal Pelajaran"
                                title="Jadwal Pelajaran">
                                <i class='bx bx-calendar text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('jadwal*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('jadwal*') ? 'font-bold' : '' }}">Jadwal Pelajaran</span>
                            </a>
                        </li>

                        {{-- Alokasi Ruangan --}}
                        <li>
                            <a href="{{ route('rooms.assignments') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('rooms.assignments*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Alokasi Ruangan"
                                title="Alokasi Ruangan">
                                <i class='bx bx-transfer text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('rooms.assignments*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('rooms.assignments*') ? 'font-bold' : '' }}">Alokasi Ruangan</span>
                            </a>
                        </li>

                        {{-- Monitoring Jurnal Mengajar --}}
                        <li>
                            <a href="{{ route('jurnal.monitoring') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('jurnal.monitoring*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Monitoring Jurnal"
                                title="Monitoring Jurnal">
                                <i class='bx bx-book-content text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('jurnal.monitoring*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('jurnal.monitoring*') ? 'font-bold' : '' }}">Monitoring Jurnal</span>
                            </a>
                        </li>

                        {{-- Monitoring PKL & Siswa Binaan --}}
                        <li>
                            <a href="{{ route('pkl.monitoring') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('pkl.monitoring*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Monitoring PKL"
                                title="Monitoring PKL">
                                <i class='bx bx-line-chart text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('pkl.monitoring*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('pkl.monitoring*') ? 'font-bold' : '' }}">Monitoring PKL</span>
                            </a>
                        </li>

                        {{-- Portofolio & TeFa Showcase --}}
                        <li>
                            <a href="{{ route('portfolio.index') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('portfolio.*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Etalase Karya & TeFa"
                                title="Etalase Karya & TeFa">
                                <i class='bx bx-bulb text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('portfolio.*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('portfolio.*') ? 'font-bold' : '' }}">Etalase Karya & TeFa</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                {{-- 3. PRESENSI & AKSES --}}
                <div class="mb-5">
                    <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                        PRESENSI & AKSES
                    </p>
                    <ul class="space-y-1 stagger-children">
                        {{-- QR Code Ruangan --}}
                        <li>
                            <a href="{{ route('absensi.qrcodes') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('absensi.qrcodes')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="QR Code Ruangan"
                                title="QR Code Ruangan">
                                <i class='bx bx-qr-scan text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('absensi.qrcodes') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('absensi.qrcodes') ? 'font-bold' : '' }}">QR Code Ruangan</span>
                            </a>
                        </li>

                        {{-- Monitoring Biometrik Wajah --}}
                        <li>
                            <a href="{{ route('admin.biometrik') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('admin.biometrik*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Biometrik Wajah"
                                title="Biometrik Wajah">
                                <i class='bx bx-scan text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('admin.biometrik*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('admin.biometrik*') ? 'font-bold' : '' }}">Biometrik Wajah</span>
                            </a>
                        </li>

                        {{-- Rekap Presensi --}}
                        <li>
                            <a href="{{ route('rekapabsensi') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('rekapabsensi')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Rekap Presensi"
                                title="Rekap Presensi">
                                <i class='bx bx-clipboard text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('rekapabsensi') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('rekapabsensi') ? 'font-bold' : '' }}">Rekap Presensi</span>
                            </a>
                        </li>

                        {{-- Gateway WhatsApp --}}
                        <li>
                            <a href="{{ route('admin.whatsapp.logs') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('admin.whatsapp*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Gateway WhatsApp"
                                title="Gateway WhatsApp">
                                <i class='bx bxl-whatsapp text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('admin.whatsapp*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('admin.whatsapp*') ? 'font-bold' : '' }}">Gateway WhatsApp</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

            {{-- 4. PENGATURAN & MASTER --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    PENGATURAN & MASTER
                </p>
                <ul class="space-y-1 stagger-children">
                    @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                        {{-- Pengaturan Sekolah --}}
                        <li>
                            <a href="{{ route('settings.index') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('settings.*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Pengaturan Sekolah"
                                title="Pengaturan Sekolah">
                                <i class='bx bx-cog text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('settings.*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('settings.*') ? 'font-bold' : '' }}">Pengaturan Sekolah</span>
                            </a>
                        </li>

                        {{-- Kalender Akademik --}}
                        <li>
                            <a href="{{ route('academic_calendar.index') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('academic_calendar.*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Kalender Akademik"
                                title="Kalender Akademik">
                                <i class='bx bx-calendar-x text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('academic_calendar.*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('academic_calendar.*') ? 'font-bold' : '' }}">Kalender Akademik</span>
                            </a>
                        </li>

                        {{-- Persetujuan Izin (Superadmin & Admin) --}}
                        <li>
                            <a href="{{ route('izin.approval') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('izin.approval')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Persetujuan Izin"
                                title="Persetujuan Izin">
                                <i class='bx bx-task text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('izin.approval') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('izin.approval') ? 'font-bold' : '' }}">Persetujuan Izin</span>
                            </a>
                        </li>

                        {{-- Data Ruangan --}}
                        <li>
                            <a href="{{ route('rooms.index') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('rooms.index*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Data Ruangan"
                                title="Data Ruangan">
                                <i class='bx bx-building-house text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('rooms.index*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('rooms.index*') ? 'font-bold' : '' }}">Data Ruangan</span>
                            </a>
                        </li>

                        {{-- Pengaturan Lokasi Absen --}}
                        <li>
                            <a href="{{ route('lokasi_sekolah') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('lokasi_sekolah*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Pengaturan Lokasi"
                                title="Pengaturan Lokasi">
                                <i class='bx bx-map-pin text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('lokasi_sekolah*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('lokasi_sekolah*') ? 'font-bold' : '' }}">Pengaturan Lokasi</span>
                            </a>
                        </li>
                    @endif

                    {{-- Manajemen Pengguna (Superadmin Only) --}}
                    @if (Auth::user()->role === 'superadmin')
                        <li>
                            <a href="{{ route('user') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('user*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Manajemen Pengguna"
                                title="Manajemen Pengguna">
                                <i class='bx bxs-user-account text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('user*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('user*') ? 'font-bold' : '' }}">Manajemen Pengguna</span>
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
                $sidebarGuru = Auth::user()->guru;
            @endphp

            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    MENU GURU
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('izin.approval') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('izin.approval')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Persetujuan Izin"
                            title="Persetujuan Izin">
                            <i class='bx bx-task text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('izin.approval') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('izin.approval') ? 'font-bold' : '' }}">Persetujuan Izin</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 1. AKADEMIK GURU --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    AKADEMIK GURU
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('teacher.schedule') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('teacher.schedule')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Jadwal Mengajar"
                            title="Jadwal Mengajar">
                            <i class='bx bx-calendar-event text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('teacher.schedule') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('teacher.schedule') ? 'font-bold' : '' }}">Jadwal Mengajar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teacher.classes') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('teacher.classes*') || request()->routeIs('teacher.class.*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Kelas yang Diajar"
                            title="Kelas yang Diajar">
                            <i class='bx bx-chalkboard text-[22px] flex-shrink-0 transition-colors {{ (request()->routeIs('teacher.classes*') || request()->routeIs('teacher.class.*')) ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ (request()->routeIs('teacher.classes*') || request()->routeIs('teacher.class.*')) ? 'font-bold' : '' }}">Kelas yang Diajar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('jurnal.index') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('jurnal.*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Jurnal Mengajar"
                            title="Jurnal Mengajar">
                            <i class='bx bx-book-reader text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('jurnal.*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('jurnal.*') ? 'font-bold' : '' }}">Jurnal Mengajar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('gradebook.index') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('gradebook.*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Buku Nilai"
                            title="Buku Nilai">
                            <i class='bx bx-edit text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('gradebook.*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('gradebook.*') ? 'font-bold' : '' }}">Buku Nilai</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pkl.advisor.logbooks') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('pkl.advisor*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Bimbingan PKL"
                            title="Bimbingan PKL">
                            <i class='bx bx-check-shield text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('pkl.advisor*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('pkl.advisor*') ? 'font-bold' : '' }}">Bimbingan PKL</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 2. TUGAS TAMBAHAN: WALI KELAS --}}
            @if($sidebarGuru && $sidebarGuru->is_homeroom)
                <div class="mb-5">
                    <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-emerald-300 uppercase tracking-[0.15em]">
                        WALI KELAS
                    </p>
                    <ul class="space-y-1 stagger-children">
                        <li>
                            <a href="{{ route('homeroom.monitoring') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('homeroom.monitoring')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Monitoring Kelas Binaan"
                                title="Monitoring Kelas Binaan">
                                <i class='bx bx-id-card text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('homeroom.monitoring') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('homeroom.monitoring') ? 'font-bold' : '' }}">Monitoring Kelas Binaan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

            {{-- 3. TUGAS TAMBAHAN: KEPALA PROGRAM KEAHLIAN --}}
            @if($sidebarGuru && $sidebarGuru->is_kaprog)
                <div class="mb-5">
                    <p class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-purple-300 uppercase tracking-[0.15em]">
                        KEPALA PROGRAM KEAHLIAN
                    </p>
                    <ul class="space-y-1 stagger-children">
                        <li>
                            <a href="{{ route('academic.index') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('academic.*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Program Keahlian"
                                title="Program Keahlian">
                                <i class='bx bx-sitemap text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('academic.*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('academic.*') ? 'font-bold' : '' }}">Program Keahlian</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('rooms.assignments') }}"
                                class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('rooms.assignments*')
                                          ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                          : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                                data-tooltip="Rolling Ruangan"
                                title="Rolling Ruangan">
                                <i class='bx bx-transfer text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('rooms.assignments*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                                <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('rooms.assignments*') ? 'font-bold' : '' }}">Rolling Ruangan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

            {{-- 4. PRESENSI GURU --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    PRESENSI GURU
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('absensi.harian') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.harian')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Absen Harian"
                            title="Absen Harian">
                            <i class='bx bx-fingerprint text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('absensi.harian') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('absensi.harian') ? 'font-bold' : '' }}">Absen Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rekapabsensi') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('rekapabsensi')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Rekap Presensi"
                            title="Rekap Presensi">
                            <i class='bx bx-clipboard text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('rekapabsensi') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('rekapabsensi') ? 'font-bold' : '' }}">Rekap Presensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.biometrik') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('admin.biometrik*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Biometrik Wajah"
                            title="Biometrik Wajah">
                            <i class='bx bx-scan text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('admin.biometrik*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('admin.biometrik*') ? 'font-bold' : '' }}">Biometrik Wajah</span>
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
                    PRESENSI SISWA
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('absensi') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Absen Harian"
                            title="Absen Harian">
                            <i class='bx bx-fingerprint text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('absensi') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('absensi') ? 'font-bold' : '' }}">Absen Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('absensi.face.register.show') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.face.register.show')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Registrasi Wajah"
                            title="Registrasi Wajah">
                            <i class='bx bx-scan text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('absensi.face.register.show') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('absensi.face.register.show') ? 'font-bold' : '' }}">Registrasi Wajah</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('izin') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('izin')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Pengajuan Izin"
                            title="Pengajuan Izin">
                            <i class='bx bx-envelope text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('izin') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('izin') ? 'font-bold' : '' }}">Pengajuan Izin</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('riwayatkehadiran') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('riwayatkehadiran')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Riwayat Kehadiran"
                            title="Riwayat Kehadiran">
                            <i class='bx bx-history text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('riwayatkehadiran') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('riwayatkehadiran') ? 'font-bold' : '' }}">Riwayat Kehadiran</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 2. AKADEMIK SISWA --}}
            <div class="mb-5">
                <p class="sidebar-section-title px-3 mb-2 text-[10px] font-bold text-blue-200/70 uppercase tracking-[0.15em]">
                    AKADEMIK SISWA
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('gradebook.parent') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('gradebook.parent*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Nilai Akademik Anak"
                            title="Nilai Akademik Anak">
                            <i class='bx bx-award text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('gradebook.parent*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('gradebook.parent*') ? 'font-bold' : '' }}">Nilai Akademik Anak</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('discipline.student.portal') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('discipline.student*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Kedisiplinan & Prestasi Anak"
                            title="Kedisiplinan & Prestasi Anak">
                            <i class='bx bx-shield-quarter text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('discipline.student*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('discipline.student*') ? 'font-bold' : '' }}">Kedisiplinan & Prestasi Anak</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('siswa.devices') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('siswa.devices*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Perangkat Saya"
                            title="Perangkat Saya">
                            <i class='bx bx-devices text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('siswa.devices*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('siswa.devices*') ? 'font-bold' : '' }}">Perangkat Saya</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('gradebook.student') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('gradebook.student*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Nilai Akademik"
                            title="Nilai Akademik">
                            <i class='bx bx-award text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('gradebook.student*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('gradebook.student*') ? 'font-bold' : '' }}">Nilai Akademik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pkl.student.portal') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('pkl.student*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Portal PKL & Magang"
                            title="Portal PKL & Magang">
                            <i class='bx bx-buildings text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('pkl.student*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('pkl.student*') ? 'font-bold' : '' }}">Portal PKL & Magang</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('discipline.student.portal') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('discipline.student*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Kedisiplinan & Prestasi"
                            title="Kedisiplinan & Prestasi">
                            <i class='bx bx-shield-quarter text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('discipline.student*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('discipline.student*') ? 'font-bold' : '' }}">Kedisiplinan & Prestasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('portfolio.student.index') }}"
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('portfolio.student*')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Portofolio & TeFa"
                            title="Portofolio & TeFa">
                            <i class='bx bx-palette text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('portfolio.student*') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('portfolio.student*') ? 'font-bold' : '' }}">Portofolio & TeFa</span>
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
                            class="sidebar-nav-link group relative flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('riwayatkehadiran')
                                      ? 'active bg-white text-[#003EA7] font-bold shadow-none'
                                      : 'text-blue-100 hover:bg-white/10 hover:text-white' }}"
                            data-tooltip="Riwayat Kehadiran Anak"
                            title="Riwayat Kehadiran Anak">
                            <i class='bx bx-history text-[22px] flex-shrink-0 transition-colors {{ request()->routeIs('riwayatkehadiran') ? "text-[#003EA7]" : "text-blue-100/90 group-hover:text-white" }}'></i>
                            <span class="sidebar-label transition-all duration-200 {{ request()->routeIs('riwayatkehadiran') ? 'font-bold' : '' }}">Riwayat Kehadiran Anak</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </nav>

    </aside>
