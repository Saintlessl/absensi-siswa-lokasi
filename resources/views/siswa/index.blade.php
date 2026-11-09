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
            <h2 class="text-xl font-medium text-dark-800">Daftar Siswa</h2>
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
            <a href="{{ route('siswa.import') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#003EA7] hover:bg-[#002D7A] text-white text-xs font-semibold rounded-xl shadow-lg shadow-blue-700/25 hover:shadow-blue-700/35 transition-all duration-200 hover:-translate-y-0.5">
                <i class='bx bx-cloud-upload text-base'></i>
                Import Excel
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
    @if (session('parent_activation_link'))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-semibold">Tautan aktivasi akun orang tua siap dibagikan melalui kanal yang aman.</p>
            <p class="mt-1">Tautan ini hanya berlaku 7 hari dan tidak memuat password.</p>
            <input class="mt-2 w-full rounded border border-amber-300 bg-white px-2 py-1 font-mono text-xs" readonly value="{{ session('parent_activation_link') }}" onclick="this.select()">
        </div>
    @endif
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
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Jurusan</label>
                    <select name="jurusan_id"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusans as $j)
                            <option value="{{ $j->id }}" {{ ($jurusan_id ?? '') == $j->id ? 'selected' : '' }}>
                                {{ $j->kode_jurusan }}
                            </option>
                        @endforeach
                    </select>
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
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Angkatan</label>
                    <select name="angkatan_id"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Angkatan</option>
                        @foreach ($angkatans as $akt)
                            <option value="{{ $akt->id }}" {{ ($angkatan_id ?? '') == $akt->id ? 'selected' : '' }}>
                                {{ $akt->nama_angkatan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-36">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Status</label>
                    <select name="status"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ ($status ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lulus" {{ ($status ?? '') === 'lulus' ? 'selected' : '' }}>Lulus (Alumni)</option>
                        <option value="dalam_proses_kelulusan" {{ ($status ?? '') === 'dalam_proses_kelulusan' ? 'selected' : '' }}>Proses Lulus</option>
                        <option value="tidak_lulus" {{ ($status ?? '') === 'tidak_lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                        <option value="pindah" {{ ($status ?? '') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                        <option value="keluar" {{ ($status ?? '') === 'keluar' ? 'selected' : '' }}>Keluar</option>
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($search || $kelas_id || ($jurusan_id ?? '') || ($angkatan_id ?? '') || ($status ?? ''))
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
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-14 text-center">No</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Identitas (NIS / NISN)</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Nama Lengkap & Akun</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Kelas & Jurusan</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Kontak Orang Tua (WA)</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($siswa as $s)
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $siswa->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-bold text-dark-800 font-mono">{{ $s->nis }}</div>
                                <div class="text-[11px] text-dark-400">NISN: {{ $s->nisn ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs flex-shrink-0 overflow-hidden">
                                        @if ($s->photo_url)
                                            <img src="{{ $s->photo_url }}" alt="{{ $s->nama }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($s->nama, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-dark-900">
                                            <a href="{{ route('siswa.show', $s->id) }}" class="hover:text-primary-600 transition">
                                                {{ $s->nama }}
                                            </a>
                                            @if ($s->jenis_kelamin === 'L')
                                                <span class="text-xs font-semibold text-blue-600">(L)</span>
                                            @elseif ($s->jenis_kelamin === 'P')
                                                <span class="text-xs font-semibold text-pink-600">(P)</span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200" title="Jenis kelamin belum diatur">Belum diatur</span>
                                            @endif
                                        </div>
                                        @if ($s->tempat_lahir || $s->tanggal_lahir)
                                            <div class="text-[11px] text-dark-500 flex items-center gap-1">
                                                <i class='bx bx-calendar text-xs text-dark-400'></i>
                                                <span>{{ $s->ttl }}</span>
                                            </div>
                                        @endif
                                        <div class="text-[11px] text-primary-600 flex items-center gap-1 font-mono">
                                            <i class='bx bx-envelope text-xs'></i> {{ $s->user->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="inline-flex flex-col items-center gap-1">
                                    <span class="font-bold text-dark-800">{{ $s->kelas->nama_kelas ?? '-' }}</span>
                                    <div class="flex items-center gap-1">
                                        @if ($s->kelas && $s->kelas->jurusan)
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-dark-100 text-dark-600">
                                                {{ $s->kelas->jurusan->kode_jurusan }}
                                            </span>
                                        @endif
                                        @if ($s->angkatan)
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-[#003EA7] border border-blue-200" title="{{ $s->angkatan->nama_angkatan }}">
                                                Akt {{ $s->angkatan->nomor_angkatan }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border {{ $s->status_badge }}">
                                        {{ $s->status_label }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($s->orangtua->first()?->telepon)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">
                                        <i class='bx bxl-whatsapp text-sm text-emerald-600'></i>
                                        <span>{{ $s->orangtua->first()->telepon }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        Belum diisi
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('siswa.show', $s->id) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 transition-colors duration-200"
                                        title="Profil & Riwayat Akademik">
                                        <i class='bx bx-history text-lg'></i>
                                    </a>
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
        {{-- Multi-Step Wizard Modal for Siswa (Exact Design System) --}}
        @include('siswa.modal-wizard')

        {{-- Modal Edit Siswa (Exact Design System) --}}
        <div id="modal-editsiswa" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editsiswa')"></div>

            {{-- Modal Positioning Container --}}
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                <div class="relative w-full max-w-3xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                    {{-- Header --}}
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Data Siswa</h2>
                            <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                                Perbarui Profil, Akun & Relasi Orang Tua
                            </span>
                        </div>
                        <button type="button" onclick="closeModal('modal-editsiswa')"
                            class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                    {{-- Body (Loaded via AJAX) --}}
                    <div id="loadeditform"></div>
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

        // Filter Kelas by Jurusan in Add Modal
        document.getElementById('modal_filter_jurusan')?.addEventListener('change', function() {
            const selectedJurusan = this.value;
            const kelasSelect = document.getElementById('kelas_id');
            if (!kelasSelect) return;

            Array.from(kelasSelect.options).forEach((opt, idx) => {
                if (idx === 0) return;
                const optJurusan = opt.getAttribute('data-jurusan');
                if (!selectedJurusan || optJurusan === selectedJurusan) {
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });
            if (kelasSelect.selectedOptions[0] && kelasSelect.selectedOptions[0].disabled) {
                kelasSelect.selectedIndex = 0;
            }
        });

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

        // Form Validation (Add) — fields live inside the wizard with id="input_*"
        document.getElementById('frmSiswa').addEventListener('submit', function(e) {
            const nis = (document.getElementById('input_nis') || {value:''}).value.trim();
            const nama = (document.getElementById('input_nama') || {value:''}).value.trim();

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

        // Photo Preview for Add Student Modal
        function previewAddStudentPhoto(input) {
            const container = document.getElementById('preview_add_photo_container');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    container.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                container.innerHTML = `<i class='bx bx-user text-2xl text-dark-400'></i>`;
            }
        }

        // 3-Step Dependent Dropdown (Jurusan + Tingkat -> Rombel)
        function filterAddStudentRombel() {
            const jurusanId = document.getElementById('modal_filter_jurusan').value;
            const tingkat = document.getElementById('modal_filter_tingkat').value;
            const rombelSelect = document.getElementById('kelas_id');
            const options = rombelSelect.querySelectorAll('option');

            options.forEach(opt => {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                const optJurusan = opt.getAttribute('data-jurusan');
                const optTingkat = opt.getAttribute('data-tingkat');

                const matchJurusan = !jurusanId || optJurusan === jurusanId;
                const matchTingkat = !tingkat || optTingkat === tingkat;

                if (matchJurusan && matchTingkat) {
                    opt.hidden = false;
                } else {
                    opt.hidden = true;
                }
            });

            const selectedOption = rombelSelect.options[rombelSelect.selectedIndex];
            if (selectedOption && selectedOption.hidden) {
                rombelSelect.value = "";
            }
        }

        const existingParent = document.getElementById('existing_orangtua_id');
        if (existingParent) {
            existingParent.addEventListener('change', function () {
                const useExisting = !!this.value;
                ['parent_name', 'parent_jenis_kelamin', 'parent_phone', 'parent_address'].forEach(id => {
                    const field = document.getElementById(id);
                    field.required = !useExisting;
                    field.disabled = useExisting;
                });
            });
        }
    </script>
@endpush
