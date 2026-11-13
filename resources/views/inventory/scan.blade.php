@extends('layouts.app')

@section('title', 'Pemindai QR Code & Barcode Inventaris')
@section('page-title', 'Inventaris Alat & Aset Praktik')
@section('page-subtitle', 'Scan Barcode / QR Code untuk Identifikasi Unit, Penyerahan & Pengembalian Alat')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('inventory.index') }}" class="hover:text-[#003FA9] transition">Inventaris Alat</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Scanner Unit</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Top Action Bar --}}
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-[#003FA9] transition">
            <i class='bx bx-arrow-back text-base'></i>
            <span>Kembali ke Katalog</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.borrowings') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-xs">
                <i class='bx bx-history text-base text-[#003FA9]'></i>
                <span>Riwayat Transaksi</span>
            </a>
        </div>
    </div>

    {{-- Scanner Main Container Card --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
            
            {{-- Left Column: Camera / Reader Region --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Kamera Pemindai</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Arahkan kamera perangkat ke barcode atau QR Code unit.</p>
                    </div>
                </div>

                <div class="relative w-full rounded-2xl overflow-hidden bg-slate-900 aspect-square flex items-center justify-center border-2 border-slate-200 shadow-inner">
                    <div id="qr-reader" class="w-full h-full"></div>
                    <div id="cameraPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center text-white/80 p-6 text-center space-y-3">
                        <i class='bx bx-camera text-4xl text-blue-300'></i>
                        <p class="text-xs font-medium">Klik tombol di bawah untuk mengaktifkan kamera pemindai.</p>
                        <button type="button" onclick="startScanner()" class="px-5 py-2 rounded-xl bg-[#003FA9] text-white text-xs font-bold hover:bg-[#002D7A] transition shadow-md">
                            Aktifkan Kamera
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2">
                    <button type="button" id="btnStopScan" onclick="stopScanner()" class="hidden px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Matikan Kamera
                    </button>
                    <span id="scanStatus" class="text-[11px] font-semibold text-slate-400">Status: Siaga</span>
                </div>
            </div>

            {{-- Right Column: Manual Code Input & Scan Results --}}
            <div class="space-y-5">
                {{-- Manual Code Input Form (Barcodes guns & manual entry) --}}
                <div class="p-5 rounded-2xl bg-[#EFF5FF] border border-slate-100 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Input Kode / Barcode Manual</label>
                        <p class="text-[11px] text-slate-500">Ketik kode unit atau gunakan scanner barcode USB / Bluetooth.</p>
                    </div>

                    <form id="formManualCode" onsubmit="event.preventDefault(); handleManualCode();" class="flex gap-2">
                        <input type="text" id="inputManualCode" placeholder="Contoh: PPLG-LTP-OMEN16-0001" autofocus
                            class="flex-1 h-11 px-4 text-xs font-mono font-bold uppercase rounded-xl border-0 bg-white text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none transition">
                        <button type="submit" class="h-11 px-5 rounded-xl bg-[#003FA9] text-white text-xs font-bold hover:bg-[#002D7A] transition shadow-sm">
                            Periksa
                        </button>
                    </form>
                </div>

                {{-- Live Results Card --}}
                <div id="scanResultArea" class="space-y-4">
                    <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-2xl text-slate-400">
                        <i class='bx bx-barcode-reader text-4xl mb-2 text-slate-300'></i>
                        <p class="text-xs font-bold text-slate-600">Menunggu Hasil Pemindaian</p>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
                            Scan barcode pada label fisik barang atau ketik kode unit di atas untuk memverifikasi.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- HTML5 QR Code Scanner CDN --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrScanner = null;
let isScanning = false;

async function startScanner() {
    const qrElement = document.getElementById('qr-reader');
    document.getElementById('cameraPlaceholder').classList.add('hidden');
    document.getElementById('btnStopScan').classList.remove('hidden');
    document.getElementById('scanStatus').textContent = 'Status: Kamera aktif...';

    try {
        html5QrScanner = new Html5Qrcode("qr-reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        await html5QrScanner.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                onScanSuccess(decodedText);
            },
            (error) => {
                // Ignore silent frame errors
            }
        );
        isScanning = true;
        document.getElementById('scanStatus').textContent = 'Status: Memindai...';
    } catch (err) {
        document.getElementById('scanStatus').textContent = 'Status: Gagal mengakses kamera.';
        Swal.fire({
            icon: 'error',
            title: 'Kamera Tidak Tersedia',
            text: 'Izin kamera ditolak atau perangkat tidak mendukung. Silakan gunakan input manual kode barang.',
            confirmButtonColor: '#003FA9'
        });
        stopScanner();
    }
}

function stopScanner() {
    if (html5QrScanner && isScanning) {
        html5QrScanner.stop().then(() => {
            html5QrScanner.clear();
            isScanning = false;
            document.getElementById('cameraPlaceholder').classList.remove('hidden');
            document.getElementById('btnStopScan').classList.add('hidden');
            document.getElementById('scanStatus').textContent = 'Status: Kamera dimatikan.';
        }).catch(err => console.error(err));
    } else {
        document.getElementById('cameraPlaceholder').classList.remove('hidden');
        document.getElementById('btnStopScan').classList.add('hidden');
    }
}

function handleManualCode() {
    const val = document.getElementById('inputManualCode').value.trim();
    if (val) {
        lookupCode(val);
    }
}

let lastScanned = null;
function onScanSuccess(decodedText) {
    if (decodedText === lastScanned) return;
    lastScanned = decodedText;
    document.getElementById('inputManualCode').value = decodedText;
    lookupCode(decodedText);

    // Audio beep feedback if supported
    try {
        const audio = new Audio("data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbqWEzHkyP2/C3Zy0VT4fQ7r5mMRNMhM/uumkvFEyAzu27aC8US4DL67pmLhRJfsnpt2UtE0d5xee2ZCwSQ3XE47FhKxFAb73crF8oEDpqueCgWSMNOWez1pVTHQsxZqLLi1IeBi1coMCBSRcDI1CWv3pMEgIj");
        audio.play();
    } catch(e) {}
}

async function lookupCode(code) {
    const resultArea = document.getElementById('scanResultArea');
    resultArea.innerHTML = `
        <div class="p-8 text-center rounded-2xl bg-slate-50 border border-slate-200">
            <i class='bx bx-loader-alt bx-spin text-3xl text-[#003FA9] mb-2'></i>
            <p class="text-xs font-bold text-slate-700">Memeriksa Kode: <span class="font-mono">${code}</span>...</p>
        </div>
    `;

    try {
        const response = await fetch(`/inventaris/scan/lookup?code=${encodeURIComponent(code)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            resultArea.innerHTML = `
                <div class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <i class='bx bx-error-circle text-xl text-rose-600'></i>
                        <span>Kode Tidak Valid</span>
                    </div>
                    <p class="text-xs text-rose-700">${data.message || 'Unit tidak terdaftar di sistem.'}</p>
                </div>
            `;
            return;
        }

        if (data.type === 'group') {
            resultArea.innerHTML = `
                <div class="p-6 rounded-2xl bg-blue-50 border border-blue-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-[#003FA9] text-white uppercase font-mono">${data.item.kode_barang}</span>
                        <span class="text-xs font-bold text-blue-900">${data.item.jurusan}</span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">${data.item.nama_barang}</h4>
                    <p class="text-xs text-blue-800 leading-relaxed">${data.message}</p>
                    <div class="pt-2">
                        <a href="${data.item.url}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#003FA9] text-white text-xs font-bold hover:bg-[#002D7A] transition">
                            <span>Buka Detail Kelompok & Unit</span>
                            <i class='bx bx-chevron-right text-base'></i>
                        </a>
                    </div>
                </div>
            `;
            return;
        }

        renderUnitResult(data);

    } catch (e) {
        resultArea.innerHTML = `
            <div class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800">
                <p class="text-xs font-bold">Terjadi kesalahan jaringan saat memverifikasi kode.</p>
            </div>
        `;
    }
}

function renderUnitResult(data) {
    const resultArea = document.getElementById('scanResultArea');
    const u = data.unit;
    const it = data.item;
    const b = data.borrowing;
    const action = data.recommended_action;

    let badgeKondisi = 'bg-emerald-50 text-emerald-700 border-emerald-200';
    if (u.kondisi === 'rusak_ringan') badgeKondisi = 'bg-amber-50 text-amber-700 border-amber-200';
    if (u.kondisi === 'rusak_berat') badgeKondisi = 'bg-rose-50 text-rose-700 border-rose-200';
    if (u.kondisi === 'perlu_pemeriksaan') badgeKondisi = 'bg-slate-100 text-slate-700 border-slate-200';

    let actionCardHtml = '';

    // Action 1: Ready for Handover
    if (action === 'handover' && b) {
        actionCardHtml = `
            <div class="p-5 rounded-2xl bg-indigo-50/70 border border-indigo-200 space-y-3">
                <div class="flex items-center gap-2 text-indigo-900 font-bold text-xs uppercase tracking-wider">
                    <i class='bx bx-check-circle text-lg text-indigo-600'></i>
                    <span>Permohonan Disetujui — Siap Diserahkan</span>
                </div>
                <div class="text-xs text-slate-700 space-y-1">
                    <div>Peminjam: <strong class="text-slate-900">${b.peminjam}</strong> (${b.role_peminjam})</div>
                    <div>Batas Pengembalian: <span class="font-mono font-bold text-indigo-900">${b.rencana_kembali}</span></div>
                    <div>Keperluan: <em class="text-slate-600">"${b.keperluan}"</em></div>
                </div>
                <form action="${b.handover_url}" method="POST" onsubmit="return confirm('Konfirmasi penyerahan unit ${u.kode_unit} ke peminjam?')">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                        <i class='bx bx-send text-base'></i>
                        <span>Konfirmasi Penyerahan Unit Ini</span>
                    </button>
                </form>
            </div>
        `;
    }
    // Action 2: Ready for Return
    else if (action === 'return' && b) {
        actionCardHtml = `
            <div class="p-5 rounded-2xl bg-blue-50/70 border border-blue-200 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-[#003FA9] font-bold text-xs uppercase tracking-wider">
                        <i class='bx bx-log-in-circle text-lg'></i>
                        <span>Sedang Dipinjam — Proses Pengembalian</span>
                    </div>
                    ${b.is_overdue ? '<span class="px-2 py-0.5 rounded text-[10px] bg-rose-100 text-rose-700 font-bold">Terlambat</span>' : ''}
                </div>
                <div class="text-xs text-slate-700 space-y-1">
                    <div>Peminjam: <strong class="text-slate-900">${b.peminjam}</strong> (${b.role_peminjam})</div>
                    <div>Batas Kembali: <span class="font-mono">${b.rencana_kembali}</span></div>
                </div>

                <form action="${b.return_url}" method="POST" class="space-y-3 pt-2 border-t border-blue-100">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Periksa Kondisi Fisik Saat Pengembalian <span class="text-rose-500">*</span></label>
                        <select name="kondisi_sesudah" required class="w-full h-10 text-xs rounded-xl border border-slate-200 bg-white px-3 font-semibold">
                            <option value="baik">Kondisi Baik Lengkap</option>
                            <option value="rusak_ringan">Rusak Ringan (Perlu Diperbaiki)</option>
                            <option value="rusak_berat">Rusak Berat / Hilang</option>
                            <option value="perlu_pemeriksaan">Perlu Pemeriksaan Petugas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Pemeriksaan</label>
                        <input type="text" name="catatan_pengembalian" placeholder="Kelengkapan, kebersihan, charger..."
                            class="w-full h-10 text-xs rounded-xl border border-slate-200 bg-white px-3">
                    </div>
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                        <i class='bx bx-check-shield text-base'></i>
                        <span>Konfirmasi Penerimaan Kembali</span>
                    </button>
                </form>
            </div>
        `;
    }
    // Action 3: Unit Available
    else {
        actionCardHtml = `
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-1">
                <div class="flex items-center gap-2 font-bold text-xs uppercase">
                    <i class='bx bx-check-circle text-base text-emerald-600'></i>
                    <span>Unit Siap Pakai</span>
                </div>
                <p class="text-xs text-emerald-700">Unit fisik ini berada dalam kondisi Baik dan siap dipinjamkan.</p>
            </div>
        `;
    }

    resultArea.innerHTML = `
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold bg-[#EFF5FF] text-[#003FA9]">${u.kode_unit}</span>
                    <span class="text-xs font-bold text-slate-500 ml-2">${it.jurusan}</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border ${badgeKondisi} uppercase">
                    ${u.kondisi.replace('_', ' ')}
                </span>
            </div>

            <div>
                <h4 class="text-base font-bold text-slate-900">${it.nama_barang}</h4>
                <div class="flex items-center gap-4 text-xs text-slate-500 mt-1">
                    <div>Kategori: <strong class="text-slate-700">${it.kategori}</strong></div>
                    <div>Lokasi: <strong class="text-slate-700">${u.lokasi}</strong></div>
                </div>
            </div>

            ${actionCardHtml}

            <div class="pt-2 flex items-center justify-between text-xs">
                <a href="${it.url}" class="text-[#003FA9] font-bold hover:underline">
                    Lihat Seluruh Kelompok Aset →
                </a>
            </div>
        </div>
    `;
}
</script>
@endpush
