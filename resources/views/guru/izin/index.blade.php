@extends('layouts.app')

@section('title', 'Pengajuan Izin Mengajar — MySkanic')
@section('page-title', 'Pengajuan Izin Mengajar')
@section('page-subtitle', 'Layanan permohonan dispensasi dinas luar, izin pribadi, dan cuti sakit pendidik')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Izin Mengajar</li>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-sm font-medium flex items-center gap-3">
            <i class='bx bx-check-circle text-xl text-emerald-600 flex-shrink-0'></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-sm font-medium flex items-center gap-3">
            <i class='bx bx-error-circle text-xl text-rose-600 flex-shrink-0'></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- FORM PENGAJUAN IZIN GURU (LEFT) --}}
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-xl font-bold">
                    <i class='bx bx-edit'></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Form Permohonan Izin</h3>
                    <p class="text-xs text-slate-400">Isi formulir dengan data yang valid</p>
                </div>
            </div>

            <form action="{{ route('teacher.izin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- 1. Nama Guru (Auto, Readonly) --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pemohon (Pendidik)</label>
                    <input type="text" value="{{ $guru->nama }} (NIP: {{ $guru->nip ?? '-' }})" readonly
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-semibold text-xs cursor-not-allowed">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Identitas diambil otomatis dari sesi login.</span>
                </div>

                {{-- 2. Jenis Izin --}}
                <div>
                    <label for="jenis" class="block text-xs font-bold text-slate-700 mb-1">
                        Jenis Izin / Kategori <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenis" id="jenis" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                        <option value="">Pilih Jenis Izin...</option>
                        <option value="sakit" {{ old('jenis') === 'sakit' ? 'selected' : '' }}>Sakit (Dengan/Tanpa Surat Dokter)</option>
                        <option value="dinas_luar" {{ old('jenis') === 'dinas_luar' ? 'selected' : '' }}>Dinas Luar / Tugas Kedinasan</option>
                        <option value="keperluan_sekolah" {{ old('jenis') === 'keperluan_sekolah' ? 'selected' : '' }}>Keperluan Sekolah / Lembaga</option>
                        <option value="keperluan_keluarga" {{ old('jenis') === 'keperluan_keluarga' ? 'selected' : '' }}>Keperluan Keluarga / Acara Penting</option>
                        <option value="keperluan_pribadi" {{ old('jenis') === 'keperluan_pribadi' ? 'selected' : '' }}>Keperluan Pribadi Mendesak</option>
                        <option value="lainnya" {{ old('jenis') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('jenis')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- 3. Jenis Durasi --}}
                <div>
                    <label for="durasi_tipe" class="block text-xs font-bold text-slate-700 mb-1">
                        Durasi Izin <span class="text-rose-500">*</span>
                    </label>
                    <select name="durasi_tipe" id="durasi_tipe" required onchange="toggleDurationFields()"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                        <option value="sehari_penuh" {{ old('durasi_tipe', 'sehari_penuh') === 'sehari_penuh' ? 'selected' : '' }}>Sehari Penuh (1 Hari)</option>
                        <option value="rentang_hari" {{ old('durasi_tipe') === 'rentang_hari' ? 'selected' : '' }}>Rentang Beberapa Hari</option>
                        <option value="sebagian_hari" {{ old('durasi_tipe') === 'sebagian_hari' ? 'selected' : '' }}>Sebagian Hari / Jam Tertentu</option>
                    </select>
                </div>

                {{-- 4. Tanggal Mulai & Selesai --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="date-range-container">
                    <div>
                        <label for="tanggal_mulai" class="block text-xs font-bold text-slate-700 mb-1">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" required value="{{ old('tanggal_mulai', date('Y-m-d')) }}"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                        @error('tanggal_mulai')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>

                    <div id="tanggal_selesai_wrapper" class="hidden">
                        <label for="tanggal_selesai" class="block text-xs font-bold text-slate-700 mb-1">
                            Tanggal Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                        @error('tanggal_selesai')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- 5. Jam Mulai & Jam Selesai (Sebagian Hari) --}}
                <div id="partial-hours-wrapper" class="grid grid-cols-2 gap-3 hidden">
                    <div>
                        <label for="jam_mulai" class="block text-xs font-bold text-slate-700 mb-1">Jam Mulai</label>
                        <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai') }}"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    </div>
                    <div>
                        <label for="jam_selesai" class="block text-xs font-bold text-slate-700 mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai') }}"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    </div>
                </div>

                {{-- 6. Keterangan / Alasan --}}
                <div>
                    <label for="keterangan" class="block text-xs font-bold text-slate-700 mb-1">
                        Keterangan / Alasan Pengajuan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="keterangan" id="keterangan" rows="3" required placeholder="Tuliskan keterangan lengkap izin mengajar..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">{{ old('keterangan') }}</textarea>
                    @error('keterangan')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- 7. Berkas Lampiran Pendukung --}}
                <div>
                    <label for="lampiran" class="block text-xs font-bold text-slate-700 mb-1">
                        Lampiran Pendukung (Opsional)
                    </label>
                    <input type="file" name="lampiran" id="lampiran" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#003EA7] hover:file:bg-blue-100">
                    <span class="text-[10px] text-slate-400 mt-1 block">Format: PDF, JPG, PNG (Maksimal 3MB). Surat dinas, resep dokter, dsb.</span>
                    @error('lampiran')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                <div class="pt-3">
                    <button type="submit"
                        class="w-full py-3 bg-[#003EA7] hover:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-900/20 transition flex items-center justify-center gap-2">
                        <i class='bx bx-send text-base'></i>
                        <span>Kirim Permohonan Izin</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- RIWAYAT IZIN GURU (RIGHT) --}}
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        <i class='bx bx-history'></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Riwayat Permohonan Saya</h3>
                        <p class="text-xs text-slate-400">Daftar permohonan izin mengajar pribadi</p>
                    </div>
                </div>

                <span class="text-xs text-slate-400">Total: <strong>{{ $riwayat->total() }}</strong> Pengajuan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100 font-bold">
                        <tr>
                            <th class="px-4 py-3">Jenis & Periode</th>
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($riwayat as $item)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-slate-900 block">{{ $item->jenis_label }}</span>
                                    <span class="text-[11px] text-slate-500 font-mono">
                                        {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : $item->tanggal->format('d/m/Y') }}
                                        @if($item->tanggal_selesai && $item->tanggal_selesai->ne($item->tanggal_mulai ?? $item->tanggal))
                                            - {{ $item->tanggal_selesai->format('d/m/Y') }}
                                        @endif
                                    </span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ $item->durasi_label }}</span>
                                </td>

                                <td class="px-4 py-3.5 max-w-xs">
                                    <p class="line-clamp-2 text-slate-700 leading-relaxed">{{ $item->keterangan }}</p>
                                    @if($item->lampiran_url)
                                        <a href="{{ $item->lampiran_url }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-[11px] text-[#003EA7] font-semibold hover:underline mt-1">
                                            <i class='bx bx-paperclip'></i> Lihat Berkas Lampiran
                                        </a>
                                    @endif

                                    @if($item->keputusan_catatan)
                                        <div class="mt-1.5 p-2 rounded-lg bg-slate-50 border border-slate-200 text-[11px]">
                                            <span class="font-bold text-slate-700">Catatan Approver:</span>
                                            <p class="text-slate-600 mt-0.5">{{ $item->keputusan_catatan }}</p>
                                            @if($item->decidedBy)
                                                <span class="text-[10px] text-slate-400 mt-1 block">Oleh: {{ $item->decidedBy->name }} ({{ $item->decided_at?->format('d/m/Y H:i') }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5">
                                    @if($item->status === 'pending')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Menunggu
                                        </span>
                                    @elseif($item->status === 'approved')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Disetujui
                                        </span>
                                    @elseif($item->status === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                            Dibatalkan
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 text-right">
                                    @if($item->status === 'pending')
                                        <form action="{{ route('teacher.izin.cancel', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan izin ini?')">
                                            @csrf
                                            <button type="submit"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 transition">
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Belum ada permohonan izin mengajar yang diajukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $riwayat->links() }}
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    function toggleDurationFields() {
        const tipe = document.getElementById('durasi_tipe').value;
        const selesaiWrapper = document.getElementById('tanggal_selesai_wrapper');
        const partialWrapper = document.getElementById('partial-hours-wrapper');

        if (tipe === 'rentang_hari') {
            selesaiWrapper.classList.remove('hidden');
            document.getElementById('tanggal_selesai').required = true;
            partialWrapper.classList.add('hidden');
        } else if (tipe === 'sebagian_hari') {
            selesaiWrapper.classList.add('hidden');
            document.getElementById('tanggal_selesai').required = false;
            partialWrapper.classList.remove('hidden');
        } else {
            selesaiWrapper.classList.add('hidden');
            document.getElementById('tanggal_selesai').required = false;
            partialWrapper.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', toggleDurationFields);
</script>
@endpush
@endsection
