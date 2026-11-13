@extends('settings.layout')

@php
    $settingsTitle = 'Manajemen Role & Hak Akses — MySkanic';
    $settingsPageTitle = 'Manajemen Role';
    $settingsPageSubtitle = 'Kelola peran pengguna, hak akses granular antar modul, dan lingkup data sistem';
@endphp

@section('settings-header-actions')
<button type="button" onclick="openAddRoleModal()"
    class="px-4 py-2.5 bg-[#003EA7] hover:bg-blue-800 text-white text-xs font-bold rounded-2xl shadow-sm transition inline-flex items-center gap-2">
    <i class='bx bx-plus-circle text-base'></i>
    <span>Tambah Role Baru</span>
</button>
@endsection

@section('settings-content')
<div class="space-y-6">

    {{-- Filter & Search Card --}}
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('settings.roles.index') }}" class="flex-1 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class='bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama role, slug, atau deskripsi..."
                    class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
            </div>
            <select name="status" onchange="this.form.submit()"
                class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('settings.roles.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 rounded-xl flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-500 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
            <span>Total: <strong>{{ $roles->total() }}</strong> Role</span>
        </div>
    </div>

    {{-- Roles Table Card --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100 font-bold">
                    <tr>
                        <th class="px-4 py-3.5">Nama Role</th>
                        <th class="px-4 py-3.5">Scope Data</th>
                        <th class="px-4 py-3.5 text-center">Pengguna</th>
                        <th class="px-4 py-3.5 text-center">Permission</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($roles as $role)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-base font-bold flex-shrink-0">
                                        <i class='bx bx-shield-alt-2'></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 text-sm">{{ $role->display_name }}</span>
                                            @if($role->is_system)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-[#003EA7]">
                                                    SYSTEM
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $role->name }}</div>
                                        @if($role->description)
                                            <p class="text-[11px] text-slate-500 mt-1 line-clamp-1 max-w-sm">{{ $role->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                @if($role->data_scope === 'all')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <i class='bx bx-globe'></i> Seluruh Sekolah
                                    </span>
                                @elseif($role->data_scope === 'department')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                                        <i class='bx bx-git-branch'></i> Jurusan / Program
                                    </span>
                                @elseif($role->data_scope === 'assigned')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        <i class='bx bx-chalkboard'></i> Kelas Diampu
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class='bx bx-user'></i> Data Pribadi
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800">
                                    {{ $role->users_count }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-center">
                                @if($role->name === 'superadmin')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                        Semua (Full Access)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $role->permissions_count > 0 ? 'bg-blue-50 text-[#003EA7] border border-blue-200' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $role->permissions_count }} / {{ $totalPermissions }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $role->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $role->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Kelola Permission Button --}}
                                    <a href="{{ route('settings.roles.permissions', $role) }}"
                                        class="px-3 py-1.5 rounded-xl bg-[#003EA7] hover:bg-blue-800 text-white font-bold text-xs inline-flex items-center gap-1 shadow-xs transition"
                                        title="Kelola Granular Permission">
                                        <i class='bx bx-key text-sm'></i>
                                        <span>Kelola Akses</span>
                                    </a>

                                    {{-- Edit Role Button --}}
                                    <button type="button" onclick="openEditRoleModal({{ json_encode($role) }})"
                                        class="p-1.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition"
                                        title="Edit Role">
                                        <i class='bx bx-edit text-base'></i>
                                    </button>

                                    {{-- Delete Button (Protected) --}}
                                    @if(!$role->is_system)
                                        <button type="button" onclick="confirmDeleteRole('{{ $role->id }}', '{{ $role->display_name }}', {{ $role->users_count }})"
                                            class="p-1.5 rounded-xl text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition"
                                            title="Hapus Role">
                                            <i class='bx bx-trash text-base'></i>
                                        </button>
                                        <form id="delete-role-form-{{ $role->id }}" action="{{ route('settings.roles.destroy', $role) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">
                                Tidak ada data role yang sesuai pencarian
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $roles->links() }}
        </div>
    </div>

</div>

