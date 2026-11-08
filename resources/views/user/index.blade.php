@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')
@section('page-subtitle', 'Kelola data pengguna sistem')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Data User</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">Daftar Pengguna</h2>
            <p class="text-sm text-dark-400 mt-1">Kelola data user dan akses sistem.</p>
        </div>
        <button onclick="openModal('modal-inputuser')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 hover:-translate-y-0.5">
            <i class='bx bx-plus text-lg'></i>
            Tambah User
        </button>
    </div>
@endsection

@section('content')
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ route('user') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Cari Pengguna</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-dark-400'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            placeholder="Nama pengguna...">
                    </div>
                </div>
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Role</label>
                    <select name="role"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Role</option>
                        <option value="superadmin" {{ $filter_role == 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                        <option value="admin" {{ $filter_role == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="kaprog" {{ $filter_role == 'kaprog' ? 'selected' : '' }}>Kaprog</option>
                        <option value="guru" {{ $filter_role == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="siswa" {{ $filter_role == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="orangtua" {{ $filter_role == 'orangtua' ? 'selected' : '' }}>Orang Tua</option>
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($search || $filter_role)
                    <a href="{{ route('user') }}"
                        class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-dark-50/50 border-b border-dark-200/50">
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-16 text-center">No</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Nama Akun</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Jenis Kelamin</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Role Akses</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($user as $u)
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $user->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs shadow-sm shadow-primary-500/20">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    {{ $u->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-dark-600">{{ $u->email }}</td>
                            <td class="px-6 py-4 text-sm text-center">
                                @if ($u->effective_gender === 'L')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        Laki-laki
                                    </span>
                                @elseif ($u->effective_gender === 'P')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-pink-50 text-pink-700 border border-pink-200">
                                        Perempuan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200" title="Informasi jenis kelamin belum diatur">
                                        <i class='bx bx-info-circle text-xs'></i> Belum diatur
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                @php
                                    $roleColors = [
                                        'superadmin' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                        'admin' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                        'kaprog' => 'bg-violet-50 text-violet-700 border border-violet-200',
                                        'guru' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'siswa' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                        'orangtua' => 'bg-rose-50 text-rose-700 border border-rose-200',
                                    ];
                                    $colorClass = $roleColors[$u->role] ?? 'bg-dark-50 text-dark-700 border border-dark-200';
                                @endphp
                                <span class="px-3 py-1 rounded-lg text-xs font-semibold {{ $colorClass }} uppercase tracking-wider">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editUser('{{ $u->id }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-700 transition-colors duration-200"
                                        title="Edit Data">
                                        <i class='bx bx-edit-alt text-lg'></i>
                                    </button>
                                    @if ($u->id !== Auth::id())
                                        <form action="{{ route('user.delete', ['id' => $u->id]) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="button"
                                                class="delete-confirm inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-700 transition-colors duration-200"
                                                title="Hapus Data">
                                                <i class='bx bx-trash text-lg'></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-dark-300 bg-dark-100 cursor-not-allowed" title="Akun Anda Sendiri">
                                            <i class='bx bx-lock-alt text-lg'></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 mb-4 rounded-full bg-dark-100 flex items-center justify-center">
                                        <i class='bx bx-group text-3xl text-dark-400'></i>
                                    </div>
                                    <h3 class="text-sm font-medium text-dark-900">Belum ada data user</h3>
                                    <p class="mt-1 text-sm text-dark-500">Silakan tambah data pengguna baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($user->hasPages())
            <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                {{ $user->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        {{-- Modal Tambah User (Exact Design System) --}}
        <div id="modal-inputuser" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-inputuser')"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                <div class="relative w-full max-w-xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Tambah Pengguna Baru</h2>
                            <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                                Konfigurasi Akun, Hak Akses & Keamanan
                            </span>
                        </div>
                        <button type="button" onclick="closeModal('modal-inputuser')"
                            class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                    <form action="{{ route('user') }}" method="POST" id="frmUser" class="space-y-4">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Pengguna <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-user text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="text" name="name" id="name"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="Nama lengkap" required autocomplete="off">
                                </div>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email Akses <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-envelope text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="email" name="email" id="email"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="email@sekolah.com" required autocomplete="off">
                                </div>
                            </div>

                            <div>
                                <label for="jenis_kelamin" class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="jenis_kelamin" id="jenis_kelamin" required
                                        class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-semibold text-slate-700 mb-1.5">Role Akses <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="role" id="role" required onchange="toggleAddJurusanField(this.value)"
                                        class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                                        <option value="">-- Pilih Role Akses --</option>
                                        <option value="superadmin">Super Administrator</option>
                                        <option value="admin">Administrator / Staf</option>
                                        <option value="kaprog">Kepala Program Keahlian (KAPROG)</option>
                                        <option value="guru">Guru</option>
                                        <option value="siswa">Siswa</option>
                                        <option value="orangtua">Orang Tua</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div id="wrapper_add_jurusan" class="hidden">
                                <label for="jurusan_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Jurusan Binaan (KAPROG) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="jurusan_id" id="jurusan_id"
                                        class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                                        <option value="">-- Pilih Jurusan --</option>
                                        @foreach ($jurusans as $j)
                                            <option value="{{ $j->id }}">{{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-lock-alt text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="password" name="password" id="password"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="Minimal 6 karakter" required autocomplete="off">
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#D8E6FC]">
                            <button type="button" onclick="closeModal('modal-inputuser')"
                                class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] hover:bg-[#d4e4fc] text-[#003FA9] font-bold text-sm transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#00338d] text-white font-bold text-sm shadow-md shadow-blue-500/20 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Edit User (Exact Design System) --}}
        <div id="modal-edituser" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-edituser')"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                <div class="relative w-full max-w-xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Akun Pengguna</h2>
                            <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                                Perbarui Identitas, Role & Keamanan Akun
                            </span>
                        </div>
                        <button type="button" onclick="closeModal('modal-edituser')"
                            class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                    <div id="loadeditform">
                        <div class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-[#003FA9] border-t-transparent"></div>
                            <p class="text-xs text-slate-500 mt-2">Memuat formulir...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endpush

@endsection

@push('scripts')
    <script>
        function toggleAddJurusanField(role) {
            const wrapper = document.getElementById('wrapper_add_jurusan');
            const select = document.getElementById('jurusan_id');
            if (role === 'kaprog') {
                wrapper.classList.remove('hidden');
                select.required = true;
            } else {
                wrapper.classList.add('hidden');
                select.required = false;
                select.value = '';
            }
        }

        function toggleEditJurusanField(role) {
            const wrapper = document.getElementById('wrapper_edit_jurusan');
            const select = document.getElementById('jurusan_id_edit');
            if (!wrapper) return;
            if (role === 'kaprog') {
                wrapper.classList.remove('hidden');
                if (select) select.required = true;
            } else {
                wrapper.classList.add('hidden');
                if (select) {
                    select.required = false;
                    select.value = '';
                }
            }
        }
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                if (modalId === 'modal-inputuser') {
                    setTimeout(() => {
                        document.getElementById('name').focus();
                    }, 100);
                }
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function editUser(id) {
            fetch('/user/edit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('loadeditform').innerHTML = html;
                    openModal('modal-edituser');

                    // Attach validation listener
                    const formObj = document.getElementById('frmEditUser');
                    if(formObj) attachEditFormListener(formObj);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Terjadi kesalahan saat memuat form edit.',
                        customClass: { popup: 'font-inter' }
                    });
                });
        }

        function attachEditFormListener(form) {
            form.addEventListener('submit', function(e) {
                const name = document.getElementById('name_edit').value;
                const email = document.getElementById('email_edit').value;

                if (!name || !email) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Nama dan Email wajib diisi!',
                        icon: 'warning',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Tutup',
                        customClass: { popup: 'font-inter rounded-2xl', confirmButton: 'rounded-xl' }
                    });
                }
            });
        }

        document.querySelectorAll('.delete-confirm').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Hapus Akses Pengguna?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'font-inter rounded-2xl',
                        confirmButton: 'rounded-xl',
                        cancelButton: 'rounded-xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        document.getElementById('frmUser').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;

            if (!name || !email) {
                e.preventDefault();
                Swal.fire({
                    title: 'Oops!',
                    text: 'Semua data wajib diisi!',
                    icon: 'warning',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'Tutup',
                    customClass: { popup: 'font-inter rounded-2xl', confirmButton: 'rounded-xl' }
                });
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-inputuser');
                closeModal('modal-edituser');
            }
        });
    </script>
@endpush
