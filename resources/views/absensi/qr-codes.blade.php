@extends('layouts.app')

@section('title', 'QR Code Kelas')
@section('page-title', 'QR Code Kelas')
@section('page-subtitle', 'Generate dan cetak QR Code absensi untuk 33 kelas')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">QR Code Kelas</li>
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
            <h2 class="text-xl font-bold text-dark-800">QR Code Absensi Per Kelas</h2>
            <p class="text-sm text-dark-400 mt-1">
                Tiap kelas memiliki kode QR unik. Cetak dan tempel di dinding kelas masing-masing.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200">
                <i class='bx bx-printer text-lg'></i>
                <span>Cetak Semua QR Code</span>
            </button>
        </div>
    </div>
@endsection

@section('content')
    {{-- Filter Bar --}}
    <div class="no-print glass-card rounded-2xl border border-dark-200/50 p-4 mb-6">
        <form action="{{ route('absensi.qrcodes') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1 w-full">
                <label class="block text-xs font-medium text-dark-600 mb-1">Cari Kelas</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bx-search text-dark-400'></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                        placeholder="Nama Kelas (contoh: PPLG 1, X Animasi)...">
                </div>
            </div>

            <div class="w-full sm:w-64">
                <label class="block text-xs font-medium text-dark-600 mb-1">Filter Jurusan</label>
                <select name="jurusan_id"
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="">Semua Jurusan ({{ $kelasList->count() }} Kelas)</option>
                    @foreach ($jurusans as $j)
                        <option value="{{ $j->id }}" {{ ($jurusan_id ?? '') == $j->id ? 'selected' : '' }}>
                            {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                <i class='bx bx-filter-alt'></i> Filter
            </button>
            @if ($search || $jurusan_id)
                <a href="{{ route('absensi.qrcodes') }}"
                    class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Grid Kartu QR Code --}}
    <div class="print-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse ($kelasList as $kelas)
            @php
                $badgeColor = match(strtoupper($kelas->jurusan->kode_jurusan ?? '')) {
                    'PPLG' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                    'ANIMASI' => 'bg-purple-100 text-purple-700 border-purple-200',
                    'TPFL' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'TO' => 'bg-rose-100 text-rose-700 border-rose-200',
                    'BCF' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    default => 'bg-blue-100 text-blue-700 border-blue-200',
                };
            @endphp
            <div class="qr-card-print glass-card rounded-2xl border border-dark-200/50 p-6 flex flex-col items-center text-center bg-white shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                {{-- Header Kartu --}}
                <div class="mb-3 w-full">
                    <p class="text-[10px] tracking-wider uppercase font-bold text-dark-400">SMK NEGERI &bull; ABSENSI KELAS</p>
                    <h3 class="text-xl font-extrabold text-dark-900 mt-0.5">{{ $kelas->nama_kelas }}</h3>
                    <div class="mt-1 flex items-center justify-center gap-1.5">
                        <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold border {{ $badgeColor }}">
                            {{ $kelas->jurusan->kode_jurusan ?? 'UMUM' }}
                        </span>
                        <span class="text-xs text-dark-500">&bull; {{ $kelas->wali_kelas ?? 'Wali Kelas' }}</span>
                    </div>
                </div>

                {{-- QR Code Canvas / Container --}}
                <div class="my-4 p-3 bg-white border-2 border-dark-900 rounded-2xl shadow-inner flex items-center justify-center">
                    <div id="qrcode-{{ $kelas->id }}" class="qrcode-container" data-token="{{ $kelas->qr_token }}"></div>
                </div>

                {{-- Instruction & Token --}}
                <div class="mt-1 space-y-1">
                    <p class="text-xs font-semibold text-dark-700">Arahkan kamera ke QR Code ini</p>
                    <p class="text-[10px] font-mono text-dark-400 bg-dark-50 px-2 py-0.5 rounded border border-dark-200 max-w-[240px] truncate mx-auto">
                        Token: {{ $kelas->qr_token ?? 'Belum ada token' }}
                    </p>
                </div>

                {{-- Download individual button (hidden in print) --}}
                <div class="no-print mt-4 pt-3 border-t border-dark-100 w-full flex items-center justify-center gap-2">
                    <button type="button" onclick="downloadSingleQr('{{ $kelas->id }}', '{{ $kelas->nama_kelas }}')"
                        class="px-3 py-1.5 rounded-lg border border-dark-200 text-dark-600 hover:bg-dark-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class='bx bx-download text-sm'></i>
                        <span>Download PNG</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center glass-card rounded-2xl border border-dark-200/50">
                <i class='bx bx-qr-scan text-4xl text-dark-400 mb-2'></i>
                <h4 class="text-base font-bold text-dark-800">Tidak Ada Data Kelas</h4>
                <p class="text-xs text-dark-500 mt-1">Silakan jalankan seeder kelas terlebih dahulu.</p>
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

        function downloadSingleQr(kelasId, namaKelas) {
            const container = document.getElementById('qrcode-' + kelasId);
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
            a.download = `QR_Code_${namaKelas.replace(/\s+/g, '_')}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>
@endpush
