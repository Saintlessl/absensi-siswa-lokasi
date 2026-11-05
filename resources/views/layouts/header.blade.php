{{-- Header / Top Navigation Bar --}}
<header class="bg-transparent px-6 sm:px-8 pt-6 pb-4">
    <div class="flex items-center justify-between">

        {{-- Left Side: Hamburger + Page Title --}}
        <div class="flex items-center gap-3">
            {{-- Mobile Hamburger --}}
            <button onclick="toggleSidebar()"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-white/60 focus:outline-none transition-all duration-200 lg:hidden"
                aria-label="Toggle sidebar">
                <i class='bx bx-menu text-2xl'></i>
            </button>

            {{-- Desktop Collapse Toggle --}}
            <button onclick="toggleSidebar()"
                class="hidden lg:inline-flex items-center justify-center w-9 h-9 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-white/60 focus:outline-none transition-all duration-200"
                aria-label="Collapse sidebar">
                <i class='bx bx-menu-alt-left text-xl'></i>
            </button>

            {{-- Page Title --}}
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight leading-tight">
                    @yield('page-title', 'Dashboard')
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 font-normal">
                    @yield('page-subtitle', 'Absensi Siswa & Manajemen Sekolah')
                </p>
            </div>
        </div>

        {{-- Right Side: Notifications + User Menu --}}
        <div class="flex items-center gap-2 sm:gap-3">

            {{-- Search Button --}}
            <button
                class="inline-flex items-center justify-center w-9 h-9 rounded-full text-slate-500 hover:text-slate-800 hover:bg-white/60 focus:outline-none transition-all duration-200"
                title="Pencarian">
                <i class='bx bx-search text-xl'></i>
            </button>

            {{-- Notification Bell --}}
            <button
                class="relative inline-flex items-center justify-center w-9 h-9 rounded-full text-slate-500 hover:text-slate-800 hover:bg-white/60 focus:outline-none transition-all duration-200"
                title="Notifikasi">
                <i class='bx bx-bell text-xl'></i>
            </button>

            {{-- Divider --}}
            <div class="hidden sm:block w-px h-6 bg-slate-300 mx-1"></div>

            {{-- User Dropdown --}}
            <div class="relative">
                <button id="user-dropdown-trigger" onclick="toggleUserDropdown()"
                    class="flex items-center gap-3 py-1 px-1.5 rounded-full hover:bg-white/60 focus:outline-none transition-all duration-200">
                    {{-- Avatar --}}
                    <div
                        class="w-10 h-10 rounded-full bg-[#0052CC] flex items-center justify-center text-white text-lg font-bold shadow-sm flex-shrink-0">
                        @if (Auth::user()->role === 'guru' && Auth::user()->guru && Auth::user()->guru->photo_path && file_exists(public_path('storage/' . Auth::user()->guru->photo_path)))
                            <img src="{{ asset('storage/' . Auth::user()->guru->photo_path) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover rounded-full">
                        @else
                            <i class='bx bxs-user'></i>
                        @endif
                    </div>
                    {{-- User Info --}}
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-bold text-slate-900 leading-tight">
                            {{ Auth::user()->salutation_name ?? Auth::user()->name ?? 'Pengguna' }}
                        </p>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mt-0.5">
                            @if (Auth::user()->role === 'superadmin')
                                SUPERADMIN
                            @elseif (Auth::user()->role === 'admin')
                                ADMIN
                            @elseif (Auth::user()->role === 'kaprog')
                                KAPROG
                            @elseif (Auth::user()->role === 'guru')
                                GURU
                            @elseif (Auth::user()->role === 'siswa')
                                SISWA
                            @elseif (Auth::user()->role === 'orangtua')
                                WALI MURID
                            @else
                                {{ strtoupper(Auth::user()->role) }}
                            @endif
                        </p>
                    </div>
                    {{-- Chevron --}}
                    <i class='bx bx-chevron-down text-slate-400 text-lg hidden sm:block transition-transform duration-200'></i>
                </button>

                {{-- Dropdown Menu --}}
                <div id="user-dropdown"
                    class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50">
                    {{-- Dropdown Header --}}
                    <div class="px-5 py-4 bg-[#0052CC]">
                        <p class="text-white font-bold text-sm">{{ Auth::user()->salutation_name ?? Auth::user()->name ?? 'Pengguna' }}</p>
                        <p class="text-blue-100 text-xs mt-0.5 truncate">{{ Auth::user()->email ?? 'admin@skanic.com' }}</p>
                    </div>

                    {{-- Dropdown Items --}}
                    <div class="py-2">
                        <a href="{{ route('profile') }}"
                            class="flex items-center gap-3 px-5 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors duration-150">
                            <i class='bx bx-user text-lg text-slate-400'></i>
                            <span>Profil Saya</span>
                        </a>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-slate-100"></div>

                    {{-- Logout --}}
                    <div class="py-2">
                        <a href="{{ route('proseslogout') }}"
                            class="flex items-center gap-3 px-5 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                            onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();">
                            <i class='bx bx-log-out text-lg'></i>
                            <span>Keluar</span>
                        </a>
                        <form id="logout-form-header" action="{{ route('proseslogout') }}" method="GET"
                            class="hidden"></form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
