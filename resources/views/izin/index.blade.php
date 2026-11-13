@extends('layouts.app')

@section('title', 'Pengajuan Izin/Sakit')
@section('page-title', 'Pengajuan Izin')
@section('page-subtitle', 'Kirimkan permohonan izin ketidakhadiran')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Pengajuan Izin</li>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Form Panel --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card rounded-2xl border border-slate-100 p-6 sm:p-8">
                <div class="border-b border-dark-100 pb-4 mb-6 relative">
                    <h3 class="text-xl font-bold text-dark-800">Form Pengajuan Izin/Sakit</h3>
                    <p class="text-sm text-dark-500 mt-1">Harap isi data dengan sebenar-benarnya.</p>
                </div>

                @if($absenHariIni)
                    {{-- Blocked State --}}
                    <div class="p-6 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mb-4">
                            @if($absenHariIni->status == 'hadir')
                                <i class='bx bx-check-shield text-3xl'></i>
                            @else
                                <i class='bx bx-task text-3xl'></i>
                            @endif
                        </div>
                        <h4 class="text-lg font-bold text-amber-800 mb-2">Anda Sudah Melakukan Perekaman!</h4>
                        <p class="text-sm text-amber-700 max-w-md">
                            Hari ini Anda sudah tercatat dalam sistem dengan status 
                            <span class="font-bold uppercase">({{ $absenHariIni->status }})</span>. 
                            Anda tidak dapat mengajukan izin ganda di hari yang sama.
                        </p>
                    </div>
                @else
                    {{-- Active Form --}}
                    <form action="{{ route('izin.store') }}" method="POST" id="frmIzin">
                        @csrf
                        <div class="space-y-6">
                            
                            {{-- Tanggal --}}
                            <div>
                                <label for="tanggal" class="block text-sm font-medium text-dark-700 mb-2">Tanggal Pengajuan <span class="text-rose-500">*</span></label>
                                <input type="date" name="tanggal" id="tanggal" required
                                    value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                                    class="block w-full px-4 py-3 border border-dark-200 rounded-xl bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                                @error('tanggal')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            
                            {{-- Kategori --}}
                            <div>
                                <label class="block text-sm font-medium text-dark-700 mb-2">Pilih Kategori <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="jenis" value="izin" class="peer sr-only" required>
                                        <div class="p-4 rounded-xl border-2 border-dark-200 bg-white hover:bg-dark-50 peer-checked:border-primary-500 peer-checked:bg-primary-50/50 transition-all duration-200 text-center">
                                            <i class='bx bx-envelope text-3xl text-dark-400 peer-checked:text-primary-500 mb-2 transition-colors duration-200 block'></i>
                                            <span class="font-bold text-dark-700 peer-checked:text-primary-700 transition-colors duration-200">Keperluan (Izin)</span>
                                        </div>
                                    </label>
                                    
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="jenis" value="sakit" class="peer sr-only" required>
                                        <div class="p-4 rounded-xl border-2 border-dark-200 bg-white hover:bg-dark-50 peer-checked:border-rose-500 peer-checked:bg-rose-50/50 transition-all duration-200 text-center">
                                            <i class='bx bx-plus-medical text-3xl text-dark-400 peer-checked:text-rose-500 mb-2 transition-colors duration-200 block'></i>
                                            <span class="font-bold text-dark-700 peer-checked:text-rose-700 transition-colors duration-200">Sakit</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Keterangan --}}
                            <div>
                                <label for="keterangan" class="block text-sm font-medium text-dark-700 mb-2">Penjelasan / Keterangan <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <textarea name="keterangan" id="keterangan" rows="4" required
                                        class="block w-full px-4 py-3 border border-dark-200 rounded-xl bg-white focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                        placeholder="Berikan alasan kenapa Anda tidak dapat masuk ke sekolah hari ini..."></textarea>
                                </div>
                                <p class="text-xs text-dark-400 mt-2">Maksimal 500 karakter.</p>
                            </div>

                            {{-- Submit --}}
                            <div class="pt-4 border-t border-dark-100 flex justify-end">
                                <button type="submit" 
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 hover:-translate-y-0.5 transition-all duration-200">
                                    <i class='bx bx-send text-xl'></i>
                                    Kirim Pengajuan
                                </button>
                            </div>

                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- Info Panel --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Student Overview --}}
            <div class="glass-card rounded-2xl border border-slate-100 p-6 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-500"></div>
                
                <h3 class="text-sm font-bold text-dark-800 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class='bx bx-id-card text-primary-500 text-lg'></i> Profil Pengirim
                </h3>

                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-white flex items-center justify-center text-xl font-bold shadow-lg shadow-primary-500/30">
                        {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-dark-900">{{ $siswa->nama }}</h4>
                        <p class="text-sm text-dark-500">{{ $siswa->nis }}</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-dark-50/50 border border-dark-100">
                        <span class="text-xs font-semibold text-dark-500 uppercase">Kelas</span>
                        <span class="text-sm font-bold text-dark-800">{{ $siswa->kelas->nama_kelas ?? 'Belum Diatur' }}</span>
                    </div>
                </div>
            </div>

            {{-- Policy --}}
            <div class="glass-card rounded-2xl border border-slate-100 p-6 bg-primary-50/30">
                <h4 class="text-sm font-bold text-dark-800 mb-3 flex items-center gap-2">
                    <i class='bx bx-info-circle text-primary-600 text-lg'></i> Ketentuan Izin:
                </h4>
                <ul class="text-sm text-dark-600 space-y-2 list-disc list-inside">
                    <li>Pengajuan izin/sakit valid hanya untuk hari ini.</li>
                    <li>Siswa tidak dapat mengubah keterangan setelah pengajuan terkirim.</li>
                    <li>Segala bentuk penyalahgunaan menu izin akan ditindak lanjuti oleh pihak sekolah.</li>
                </ul>
            </div>

            {{-- Riwayat Pengajuan --}}
            <div class="glass-card rounded-2xl border border-slate-100 p-6">
                <h4 class="text-sm font-bold text-dark-800 mb-4 flex items-center gap-2">
                    <i class='bx bx-history text-primary-600 text-lg'></i> Riwayat Pengajuan
                </h4>

                @if(($riwayat ?? collect())->isEmpty())
                    <p class="text-xs text-dark-400">Belum ada pengajuan.</p>
                @else
                    <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                        @foreach($riwayat as $r)
                            <div class="p-3 rounded-xl border border-dark-100 bg-dark-50/40">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-mono font-semibold text-dark-700">{{ $r->tanggal->translatedFormat('d M Y') }}</span>
                                    @if($r->status === 'pending')
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">MENUNGGU</span>
                                    @elseif($r->status === 'approved')
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">DISETUJUI</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800 border border-rose-200">DITOLAK</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-dark-600 mt-1">{{ ucfirst($r->jenis) }} &bull; {{ \Illuminate\Support\Str::limit($r->keterangan, 60) }}</p>
                                @if($r->keputusan_catatan)
                                    <p class="text-[10px] text-dark-400 mt-1 italic">Catatan: {{ $r->keputusan_catatan }}</p>
                                @endif
                                @if($r->status === 'pending')
                                    <form action="{{ route('izin.cancel', $r->id) }}" method="POST" class="mt-2">
                                        @csrf
                                        <button type="submit" class="text-[10px] font-semibold text-rose-600 hover:text-rose-800">
                                            <i class='bx bx-x-circle'></i> Batalkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('frmIzin');
        if(form) {
            form.addEventListener('submit', function(e) {
                const statusElems = document.getElementsByName('jenis');
                let isChecked = false;
                for (var i = 0; i < statusElems.length; i++) {
                    if (statusElems[i].checked) {
                        isChecked = true;
                        break;
                    }
                }

                const keterangan = document.getElementById('keterangan').value.trim();

                if(!isChecked || !keterangan) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Form Belum Lengkap',
                        text: 'Silakan pilih kategori dan berikan keterangan!',
                        confirmButtonColor: '#3b82f6',
                        customClass: { popup: 'font-inter rounded-2xl' }
                    });
                }
            });
        }
    });
</script>

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil dikirim!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#10b981',
            customClass: { popup: 'font-inter rounded-2xl' }
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '{{ session('error') }}',
            confirmButtonColor: '#ef4444',
            customClass: { popup: 'font-inter rounded-2xl' }
        });
    </script>
@endif
@endpush
