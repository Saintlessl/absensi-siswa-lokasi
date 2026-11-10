@extends('layouts.app')

@section('title', 'Data Orang Tua')
@section('page-title', 'Data Orang Tua')
@section('page-subtitle', 'Kelola data orang tua siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Data Orang Tua</li>
@endsection

@section('content')
    {{-- Main Container Card (Matching Data Siswa Reference 1) --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100">
        {{-- Card Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Daftar Orang Tua</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola data orang tua yang terhubung dengan akun siswa.</p>
            </div>
            <div class="flex items-center gap-2.5 self-end sm:self-auto">
                <a href="{{ route('siswa') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold rounded-xl shadow-md shadow-blue-900/10 transition-all">
                    <i class='bx bx-plus text-base'></i>
                    <span>Tambah Data</span>
                </a>
            </div>
        </div>

        {{-- Search Bar / Filter Bar --}}
        <form action="{{ route('orangtua') }}" method="GET" class="mb-6">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                {{-- Search Input --}}
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Cari Orang Tua / Siswa</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class='bx bx-search text-base'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="w-full h-11 pl-10 pr-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-medium placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                            placeholder="Cari nama orang tua atau nama siswa...">
                    </div>
                </div>

                {{-- Actions: Filter & Reset --}}
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="h-11 px-5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm transition">
                        <i class='bx bx-filter-alt text-sm'></i>
                        <span>Filter</span>
                    </button>
                    @if ($search)
                        <a href="{{ route('orangtua') }}"
                            class="h-11 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold inline-flex items-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table Data Orang Tua (Matching Data Siswa: Clean typography, no chips, no translucent borders) --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NAMA ORANG TUA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">JENIS KELAMIN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NO. TELEPON / WHATSAPP</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">SISWA TERKAIT</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-28">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($orang_tua as $ot)
                        <tr class="hover:bg-[#EFF5FF]/40 transition">
                            {{-- 1. No --}}
                            <td class="py-4 px-4 text-xs font-medium text-slate-400 text-center">
                                {{ $orang_tua->firstItem() + $loop->index }}
                            </td>

                            {{-- 2. Nama Orang Tua & Email --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#003FA9] text-white flex-shrink-0 flex items-center justify-center text-xs font-extrabold shadow-sm">
                                        {{ strtoupper(substr($ot->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 block text-xs sm:text-sm">
                                            {{ $ot->nama }}
                                        </span>
                                        @if ($ot->user?->email)
                                            <span class="text-[11px] text-slate-400 font-mono block">
                                                {{ $ot->user->email }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- 3. Jenis Kelamin (Clean solid text) --}}
                            <td class="py-4 px-4 text-center">
                                @if ($ot->jenis_kelamin === 'L')
                                    <span class="text-xs font-semibold text-slate-700">Laki-laki</span>
                                @elseif ($ot->jenis_kelamin === 'P')
                                    <span class="text-xs font-semibold text-slate-700">Perempuan</span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum diatur</span>
                                @endif
                            </td>

                            {{-- 4. No. Telepon / WhatsApp --}}
                            <td class="py-4 px-4">
                                @if ($ot->telepon)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $ot->telepon) }}" target="_blank"
                                        class="text-[#003FA9] hover:underline font-bold text-xs inline-flex items-center gap-1 font-mono">
                                        <i class='bx bxl-whatsapp text-emerald-600 text-sm'></i>
                                        {{ $ot->telepon }}
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- 5. Siswa Terkait --}}
                            <td class="py-4 px-4">
                                @if ($ot->siswa && $ot->siswa->isNotEmpty())
                                    <div class="space-y-1">
                                        @foreach ($ot->siswa as $anak)
                                            <a href="{{ route('siswa.show', $anak->id) }}"
                                                class="font-bold text-slate-900 hover:text-[#003FA9] transition block text-xs">
                                                {{ $anak->nama }}
                                                @if ($anak->kelas)
                                                    <span class="font-normal text-slate-400">({{ $anak->kelas->nama_kelas }})</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum terhubung</span>
                                @endif
                            </td>

                            {{-- 6. Aksi (Minimalist icons matching Data Siswa) --}}
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="editOrangTua('{{ $ot->id }}')"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Edit Orang Tua">
                                        <i class='bx bx-edit text-lg'></i>
                                    </button>
                                    <form action="{{ route('orangtua.delete', ['id' => $ot->id]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="button"
                                            class="delete-confirm p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Hapus Orang Tua">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 mb-3 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                        <i class='bx bx-user-pin text-2xl'></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Belum ada data orang tua</h3>
                                    <p class="text-xs text-slate-400 mt-1">Data orang tua terbuat secara otomatis saat mendaftarkan siswa baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($orang_tua->hasPages())
            <div class="pt-6 border-t border-slate-100">
                {{ $orang_tua->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        {{-- Modal Tambah Orang Tua (Design System Matching Data Siswa) --}}
        <div id="modal-inputorangtua" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-inputorangtua')"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                <div class="relative w-full max-w-2xl rounded-[28px] bg-white p-6 sm:p-8 shadow-2xl border border-slate-100 my-auto transform transition-all text-slate-800">
                    <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-6">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Tambah Data Orang Tua</h2>
                            <p class="text-xs text-slate-400 mt-1">
                                Informasi pribadi, siswa terkait dan akun portal orang tua.
                            </p>
                        </div>
                        <button type="button" onclick="closeModal('modal-inputorangtua')"
                            class="text-slate-400 hover:text-slate-800 p-1.5 rounded-xl transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('orangtua.store') }}" method="POST" id="frmOrangTua" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2 pb-1 border-b border-slate-100">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Informasi Pribadi & Kontak
                                </h4>
                            </div>

                            <div>
                                <label for="nama" class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-user text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="text" name="nama" id="nama"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="Nama Orang Tua" required autocomplete="off">
                                </div>
                            </div>

                            <div>
                                <label for="telepon" class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Telepon <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-phone text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="text" name="telepon" id="telepon"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="08xxxxxxxxxx" required autocomplete="off">
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="alamat" class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap</label>
                                <textarea name="alamat" id="alamat" rows="2"
                                    class="block w-full p-3.5 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition resize-none"
                                    placeholder="Alamat Lengkap"></textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label for="siswa_id" class="block text-xs font-bold text-slate-700 mb-1.5">Siswa Terkait <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="siswa_id" id="siswa_id" required
                                        class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                                        <option value="">-- Pilih Siswa --</option>
                                        @foreach ($siswa as $s)
                                            <option value="{{ $s->id }}">{{ $s->nama }} - {{ $s->kelas->nama_kelas ?? '-' }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2 pt-3 pb-1 border-b border-slate-100">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Akun Akses Portal Orang Tua
                                </h4>
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Email Akun <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-envelope text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="email" name="email" id="email"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="email@example.com" required autocomplete="off">
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class='bx bx-lock-alt text-slate-400 text-lg'></i>
                                    </div>
                                    <input type="password" name="password" id="password"
                                        class="block w-full pl-10 pr-4 h-11 rounded-xl bg-[#EFF5FF] border-none text-slate-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                        placeholder="Minimal 6 karakter" required autocomplete="off">
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="pt-5 flex items-center justify-end gap-3 border-t border-slate-100">
                            <button type="button" onclick="closeModal('modal-inputorangtua')"
                                class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs sm:text-sm shadow-sm transition">
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
                <div class="relative w-full max-w-2xl rounded-[28px] bg-white p-6 sm:p-8 shadow-2xl border border-slate-100 my-auto transform transition-all text-slate-800">
                    <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-6">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Edit Data Orang Tua</h2>
                            <p class="text-xs text-slate-400 mt-1">
                                Perbarui profil, kontak & akun portal orang tua.
                            </p>
                        </div>
                        <button type="button" onclick="closeModal('modal-editorangtua')"
                            class="text-slate-400 hover:text-slate-800 p-1.5 rounded-xl transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

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
                        const namaInput = document.getElementById('nama');
                        if (namaInput) namaInput.focus();
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
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Terjadi kesalahan saat memuat form edit.',
                        customClass: {
                            popup: 'font-inter rounded-2xl',
                            confirmButton: 'rounded-xl'
                        }
                    });
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

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-inputorangtua');
                closeModal('modal-editorangtua');
            }
        });
    </script>
@endpush