{{-- MODAL TAMBAH ROLE --}}
<div id="modal-add-role" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class='bx bx-shield-plus text-primary-600 text-xl'></i> Tambah Role Baru
            </h3>
            <button type="button" onclick="closeAddRoleModal()" class="text-slate-400 hover:text-slate-600 text-xl">
                <i class='bx bx-x'></i>
            </button>
        </div>

        <form action="{{ route('settings.roles.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Tampilan Role (Display Name) <span class="text-rose-500">*</span></label>
                <input type="text" name="display_name" required placeholder="Contoh: Staff Tata Usaha"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Slug / Identifier Sistem <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: tata_usaha (huruf kecil, underscore)"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Hanya boleh huruf, angka, garis bawah.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Lingkup Akses Data (Data Scope) <span class="text-rose-500">*</span></label>
                <select name="data_scope" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    <option value="all">Seluruh Sekolah (Melihat semua jurusan & data)</option>
                    <option value="department">Berdasarkan Jurusan / Program Keahlian (Scope Kaprog)</option>
                    <option value="assigned">Berdasarkan Penugasan Kelas / Mapel (Scope Guru)</option>
                    <option value="own">Hanya Data Milik Sendiri (Scope Siswa / Ortu)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Wewenang</label>
                <textarea name="description" rows="3" placeholder="Jelaskan peran dan tanggung jawab role ini..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeAddRoleModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#003EA7] hover:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    Simpan Role
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT ROLE --}}
<div id="modal-edit-role" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class='bx bx-edit text-primary-600 text-xl'></i> Edit Konfigurasi Role
            </h3>
            <button type="button" onclick="closeEditRoleModal()" class="text-slate-400 hover:text-slate-600 text-xl">
                <i class='bx bx-x'></i>
            </button>
        </div>

        <form id="edit-role-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Tampilan Role (Display Name) <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_display_name" name="display_name" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Lingkup Akses Data (Data Scope) <span class="text-rose-500">*</span></label>
                <select id="edit_data_scope" name="data_scope" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    <option value="all">Seluruh Sekolah (Melihat semua jurusan & data)</option>
                    <option value="department">Berdasarkan Jurusan / Program Keahlian (Scope Kaprog)</option>
                    <option value="assigned">Berdasarkan Penugasan Kelas / Mapel (Scope Guru)</option>
                    <option value="own">Hanya Data Milik Sendiri (Scope Siswa / Ortu)</option>
                </select>
            </div>

            <div id="edit_status_container">
                <label class="block text-xs font-bold text-slate-700 mb-1">Status Role</label>
                <select id="edit_status" name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Wewenang</label>
                <textarea id="edit_description" name="description" rows="3"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditRoleModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#003EA7] hover:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddRoleModal() {
        document.getElementById('modal-add-role').classList.remove('hidden');
    }
    function closeAddRoleModal() {
        document.getElementById('modal-add-role').classList.add('hidden');
    }

    function openEditRoleModal(role) {
        const form = document.getElementById('edit-role-form');
        form.action = `/pengaturan/roles/${role.id}`;
        document.getElementById('edit_display_name').value = role.display_name;
        document.getElementById('edit_data_scope').value = role.data_scope;
        document.getElementById('edit_description').value = role.description || '';
        document.getElementById('edit_status').value = role.status;

        if (role.is_system) {
            document.getElementById('edit_status_container').classList.add('opacity-50', 'pointer-events-none');
        } else {
            document.getElementById('edit_status_container').classList.remove('opacity-50', 'pointer-events-none');
        }

        document.getElementById('modal-edit-role').classList.remove('hidden');
    }

    function closeEditRoleModal() {
        document.getElementById('modal-edit-role').classList.add('hidden');
    }

    function confirmDeleteRole(roleId, roleName, userCount) {
        if (userCount > 0) {
            Swal.fire({
                title: 'Tidak Dapat Dihapus',
                text: `Role ${roleName} masih digunakan oleh ${userCount} pengguna aktif.`,
                icon: 'warning',
                confirmButtonColor: '#003EA7'
            });
            return;
        }

        Swal.fire({
            title: 'Hapus Role?',
            text: `Apakah Anda yakin ingin menghapus role "${roleName}"? Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Role',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-role-form-${roleId}`).submit();
            }
        });
    }
</script>
@endpush
@endsection
