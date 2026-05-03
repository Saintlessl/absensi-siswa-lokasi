{{-- Header / Top Navigation Bar --}}
<header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-dark-200/60 shadow-sm shadow-dark-900/5">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">

        {{-- Left Side: Hamburger + Page Title --}}
        <div class="flex items-center gap-3">
            {{-- Mobile Hamburger --}}
            <button onclick="toggleSidebar()"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-dark-500 hover:text-dark-700 hover:bg-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500/30 transition-all duration-200 lg:hidden"
                aria-label="Toggle sidebar">
                <i class='bx bx-menu text-2xl'></i>
            </button>

            {{-- Desktop Collapse Toggle --}}
            <button onclick="toggleSidebar()"
                class="hidden lg:inline-flex items-center justify-center w-10 h-10 rounded-xl text-dark-400 hover:text-dark-600 hover:bg-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500/30 transition-all duration-200"
                aria-label="Collapse sidebar">
                <i class='bx bx-menu-alt-left text-xl'></i>
            </button>

            {{-- Page Title --}}
            <div class="hidden sm:block">
                <h2 class="text-lg font-semibold text-dark-800 tracking-tight">
                    @yield('page-title', 'Dashboard')
                </h2>
                @hasSection('page-subtitle')
                    <p class="text-xs text-dark-400 -mt-0.5">@yield('page-subtitle')</p>
                @endif
            </div>
        </div>

        {{-- Right Side: Notifications + User Menu --}}
        <div class="flex items-center gap-2 sm:gap-3">

            {{-- Search Button --}}
            <button
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-dark-400 hover:text-dark-600 hover:bg-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500/30 transition-all duration-200"
                title="Pencarian">
                <i class='bx bx-search text-xl'></i>
            </button>

            {{-- Notification Bell --}}
            <button
                class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl text-dark-400 hover:text-dark-600 hover:bg-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500/30 transition-all duration-200"
                title="Notifikasi">
                <i class='bx bx-bell text-xl'></i>
                {{-- Notification Badge --}}
                {{-- <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white pulse-ring"></span> --}}
            </button>

            {{-- Divider --}}
            <div class="hidden sm:block w-px h-8 bg-dark-200"></div>

            {{-- User Dropdown --}}
            <div class="relative">
                <button id="user-dropdown-trigger" onclick="toggleUserDropdown()"
                    class="flex items-center gap-2.5 px-2 py-1.5 rounded-xl hover:bg-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500/30 transition-all duration-200">
                    {{-- Avatar --}}
                    <div
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-md shadow-primary-500/20">
                        <span class="text-white text-sm font-bold">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </span>
                    </div>
                    {{-- User Info --}}
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-semibold text-dark-700 leading-tight">
                            {{ Auth::user()->name ?? 'Admin' }}
                        </p>
                        <p class="text-[11px] text-dark-400 leading-tight">{{ Auth::user()->role ?? 'Administrator' }}
                        </p>
                    </div>
                    {{-- Chevron --}}
                    <i
                        class='bx bx-chevron-down text-dark-400 text-lg hidden sm:block transition-transform duration-200'></i>
                </button>

                {{-- Dropdown Menu --}}
                <div id="user-dropdown"
                    class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl shadow-dark-900/10 border border-dark-100 overflow-hidden">
                    {{-- Dropdown Header --}}
                    <div class="px-5 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                        <p class="text-white font-semibold text-sm">{{ Auth::user()->name ?? 'Admin' }}</p>
                        <p class="text-primary-200 text-xs mt-0.5">{{ Auth::user()->email ?? 'admin@spk.com' }}</p>
                    </div>

                    {{-- Dropdown Items --}}
                    <div class="py-2">
                        <a href="{{ route('profile') }}"
                            class="flex items-center gap-3 px-5 py-2.5 text-sm text-dark-600 hover:bg-dark-50 hover:text-dark-800 transition-colors duration-150">
                            <i class='bx bx-user text-lg text-dark-400'></i>
                            <span>Profil Saya</span>
                        </a>

                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-dark-100"></div>

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

    {{-- Mobile Page Title (shown below header row on small screens) --}}
    <div class="sm:hidden border-t border-dark-100 px-4 py-2">
        <h2 class="text-sm font-semibold text-dark-700">@yield('page-title', 'Dashboard')</h2>
    </div>
</header>
