<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Unit - {{ $item->nama_barang }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .label-card {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-6 font-sans antialiased text-slate-800">

    {{-- Top Action Bar (No Print) --}}
    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.show', $item->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white rounded-xl text-xs font-bold text-slate-700 shadow-sm border border-slate-200 hover:bg-slate-50 transition">
                <i class='bx bx-arrow-back text-base'></i>
                <span>Kembali ke Detail Aset</span>
            </a>
            <span class="text-xs text-slate-500 font-medium">Total: {{ $units->count() }} Label Unit</span>
        </div>

        <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#003FA9] text-white text-xs font-bold rounded-xl shadow-md hover:bg-[#002D7A] transition">
            <i class='bx bx-printer text-base'></i>
            <span>Cetak Label Sekarang</span>
        </button>
    </div>

    {{-- Label Cards Grid (A4 / Sticker Sheet Format) --}}
    <div class="max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($units as $unit)
            <div class="label-card bg-white p-4 rounded-2xl border-2 border-slate-800 shadow-sm flex flex-col justify-between text-left">
                {{-- Label Header --}}
                <div class="pb-2 border-b border-slate-300 flex items-center justify-between">
                    <div>
                        <h4 class="text-[10px] font-extrabold uppercase tracking-tight text-slate-900 leading-none">SMK NEGERI 1 CIOMAS</h4>
                        <p class="text-[9px] font-bold text-[#003FA9] mt-0.5">MYSKANIC ASSET MANAGEMENT</p>
                    </div>
                    <span class="px-1.5 py-0.5 text-[8px] font-extrabold uppercase bg-slate-900 text-white rounded">
                        {{ $item->jurusan ? $item->jurusan->kode_jurusan : 'UMUM' }}
                    </span>
                </div>

                {{-- QR & Unit Identifiers --}}
                <div class="py-3 flex items-center gap-3">
                    <div class="w-20 h-20 bg-white p-1 rounded-lg border border-slate-300 shrink-0 flex items-center justify-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($unit->kode_unit) }}"
                             alt="QR" class="w-full h-full object-contain">
                    </div>

                    <div class="flex-1 min-w-0">
                        <h5 class="text-xs font-extrabold text-slate-900 truncate" title="{{ $item->nama_barang }}">
                            {{ $item->nama_barang }}
                        </h5>
                        <p class="text-[11px] font-mono font-extrabold text-[#003FA9] mt-1 tracking-tight">
                            {{ $unit->kode_unit }}
                        </p>
                        @if($unit->barcode_eksternal)
                            <p class="text-[9px] font-mono text-slate-500 truncate">Ext: {{ $unit->barcode_eksternal }}</p>
                        @endif
                        <p class="text-[9px] text-slate-600 mt-1 truncate">
                            Lokasi: {{ $unit->effective_lokasi }}
                        </p>
                    </div>
                </div>

                {{-- Label Footer --}}
                <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-[8px] font-mono text-slate-400 uppercase">
                    <span>Kelompok: {{ $item->kode_barang }}</span>
                    <span>Tgl: {{ now()->format('d/m/Y') }}</span>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
