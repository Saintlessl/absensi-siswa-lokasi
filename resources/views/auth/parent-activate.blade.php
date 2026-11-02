@extends('layouts.app')

@section('title', 'Aktivasi Akun Orang Tua')
@section('content')
<div class="max-w-md mx-auto mt-16 bg-white border border-dark-200 rounded-xl p-6">
    <h1 class="text-xl font-semibold text-dark-900">Aktivasi akun orang tua</h1>
    <p class="mt-2 text-sm text-dark-500">Tetapkan password pribadi Anda. Jangan gunakan password yang sama dengan akun lain.</p>
    <form class="mt-6 space-y-4" method="POST" action="{{ route('parent.activate.store', $token) }}">
        @csrf
        <div><label class="block text-sm font-medium">Password baru</label><input class="mt-1 w-full rounded-lg border-dark-300" type="password" name="password" required minlength="8" autocomplete="new-password"></div>
        <div><label class="block text-sm font-medium">Konfirmasi password</label><input class="mt-1 w-full rounded-lg border-dark-300" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></div>
        <button class="w-full rounded-lg bg-primary-600 px-4 py-2 text-white font-medium">Aktifkan akun</button>
    </form>
</div>
@endsection
