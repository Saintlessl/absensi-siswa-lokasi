@extends('layouts.app')

@section('title', 'Data Jadwal Pelajaran')
@section('page-title', 'Data Jadwal Pelajaran')
@section('page-subtitle', 'Kelola data Jadwal Pelajaran')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Data Jadwal Pelajaran</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">Daftar Jadwal Pelajaran</h2>
            <p class="text-sm text-dark-400 mt-1">Kelola data Jadwal Pelajaran yang ada di sekolah.</p>
        </div>
        <button onclick="openModal('modal-inputjadwal')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 hover:-translate-y-0.5">
            <i class='bx bx-plus text-lg'></i>
            Tambah Data
        </button>
    </div>
@endsection

@section('content')
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ route('jadwal') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Hari</label>
                    <select name="hari"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Hari</option>
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                            <option value="{{ $h }}" {{ $filter_hari == $h ? 'selected' : '' }}>{{ $h }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Kelas</label>
                    <select name="kelas_id"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}" {{ $filter_kelas == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($filter_hari || $filter_kelas)
                    <a href="{{ route('jadwal') }}"
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
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Hari</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Jam
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Guru
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Mata
                            Pelajaran</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center w-28">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($jadwal as $s)
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $jadwal->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">{{ $s->kelas->nama_kelas }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">{{ $s->hari }}</td>

                            <td class="px-6 py-4 text-sm text-dark-600 text-center">
                                {{ \Carbon\Carbon::parse($s->jam_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($s->jam_selesai)->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800 text-center">{{ $s->guru->nama }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800 text-center">{{ $s->mata_pelajaran }}
                            </td>

                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editJadwal('{{ $s->id }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-700 transition-colors duration-200"
                                        title="Edit Data">
                                        <i class='bx bx-edit-alt text-lg'></i>
                                    </button>
                                    <form action="{{ route('jadwal.delete', ['id' => $s->id]) }}" method="POST"
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
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 mb-4 rounded-full bg-dark-100 flex items-center justify-center">
                                        <i class='bx bx-calendar-x text-3xl text-dark-400'></i>
                                    </div>
                                    <h3 class="text-sm font-medium text-dark-900">Belum ada data jadwal</h3>
                                    <p class="mt-1 text-sm text-dark-500">Silakan tambah data jadwal baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($jadwal->hasPages())
            <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                {{ $jadwal->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        {{-- Modal Tambah jadwal (Wizard) --}}
        @include('jadwal.modal-wizard')

        {{-- Modal Edit Jadwal (Exact Design System) --}}
        <div id="modal-editjadwal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editjadwal')"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                <div class="relative w-full max-w-2xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Jadwal Pelajaran</h2>
                            <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                                Perbarui Mapel, Guru Pengajar, Kelas & Waktu
                            </span>
                        </div>
                        <button type="button" onclick="closeModal('modal-editjadwal')"
                            class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                    {{-- Body (Loaded via AJAX) --}}
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
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });

        function updateMapel() {
            const selectGuru = document.getElementById('guru_id');
            const inputMapel = document.getElementById('mata_pelajaran');

            const selectedOption = selectGuru.options[selectGuru.selectedIndex];

            const mapel = selectedOption.getAttribute('data-mapel');

            inputMapel.value = mapel;
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                // Focus otomatis pada input pertama (Mata Pelajaran)
                if (modalId === 'modal-inputjadwal') {
                    setTimeout(() => {
                        document.getElementById('mata_pelajaran').focus();
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

        // Validasi Form Tambah Jadwal
        document.getElementById('frmJadwal').addEventListener('submit', function(e) {
            const mapel = document.getElementById('mata_pelajaran').value;
            const guru = document.getElementById('guru_id').value;
            const kelas = document.getElementById('kelas_id').value;
            const jamMulai = document.getElementById('jam_mulai').value;
            const jamSelesai = document.getElementById('jam_selesai').value;

            if (!mapel || !guru || !kelas || !jamMulai || !jamSelesai) {
                e.preventDefault();
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Semua data jadwal wajib diisi!',
                    icon: 'warning',
                    confirmButtonColor: '#10b981',
                    customClass: {
                        popup: 'font-inter rounded-2xl'
                    }
                });
                return;
            }

            if (jamSelesai <= jamMulai) {
                e.preventDefault();
                Swal.fire({
                    title: 'Input Tidak Valid',
                    text: 'Jam selesai harus lebih besar dari jam mulai!',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-inputjadwal');
            }
        });

        // Modal Edit Call
        function editJadwal(id) {
            // Tampilkan loading (opsional)
            document.getElementById('loadeditform').innerHTML =
                '<div class="text-center py-5"><i class="bx bx-loader-alt bx-spin text-3xl text-primary-600"></i></div>';
            openModal('modal-editjadwal');

            fetch('/jadwal/edit', { // Pastikan route ini sesuai di web.php
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

                    // Inisialisasi ulang flatpickr untuk input jam di dalam modal
                    flatpickr(".timepicker", {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        time_24hr: true
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data.'
                    });
                });
        }

        function attachEditFormListener(form) {
            form.addEventListener('submit', function(e) {
                const namaKelas = document.getElementById('nama_kelas_edit').value;
                const waliKelas = document.getElementById('wali_kelas_edit').value;

                if (!namaKelas || !waliKelas) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Nama Kelas dan Wali Kelas wajib diisi!',
                        icon: 'warning',
                        confirmButtonColor: '#6366f1',
                        customClass: {
                            popup: 'font-inter rounded-2xl'
                        }
                    });
                }
            });
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-confirm')) {
                e.preventDefault();
                const button = e.target.closest('.delete-confirm');
                const form = button.closest('form');

                Swal.fire({
                    title: 'Hapus Jadwal?',
                    text: "Data jadwal ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444', // Warna Merah (Rose-600)
                    cancelButtonColor: '#64748b', // Warna Abu-abu
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'font-inter rounded-2xl',
                        confirmButton: 'px-6 py-2.5 rounded-xl',
                        cancelButton: 'px-6 py-2.5 rounded-xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'guru_id_edit') {
                const selectGuru = e.target;
                const inputMapel = document.getElementById('mata_pelajaran_edit');

                // Ambil atribut data-mapel dari option yang dipilih
                const selectedOption = selectGuru.options[selectGuru.selectedIndex];
                const mapel = selectedOption.getAttribute('data-mapel');

                if (inputMapel) {
                    inputMapel.value = mapel || '';
                    console.log("Mapel diupdate ke: " + mapel); // Untuk debugging
                }
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-inputkelas');
                closeModal('modal-editkelas');
            }
        });
    </script>
@endpush
