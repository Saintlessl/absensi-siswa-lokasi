@extends('layouts.app')

@section('title', 'QR Code Ruangan')
@section('page-title', 'QR Code Ruangan')
@section('page-subtitle', 'QR Code presensi permanen per ruangan fisik sekolah')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><span class="text-dark-500">Presensi</span></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">QR Code Ruangan</li>
@endsection

@push('styles')
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            #sidebar, header, footer, .no-print {
                display: none !important;
            }
            main {
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1.5rem !important;
                page-break-inside: auto !important;
            }
            .qr-card-print {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                margin-bottom: 1.5rem !important;
                padding: 1.5rem !important;
            }
        }
    </style>
@endpush

@section('page-header')
    <div class="no-print flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">QR Code Presensi Fisik Ruangan</h2>
            <p class="text-sm text-dark-400 mt-1">
                Setiap ruangan fisik memiliki QR Code permanen. Kelas yang menggunakan ruangan dapat di-rolling tanpa mencetak ulang QR.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('rooms.assignments') }}"
                class="inline-flex items-center gap-2 px-4 py-2 border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 text-sm font-medium rounded-xl shadow-sm transition-all duration-200">
                <i class='bx bx-transfer text-lg text-primary-600'></i>
                <span>Kelola Alokasi / Rolling</span>
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200">
                <i class='bx bx-printer text-lg'></i>
                <span>Cetak Semua QR Ruangan</span>
            </button>
        </div>
    </div>
@endsection

