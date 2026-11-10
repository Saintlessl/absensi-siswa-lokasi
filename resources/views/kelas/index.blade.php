@extends('layouts.app')

@section('title', 'Data Rombongan Belajar')
@section('page-title', 'Rombongan Belajar (Rombel)')
@section('page-subtitle', 'Kelola struktur kelas rombel per jurusan dan tingkat')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600 transition-colors">Akademik</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Tingkat & Rombel</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">Daftar Rombongan Belajar (Rombel)</h2>
            <p class="text-sm text-dark-400 mt-1">Struktur kelas hierarkis terstandarisasi berdasarkan Jurusan dan Tingkat.</p>
        </div>
        <button onclick="openModal('modal-inputkelas')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 hover:-translate-y-0.5">
            <i class='bx bx-plus text-lg'></i>
            Tambah Rombel
        </button>
    </div>
@endsection

@section('content')
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ route('kelas') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Cari Rombel / Wali</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-dark-400'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            placeholder="Nama Rombel / Wali Kelas...">
                    </div>
                </div>
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Jurusan</label>
                    <select name="jurusan_id" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusans as $j)
                            <option value="{{ $j->id }}" {{ ($jurusan_id ?? '') == $j->id ? 'selected' : '' }}>
                                {{ $j->kode_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($search || $jurusan_id)
                    <a href="{{ route('kelas') }}"
                        class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NAMA ROMBEL</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">TINGKAT</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">JURUSAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TAHUN AJARAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">WALI KELAS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">KAPASITAS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-28">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($kelas as $k)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4 text-xs font-medium text-slate-400 text-center">{{ $kelas->firstItem() + $loop->index }}</td>
                            <td class="py-4 px-4">
                                <a href="{{ route('academic.class', $k) }}" class="font-bold text-slate-900 hover:text-[#003FA9] transition font-mono text-xs sm:text-sm inline-flex items-center gap-1.5">
                                    {{ $k->nama_kelas }}
                                    <i class='bx bx-link-external text-xs text-slate-400'></i>
                                </a>
                            </td>
                            <td class="py-4 px-4 text-center font-semibold text-slate-800 text-xs sm:text-sm">
                                Kelas {{ $k->tingkat ?: '-' }}
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-900 font-mono text-xs sm:text-sm">
                                {{ $k->jurusan->kode_jurusan ?? '-' }}
                            </td>
                            <td class="py-4 px-4 text-xs font-mono text-slate-600">
                                {{ $k->tahun_ajaran ?: '2026/2027' }}
                            </td>
                            <td class="py-4 px-4 font-medium text-slate-800 text-xs sm:text-sm">
                                {{ $k->wali_kelas ?: '-' }}
                            </td>
                            <td class="py-4 px-4 text-center font-semibold text-slate-800 text-xs sm:text-sm">
                                {{ $k->jumlah_siswa }} Siswa
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('academic.class', $k) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Detail Rombel">
                                        <i class='bx bx-info-circle text-lg'></i>
                                    </a>
                                    <button type="button" onclick="editKelas('{{ $k->id }}')"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Edit Rombel">
                                        <i class='bx bx-edit text-lg'></i>
                                    </button>
                                    <form action="{{ route('kelas.delete', ['id' => $k->id]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="button"
                                            class="delete-confirm p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Hapus Rombel">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class='bx bx-home-alt text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Belum Ada Data Rombel</p>
                                <p class="text-xs text-slate-400 mt-0.5">Silakan tambahkan rombongan belajar baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($kelas->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{ $kelas->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        @include('kelas.modal-wizard')

        {{-- Modal Edit Rombel (Exact Design System) --}}
        <div id="modal-editkelas" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editkelas')"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                <div class="relative w-full max-w-2xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Rombongan Belajar</h2>
                            <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                                Perbarui Struktur, Tingkat & Wali Kelas
                            </span>
                        </div>
                        <button type="button" onclick="closeModal('modal-editkelas')"
                            class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>
                    <div id="loadeditformkelas"></div>
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
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function updateAddKelasPreview() {
            const jurusanSelect = document.getElementById('jurusan_id');
            const tingkatSelect = document.getElementById('tingkat');
            const noRombelInput = document.getElementById('nomor_rombel');
            const previewEl = document.getElementById('preview_add_nama_kelas');

            const opt = jurusanSelect.options[jurusanSelect.selectedIndex];
            const kode = opt ? (opt.getAttribute('data-kode') || '') : '';
            const tingkat = tingkatSelect ? tingkatSelect.value : '';
            const no = noRombelInput ? noRombelInput.value : '';

            if (tingkat && kode && no) {
                previewEl.textContent = `${tingkat} ${kode} ${no}`;
            } else {
                previewEl.textContent = '-';
            }
        }

        function editKelas(id) {
            const container = document.getElementById('loadeditformkelas');
            container.innerHTML = `
                <div class="flex items-center justify-center py-12">
                    <i class='bx bx-loader-alt bx-spin text-3xl text-primary-600'></i>
                </div>
            `;
            openModal('modal-editkelas');

            fetch("{{ route('kelas.edit') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(error => {
                container.innerHTML = `
                    <div class="text-center py-8 text-rose-500">
                        <i class='bx bx-error-circle text-4xl mb-2'></i>
                        <p class="text-sm">Gagal memuat data rombel.</p>
                    </div>
                `;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateAddKelasPreview();
            const deleteButtons = document.querySelectorAll('.delete-confirm');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data rombel yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold',
                            cancelButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
