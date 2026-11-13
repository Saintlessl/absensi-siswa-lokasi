@extends('settings.layout')

@php
    $settingsTitle = "Kelola Permission: {$role->display_name} — MySkanic";
    $settingsPageTitle = 'Kelola Hak Akses Permission';
    $settingsPageSubtitle = "Konfigurasi granular izin tindakan dan modul untuk role {$role->display_name}";
@endphp

@section('settings-header-actions')
<a href="{{ route('settings.roles.index') }}"
    class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold inline-flex items-center gap-1.5 transition">
    <i class='bx bx-arrow-back'></i> Kembali ke Daftar Role
</a>
@endsection

@section('settings-content')
<div class="space-y-6">

    {{-- Role Identity Banner --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#003EA7] to-blue-500 flex items-center justify-center text-white text-2xl font-bold shadow-md shadow-blue-900/10 flex-shrink-0">
                <i class='bx bx-shield-quarter'></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-slate-900">{{ $role->display_name }}</h2>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700">
                        {{ $role->name }}
                    </span>
                    @if($role->is_system)
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-[#003EA7]">
                            ROLE INTI
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $role->description ?? 'Tidak ada deskripsi peran.' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-2xl bg-blue-50 border border-blue-100 text-right">
                <span class="text-[10px] uppercase tracking-wider font-bold text-blue-600 block">Status Akses</span>
                <span id="active-count-indicator" class="text-sm font-black text-[#003EA7]">
                    {{ count($activePermissionIds) }} Aktif
                </span>
            </div>
        </div>
    </div>

    @if($role->name === 'superadmin')
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs flex items-center gap-3">
            <i class='bx bxs-shield text-xl text-amber-600 flex-shrink-0'></i>
            <span>
                <strong>Perhatian:</strong> Role <strong>Super Administrator</strong> memiliki proteksi otomatis dengan akses tak terbatas (Full Access) ke semua modul dan aksi di sistem MySkanic.
            </span>
        </div>
    @endif

    {{-- Permissions Form --}}
    <form id="permission-form" action="{{ route('settings.roles.permissions.update', $role) }}" method="POST" class="space-y-6">
        @csrf

        <div class="space-y-6">
            @foreach($modules as $moduleName => $permissions)
                @php
                    $moduleActiveCount = $permissions->whereIn('id', $activePermissionIds)->count();
                    $moduleTotal = $permissions->count();
                    $moduleSlug = \Illuminate\Support\Str::slug($moduleName);
                @endphp

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-4">
                    {{-- Module Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-lg font-bold">
                                @if(str_contains(strtolower($moduleName), 'siswa'))
                                    <i class='bx bx-user'></i>
                                @elseif(str_contains(strtolower($moduleName), 'guru'))
                                    <i class='bx bx-id-card'></i>
                                @elseif(str_contains(strtolower($moduleName), 'inventaris'))
                                    <i class='bx bx-box'></i>
                                @elseif(str_contains(strtolower($moduleName), 'izin'))
                                    <i class='bx bx-task'></i>
                                @elseif(str_contains(strtolower($moduleName), 'kedisiplinan'))
                                    <i class='bx bx-error-alt'></i>
                                @elseif(str_contains(strtolower($moduleName), 'pkl'))
                                    <i class='bx bx-briefcase'></i>
                                @elseif(str_contains(strtolower($moduleName), 'akademik'))
                                    <i class='bx bx-book-open'></i>
                                @else
                                    <i class='bx bx-slider'></i>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">{{ $moduleName }}</h3>
                                <span class="text-[11px] text-slate-400 font-medium">
                                    <span id="counter-{{ $moduleSlug }}">{{ $moduleActiveCount }}</span> dari {{ $moduleTotal }} hak akses aktif
                                </span>
                            </div>
                        </div>

                        {{-- Batch Action Buttons for this Module --}}
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleModuleGroup('{{ $moduleSlug }}', true)"
                                class="px-2.5 py-1 text-[11px] font-bold text-[#003EA7] bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                Pilih Semua
                            </button>
                            <button type="button" onclick="toggleModuleGroup('{{ $moduleSlug }}', false)"
                                class="px-2.5 py-1 text-[11px] font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                Kosongkan
                            </button>
                        </div>
                    </div>

                    {{-- Module Permissions Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 pt-2">
                        @foreach($permissions as $perm)
                            @php
                                $isChecked = in_array($perm->id, $activePermissionIds) || $role->name === 'superadmin';
                            @endphp
                            <label class="group relative flex items-start justify-between gap-3 p-3.5 rounded-2xl border transition-all cursor-pointer {{ $isChecked ? 'bg-blue-50/40 border-blue-200' : 'bg-slate-50/50 border-slate-100 hover:border-slate-200' }}">
                                <div class="min-w-0 flex-1 pr-2">
                                    <span class="block text-xs font-bold text-slate-800 leading-snug group-hover:text-[#003EA7] transition-colors">
                                        {{ $perm->display_name }}
                                    </span>
                                    <span class="block text-[10px] font-mono text-slate-400 mt-0.5">
                                        {{ $perm->name }}
                                    </span>
                                    @if($perm->description)
                                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                            {{ $perm->description }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Modern Switch / Toggle --}}
                                <div class="relative inline-flex items-center cursor-pointer flex-shrink-0 pt-0.5">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                        data-module="{{ $moduleSlug }}"
                                        onchange="updateCounter('{{ $moduleSlug }}')"
                                        class="perm-checkbox sr-only peer"
                                        {{ $isChecked ? 'checked' : '' }}
                                        {{ $role->name === 'superadmin' ? 'disabled' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#003EA7]"></div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Sticky Footer Action Bar --}}
        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-xl border border-slate-200 flex items-center justify-between gap-4">
            <div class="text-xs text-slate-600 hidden sm:block">
                <span>Perubahan permission langsung tersinkronisasi tanpa pengguna harus login ulang.</span>
            </div>

            <div class="flex items-center gap-3 ml-auto">
                <a href="{{ route('settings.roles.index') }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>

                @if($role->name !== 'superadmin')
                    <button type="button" onclick="confirmSavePermissions()"
                        class="px-6 py-2.5 bg-[#003EA7] hover:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-900/20 transition inline-flex items-center gap-2">
                        <i class='bx bx-save text-base'></i>
                        <span>Simpan Perubahan Hak Akses</span>
                    </button>
                @endif
            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
    function toggleModuleGroup(moduleSlug, state) {
        const checkboxes = document.querySelectorAll(`input[data-module="${moduleSlug}"]`);
        checkboxes.forEach(cb => {
            if (!cb.disabled) {
                cb.checked = state;
            }
        });
        updateCounter(moduleSlug);
        updateOverallCounter();
    }

    function updateCounter(moduleSlug) {
        const checkboxes = document.querySelectorAll(`input[data-module="${moduleSlug}"]`);
        let checked = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) checked++;
        });
        const el = document.getElementById(`counter-${moduleSlug}`);
        if (el) el.textContent = checked;
        updateOverallCounter();
    }

    function updateOverallCounter() {
        const allCheckboxes = document.querySelectorAll('.perm-checkbox:checked');
        const indicator = document.getElementById('active-count-indicator');
        if (indicator) {
            indicator.textContent = `${allCheckboxes.length} Aktif`;
        }
    }

    function confirmSavePermissions() {
        const total = document.querySelectorAll('.perm-checkbox:checked').length;
        Swal.fire({
            title: 'Simpan Hak Akses?',
            text: `Anda akan menerapkan ${total} permission untuk role "${{ json_encode($role->display_name) }}". Hak akses seluruh pengguna dengan role ini akan langsung diperbarui.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#003EA7',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Simpan Permission',
            cancelButtonText: 'Periksa Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('permission-form').submit();
            }
        });
    }
</script>
@endpush
@endsection
