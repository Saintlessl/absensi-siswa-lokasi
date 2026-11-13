@extends('settings.layout')

@php
    $settingsTitle = 'Profil & Pengaturan Akun — MySkanic';
    $settingsPageTitle = 'Profil & Keamanan Akun';
    $settingsPageSubtitle = 'Kelola informasi identitas akun pribadi dan perbarui kata sandi keamanan Anda';
@endphp

@section('settings-content')
<div class="space-y-6">

    {{-- Profil Banner Card --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 flex flex-col sm:flex-row items-center gap-6">
        <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-[#003EA7] to-blue-500 flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-blue-900/10 flex-shrink-0 overflow-hidden">
            @if($user->guru && $user->guru->photo_path && file_exists(public_path('storage/' . $user->guru->photo_path)))
                <img src="{{ asset('storage/' . $user->guru->photo_path) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
            @elseif($user->siswa && $user->siswa->photo_path && file_exists(public_path('storage/' . $user->siswa->photo_path)))
                <img src="{{ asset('storage/' . $user->siswa->photo_path) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
            @else
                <i class='bx bxs-user'></i>
            @endif
        </div>
        <div class="text-center sm:text-left flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900 truncate">{{ $user->salutation_name ?? $user->name }}</h2>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-blue-50 text-[#003EA7] border border-blue-200 w-fit mx-auto sm:mx-0">
                    {{ strtoupper($user->roleRel->display_name ?? $user->role) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1 flex items-center justify-center sm:justify-start gap-1.5">
                <i class='bx bx-envelope'></i> {{ $user->email }}
            </p>
            @if($user->jurusan)
                <p class="text-xs text-slate-400 mt-0.5">
                    Program Keahlian: <strong class="text-slate-700">{{ $user->jurusan->nama_jurusan }}</strong>
                </p>
            @endif
        </div>
    </div>

    {{-- Form 1: Ubah Informasi Akun --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
        <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-xl font-bold">
                <i class='bx bx-user'></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Informasi Akun</h3>
                <p class="text-xs text-slate-400">Perbarui nama pengguna dan alamat email terdaftar</p>
            </div>
        </div>

        <form action="{{ route('settings.profile.info') }}" method="POST" class="space-y-4 max-w-xl">
            @csrf
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                @error('name')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                @error('email')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="pt-3">
                <button type="submit" class="px-5 py-2.5 bg-[#003EA7] hover:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    Simpan Informasi
                </button>
            </div>
        </form>
    </div>

    {{-- Form 2: Ubah Password --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
        <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class='bx bx-lock-alt'></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Ubah Kata Sandi</h3>
                <p class="text-xs text-slate-400">Pastikan menggunakan kombinasi kata sandi yang aman dan tidak mudah ditebak</p>
            </div>
        </div>

        <form action="{{ route('settings.profile.password') }}" method="POST" class="space-y-4 max-w-xl">
            @csrf

            <div>
                <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1">Password Saat Ini</label>
                <input type="password" name="current_password" id="current_password" required placeholder="Masukkan password lama..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                @error('current_password')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1">Password Baru</label>
                    <input type="password" name="password" id="password" required placeholder="Minimal 6 karakter..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    @error('password')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Ulangi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Ketik ulang password..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
