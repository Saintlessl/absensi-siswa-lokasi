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
            <p class="text-primary-300 text-[11px] font-medium tracking-wider uppercase">SMK Negeri</p>
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
        @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
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

                    {{-- Data Jurusan --}}
                    <li>
                        <a href="{{ Route::has('jurusan') ? route('jurusan') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('jurusan.*') || request()->routeIs('jurusan')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('jurusan.*') || request()->routeIs('jurusan')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-layer text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Data Jurusan</span>
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

        @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
            {{-- Pengaturan --}}
            <div class="mb-6">
                <p
                    class="sidebar-section-title px-3 mb-2 text-[10px] font-semibold text-dark-400 uppercase tracking-[0.15em]">
                    Pengaturan
                </p>
                <ul class="space-y-1 stagger-children">
                    @if (Auth::user()->role == 'superadmin')
                        {{-- Manajemen User (Superadmin Only) --}}
                        <li>
                            <a href="{{ Route::has('user') ? route('user') : '#' }}"
                                class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('user.*') || request()->routeIs('user')
                                      ? 'bg-purple-600/20 text-purple-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('user.*') || request()->routeIs('user')
                                            ? 'bg-purple-600/30 text-purple-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bxs-user-account text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Manajemen User</span>
                            </a>
                        </li>

                        {{-- Setting Lokasi (Superadmin Only) --}}
                        <li>
                            <a href="{{ Route::has('lokasi_sekolah') ? route('lokasi_sekolah') : '#' }}"
                                class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('lokasi_sekolah.*') || request()->routeIs('lokasi_sekolah')
                                      ? 'bg-purple-600/20 text-purple-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('lokasi_sekolah.*') || request()->routeIs('lokasi_sekolah')
                                            ? 'bg-purple-600/30 text-purple-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                    <i class='bx bx-map-pin text-lg'></i>
                                </div>
                                <span class="sidebar-label transition-all duration-300">Setting Lokasi</span>
                            </a>
                        </li>
                    @endif

                    {{-- QR Code Kelas --}}
                    <li>
                        <a href="{{ Route::has('absensi.qrcodes') ? route('absensi.qrcodes') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
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
                            <span class="sidebar-label transition-all duration-300">QR Code Kelas</span>
                        </a>
                    </li>

                    {{-- Biometrik Siswa --}}
                    <li>
                        <a href="{{ Route::has('admin.biometrik') ? route('admin.biometrik') : '#' }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('admin.biometrik')
                                  ? 'bg-primary-600/20 text-primary-400 menu-active'
                                  : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                    {{ request()->routeIs('admin.biometrik')
                                        ? 'bg-primary-600/30 text-primary-400'
                                        : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-user-check text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Biometrik Wajah</span>
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
                        <a href="{{ route('absensi.face.register.show') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('absensi.face.register.show')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('absensi.face.register.show')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-scan text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Registrasi Wajah</span>
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
                    <li>
                        <a href="{{ route('siswa.devices') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('siswa.devices*')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('siswa.devices*')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-devices text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Perangkat Saya</span>
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
                    <li>
                        <a href="{{ route('admin.biometrik') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('admin.biometrik*')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('admin.biometrik*')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-user-check text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Biometrik Wajah</span>
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
                    <li>
                        <a href="{{ route('siswa.devices') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('siswa.devices*')
                                      ? 'bg-primary-600/20 text-primary-400 menu-active'
                                      : 'text-dark-300 hover:bg-white/5 hover:text-white' }}">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                                        {{ request()->routeIs('siswa.devices*')
                                            ? 'bg-primary-600/30 text-primary-400'
                                            : 'bg-white/5 text-dark-400 group-hover:bg-white/10 group-hover:text-white' }}">
                                <i class='bx bx-devices text-lg'></i>
                            </div>
                            <span class="sidebar-label transition-all duration-300">Perangkat Saya</span>
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
