@extends('layouts.app')

@section('title', 'Perangkat Terdaftar')
@section('page-title', 'Perangkat Terdaftar')
@section('page-subtitle', 'Kelola perangkat yang memiliki akses ke akun Anda')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Perangkat Saya</li>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Slot Usage Header Banner --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 p-6 relative overflow-hidden bg-gradient-to-r from-primary-900/10 via-dark-50/50 to-white">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-primary-600 text-white flex items-center justify-center text-3xl shadow-lg shadow-primary-500/30 flex-shrink-0">
                        <i class='bx bx-devices'></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-dark-900">Slot Perangkat Akun</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $activeCount >= 2 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' }}">
                                {{ $activeCount }} / 2 Slot Digunakan
                            </span>
                        </div>
                        <p class="text-xs text-dark-500 mt-1 max-w-xl leading-relaxed">
                            Setiap akun siswa dibatasi maksimal <strong>2 perangkat aktif</strong> secara bersamaan. Perangkat pertama yang digunakan otomatis menjadi perangkat utama.
                        </p>
                    </div>
                </div>

                {{-- Slot Visual Indicator --}}
                <div class="flex items-center gap-3 bg-white p-3 rounded-2xl border border-dark-200 shadow-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-3.5 h-3.5 rounded-full {{ $activeCount >= 1 ? 'bg-emerald-500' : 'bg-dark-200' }}"></div>
                        <span class="text-xs font-semibold text-dark-700">Slot 1: {{ $activeCount >= 1 ? 'Terisi' : 'Kosong' }}</span>
                    </div>
                    <span class="text-dark-300">|</span>
                    <div class="flex items-center gap-2">
                        <div class="w-3.5 h-3.5 rounded-full {{ $activeCount >= 2 ? 'bg-emerald-500' : 'bg-dark-200' }}"></div>
                        <span class="text-xs font-semibold text-dark-700">Slot 2: {{ $activeCount >= 2 ? 'Terisi' : 'Kosong' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Device Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($devices as $d)
                @php
                    $isCurrent = !empty($currentDeviceToken) && $currentDeviceToken === $d->device_token;
                    $platformLower = strtolower($d->platform ?? '');
                    $isMobile = str_contains($platformLower, 'android') || str_contains($platformLower, 'ios') || str_contains($platformLower, 'iphone');
                @endphp
                <div class="glass-card rounded-2xl border {{ $d->is_active ? ($isCurrent ? 'border-primary-500 ring-2 ring-primary-500/20' : 'border-dark-200/70') : 'border-dark-200 bg-dark-50/50 opacity-70' }} p-6 relative transition-all">
                    
                    {{-- Header Card --}}
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl {{ $d->is_active ? 'bg-primary-50 text-primary-600' : 'bg-dark-200 text-dark-400' }} flex items-center justify-center text-2xl flex-shrink-0">
                                @if($isMobile)
                                    <i class='bx bx-mobile-alt'></i>
                                @else
                                    <i class='bx bx-laptop'></i>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-dark-900 flex items-center gap-2">
                                    {{ $d->device_name }}
                                </h4>
                                <p class="text-xs text-dark-400">{{ $d->browser }} &bull; {{ $d->platform }}</p>
                            </div>
                        </div>

                        {{-- Badges --}}
                        <div class="flex flex-col items-end gap-1">
                            @if($d->is_primary)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary-100 text-primary-700 border border-primary-200">
                                    Perangkat Utama
                                </span>
                            @endif
                            @if($isCurrent)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                    <i class='bx bx-radio-circle-marked'></i> Perangkat Ini
                                </span>
                            @endif
                            @if(!$d->is_active)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-dark-200 text-dark-600">
                                    Dicabut
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Technical details --}}
                    <div class="space-y-2 py-3 border-y border-dark-100 text-xs text-dark-600">
                        <div class="flex items-center justify-between">
                            <span class="text-dark-400">Didaftarkan:</span>
                            <span class="font-medium text-dark-800">{{ $d->registered_at ? $d->registered_at->format('d M Y, H:i') : '-' }} WIB</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-dark-400">Terakhir Aktif:</span>
                            <span class="font-medium text-dark-800">{{ $d->last_active_at ? $d->last_active_at->diffForHumans() : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-dark-400">Alamat IP:</span>
                            <span class="font-mono text-dark-700">{{ $d->ip_address ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    @if($d->is_active)
                        <div class="mt-4 flex items-center justify-end">
                            <form action="{{ route('siswa.devices.revoke', ['id' => $d->id]) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin melepaskan perangkat ini? Anda harus login dan verifikasi ulang jika ingin menggunakan perangkat ini kembali.')">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                                    <i class='bx bx-log-out-circle text-sm'></i> Lepaskan Perangkat
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="mt-4 text-right">
                            <span class="text-[11px] text-dark-400 italic">Dicabut pada {{ $d->revoked_at ? $d->revoked_at->format('d M Y, H:i') : '-' }}</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-2 glass-card rounded-2xl border border-dark-200/50 p-12 text-center text-dark-400">
                    <i class='bx bx-devices text-5xl mb-2 text-dark-300'></i>
                    <p class="font-medium text-dark-600 text-sm">Belum ada perangkat yang terdaftar.</p>
                    <p class="text-xs text-dark-400 mt-1">Perangkat akan otomatis terdaftar saat Anda berhasil login dan memverifikasi wajah.</p>
                </div>
            @endforelse
        </div>

    </div>
@endsection
