@extends('layouts.app')

@section('title', 'Data Siswa')
@section('page-title', 'Data Siswa')
@section('page-subtitle', 'Kelola data siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Data Siswa</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dark-800">Daftar Siswa</h2>
            <p class="text-sm text-dark-400 mt-1">Kelola data siswa yang ada di sekolah.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('siswa.export.pdf', ['search' => $search ?? '', 'kelas_id' => $kelas_id ?? '']) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/35 transition-all duration-200 hover:-translate-y-0.5"
                target="_blank">
                <i class='bx bxs-file-pdf text-base'></i>
                PDF
            </a>
            <a href="{{ route('siswa.export.excel', ['search' => $search ?? '', 'kelas_id' => $kelas_id ?? '']) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/35 transition-all duration-200 hover:-translate-y-0.5">
                <i class='bx bxs-file text-base'></i>
                Excel
            </a>
            <button onclick="openModal('modal-inputsiswa')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 hover:-translate-y-0.5">
                <i class='bx bx-plus text-lg'></i>
                Tambah Data
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ route('siswa') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Cari Siswa</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-dark-400'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            placeholder="Nama / NIS...">
                    </div>
                </div>
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Kelas</label>
                    <select name="kelas_id"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($search || $kelas_id)
                    <a href="{{ route('siswa') }}"
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
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">NIS</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">L/P</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Kelas</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center w-28">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($siswa as $s)
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $siswa->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">{{ $s->nis }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($s->nama, 0, 1)) }}
                                    </div>
                                    {{ $s->nama }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $s->jenis_kelamin }}</td>
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editSiswa('{{ $s->id }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-700 transition-colors duration-200"
                                        title="Edit Data">
                                        <i class='bx bx-edit-alt text-lg'></i>
                                    </button>
                                    <form action="{{ route('siswa.delete', ['id' => $s->id]) }}" method="POST"
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
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 mb-4 rounded-full bg-dark-100 flex items-center justify-center">
                                        <i class='bx bx-user text-3xl text-dark-400'></i>
                                    </div>
                                    <h3 class="text-sm font-medium text-dark-900">Belum ada data siswa</h3>
                                    <p class="mt-1 text-sm text-dark-500">Silakan tambah data siswa baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($siswa->hasPages())
            <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                {{ $siswa->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        {{-- Modal Tambah Siswa --}}
        <div id="modal-inputsiswa" class="fixed inset-0 z-[60] hidden">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-inputsiswa')"></div>

            {{-- Modal Panel --}}
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl fade-in">
                        {{-- Header --}}
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-user-plus text-primary-600 text-xl'></i>
                                Tambah Data Siswa
                            </h3>
                            <button type="button" onclick="closeModal('modal-inputsiswa')"
                                class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="px-6 py-5">
                            <form action="{{ route('siswa.store') }}" method="POST" id="frmSiswa">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="nis" class="block text-sm font-medium text-dark-700 mb-1.5">NIS</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class='bx bx-id-card text-dark-400 text-lg'></i>
                                            </div>
                                            <input type="text" name="nis" id="nis"
                                                class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                                placeholder="NIS" required autocomplete="off">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="nama" class="block text-sm font-medium text-dark-700 mb-1.5">Nama Legkap</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class='bx bx-user text-dark-400 text-lg'></i>
                                            </div>
                                            <input type="text" name="nama" id="nama"
                                                class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                                placeholder="Nama" required autocomplete="off">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="jenis_kelamin" class="block text-sm font-medium text-dark-700 mb-1.5">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" id="jenis_kelamin" required
                                            class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="kelas_id" class="block text-sm font-medium text-dark-700 mb-1.5">Kelas</label>
                                        <select name="kelas_id" id="kelas_id" required
                                            class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach($kelas as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-2 mt-2">
                                        <hr class="border-dark-200 mb-4">
                                        <h4 class="text-sm font-semibold text-dark-600 mb-4">Akun Login Siswa</h4>
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-dark-700 mb-1.5">Email</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class='bx bx-envelope text-dark-400 text-lg'></i>
                                            </div>
                                            <input type="email" name="email" id="email"
                                                class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                                placeholder="Email" required autocomplete="off">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="password" class="block text-sm font-medium text-dark-700 mb-1.5">Password</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class='bx bx-lock-alt text-dark-400 text-lg'></i>
                                            </div>
                                            <input type="password" name="password" id="password"
                                                class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                                placeholder="Password minimal 6 karakter" required autocomplete="off">
                                        </div>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="mt-8 flex items-center justify-end gap-3">
                                    <button type="button" onclick="closeModal('modal-inputsiswa')"
                                        class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 hover:text-dark-800 font-medium transition-colors duration-200">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center gap-2">
                                        <i class='bx bx-save text-lg'></i>
                                        Simpan Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Edit Siswa --}}
        <div id="modal-editsiswa" class="fixed inset-0 z-[60] hidden">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editsiswa')"></div>

            {{-- Modal Panel --}}
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl fade-in">
                        {{-- Header --}}
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-edit-alt text-primary-600 text-xl'></i>
                                Edit Data Siswa
                            </h3>
                            <button type="button" onclick="closeModal('modal-editsiswa')"
                                class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>

                        {{-- Body (Loaded via AJAX) --}}
                        <div class="px-6 py-5" id="loadeditform">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endpush

@endsection

@push('scripts')
    <script>
        // Modal Functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                // Prevent body scroll
                document.body.style.overflow = 'hidden';

                // Focus first input if it's the add modal
                if (modalId === 'modal-inputsiswa') {
                    setTimeout(() => {
                        document.getElementById('nis').focus();
                    }, 100);
                }
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                // Restore body scroll
                document.body.style.overflow = '';
            }
        }

        // Modal Edit Call
        function editSiswa(id) {
            fetch('/siswa/edit', {
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
                    openModal('modal-editsiswa');

                    // Attach form listener for the newly loaded edit form
                    const formObj = document.getElementById('frmEditSiswa');
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
                const nis = document.getElementById('nis_edit').value;
                const nama = document.getElementById('nama_edit').value;

                if (!nis || !nama) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Data wajib Harus Diisi!',
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

        // Delete Confirmation
        document.querySelectorAll('.delete-confirm').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Hapus Data Siswa?',
                    text: "Data yang dihapus beserta akun terkait tidak dapat dikembalikan!",
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

        // Form Validation (Add)
        document.getElementById('frmSiswa').addEventListener('submit', function(e) {
            const nis = document.getElementById('nis').value;
            const nama = document.getElementById('nama').value;

            if (!nis || !nama) {
                e.preventDefault();
                Swal.fire({
                    title: 'Oops!',
                    text: 'Data wajib Harus Diisi!',
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

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-inputsiswa');
                closeModal('modal-editsiswa');
            }
        });
    </script>
@endpush
