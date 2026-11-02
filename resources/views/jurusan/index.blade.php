@extends('layouts.app')

@section('title', 'Data Jurusan')
@section('page-title', 'Data Jurusan')
@section('page-subtitle', 'Kelola data Jurusan / Program Keahlian SMK')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Data Jurusan</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dark-800">Daftar Jurusan</h2>
            <p class="text-sm text-dark-400 mt-1">Kelola data program keahlian yang ada di SMK.</p>
        </div>
        <button onclick="openModal('modal-inputjurusan')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 hover:-translate-y-0.5">
            <i class='bx bx-plus text-lg'></i>
            Tambah Jurusan
        </button>
    </div>
@endsection

@section('content')
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ route('jurusan') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Cari Jurusan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-dark-400'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            placeholder="Kode / Nama / Deskripsi Jurusan...">
                    </div>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($search)
                    <a href="{{ route('jurusan') }}"
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
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Nama Jurusan</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Kelas</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Siswa</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($jurusans as $j)
                        @php
                            $badgeColor = match(strtoupper($j->kode_jurusan)) {
                                'PPLG' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'ANIMASI' => 'bg-purple-100 text-purple-700 border-purple-200',
                                'TPFL' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'TO' => 'bg-rose-100 text-rose-700 border-rose-200',
                                'BCF' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                default => 'bg-blue-100 text-blue-700 border-blue-200',
                            };
                        @endphp
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $jurusans->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $badgeColor }}">
                                    {{ $j->kode_jurusan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-dark-800">{{ $j->nama_jurusan }}</td>
                            <td class="px-6 py-4 text-xs text-dark-500 max-w-xs truncate" title="{{ $j->deskripsi }}">
                                {{ $j->deskripsi ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-dark-100 text-dark-700">
                                    {{ $j->kelas_count }} Kelas
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700">
                                    {{ $j->siswa_count }} Siswa
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editJurusan('{{ $j->id }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-700 transition-colors duration-200"
                                        title="Edit Data">
                                        <i class='bx bx-edit-alt text-lg'></i>
                                    </button>
                                    <form action="{{ route('jurusan.delete', ['id' => $j->id]) }}" method="POST" class="inline">
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
                                    <div class="w-12 h-12 rounded-full bg-dark-100 flex items-center justify-center text-dark-400 mb-3">
                                        <i class='bx bx-layer text-2xl'></i>
                                    </div>
                                    <p class="text-sm font-medium text-dark-800">Tidak ada data jurusan</p>
                                    <p class="text-xs text-dark-400 mt-1">Data belum ditambahkan atau filter tidak sesuai.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($jurusans->hasPages())
            <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/20">
                {{ $jurusans->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        {{-- Modal Tambah Jurusan --}}
        <div id="modal-inputjurusan" class="fixed inset-0 z-[60] hidden">
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-inputjurusan')"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl fade-in">
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-layer text-primary-600 text-xl'></i>
                                Tambah Data Jurusan
                            </h3>
                            <button type="button" onclick="closeModal('modal-inputjurusan')"
                                class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>
                        <div class="px-6 py-5">
                            <form action="{{ route('jurusan.store') }}" method="POST" id="formTambahJurusan">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label for="kode_jurusan" class="block text-sm font-medium text-dark-700 mb-1.5">
                                            Kode Jurusan <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class='bx bx-tag text-dark-400 text-lg'></i>
                                            </div>
                                            <input type="text" name="kode_jurusan" id="kode_jurusan"
                                                class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 uppercase focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                                placeholder="Contoh: PPLG, ANIMASI, TPFL, TO, BCF" required autocomplete="off">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="nama_jurusan" class="block text-sm font-medium text-dark-700 mb-1.5">
                                            Nama Lengkap Jurusan <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class='bx bx-book-bookmark text-dark-400 text-lg'></i>
                                            </div>
                                            <input type="text" name="nama_jurusan" id="nama_jurusan"
                                                class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                                placeholder="Contoh: Pengembangan Perangkat Lunak dan Gim" required autocomplete="off">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="deskripsi" class="block text-sm font-medium text-dark-700 mb-1.5">Deskripsi / Kompetensi</label>
                                        <textarea name="deskripsi" id="deskripsi" rows="3"
                                            class="block w-full p-3 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200 text-sm"
                                            placeholder="Deskripsi singkat tentang fokus keahlian jurusan ini..."></textarea>
                                    </div>
                                </div>

                                <div class="mt-8 flex items-center justify-end gap-3">
                                    <button type="button" onclick="closeModal('modal-inputjurusan')"
                                        class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 hover:text-dark-800 font-medium transition-colors duration-200">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center gap-2">
                                        <i class='bx bx-save text-lg'></i>
                                        Simpan Jurusan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Edit Jurusan --}}
        <div id="modal-editjurusan" class="fixed inset-0 z-[60] hidden">
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editjurusan')"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl fade-in">
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-edit-alt text-primary-600 text-xl'></i>
                                Edit Data Jurusan
                            </h3>
                            <button type="button" onclick="closeModal('modal-editjurusan')"
                                class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>
                        <div class="px-6 py-5" id="loadeditformjurusan"></div>
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

        function editJurusan(id) {
            const container = document.getElementById('loadeditformjurusan');
            container.innerHTML = `
                <div class="flex items-center justify-center py-12">
                    <i class='bx bx-loader-alt bx-spin text-3xl text-primary-600'></i>
                </div>
            `;
            openModal('modal-editjurusan');

            fetch("{{ route('jurusan.edit') }}", {
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
                        <p class="text-sm">Gagal memuat data jurusan.</p>
                    </div>
                `;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-confirm');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data jurusan yang dihapus tidak dapat dikembalikan!",
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