@section('content')
    {{-- Filter Bar --}}
    <div class="no-print glass-card rounded-2xl border border-dark-200/50 p-4 mb-6">
        <form action="{{ route('absensi.qrcodes') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1 w-full">
                <label class="block text-xs font-medium text-dark-600 mb-1">Cari Ruangan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bx-search text-dark-400'></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                        placeholder="Nama ruangan, kode (misal: R-201, Lab Komputer)...">
                </div>
            </div>

            @if($buildings->isNotEmpty())
                <div class="w-full sm:w-56">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Filter Gedung</label>
                    <select name="building"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Gedung</option>
                        @foreach ($buildings as $b)
                            <option value="{{ $b }}" {{ $building == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($types->isNotEmpty())
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Tipe Ruangan</label>
                    <select name="type"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Tipe</option>
                        @foreach ($types as $t)
                            <option value="{{ $t }}" {{ $type == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="submit"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                <i class='bx bx-filter-alt'></i> Filter
            </button>
            @if ($search || $building || $type)
                <a href="{{ route('absensi.qrcodes') }}"
                    class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Grid Kartu QR Code Ruangan --}}
    <div class="print-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse ($rooms as $room)
            @php
                $activeAssignment = $room->assignments->first();
                $assignedKelas = $activeAssignment?->kelas;
            @endphp
            <div class="qr-card-print glass-card rounded-2xl border border-dark-200/50 p-6 flex flex-col items-center text-center bg-white shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                {{-- Badge Status Ruangan --}}
                <div class="w-full flex items-center justify-between gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold font-mono uppercase bg-dark-100 text-dark-700 border border-dark-200">
                        {{ $room->code }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $room->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-dark-100 text-dark-500' }}">
                        {{ $room->is_active ? 'Ruangan Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                {{-- Header Kartu --}}
                <div class="mb-3 w-full">
                    <p class="text-[10px] tracking-wider uppercase font-bold text-dark-400">SMK NEGERI &bull; ABSENSI RUANGAN</p>
                    <h3 class="text-xl font-extrabold text-dark-900 mt-0.5">{{ $room->name }}</h3>
                    <div class="mt-1 flex items-center justify-center gap-2 text-xs text-dark-500">
                        <span><i class='bx bx-building text-dark-400'></i> {{ $room->building ?? 'Gedung Utama' }}</span>
                        <span>&bull;</span>
                        <span><i class='bx bx-user text-dark-400'></i> Kapasitas {{ $room->capacity ?? 36 }}</span>
                    </div>
                </div>

                {{-- Active Class Badge --}}
                <div class="w-full py-2 px-3 rounded-xl mb-2 text-xs {{ $assignedKelas ? 'bg-primary-50/80 border border-primary-200 text-primary-900' : 'bg-amber-50 border border-amber-200 text-amber-800' }}">
                    <p class="text-[10px] uppercase font-bold {{ $assignedKelas ? 'text-primary-600' : 'text-amber-600' }}">Alokasi Rombel Saat Ini</p>
                    @if($assignedKelas)
                        <div class="font-bold text-sm mt-0.5 flex items-center justify-center gap-1.5">
                            <i class='bx bx-door-open text-primary-600'></i>
                            <span>{{ $assignedKelas->nama_kelas }}</span>
                            @if($assignedKelas->jurusan)
                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-primary-200/60 font-mono">{{ $assignedKelas->jurusan->kode_jurusan }}</span>
                            @endif
                        </div>
                        <p class="text-[10px] text-dark-500 mt-0.5">Wali: {{ $assignedKelas->wali_kelas ?? '-' }}</p>
                    @else
                        <p class="font-semibold text-xs mt-0.5 italic">Belum dialokasikan ke rombel</p>
                    @endif
                </div>

                {{-- QR Code Container --}}
                <div class="my-3 p-3 bg-white border-2 border-dark-900 rounded-2xl shadow-inner flex items-center justify-center">
                    <div id="qrcode-{{ $room->id }}" class="qrcode-container" data-token="{{ $room->qr_token }}"></div>
                </div>

                {{-- Instruction & Token --}}
                <div class="mt-1 space-y-1 w-full">
                    <p class="text-xs font-semibold text-dark-700">Scan QR ini melalui aplikasi presensi</p>
                    <p class="text-[10px] font-mono text-dark-400 bg-dark-50 px-2 py-0.5 rounded border border-dark-200 truncate max-w-[260px] mx-auto">
                        Token: {{ $room->qr_token }}
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="no-print mt-4 pt-3 border-t border-dark-100 w-full flex items-center justify-center gap-2">
                    <button type="button" onclick="downloadSingleQr('{{ $room->id }}', '{{ $room->name }}')"
                        class="px-3 py-1.5 rounded-lg border border-dark-200 text-dark-600 hover:bg-dark-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class='bx bx-download text-sm'></i>
                        <span>Download PNG</span>
                    </button>
                    @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                        <form action="{{ route('rooms.regenerate', $room) }}" method="POST" onsubmit="return confirm('Regenerate token QR ruangan {{ $room->name }}? QR lama tidak akan berlaku lagi!')">
                            @csrf
                            <button type="submit"
                                class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold flex items-center gap-1 transition-colors"
                                title="Regenerate QR Token">
                                <i class='bx bx-refresh text-sm'></i>
                                <span>Reset QR</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center glass-card rounded-2xl border border-dark-200/50">
                <i class='bx bx-building-house text-4xl text-dark-400 mb-2'></i>
                <h4 class="text-base font-bold text-dark-800">Tidak Ada Data Ruangan</h4>
                <p class="text-xs text-dark-500 mt-1">Belum ada data ruangan fisik yang terdaftar.</p>
                <div class="mt-4">
                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 text-white rounded-xl text-xs font-semibold hover:bg-primary-700 transition">
                        <i class='bx bx-plus'></i> Tambah Data Ruangan
                    </a>
                </div>
            </div>
        @endforelse
    </div>
@endsection

@push('scripts')
    {{-- QRCode.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrContainers = document.querySelectorAll('.qrcode-container');
            qrContainers.forEach(container => {
                const token = container.getAttribute('data-token');
                if (token && token.trim() !== '') {
                    new QRCode(container, {
                        text: token,
                        width: 170,
                        height: 170,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                } else {
                    container.innerHTML = "<p class='text-xs text-rose-500 p-4'>Token belum dibuat</p>";
                }
            });
        });

        function downloadSingleQr(roomId, roomName) {
            const container = document.getElementById('qrcode-' + roomId);
            if (!container) return;

            const img = container.querySelector('img');
            const canvas = container.querySelector('canvas');

            let src = '';
            if (canvas) {
                src = canvas.toDataURL('image/png');
            } else if (img) {
                src = img.src;
            }

            if (!src) {
                alert('QR Code belum selesai dimuat.');
                return;
            }

            const a = document.createElement('a');
            a.href = src;
            a.download = `QR_Ruangan_${roomName.replace(/\s+/g, '_')}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>
@endpush
