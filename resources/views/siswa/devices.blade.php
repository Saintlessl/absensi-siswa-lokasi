@extends('layouts.app')

@section('title', 'Perangkat Terdaftar')
@section('page-title', 'Perangkat Terdaftar')
@section('page-subtitle', 'Kelola perangkat yang memiliki akses ke akun Anda')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Perangkat Saya</li>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Slot Usage Header Banner --}}
        <div class="bg-white rounded-[28px] p-6 sm:p-7 shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-[#003FA9] text-white flex items-center justify-center text-3xl shadow-md shadow-blue-900/10 flex-shrink-0">
                        <i class='bx bx-devices'></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight">Slot Perangkat Akun</h3>
                            <span class="px-3 py-0.5 rounded-lg text-xs font-bold {{ $activeCount >= 2 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $activeCount }} / 2 Slot Digunakan
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 max-w-xl leading-relaxed">
                            Setiap akun siswa dibatasi maksimal <strong>2 perangkat aktif</strong> secara bersamaan. Perangkat pertama yang digunakan otomatis menjadi perangkat utama.
                        </p>
                    </div>
                </div>

                {{-- Slot Visual Indicator --}}
                <div class="flex items-center gap-3 bg-[#EFF5FF] p-3.5 rounded-2xl">
                    <div class="flex items-center gap-2">
                        <div class="w-3.5 h-3.5 rounded-full {{ $activeCount >= 1 ? 'bg-emerald-600' : 'bg-slate-300' }}"></div>
                        <span class="text-xs font-bold text-slate-700">Slot 1: {{ $activeCount >= 1 ? 'Terisi' : 'Kosong' }}</span>
                    </div>
                    <span class="text-slate-300">|</span>
                    <div class="flex items-center gap-2">
                        <div class="w-3.5 h-3.5 rounded-full {{ $activeCount >= 2 ? 'bg-emerald-600' : 'bg-slate-300' }}"></div>
                        <span class="text-xs font-bold text-slate-700">Slot 2: {{ $activeCount >= 2 ? 'Terisi' : 'Kosong' }}</span>
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
                <div class="bg-white rounded-[28px] p-6 sm:p-7 shadow-sm border {{ $d->is_active ? ($isCurrent ? 'border-[#003FA9] ring-2 ring-blue-500/20' : 'border-slate-100') : 'border-slate-100 bg-slate-50/50 opacity-70' }} relative transition-all">
                    
                    {{-- Header Card --}}
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl {{ $d->is_active ? 'bg-[#EFF5FF] text-[#003FA9]' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-2xl flex-shrink-0">
                                @if($isMobile)
                                    <i class='bx bx-mobile-alt'></i>
                                @else
                                    <i class='bx bx-laptop'></i>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    {{ $d->device_name }}
                                </h4>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $d->browser }} &bull; {{ $d->platform }}</p>
                            </div>
                        </div>

                        {{-- Badges --}}
                        <div class="flex flex-col items-end gap-1.5">
                            @if($d->is_primary)
                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-blue-100 text-[#003FA9]">
                                    Perangkat Utama
                                </span>
                            @endif
                            @if($isCurrent)
                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                    <i class='bx bx-radio-circle-marked'></i> Perangkat Ini
                                </span>
                            @endif
                            @if(!$d->is_active)
                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600">
                                    Dicabut
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Technical details --}}
                    <div class="space-y-2.5 py-3 border-y border-slate-100 text-xs text-slate-600">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-medium">Didaftarkan:</span>
                            <span class="font-bold text-slate-800">{{ $d->registered_at ? $d->registered_at->format('d M Y, H:i') : '-' }} WIB</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-medium">Terakhir Aktif:</span>
                            <span class="font-bold text-slate-800">{{ $d->last_active_at ? $d->last_active_at->diffForHumans() : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-medium">Alamat IP:</span>
                            <span class="font-mono text-slate-800 font-bold">{{ $d->ip_address ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    @if($d->is_active)
                        <div class="mt-4 flex items-center justify-end">
                            <form action="{{ route('siswa.devices.revoke', ['id' => $d->id]) }}" method="POST"
                                data-confirm="Apakah Anda yakin ingin melepaskan perangkat ini? Anda harus login dan verifikasi ulang jika ingin menggunakan perangkat ini kembali."
                                data-confirm-title="Lepaskan Perangkat"
                                data-confirm-danger="true">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold flex items-center gap-1.5 transition-colors">
                                    <i class='bx bx-log-out-circle text-sm'></i>
                                    <span>Lepaskan Perangkat</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="mt-4 text-right">
                            <span class="text-[11px] text-slate-400 italic">Dicabut pada {{ $d->revoked_at ? $d->revoked_at->format('d M Y, H:i') : '-' }}</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-2 bg-white rounded-[28px] p-12 text-center text-slate-400 shadow-sm border border-slate-100">
                    <i class='bx bx-devices text-5xl mb-2 text-slate-300'></i>
                    <p class="font-bold text-slate-700 text-sm">Belum ada perangkat yang terdaftar.</p>
                    <p class="text-xs text-slate-400 mt-1">Perangkat akan otomatis terdaftar saat Anda berhasil login dan memverifikasi wajah.</p>
                </div>
            @endforelse
        </div>

    </div>
@endsection
