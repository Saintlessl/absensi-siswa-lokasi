{{-- Sidebar --}}
<aside id="sidebar"
    class="fixed top-0 left-0 z-50 h-screen lg:w-[272px] w-[272px] -translate-x-full lg:translate-x-0 sidebar-transition sidebar-gradient shadow-2xl shadow-dark-900/30 flex flex-col">

    {{-- Logo Section --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-xl bg-white from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/30">
            <img src="{{ asset('img/logo.png') }}" width="100px;" alt="">
        </div>
        <div class="sidebar-logo-text overflow-hidden transition-all duration-300">
            <h1 class="text-white font-bold text-base leading-tight tracking-tight">Absensi Siswa</h1>
            <p class="text-primary-300 text-[11px] font-medium tracking-wider uppercase">SMP IT Yapura</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 sidebar-scroll overflow-y-auto">

        {{-- Main Menu --}}
        <div class="mb-6">
            <p
                class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                Menu Utama
            </p>
            <ul class="space-y-1 stagger-children">
                {{-- Dashboard --}}
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
        @if (Auth::user()->role == 'admin')
            {{-- Data Master --}}
            <div class="mb-6">
                <p
                    class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    Data Master
                </p>
                <ul class="space-y-1 stagger-children">
                    {{-- Data Siswa --}}
                    <li>
                        <a href="{{ Route::has('siswa') ? route('siswa') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('siswa.*')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('siswa.*')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-list-check text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Data Siswa</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ Route::has('kelas') ? route('kelas') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('kelas.*')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('kelas.*')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-list-check text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Data Kelas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ Route::has('guru') ? route('guru') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('guru.*')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('guru.*')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-list-check text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Data Guru</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ Route::has('orangtua') ? route('orangtua') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('orangtua.*') || request()->routeIs('orangtua')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('orangtua.*') || request()->routeIs('orangtua')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-user-pin text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Data Orang Tua</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ Route::has('jadwal') ? route('jadwal') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('jadwal.*')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('jadwal.*')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-list-check text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Jadwal Pelajaran</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        @if (Auth::user()->role == 'admin')
            {{-- Pengaturan --}}
            <div class="mb-6">
                <p
                    class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    Pengaturan
                </p>
                <ul class="space-y-1 stagger-children">
                    {{-- Manajemen User --}}
                    <li>
                        <a href="{{ Route::has('user') ? route('user') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('user.*') || request()->routeIs('user')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('user.*') || request()->routeIs('user')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bxs-user-account text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Manajemen User</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ Route::has('lokasi_sekolah') ? route('lokasi_sekolah') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('lokasi_sekolah.*') || request()->routeIs('lokasi_sekolah')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('lokasi_sekolah.*') || request()->routeIs('lokasi_sekolah')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-map-pin text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Setting Lokasi</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        @if (Auth::user()->role == 'siswa')
            <div class="mb-6">
                <p
                    class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    Kehadiran
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('absensi') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('absensi')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-fingerprint text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Absen Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('izin') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('izin')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('izin')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-envelope text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Pengajuan Izin</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('riwayatkehadiran') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('riwayatkehadiran')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('riwayatkehadiran')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-history text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Riwayat Kehadiran</span>
                        </a>
                    </li>

                </ul>
            </div>
        @endif

        @if (Auth::user()->role == 'guru')
            <div class="mb-6">

                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('absensi.harian') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.harian')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('absensi.harian')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-fingerprint text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Absen Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rekapabsensi') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('rekapabsensi')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('rekapabsensi')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-fingerprint text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Rekap Absensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rekapkelas') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('rekapkelas')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('rekapkelas')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-fingerprint text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Rekap Absensi PerKelas</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif
        @if (Auth::user()->role == 'orangtua')
            <div class="mb-6">
                <p
                    class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    Kehadiran
                </p>
                <ul class="space-y-1 stagger-children">
                    <li>
                        <a href="{{ route('absensi') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('absensi')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-fingerprint text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Absen Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('riwayatkehadiran') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('riwayatkehadiran')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('riwayatkehadiran')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-history text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Riwayat Kehadiran</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </nav>

    {{-- Sidebar Footer: User Info --}}
    <div class="border-t border-white/10 px-4 py-4">
        <div class="flex items-center gap-3">
            <div
                class="flex-shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br from-accent-500 to-accent-700 flex items-center justify-center shadow-lg shadow-accent-500/20">
                <span class="text-white text-sm font-bold">
                    {{ strtoupper(substr(Auth::user()->username ?? 'A', 0, 1)) }}
                </span>
            </div>
            <div class="sidebar-logo-text overflow-hidden flex-1 min-w-0 transition-all duration-300">
                <p class="text-white text-sm font-semibold truncate">{{ Auth::user()->username ?? 'Admin' }}</p>
                <p class="text-dark-400 text-[11px] truncate">{{ Auth::user()->email ?? 'admin@spk.com' }}</p>
            </div>
            <a href="{{ route('proseslogout') }}"
                class="sidebar-logo-text flex-shrink-0 w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500/20 flex items-center justify-center text-red-400 hover:text-red-300 transition-all duration-200"
                title="Logout">
                <i class='bx bx-log-out text-lg'></i>
            </a>
        </div>
    </div>
</aside>
