@extends('layouts.app')

@section('title', 'Aktivasi Akun Orang Tua')
@section('page-title', 'Aktivasi Akun Orang Tua')
@section('page-subtitle', 'Tetapkan password pribadi akun orang tua siswa.')

@section('content')
<div class="max-w-md mx-auto my-12 bg-white border border-slate-100 rounded-[28px] p-6 sm:p-8 shadow-sm">
    <div class="w-12 h-12 rounded-2xl bg-[#003FA9] text-white flex items-center justify-center text-2xl mb-4 shadow-sm">
        <i class='bx bx-key'></i>
    </div>
    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Aktivasi Akun Orang Tua</h1>
    <p class="mt-1 text-xs text-slate-400">Tetapkan password baru akun portal Anda. Jaga kerahasiaan password dan gunakan minimal 8 karakter.</p>

    <form class="mt-6 space-y-4" method="POST" action="{{ route('parent.activate.store', $token) }}">
        @csrf
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Password Baru <span class="text-rose-500">*</span></label>
            <input class="w-full h-11 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs sm:text-sm px-4 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Password <span class="text-rose-500">*</span></label>
            <input class="w-full h-11 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs sm:text-sm px-4 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Ketik ulang password">
        </div>
        <button type="submit" class="w-full h-11 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs sm:text-sm shadow-sm transition">
            Aktifkan Akun
        </button>
    </form>
</div>
@endsection
