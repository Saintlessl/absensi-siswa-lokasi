@extends('layouts.app')

@section('is-settings-layout', true)

@section('title', $settingsTitle ?? 'Pengaturan — MySkanic')
@section('page-title', 'Pengaturan')
@section('page-subtitle', $settingsPageSubtitle ?? 'Pusat administrasi, konfigurasi sekolah, dan hak akses MySkanic')

@section('content')
<div class="flex-1 min-h-0 flex flex-col lg:flex-row gap-5 xl:gap-6 items-stretch h-full">

    {{-- =========================================================================
         1. DESKTOP & TABLET STICKY INTERNAL NAVIGATION
         ========================================================================= --}}
    <aside class="hidden md:flex w-64 lg:w-72 xl:w-80 flex-shrink-0 flex-col justify-between h-full sticky top-0 self-start select-none">
        
        {{-- Top Section: User Profile Card & Navigation Menu --}}
        <div class="flex flex-col min-h-0 flex-1">
            
            {{-- Profile Card (Matches Reference Screenshot: White card, Avatar circle, Name, KAPROG) --}}
            <a href="{{ route('settings.profile') }}"
               class="group block bg-white rounded-2xl p-4 shadow-sm border {{ request()->routeIs('settings.profile*') ? 'border-[#003EA7] ring-2 ring-[#003EA7]/20 bg-blue-50/20' : 'border-slate-100/90 hover:border-blue-200' }} transition-all flex-shrink-0 mb-3"
               title="Klik untuk kelola Profil & Keamanan Akun">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-full bg-[#003EA7] text-white flex items-center justify-center text-xl font-bold flex-shrink-0 overflow-hidden shadow-sm group-hover:scale-105 transition-transform">
                        @if(Auth::user()->guru && Auth::user()->guru->photo_path && file_exists(public_path('storage/' . Auth::user()->guru->photo_path)))
                            <img src="{{ asset('storage/' . Auth::user()->guru->photo_path) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                        @elseif(Auth::user()->siswa && Auth::user()->siswa->photo_path && file_exists(public_path('storage/' . Auth::user()->siswa->photo_path)))
                            <img src="{{ asset('storage/' . Auth::user()->siswa->photo_path) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                        @else
                            <i class='bx bxs-user text-2xl'></i>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-bold text-slate-900 truncate group-hover:text-[#003EA7] transition-colors" title="{{ Auth::user()->salutation_name ?? Auth::user()->name }}">
                            {{ Auth::user()->salutation_name ?? Auth::user()->name }}
                        </h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">
                            {{ strtoupper(Auth::user()->roleRel->display_name ?? Auth::user()->role) }}
                        </p>
                    </div>
                </div>
            </a>

            {{-- Navigation Menu List (Sticky, sleek scroll if vertical viewport is compact) --}}
            <nav class="flex-1 min-h-0 overflow-y-auto settings-nav-scroll space-y-1.5 pr-1 py-1" aria-label="Internal Pengaturan">
                
                {{-- 1. Home --}}
                @php $isHome = request()->routeIs('settings.home'); @endphp
                <a href="{{ route('settings.home') }}"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $isHome ? 'bg-[#003EA7] text-white shadow-md shadow-blue-900/15 font-semibold' : 'text-slate-800 hover:bg-white/60 font-semibold group' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-transform {{ $isHome ? 'bg-white/20 text-white' : 'bg-[#003EA7] text-white group-hover:scale-105 shadow-xs' }}">
                            <i class='bx bxs-home'></i>
                        </div>
                        <span class="text-sm {{ $isHome ? 'text-white font-bold' : 'text-slate-800 group-hover:text-slate-900' }}">Home</span>
                    </div>
                </a>

                {{-- 2. Jam Absensi --}}
                @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.jam_sekolah'))
                @php $isSchool = request()->routeIs('settings.school*'); @endphp
                <a href="{{ route('settings.school') }}"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $isSchool ? 'bg-[#003EA7] text-white shadow-md shadow-blue-900/15 font-semibold' : 'text-slate-800 hover:bg-white/60 font-semibold group' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-transform {{ $isSchool ? 'bg-white/20 text-white' : 'bg-[#003EA7] text-white group-hover:scale-105 shadow-xs' }}">
                            <i class='bx bxs-school'></i>
                        </div>
                        <span class="text-sm {{ $isSchool ? 'text-white font-bold' : 'text-slate-800 group-hover:text-slate-900' }}">Jam Absensi</span>
                    </div>
                </a>
                @endif

                {{-- 3. Kalender Akademik --}}
                @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.kalender'))
                @php $isCal = request()->routeIs('settings.calendar*') || request()->routeIs('academic_calendar.*'); @endphp
                <a href="{{ route('settings.calendar') }}"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $isCal ? 'bg-[#003EA7] text-white shadow-md shadow-blue-900/15 font-semibold' : 'text-slate-800 hover:bg-white/60 font-semibold group' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-transform {{ $isCal ? 'bg-white/20 text-white' : 'bg-[#003EA7] text-white group-hover:scale-105 shadow-xs' }}">
                            <i class='bx bxs-calendar'></i>
                        </div>
                        <span class="text-sm {{ $isCal ? 'text-white font-bold' : 'text-slate-800 group-hover:text-slate-900' }}">Kalender Akademik</span>
                    </div>
                </a>
                @endif

                {{-- 4. Lokasi Sekolah --}}
                @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.lokasi'))
                @php $isLoc = request()->routeIs('settings.location*') || request()->routeIs('lokasi_sekolah*'); @endphp
                <a href="{{ route('settings.location') }}"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $isLoc ? 'bg-[#003EA7] text-white shadow-md shadow-blue-900/15 font-semibold' : 'text-slate-800 hover:bg-white/60 font-semibold group' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-transform {{ $isLoc ? 'bg-white/20 text-white' : 'bg-[#003EA7] text-white group-hover:scale-105 shadow-xs' }}">
                            <i class='bx bxs-map'></i>
                        </div>
                        <span class="text-sm {{ $isLoc ? 'text-white font-bold' : 'text-slate-800 group-hover:text-slate-900' }}">Lokasi Sekolah</span>
                    </div>
                </a>
                @endif

                {{-- 5. Persetujuan Izin --}}
                @if(Auth::user()->hasRole(['superadmin', 'admin', 'kaprog', 'guru']) || Auth::user()->hasPermission('izin.view_siswa') || Auth::user()->hasPermission('izin.view_guru'))
                @php 
                    $isIzin = request()->routeIs('settings.izin.approval*') || request()->routeIs('izin.approval*');
                    $pendingBadgeCount = \App\Models\Izin::where('status', 'pending')->count();
                @endphp
                <a href="{{ route('settings.izin.approval') }}"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $isIzin ? 'bg-[#003EA7] text-white shadow-md shadow-blue-900/15 font-semibold' : 'text-slate-800 hover:bg-white/60 font-semibold group' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-transform {{ $isIzin ? 'bg-white/20 text-white' : 'bg-[#003EA7] text-white group-hover:scale-105 shadow-xs' }}">
                            <i class='bx bxs-badge-check'></i>
                        </div>
                        <span class="text-sm {{ $isIzin ? 'text-white font-bold' : 'text-slate-800 group-hover:text-slate-900' }}">Persetujuan Izin</span>
                    </div>
                    @if($pendingBadgeCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $isIzin ? 'bg-white text-[#003EA7]' : 'bg-amber-500 text-white' }}">
                            {{ $pendingBadgeCount }}
                        </span>
                    @endif
                </a>
                @endif

                {{-- 6. Data Ruangan --}}
                @if(Auth::user()->hasRole(['superadmin', 'admin', 'kaprog']) || Auth::user()->hasPermission('pengaturan.ruangan'))
                @php $isRoom = request()->routeIs('settings.rooms*') || request()->routeIs('rooms.*'); @endphp
                <a href="{{ route('settings.rooms') }}"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $isRoom ? 'bg-[#003EA7] text-white shadow-md shadow-blue-900/15 font-semibold' : 'text-slate-800 hover:bg-white/60 font-semibold group' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-transform {{ $isRoom ? 'bg-white/20 text-white' : 'bg-[#003EA7] text-white group-hover:scale-105 shadow-xs' }}">
                            <i class='bx bxs-building-house'></i>
                        </div>
                        <span class="text-sm {{ $isRoom ? 'text-white font-bold' : 'text-slate-800 group-hover:text-slate-900' }}">Data Ruangan</span>
                    </div>
                </a>
                @endif

                {{-- 7. Manajemen Pengguna --}}
                @if(Auth::user()->hasRole('superadmin') || Auth::user()->hasPermission('pengaturan.pengguna'))
                @php $isUser = request()->routeIs('settings.users*') || request()->routeIs('user*'); @endphp
                <a href="{{ route('settings.users') }}"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $isUser ? 'bg-[#003EA7] text-white shadow-md shadow-blue-900/15 font-semibold' : 'text-slate-800 hover:bg-white/60 font-semibold group' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-transform {{ $isUser ? 'bg-white/20 text-white' : 'bg-[#003EA7] text-white group-hover:scale-105 shadow-xs' }}">
                            <i class='bx bxs-user-detail'></i>
                        </div>
                        <span class="text-sm {{ $isUser ? 'text-white font-bold' : 'text-slate-800 group-hover:text-slate-900' }}">Manajemen Pengguna</span>
                    </div>
                </a>
                @endif

                {{-- 8. Manajemen Role --}}
                @if(Auth::user()->hasRole('superadmin') || Auth::user()->hasPermission('pengaturan.role'))
                @php $isRole = request()->routeIs('settings.roles*'); @endphp
                <a href="{{ route('settings.roles.index') }}"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $isRole ? 'bg-[#003EA7] text-white shadow-md shadow-blue-900/15 font-semibold' : 'text-slate-800 hover:bg-white/60 font-semibold group' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-transform {{ $isRole ? 'bg-white/20 text-white' : 'bg-[#003EA7] text-white group-hover:scale-105 shadow-xs' }}">
                            <i class='bx bxs-shield-quarter'></i>
                        </div>
                        <span class="text-sm {{ $isRole ? 'text-white font-bold' : 'text-slate-800 group-hover:text-slate-900' }}">Manajemen Role</span>
                    </div>
                </a>
                @endif

            </nav>
        </div>

        {{-- Bottom Section: Keluar Button (Anchored at Bottom, matches Reference Screenshot) --}}
        <div class="pt-3 border-t border-blue-200/40 flex-shrink-0">
            <form action="{{ route('proseslogout') }}" method="POST" class="w-full m-0">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-rose-600 hover:bg-rose-50/80 transition-all duration-150 group">
                    <div class="w-8 h-8 rounded-lg bg-rose-600 text-white flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform shadow-xs">
                        <i class='bx bx-log-out'></i>
                    </div>
                    <span class="text-sm font-bold text-rose-600">Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    {{-- =========================================================================
         2. MOBILE RESPONSIVE NAVIGATION (FOR SCREENS < MD)
         ========================================================================= --}}
    <div class="md:hidden flex flex-col gap-2.5 mb-2 flex-shrink-0" x-data="{ mobileNavOpen: false }">
        {{-- Mobile Compact Profile Bar --}}
        <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-full bg-[#003EA7] text-white flex items-center justify-center text-lg font-bold flex-shrink-0 overflow-hidden">
                    @if(Auth::user()->guru && Auth::user()->guru->photo_path && file_exists(public_path('storage/' . Auth::user()->guru->photo_path)))
                        <img src="{{ asset('storage/' . Auth::user()->guru->photo_path) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                    @elseif(Auth::user()->siswa && Auth::user()->siswa->photo_path && file_exists(public_path('storage/' . Auth::user()->siswa->photo_path)))
                        <img src="{{ asset('storage/' . Auth::user()->siswa->photo_path) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                    @else
                        <i class='bx bxs-user'></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <h4 class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->salutation_name ?? Auth::user()->name }}</h4>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ strtoupper(Auth::user()->roleRel->display_name ?? Auth::user()->role) }}</p>
                </div>
            </div>
            
            <button @click="mobileNavOpen = !mobileNavOpen" type="button"
                    class="px-3 py-1.5 rounded-xl bg-blue-50 text-[#003EA7] text-xs font-bold flex items-center gap-1.5 focus:outline-none">
                <span>Menu</span>
                <i class='bx' :class="mobileNavOpen ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
            </button>
        </div>

        {{-- Mobile Horizontal Quick Tab Bar --}}
        <div class="flex items-center gap-2 overflow-x-auto py-1 no-scrollbar select-none">
            <a href="{{ route('settings.home') }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('settings.home') ? 'bg-[#003EA7] text-white shadow-xs' : 'bg-white/80 text-slate-700 hover:bg-white' }}">
                <i class='bx bxs-home'></i> <span>Home</span>
            </a>
            @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.jam_sekolah'))
            <a href="{{ route('settings.school') }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('settings.school*') ? 'bg-[#003EA7] text-white shadow-xs' : 'bg-white/80 text-slate-700 hover:bg-white' }}">
                <i class='bx bxs-school'></i> <span>Jam Absensi</span>
            </a>
            @endif
            @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.kalender'))
            <a href="{{ route('settings.calendar') }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('settings.calendar*') || request()->routeIs('academic_calendar.*') ? 'bg-[#003EA7] text-white shadow-xs' : 'bg-white/80 text-slate-700 hover:bg-white' }}">
                <i class='bx bxs-calendar'></i> <span>Kalender</span>
            </a>
            @endif
            @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.lokasi'))
            <a href="{{ route('settings.location') }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('settings.location*') || request()->routeIs('lokasi_sekolah*') ? 'bg-[#003EA7] text-white shadow-xs' : 'bg-white/80 text-slate-700 hover:bg-white' }}">
                <i class='bx bxs-map'></i> <span>Lokasi</span>
            </a>
            @endif
            @if(Auth::user()->hasRole(['superadmin', 'admin', 'kaprog', 'guru']) || Auth::user()->hasPermission('izin.view_siswa') || Auth::user()->hasPermission('izin.view_guru'))
            <a href="{{ route('settings.izin.approval') }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('settings.izin.approval*') || request()->routeIs('izin.approval*') ? 'bg-[#003EA7] text-white shadow-xs' : 'bg-white/80 text-slate-700 hover:bg-white' }}">
                <i class='bx bxs-badge-check'></i> <span>Izin</span>
            </a>
            @endif
        </div>

        {{-- Mobile Full Menu Collapsible Drawer --}}
        <div x-show="mobileNavOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="bg-white rounded-2xl p-4 shadow-lg border border-blue-100 space-y-2">
            
            <a href="{{ route('settings.home') }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs font-bold {{ request()->routeIs('settings.home') ? 'bg-[#003EA7] text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class='bx bxs-home text-base'></i> Home
            </a>
            @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.jam_sekolah'))
            <a href="{{ route('settings.school') }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs font-bold {{ request()->routeIs('settings.school*') ? 'bg-[#003EA7] text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class='bx bxs-school text-base'></i> Jam Absensi
            </a>
            @endif
            @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.kalender'))
            <a href="{{ route('settings.calendar') }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs font-bold {{ request()->routeIs('settings.calendar*') ? 'bg-[#003EA7] text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class='bx bxs-calendar text-base'></i> Kalender Akademik
            </a>
            @endif
            @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.lokasi'))
            <a href="{{ route('settings.location') }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs font-bold {{ request()->routeIs('settings.location*') ? 'bg-[#003EA7] text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class='bx bxs-map text-base'></i> Lokasi Sekolah
            </a>
            @endif
            @if(Auth::user()->hasRole(['superadmin', 'admin', 'kaprog', 'guru']) || Auth::user()->hasPermission('izin.view_siswa') || Auth::user()->hasPermission('izin.view_guru'))
            <a href="{{ route('settings.izin.approval') }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs font-bold {{ request()->routeIs('settings.izin.approval*') ? 'bg-[#003EA7] text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class='bx bxs-badge-check text-base'></i> Persetujuan Izin
            </a>
            @endif
            @if(Auth::user()->hasRole(['superadmin', 'admin', 'kaprog']) || Auth::user()->hasPermission('pengaturan.ruangan'))
            <a href="{{ route('settings.rooms') }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs font-bold {{ request()->routeIs('settings.rooms*') ? 'bg-[#003EA7] text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class='bx bxs-building-house text-base'></i> Data Ruangan
            </a>
            @endif
            @if(Auth::user()->hasRole('superadmin') || Auth::user()->hasPermission('pengaturan.pengguna'))
            <a href="{{ route('settings.users') }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs font-bold {{ request()->routeIs('settings.users*') ? 'bg-[#003EA7] text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class='bx bxs-user-detail text-base'></i> Manajemen Pengguna
            </a>
            @endif
            @if(Auth::user()->hasRole('superadmin') || Auth::user()->hasPermission('pengaturan.role'))
            <a href="{{ route('settings.roles.index') }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs font-bold {{ request()->routeIs('settings.roles*') ? 'bg-[#003EA7] text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class='bx bxs-shield-quarter text-base'></i> Manajemen Role
            </a>
            @endif
            <a href="{{ route('settings.profile') }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs font-bold {{ request()->routeIs('settings.profile*') ? 'bg-[#003EA7] text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                <i class='bx bxs-user-circle text-base'></i> Profil & Keamanan Akun
            </a>

            <div class="pt-2 border-t border-slate-100">
                <form action="{{ route('proseslogout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 p-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50">
                        <i class='bx bx-log-out text-base'></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         3. RIGHT COLUMN: INDEPENDENT SCROLLING CONTENT CONTAINER
         ========================================================================= --}}
    <div class="flex-1 min-w-0 flex flex-col h-full lg:overflow-hidden">
        
        {{-- Rounded Surface Container (#EFF5FF with white cards inside, matching reference screenshot) --}}
        <div class="bg-[#EFF5FF] rounded-[24px] sm:rounded-[32px] p-4 sm:p-6 lg:p-7 border border-white/80 shadow-sm flex-1 min-h-0 flex flex-col overflow-hidden">
            
            {{-- Unified Header of Current Subpage (Title, Subtitle, and Optional Action Buttons) --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 pb-3 border-b border-blue-200/50 flex-shrink-0">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        {{ $settingsPageTitle ?? 'Home' }}
                    </h2>
                    @if(isset($settingsPageSubtitle))
                        <p class="text-xs text-slate-500 mt-0.5 font-normal">
                            {{ $settingsPageSubtitle }}
                        </p>
                    @endif
                </div>

                @hasSection('settings-header-actions')
                    <div class="flex-shrink-0">
                        @yield('settings-header-actions')
                    </div>
                @endif
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <i class='bx bx-check-circle text-lg text-emerald-600'></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-base">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs font-semibold flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <i class='bx bx-error-circle text-lg text-rose-600'></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-base">&times;</button>
                </div>
            @endif

            {{-- INDEPENDENT SCROLLABLE SUBPAGE CONTENT CONTAINER --}}
            <div class="flex-1 min-h-0 overflow-y-auto pr-1 sm:pr-2 settings-scroll">
                @yield('settings-content')
            </div>

        </div>
    </div>

</div>
@endsection
