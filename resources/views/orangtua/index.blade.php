@extends('layouts.app')

@section('title', 'Data Orang Tua')
@section('page-title', 'Data Orang Tua')
@section('page-subtitle', 'Kelola data orang tua siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Data Orang Tua</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">Daftar Orang Tua</h2>
            <p class="text-sm text-dark-400 mt-1">Kelola data orang tua yang terhubung dengan akun siswa.</p>
        </div>
        <a href="{{ route('siswa') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors"><i class='bx bx-user-plus text-lg'></i> Tambah melalui Data Siswa</a>
    </div>
@endsection

@section('content')
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ route('orangtua') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Cari Orang Tua / Siswa</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-dark-400'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            placeholder="Nama Orang Tua / Nama Siswa...">
                    </div>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($search)
                    <a href="{{ route('orangtua') }}"
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
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-16 text-center">
                            No</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Nama Orang Tua
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Jenis Kelamin
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Telepon</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Siswa Terkait
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center w-28">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($orang_tua as $ot)
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $orang_tua->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs shadow-sm shadow-primary-500/20">
                                        {{ strtoupper(substr($ot->nama, 0, 1)) }}
                                    </div>
                                    <p>{{ $ot->nama }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                @if ($ot->jenis_kelamin === 'L')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        Laki-laki
                                    </span>
                                @elseif ($ot->jenis_kelamin === 'P')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-pink-50 text-pink-700 border border-pink-200">
                                        Perempuan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200" title="Informasi jenis kelamin belum diatur">
                                        <i class='bx bx-info-circle text-xs'></i> Belum diatur
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-dark-600">{{ $ot->telepon }}</td>
                            <td class="px-6 py-4 text-sm text-dark-600">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class='bx bx-user text-sm'></i>
                                    {{ $ot->siswa->pluck('nama')->join(', ') ?: 'Belum terhubung' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editOrangTua('{{ $ot->id }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-700 transition-colors duration-200"
                                        title="Edit Data">
                                        <i class='bx bx-edit-alt text-lg'></i>
                                    </button>
                                    <form action="{{ route('orangtua.delete', ['id' => $ot->id]) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        <button type="button"
                                            class="delete-confirm inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-700 transition-colors duration-200"
                                            title="Hapus Data">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 mb-4 rounded-full bg-dark-100 flex items-center justify-center">
                                        <i class='bx bx-user-pin text-3xl text-dark-400'></i>
                                    </div>
                                    <h3 class="text-sm font-medium text-dark-900">Belum ada data orang tua</h3>
                                    <p class="mt-1 text-sm text-dark-500">Silakan tambah data orang tua baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($orang_tua->hasPages())
            <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                {{ $orang_tua->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        {{-- Modal Tambah Orang Tua (Exact Design System) --}}
        <div id="modal-inputorangtua" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-inputorangtua')"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                <div class="relative w-full max-w-2xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Tambah Data Orang Tua</h2>
                            <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                                Informasi Pribadi, Siswa Terkait & Akun Portal
                            </span>
                        </div>
                        <button type="button" onclick="closeModal('modal-inputorangtua')"
                            class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                    <form action="{{ route('orangtua.store') }}" method="POST" id="frmOrangTua" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full inline-block mb-1">
                                    Informasi Pribadi & Kontak
                                </span>
                            </div>

                            <div>
                                <label for="nama" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-user text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="text" name="nama" id="nama"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="Nama Orang Tua" required autocomplete="off">
                                </div>
                            </div>

                            <div>
                                <label for="telepon" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Telepon <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-phone text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="text" name="telepon" id="telepon"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="08xxxxxxxxxx" required autocomplete="off">
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="alamat" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Lengkap</label>
                                <textarea name="alamat" id="alamat" rows="2"
                                    class="block w-full p-3.5 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition resize-none"
                                    placeholder="Alamat Lengkap"></textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label for="siswa_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Siswa Terkait <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="siswa_id" id="siswa_id" required
                                        class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                                        <option value="">-- Pilih Siswa --</option>
                                        @foreach ($siswa as $s)
                                            <option value="{{ $s->id }}">{{ $s->nama }} - {{ $s->kelas->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2 pt-2">
                                <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full inline-block mb-1">
                                    Akun Akses Portal Orang Tua
                                </span>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email Akun <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-envelope text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="email" name="email" id="email"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="email@example.com" required autocomplete="off">
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
                            <button type="button" onclick="closeModal('modal-inputorangtua')"
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

        {{-- Modal Edit Orang Tua (Exact Design System) --}}
        <div id="modal-editorangtua" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editorangtua')"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                <div class="relative w-full max-w-2xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Data Orang Tua</h2>
                            <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                                Perbarui Profil, Kontak & Password Akun
                            </span>
                        </div>
                        <button type="button" onclick="closeModal('modal-editorangtua')"
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
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                if (modalId === 'modal-inputorangtua') {
                    setTimeout(() => {
                        document.getElementById('nama').focus();
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

        function editOrangTua(id) {
            fetch('/orangtua/edit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('loadeditform').innerHTML = html;
                    openModal('modal-editorangtua');

                    // Attach validation listener
                    const formObj = document.getElementById('frmEditOrangTua');
                    if (formObj) attachEditFormListener(formObj);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Terjadi kesalahan saat memuat form edit.',
                        customClass: {
                            popup: 'font-inter'
                        }
                    });
                });
        }

        function attachEditFormListener(form) {
            form.addEventListener('submit', function(e) {
                const nama = document.getElementById('nama_edit').value;
                const telepon = document.getElementById('telepon_edit').value;
                const siswa = document.getElementById('siswa_id_edit').value;

                if (!nama || !telepon || !siswa) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Nama, Telepon, dan Siswa wajib diisi!',
                        icon: 'warning',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            popup: 'font-inter rounded-2xl',
                            confirmButton: 'rounded-xl'
                        }
                    });
                }
            });
        }

        document.querySelectorAll('.delete-confirm').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Hapus Data Orang Tua?',
                    text: "Seluruh data dan akses akun miliknya akan terhapus!",
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

        document.getElementById('frmOrangTua').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama').value;
            const telepon = document.getElementById('telepon').value;
            const siswa = document.getElementById('siswa_id').value;

            if (!nama || !telepon || !siswa) {
                e.preventDefault();
                Swal.fire({
                    title: 'Oops!',
                    text: 'Semua data wajib diisi!',
                    icon: 'warning',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        popup: 'font-inter rounded-2xl',
                        confirmButton: 'rounded-xl'
                    }
                });
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-inputorangtua');
                closeModal('modal-editorangtua');
            }
        });
    </script>
@endpush
