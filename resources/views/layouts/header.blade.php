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
            @php
                $resolvedHeaderTitle = View::hasSection('page-title')
                    ? trim(View::yieldContent('page-title'))
                    : (View::hasSection('title') ? trim(preg_replace('/(\s*[\x{2014}-]\s*MySkanic|\s*[\x{2014}-]\s*SMK Neg.*)/iu', '', View::yieldContent('title'))) : (request()->routeIs('dashboard') ? 'Dashboard' : ''));
                if (empty($resolvedHeaderTitle)) {
                    $resolvedHeaderTitle = request()->routeIs('dashboard') ? 'Dashboard' : 'Halaman';
                }

                $resolvedHeaderSubtitle = View::hasSection('page-subtitle')
                    ? trim(View::yieldContent('page-subtitle'))
                    : (request()->routeIs('dashboard') ? 'Absensi Siswa & Manajemen Sekolah' : '');
            @endphp
            <div>
                <h2 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight leading-tight">
                    {!! $resolvedHeaderTitle !!}
                </h2>
                @if(!empty($resolvedHeaderSubtitle))
                    <p class="text-xs text-slate-500 mt-0.5 font-normal">
                        {!! $resolvedHeaderSubtitle !!}
                    </p>
                @endif
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

            {{-- Notification Bell with Centralized Popover --}}
            <div x-data="headerNotificationCenter()" x-init="init()" class="relative" @click.outside="open = false">
                <button @click="open = !open" type="button"
                    class="relative inline-flex items-center justify-center w-9 h-9 rounded-full text-slate-500 hover:text-slate-800 hover:bg-white/60 focus:outline-none transition-all duration-200"
                    title="Notifikasi" :class="open ? 'bg-white shadow-sm text-[#0052CC]' : ''">
                    <i class='bx bx-bell text-xl'></i>
                    <template x-if="unreadCount > 0">
                        <span
                            class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white animate-pulse"
                            x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                    </template>
                </button>

                {{-- Notification Dropdown Card --}}
                <div x-show="open" x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                    class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-blue-100/80 z-50 overflow-hidden divide-y divide-slate-100">

                    {{-- Popover Header --}}
                    <div class="px-4 py-3 bg-[#F8FAFC] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Notifikasi</h3>
                            <template x-if="unreadCount > 0">
                                <span class="text-[10px] font-semibold bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full"
                                    x-text="unreadCount + ' Baru'"></span>
                            </template>
                        </div>
                        <template x-if="unreadCount > 0">
                            <button @click="markAllAsRead()" type="button"
                                class="text-[11px] font-semibold text-[#0052CC] hover:text-[#003FA9] hover:underline focus:outline-none">
                                Tandai Dibaca
                            </button>
                        </template>
                    </div>

                    {{-- Popover Content List --}}
                    <div class="max-h-[360px] overflow-y-auto divide-y divide-slate-50">
                        <template x-if="loading && items.length === 0">
                            <div class="py-8 text-center text-xs text-slate-400">
                                <i class='bx bx-loader-alt bx-spin text-lg mb-1 block'></i> Memuat notifikasi...
                            </div>
                        </template>

                        <template x-if="!loading && items.length === 0">
                            <div class="py-8 px-4 text-center">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-2">
                                    <i class='bx bx-bell-off text-xl'></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">Belum Ada Notifikasi</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Semua pemberitahuan penting akan muncul di sini.</p>
                            </div>
                        </template>

                        <template x-for="item in items" :key="item.id">
                            <div @click="openItem(item)"
                                class="p-3.5 flex items-start gap-3 hover:bg-blue-50/50 cursor-pointer transition-colors relative"
                                :class="!item.read_at ? 'bg-blue-50/20' : ''">
                                {{-- Icon by Category --}}
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-base"
                                    :class="{
                                        'bg-blue-100 text-[#0052CC]': item.category === 'attendance',
                                        'bg-amber-100 text-amber-700': item.category === 'leave',
                                        'bg-purple-100 text-purple-700': item.category === 'academic',
                                        'bg-slate-100 text-slate-700': item.category === 'administrative',
                                    }">
                                    <i :class="'bx ' + (item.icon || 'bx-bell')"></i>
                                </div>

                                {{-- Text Content --}}
                                <div class="flex-1 min-w-0 pr-2">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <p class="text-xs font-bold text-slate-800 truncate" x-text="item.title"></p>
                                        <span class="text-[10px] text-slate-400 whitespace-nowrap flex-shrink-0" x-text="item.time_ago"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                                </div>

                                {{-- Unread Dot --}}
                                <template x-if="!item.read_at">
                                    <span class="w-2 h-2 rounded-full bg-[#0052CC] flex-shrink-0 mt-1.5"></span>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Popover Footer --}}
                    <div class="p-2.5 bg-[#F8FAFC] text-center">
                        <a href="{{ route('notifications.index') }}"
                            class="inline-flex items-center justify-center gap-1.5 w-full py-1.5 px-3 rounded-xl text-xs font-semibold text-[#0052CC] hover:bg-blue-100/60 transition-colors">
                            <span>Lihat Semua Notifikasi</span>
                            <i class='bx bx-right-arrow-alt text-base'></i>
                        </a>
                    </div>
                </div>
            </div>

            <script>
                function headerNotificationCenter() {
                    return {
                        open: false,
                        unreadCount: 0,
                        items: [],
                        loading: false,
                        pollingTimer: null,
                        init() {
                            this.fetchNotifications();
                            // Polling every 30 seconds
                            this.pollingTimer = setInterval(() => {
                                if (document.visibilityState === 'visible') {
                                    this.fetchNotifications(true);
                                }
                            }, 30000);
                        },
                        fetchNotifications(silent = false) {
                            if (!silent) this.loading = true;
                            fetch('{{ route('notifications.unread') }}', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                this.unreadCount = data.unread_count || 0;
                                this.items = data.notifications || [];
                                this.loading = false;
                            })
                            .catch(() => {
                                this.loading = false;
                            });
                        },
                        markAllAsRead() {
                            fetch('{{ route('notifications.mark_all_read') }}', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(() => {
                                this.unreadCount = 0;
                                this.items.forEach(i => i.read_at = new Date().toISOString());
                            });
                        },
                        openItem(item) {
                            if (!item.read_at) {
                                fetch(`/notifikasi/${item.id}/mark-read`, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                });
                                item.read_at = new Date().toISOString();
                                if (this.unreadCount > 0) this.unreadCount--;
                            }
                            if (item.action_url) {
                                window.location.href = item.action_url;
                            }
                        }
                    }
                }
            </script>

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
                        <form id="logout-form-header" action="{{ route('proseslogout') }}" method="POST" class="hidden">@csrf</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
