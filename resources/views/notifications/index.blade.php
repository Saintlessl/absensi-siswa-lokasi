@extends('layouts.app')

@section('page-title', 'Pusat Notifikasi')
@section('page-subtitle', 'Kelola pemberitahuan kehadiran, perizinan, dan administrasi akademik')

@section('content')
<div class="space-y-6">

    {{-- Top Action & Filter Bar --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        {{-- Category Pills --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
            @php
                $categories = [
                    'all'            => ['label' => 'Semua', 'icon' => 'bx-layer'],
                    'attendance'     => ['label' => 'Kehadiran', 'icon' => 'bx-fingerprint'],
                    'leave'          => ['label' => 'Perizinan', 'icon' => 'bx-envelope'],
                    'academic'       => ['label' => 'Akademik', 'icon' => 'bx-book-open'],
                    'administrative' => ['label' => 'Administratif', 'icon' => 'bx-shield-quarter'],
                ];
            @endphp
            @foreach($categories as $key => $cat)
                <a href="{{ route('notifications.index', ['category' => $key]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-200
                          {{ $category === $key
                              ? 'bg-[#0052CC] text-white shadow-sm'
                              : 'bg-slate-100/80 text-slate-600 hover:bg-blue-50 hover:text-[#0052CC]' }}">
                    <i class='bx {{ $cat['icon'] }} text-sm'></i>
                    <span>{{ $cat['label'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Bulk Action Buttons --}}
        <div class="flex items-center gap-2.5 flex-shrink-0">
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark_all_read') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-50 text-[#0052CC] hover:bg-blue-100 transition-colors">
                        <i class='bx bx-check-double text-base'></i>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
            @endif

            <form action="{{ route('notifications.clear_read') }}" method="POST" onsubmit="return confirm('Hapus seluruh notifikasi yang sudah dibaca?')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                    <i class='bx bx-trash text-base'></i>
                    <span>Bersihkan yang Sudah Dibaca</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Notification List Container --}}
    <div class="bg-white rounded-3xl shadow-sm border border-blue-50 overflow-hidden">
        @if($notifications->isEmpty())
            <div class="py-16 px-6 text-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <i class='bx bx-bell-off text-3xl'></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak Ada Notifikasi</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    Saat ini belum ada pemberitahuan pada kategori ini. Notifikasi aktivitas terbaru akan muncul di sini secara otomatis.
                </p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($notifications as $notif)
                    @php
                        $data = $notif->data;
                        $catKey = $data['category'] ?? 'academic';
                        $catStyle = match($catKey) {
                            'attendance'     => ['bg' => 'bg-blue-100', 'text' => 'text-[#0052CC]', 'label' => 'Kehadiran'],
                            'leave'          => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'label' => 'Perizinan'],
                            'academic'       => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Akademik'],
                            'administrative' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-800', 'label' => 'Administratif'],
                            default          => ['bg' => 'bg-blue-100', 'text' => 'text-[#0052CC]', 'label' => 'Informasi'],
                        };
                        $isUnread = is_null($notif->read_at);
                    @endphp
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors {{ $isUnread ? 'bg-blue-50/20' : 'hover:bg-slate-50/70' }}">
                        <div class="flex items-start gap-3.5 min-w-0">
                            {{-- Category Icon --}}
                            <div class="w-10 h-10 rounded-xl {{ $catStyle['bg'] }} {{ $catStyle['text'] }} flex items-center justify-center flex-shrink-0 text-xl shadow-xs">
                                <i class='bx {{ $data['icon'] ?? 'bx-bell' }}'></i>
                            </div>

                            {{-- Details --}}
                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md {{ $catStyle['bg'] }} {{ $catStyle['text'] }}">
                                        {{ $catStyle['label'] }}
                                    </span>
                                    <h4 class="text-sm font-bold text-slate-800 {{ $isUnread ? 'text-[#0052CC]' : '' }}">
                                        {{ $data['title'] ?? 'Pemberitahuan' }}
                                    </h4>
                                    @if($isUnread)
                                        <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0" title="Belum dibaca"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed max-w-3xl">
                                    {{ $data['message'] ?? '-' }}
                                </p>
                                <p class="text-[11px] text-slate-400">
                                    <i class='bx bx-time-five align-middle'></i> {{ $notif->created_at->diffForHumans() }} ({{ $notif->created_at->translatedFormat('d M Y, H:i') }})
                                </p>
                            </div>
                        </div>

                        {{-- Action Controls --}}
                        <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                            @if(!empty($data['action_url']))
                                <a href="{{ route('notifications.mark_read', $notif->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#0052CC] text-white hover:bg-[#003FA9] transition-colors shadow-xs">
                                    <span>Buka</span>
                                    <i class='bx bx-link-external text-sm'></i>
                                </a>
                            @elseif($isUnread)
                                <form action="{{ route('notifications.mark_read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 text-[#0052CC] hover:bg-blue-100 transition-colors">
                                        <i class='bx bx-check text-sm'></i>
                                        <span>Tandai Dibaca</span>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                    title="Hapus">
                                    <i class='bx bx-trash text-base'></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination Links --}}
            @if($notifications->hasPages())
                <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
