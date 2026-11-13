@extends('layouts.app')

@section('title', 'Data Siswa')
@section('page-title', 'Data Siswa')
@section('page-subtitle', 'Kelola data siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Data Siswa</li>
@endsection

@section('content')
    @if (session('parent_activation_link'))
        <div class="mb-5 rounded-2xl bg-amber-50 p-4 border border-amber-200 text-sm text-amber-900 shadow-sm">
            <div class="flex items-center gap-2 font-bold">
                <i class='bx bx-key text-base text-amber-700'></i>
                <span>Tautan aktivasi akun orang tua siap dibagikan:</span>
            </div>
            <p class="mt-1 text-xs text-amber-700">Tautan ini hanya berlaku 7 hari dan tidak memuat password.</p>
            <input class="mt-2 w-full rounded-xl border border-amber-300 bg-white px-3 py-2 font-mono text-xs text-slate-800" readonly value="{{ session('parent_activation_link') }}" onclick="this.select()">
        </div>
    @endif

    {{-- Indikator Siswa Belum Memiliki Pasfoto (Requirement Section 8) --}}
    @if(isset($tanpaFotoCount) && $tanpaFotoCount > 0)
        <div class="mb-5 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <i class='bx bx-camera-off text-xl'></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-amber-800">Terdapat {{ $tanpaFotoCount }} siswa belum memiliki foto profil.</p>
                    <p class="text-[11px] text-amber-600 mt-0.5">Lengkapi foto profil melalui menu Edit Siswa agar identitas visual dapat dikenali guru dan staf.</p>
                </div>
            </div>
            <a href="{{ route('siswa', array_merge(request()->query(), ['tanpa_foto' => 1])) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition shadow-xs whitespace-nowrap">
                <i class='bx bx-filter-alt'></i>
                <span>Lihat Siswa Tanpa Foto</span>
            </a>
        </div>
    @endif

    {{-- Main Container Card (Matching Reference 1: SUPER ADMIN - DATA SISWA.png) --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100">
        {{-- Card Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Daftar Siswa</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola data siswa yang ada di sekolah.</p>
            </div>
            <div class="flex items-center gap-2.5 self-end sm:self-auto">
                <button type="button" onclick="openModal('modal-inputsiswa')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold rounded-xl shadow-md shadow-blue-900/10 transition-all">
                    <i class='bx bx-plus text-base'></i>
                    <span>Tambah Data</span>
                </button>
                {{-- Quick Actions Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false" type="button"
                        class="w-10 h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 flex items-center justify-center text-xl font-bold transition shadow-xs"
                        title="Menu Tindakan Lainnya">
                        <i class='bx bx-dots-horizontal-rounded'></i>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 rounded-2xl bg-white shadow-xl border border-slate-100 py-2 z-30 text-xs">
                        <a href="{{ route('siswa.import') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-[#003FA9] font-semibold transition">
                            <i class='bx bx-cloud-upload text-base text-[#003FA9]'></i>
                            <span>Import Excel</span>
                        </a>
                        <a href="{{ route('siswa.export.excel', request()->all()) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 font-semibold transition">
                            <i class='bx bxs-file text-base text-emerald-600'></i>
                            <span>Export Excel</span>
                        </a>
                        <a href="{{ route('siswa.export.pdf', request()->all()) }}" target="_blank" class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 hover:bg-rose-50 hover:text-rose-700 font-semibold transition">
                            <i class='bx bxs-file-pdf text-base text-rose-600'></i>
                            <span>Export PDF</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search & Filter Bar (Matching Reference 1: Form controls in a clean row) --}}
        <form action="{{ route('siswa') }}" method="GET" class="mb-6">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-end gap-3">
                {{-- Cari Siswa Input --}}
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Cari Siswa</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class='bx bx-search text-base'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="w-full h-11 pl-10 pr-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-medium placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                            placeholder="Nama / NIS...">
                    </div>
                </div>

                {{-- Kelas Filter --}}
                <div class="w-full sm:w-44">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Kelas</label>
                    <div class="relative">
                        <select name="kelas_id"
                            class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                {{-- Jurusan Filter --}}
                <div class="w-full sm:w-40">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Jurusan</label>
                    <div class="relative">
                        <select name="jurusan_id"
                            class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                            <option value="">Semua Jurusan</option>
                            @foreach ($jurusans as $j)
                                <option value="{{ $j->id }}" {{ ($jurusan_id ?? '') == $j->id ? 'selected' : '' }}>
                                    {{ $j->kode_jurusan }}
                                </option>
                            @endforeach
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="w-full sm:w-36">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Status</label>
                    <div class="relative">
                        <select name="status"
                            class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ ($status ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="lulus" {{ ($status ?? '') === 'lulus' ? 'selected' : '' }}>Lulus</option>
                            <option value="dalam_proses_kelulusan" {{ ($status ?? '') === 'dalam_proses_kelulusan' ? 'selected' : '' }}>Proses Lulus</option>
                            <option value="tidak_lulus" {{ ($status ?? '') === 'tidak_lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                            <option value="pindah" {{ ($status ?? '') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                            <option value="keluar" {{ ($status ?? '') === 'keluar' ? 'selected' : '' }}>Keluar</option>
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                {{-- Actions: Filter & Reset --}}
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="h-11 px-5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm transition">
                        <i class='bx bx-filter-alt text-sm'></i>
                        <span>Filter</span>
                    </button>
                    @if ($search || $kelas_id || ($jurusan_id ?? '') || ($angkatan_id ?? '') || ($status ?? ''))
                        <a href="{{ route('siswa') }}"
                            class="h-11 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold inline-flex items-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table Data Siswa (Exact Reference 1: Clean typography, no chips, no decorative translucent borders) --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">IDENTITAS (NIS / NISN)</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NAMA LENGKAP</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KELAS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KONTAK ORANG TUA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-28">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($siswa as $s)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- 1. No --}}
                            <td class="py-4 px-4 text-xs font-medium text-slate-400 text-center">
                                {{ $siswa->firstItem() + $loop->index }}
                            </td>

                            {{-- 2. Identitas (NIS / NISN) --}}
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900 font-mono text-xs sm:text-sm">{{ $s->nis }}</div>
                                <div class="text-[11px] text-slate-400 font-normal">NISN: {{ $s->nisn ?? '-' }}</div>
                            </td>

                            {{-- 3. Identitas Visual (Foto, Nama Lengkap & Email) --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-[#EFF5FF] border border-blue-100 shrink-0 flex items-center justify-center shadow-xs">
                                        @if ($s->photo_url)
                                            <img src="{{ $s->photo_url }}" alt="{{ $s->nama }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-xs font-bold text-[#003FA9]">{{ strtoupper(substr($s->nama, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('siswa.show', $s->id) }}" class="font-bold text-slate-900 hover:text-[#003FA9] transition block truncate">
                                            {{ $s->nama }}
                                        </a>
                                        @if($s->user?->email)
                                            <div class="text-[11px] text-slate-400 font-mono truncate">{{ $s->user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- 4. Kelas (Clean text, no chip) --}}
                            <td class="py-4 px-4 font-semibold text-slate-800 text-xs sm:text-sm">
                                {{ $s->kelas->nama_kelas ?? '-' }}
                            </td>

                            {{-- 5. Kontak Orang Tua (Royal Blue text, no translucent green badge) --}}
                            <td class="py-4 px-4">
                                @if($s->orangtua->first()?->telepon)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $s->orangtua->first()->telepon) }}" target="_blank"
                                       class="text-[#003FA9] hover:underline font-bold text-xs inline-flex items-center gap-1 font-mono">
                                        {{ $s->orangtua->first()->telepon }}
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- 6. Aksi (Minimalist icons matching Reference 1) --}}
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('siswa.show', $s->id) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Detail Siswa">
                                        <i class='bx bx-info-circle text-lg'></i>
                                    </a>
                                    @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
                                        <button type="button" onclick="editSiswa('{{ $s->id }}')"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                            title="Edit Siswa">
                                            <i class='bx bx-edit text-lg'></i>
                                        </button>
                                        <form action="{{ route('siswa.delete', ['id' => $s->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="button"
                                                class="delete-confirm p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                title="Hapus Siswa">
                                                <i class='bx bx-trash text-lg'></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class='bx bx-user text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Belum ada data siswa</p>
                                <p class="text-xs text-slate-400 mt-0.5">Tidak ditemukan data siswa yang sesuai filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($siswa->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{ $siswa->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        {{-- Multi-Step Wizard Modal for Siswa (Exact Design System) --}}
        @include('siswa.modal-wizard')

        {{-- Modal Edit Siswa (Design System) --}}
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
                document.body.style.overflow = 'hidden';

                if (modalId === 'modal-inputsiswa') {
                    setTimeout(() => {
                        const firstInput = document.getElementById('input_nis') || document.getElementById('nis');
                        if (firstInput) firstInput.focus();
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
                const nis = document.getElementById('nis_edit')?.value;
                const nama = document.getElementById('nama_edit')?.value;

                if (!nis || !nama) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Data wajib Harus Diisi!',
                        icon: 'warning',
                        confirmButtonColor: '#003FA9',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            popup: 'font-inter rounded-2xl',
                            confirmButton: 'rounded-xl'
                        }
                    });
                }
            });
        }

        function previewEditStudentPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById('preview_edit_photo_container');
                    if (container) {
                        container.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">`;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
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
    </script>
@endpush
