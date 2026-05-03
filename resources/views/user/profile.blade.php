@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun Anda')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Profil</li>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Sidebar Profil --}}
        <div class="lg:col-span-1">
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 text-center">
                {{-- Avatar --}}
                <div class="flex justify-center mb-4">
                    <div
                        class="w-24 h-24 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-3xl font-bold shadow-xl shadow-primary-500/30">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </div>
                <h3 class="text-lg font-bold text-dark-800">{{ $user->name }}</h3>
                <p class="text-sm text-dark-500 mt-1">{{ $user->email }}</p>

                <div class="mt-3">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider
                        @if ($user->role === 'admin') bg-indigo-50 text-indigo-700 border border-indigo-200
                        @elseif($user->role === 'guru') bg-emerald-50 text-emerald-700 border border-emerald-200
                        @elseif($user->role === 'siswa') bg-blue-50 text-blue-700 border border-blue-200
                        @else bg-amber-50 text-amber-700 border border-amber-200 @endif">
                        <i class='bx bx-shield-quarter text-sm'></i>
                        {{ $user->role }}
                    </span>
                </div>

                <hr class="border-dark-100 my-5">

                <div class="space-y-3 text-left">
                    <div class="flex items-center gap-3 text-sm">
                        <div
                            class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
                            <i class='bx bx-envelope'></i>
                        </div>
                        <div>
                            <p class="text-[11px] text-dark-400 uppercase tracking-wider font-semibold">Email</p>
                            <p class="text-dark-800 font-medium">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <div
                            class="flex-shrink-0 w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class='bx bx-calendar'></i>
                        </div>
                        <div>
                            <p class="text-[11px] text-dark-400 uppercase tracking-wider font-semibold">Bergabung</p>
                            <p class="text-dark-800 font-medium">
                                {{ $user->created_at->isoFormat('D MMMM Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Area --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Edit Profil --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30 flex items-center gap-2">
                    <i class='bx bx-user-circle text-primary-600 text-xl'></i>
                    <h3 class="text-base font-bold text-dark-800">Informasi Profil</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-dark-700 mb-1.5">Nama
                                    Lengkap</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class='bx bx-user text-dark-400 text-lg'></i>
                                    </div>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                        class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                        required>
                                </div>
                                @error('name')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="email"
                                    class="block text-sm font-medium text-dark-700 mb-1.5">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class='bx bx-envelope text-dark-400 text-lg'></i>
                                    </div>
                                    <input type="email" name="email" id="email"
                                        value="{{ old('email', $user->email) }}"
                                        class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                        required>
                                </div>
                                @error('email')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                class="px-5 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center gap-2">
                                <i class='bx bx-save text-lg'></i>
                                Simpan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Ganti Password --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30 flex items-center gap-2">
                    <i class='bx bx-lock-alt text-amber-600 text-xl'></i>
                    <h3 class="text-base font-bold text-dark-800">Ubah Password</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="current_password"
                                    class="block text-sm font-medium text-dark-700 mb-1.5">Password Lama</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class='bx bx-lock text-dark-400 text-lg'></i>
                                    </div>
                                    <input type="password" name="current_password" id="current_password"
                                        class="block w-full pl-10 pr-10 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                        placeholder="Masukkan password lama" required>
                                    <button type="button" onclick="togglePassword('current_password', this)"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-dark-400 hover:text-dark-600">
                                        <i class='bx bx-hide text-lg'></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-dark-700 mb-1.5">Password
                                        Baru</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class='bx bx-lock-alt text-dark-400 text-lg'></i>
                                        </div>
                                        <input type="password" name="password" id="password"
                                            class="block w-full pl-10 pr-10 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                            placeholder="Minimal 6 karakter" required>
                                        <button type="button" onclick="togglePassword('password', this)"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-dark-400 hover:text-dark-600">
                                            <i class='bx bx-hide text-lg'></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-medium text-dark-700 mb-1.5">Konfirmasi Password</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class='bx bx-lock-alt text-dark-400 text-lg'></i>
                                        </div>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="block w-full pl-10 pr-10 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                            placeholder="Ulangi password baru" required>
                                        <button type="button" onclick="togglePassword('password_confirmation', this)"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-dark-400 hover:text-dark-600">
                                            <i class='bx bx-hide text-lg'></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-medium rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/40 transition-all duration-200 flex items-center gap-2">
                                <i class='bx bx-key text-lg'></i>
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                input.type = 'password';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        }
    </script>
@endpush
