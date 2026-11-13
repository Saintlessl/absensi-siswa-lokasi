@extends('settings.layout')

@php
    $settingsTitle = 'Identitas & Jam Sekolah — MySkanic';
    $settingsPageTitle = 'Identitas & Jam Sekolah';
    $settingsPageSubtitle = 'Kelola logo aplikasi, favicon browser, nama resmi sekolah, dan konfigurasi jam presensi';
@endphp

@section('settings-content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class='bx bx-check-circle text-xl text-emerald-600 flex-shrink-0'></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 transition">
                <i class='bx bx-x text-xl'></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
            <div class="flex items-center gap-2 font-bold mb-1.5">
                <i class='bx bx-error-circle text-xl text-rose-600 flex-shrink-0'></i>
                <span>Terdapat kesalahan pada formulir:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700 pl-6">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('settings.school.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- =========================================================================
             CARD 1: BRANDING & IDENTITAS SEKOLAH (LOGO & FAVICON)
             ========================================================================= --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
            <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-[#EFF5FF] text-[#003EA7] flex items-center justify-center text-xl font-bold flex-shrink-0">
                    <i class='bx bxs-palette'></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Branding & Logo Aplikasi</h3>
                    <p class="text-xs text-slate-400">Kustomisasi logo utama pada sidebar/header dan ikon favicon pada tab browser</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- 1. LOGO UTAMA / SIDEBAR --}}
                <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <i class='bx bx-image text-[#003EA7]'></i>
                                <span>Logo Utama / Sidebar</span>
                            </label>
                            @if($hasCustomLogo ?? false)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#EFF5FF] text-[#003EA7]">
                                    Logo Kustom Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">
                                    Logo Default
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-4 my-4">
                            <div class="w-20 h-20 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-center p-2 overflow-hidden flex-shrink-0">
                                <img id="logo_preview"
                                     src="{{ $logoUrl ?? asset('img/school-crest.png') }}"
                                     alt="Preview Logo"
                                     class="max-w-full max-h-full object-contain">
                            </div>
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800">Preview Logo Sidebar</p>
                                <p class="text-[11px] text-slate-500 leading-relaxed">
                                    Format: <strong>PNG, SVG, WEBP, JPG</strong>.<br>
                                    Rekomendasi rasio 1:1, latar transparan, maks. 2 MB.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200/60 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <label for="logo_input"
                                   class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold border border-slate-300 transition shadow-xs">
                                <i class='bx bx-upload text-sm text-[#003EA7]'></i>
                                <span>Pilih Logo Baru</span>
                            </label>
                            <input type="file"
                                   name="logo"
                                   id="logo_input"
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="hidden"
                                   onchange="previewImage(this, 'logo_preview', 'logo_filename')">
                            <span id="logo_filename" class="text-xs text-slate-500 truncate max-w-[180px]">Belum ada file dipilih</span>
                        </div>

                        @if($hasCustomLogo ?? false)
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-rose-600 hover:text-rose-700 pt-1">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                <span>Reset kembali ke logo default sekolah</span>
                            </label>
                        @endif
                        @error('logo')
                            <p class="text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 2. FAVICON BROWSER --}}
                <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <i class='bx bx-window-alt text-[#003EA7]'></i>
                                <span>Favicon Tab Browser</span>
                            </label>
                            @if($hasCustomFavicon ?? false)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#EFF5FF] text-[#003EA7]">
                                    Favicon Kustom Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">
                                    Favicon Default
                                </span>
                            @endif
                        </div>

                        {{-- Realistic Browser Tab Mockup --}}
                        <div class="my-4 space-y-2">
                            <div class="p-2.5 bg-slate-200/80 rounded-xl">
                                <div class="inline-flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg shadow-xs text-xs font-semibold text-slate-700 border border-slate-200 max-w-full">
                                    <img id="favicon_tab_preview"
                                         src="{{ $faviconUrl ?? asset('img/logo.png') }}"
                                         alt="Preview Favicon Tab"
                                         class="w-4 h-4 object-contain flex-shrink-0">
                                    <span class="truncate text-[11px] font-medium text-slate-800">MySkanic — Dashboard Presensi</span>
                                    <i class='bx bx-x text-slate-400 text-sm ml-1'></i>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Format: <strong>ICO, PNG, SVG, WEBP, JPG</strong>.<br>
                                Ukuran disarankan 32×32 atau 64×64 px, maks. 1 MB.
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200/60 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <label for="favicon_input"
                                   class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold border border-slate-300 transition shadow-xs">
                                <i class='bx bx-upload text-sm text-[#003EA7]'></i>
                                <span>Pilih Favicon Baru</span>
                            </label>
                            <input type="file"
                                   name="favicon"
                                   id="favicon_input"
                                   accept=".ico,image/png,image/x-icon,image/vnd.microsoft.icon,image/jpeg,image/webp,image/svg+xml"
                                   class="hidden"
                                   onchange="previewFavicon(this)">
                            <span id="favicon_filename" class="text-xs text-slate-500 truncate max-w-[180px]">Belum ada file dipilih</span>
                        </div>

                        @if($hasCustomFavicon ?? false)
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-rose-600 hover:text-rose-700 pt-1">
                                <input type="checkbox" name="remove_favicon" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                <span>Reset kembali ke favicon default</span>
                            </label>
                        @endif
                        @error('favicon')
                            <p class="text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Nama Resmi Sekolah --}}
            <div class="mt-6 pt-5 border-t border-slate-100 max-w-2xl">
                <label for="nama_sekolah" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Nama Resmi Sekolah / Lembaga
                </label>
                <input type="text"
                       name="nama_sekolah"
                       id="nama_sekolah"
                       maxlength="150"
                       value="{{ old('nama_sekolah', $namaSekolah) }}"
                       placeholder="Contoh: SMK Negeri 1 Ciomas"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                <span class="text-[11px] text-slate-400 mt-1 block">Digunakan pada judul tab browser, header sidebar, dan kop laporan resmi.</span>
                @error('nama_sekolah')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- =========================================================================
             CARD 2: KONFIGURASI JAM PRESENSI SISWA
             ========================================================================= --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
            <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-[#EFF5FF] text-[#003EA7] flex items-center justify-center text-xl font-bold flex-shrink-0">
                    <i class='bx bx-time-five'></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Konfigurasi Jam Presensi Sekolah</h3>
                    <p class="text-xs text-slate-400">Atur batas waktu keterlambatan presensi pagi dan jam buka presensi kepulangan siswa</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-2xl">
                <div>
                    <label for="jam_masuk" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Batas Jam Masuk (Terlambat Setelah Ini) <span class="text-rose-500">*</span>
                    </label>
                    <input type="time"
                           name="jam_masuk"
                           id="jam_masuk"
                           required
                           value="{{ old('jam_masuk', $jamMasuk) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    @error('jam_masuk')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                    <span class="text-[11px] text-slate-400 mt-1 block">Siswa yang melakukan presensi setelah waktu ini otomatis berstatus Terlambat.</span>
                </div>

                <div>
                    <label for="jam_pulang" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Jam Buka Presensi Pulang <span class="text-rose-500">*</span>
                    </label>
                    <input type="time"
                           name="jam_pulang"
                           id="jam_pulang"
                           required
                           value="{{ old('jam_pulang', $jamPulang) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    @error('jam_pulang')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                    <span class="text-[11px] text-slate-400 mt-1 block">Presensi kepulangan siswa baru dapat dikonfirmasi setelah waktu ini.</span>
                </div>
            </div>

            <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-end">
                <button type="submit"
                        class="px-6 py-2.5 bg-[#003EA7] hover:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-900/20 transition inline-flex items-center gap-2">
                    <i class='bx bx-save text-base'></i>
                    <span>Simpan Seluruh Pengaturan</span>
                </button>
            </div>
        </div>

    </form>

</div>

<script>
    function previewImage(input, previewId, labelId) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(previewId);
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(file);

            const label = document.getElementById(labelId);
            if (label) label.textContent = file.name;
        }
    }

    function previewFavicon(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const tabImg = document.getElementById('favicon_tab_preview');
                if (tabImg) tabImg.src = e.target.result;
            };
            reader.readAsDataURL(file);

            const label = document.getElementById('favicon_filename');
            if (label) label.textContent = file.name;
        }
    }
</script>
@endsection
