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
            <h2 class="text-xl font-bold text-dark-800">Daftar Rombongan Belajar (Rombel)</h2>
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
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-dark-50/50 border-b border-dark-200/50">
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-14 text-center">No</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Nama Rombel</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Tingkat</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Jurusan</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Tahun Ajaran</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Wali Kelas</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Kapasitas</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($kelas as $k)
                        @php
                            $badgeColor = match(strtoupper($k->jurusan->kode_jurusan ?? '')) {
                                'PPLG' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'ANIMASI' => 'bg-purple-100 text-purple-700 border-purple-200',
                                'TPFL' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'TO' => 'bg-rose-100 text-rose-700 border-rose-200',
                                'BCF' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                default => 'bg-blue-100 text-blue-700 border-blue-200',
                            };
                        @endphp
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $kelas->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-dark-900 font-mono">
                                <a href="{{ route('academic.class', $k) }}" class="hover:text-primary-600 transition-colors inline-flex items-center gap-1.5">
                                    {{ $k->nama_kelas }}
                                    <i class='bx bx-link-external text-xs text-dark-400'></i>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-dark-100 text-dark-800">
                                    Kelas {{ $k->tingkat ?: '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $badgeColor }}">
                                    {{ $k->jurusan->kode_jurusan ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-dark-600">
                                {{ $k->tahun_ajaran ?: '2026/2027' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-dark-700">
                                {{ $k->wali_kelas }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700">
                                    {{ $k->jumlah_siswa }} Siswa
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editKelas('{{ $k->id }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-700 transition-colors duration-200"
                                        title="Edit Data">
                                        <i class='bx bx-edit-alt text-lg'></i>
                                    </button>
                                    <form action="{{ route('kelas.delete', ['id' => $k->id]) }}" method="POST" class="inline">
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
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-dark-100 flex items-center justify-center text-dark-400 mb-3">
                                        <i class='bx bx-home-alt text-2xl'></i>
                                    </div>
                                    <h3 class="text-sm font-semibold text-dark-800">Belum Ada Data Rombel</h3>
                                    <p class="text-xs text-dark-400 mt-1">Silakan tambahkan rombongan belajar baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($kelas->hasPages())
            <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                {{ $kelas->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        {{-- Modal Tambah Rombel --}}
        <div id="modal-inputkelas" class="fixed inset-0 z-[60] hidden">
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-inputkelas')"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl fade-in">
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-plus-circle text-primary-600 text-xl'></i>
                                Tambah Rombongan Belajar
                            </h3>
                            <button type="button" onclick="closeModal('modal-inputkelas')"
                                class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>
                        <div class="px-6 py-5">
                            <form action="{{ route('kelas.store') }}" method="POST" id="frmKelas">
                                @csrf

                                {{-- Live preview pill --}}
                                <div class="mb-4 p-3 bg-primary-50/60 border border-primary-200/70 rounded-xl flex items-center justify-between">
                                    <span class="text-xs text-primary-700 font-medium">Format Nama Rombel:</span>
                                    <span id="preview_add_nama_kelas" class="font-bold text-sm text-primary-900 font-mono">10 PPLG 1</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="jurusan_id" class="block text-xs font-semibold text-dark-700 mb-1.5">
                                            Jurusan (Program Keahlian) <span class="text-rose-500">*</span>
                                        </label>
                                        <select name="jurusan_id" id="jurusan_id" required onchange="updateAddKelasPreview()"
                                            class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                                            <option value="">-- Pilih Jurusan --</option>
                                            @foreach ($jurusans as $j)
                                                <option value="{{ $j->id }}" data-kode="{{ $j->kode_jurusan }}">{{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="tingkat" class="block text-xs font-semibold text-dark-700 mb-1.5">
                                            Tingkat Kelas <span class="text-rose-500">*</span>
                                        </label>
                                        <select name="tingkat" id="tingkat" required onchange="updateAddKelasPreview()"
                                            class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                                            <option value="10">Kelas 10 (Sepuluh)</option>
                                            <option value="11">Kelas 11 (Sebelas)</option>
                                            <option value="12">Kelas 12 (Dua Belas)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="nomor_rombel" class="block text-xs font-semibold text-dark-700 mb-1.5">
                                            Nomor Rombel <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" name="nomor_rombel" id="nomor_rombel" value="1" min="1" max="99" required oninput="updateAddKelasPreview()"
                                            class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all font-mono"
                                            placeholder="Contoh: 1, 2, 3">
                                    </div>

                                    <div>
                                        <label for="tahun_ajaran" class="block text-xs font-semibold text-dark-700 mb-1.5">
                                            Tahun Ajaran <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" name="tahun_ajaran" id="tahun_ajaran" value="2026/2027" required
                                            class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all font-mono"
                                            placeholder="2026/2027">
                                    </div>

                                    <div>
                                        <label for="wali_kelas" class="block text-xs font-semibold text-dark-700 mb-1.5">
                                            Wali Kelas <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" name="wali_kelas" id="wali_kelas" required
                                            class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                                            placeholder="Nama Wali Kelas">
                                    </div>

                                    <div>
                                        <label for="jumlah_siswa" class="block text-xs font-semibold text-dark-700 mb-1.5">
                                            Kapasitas Siswa <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" name="jumlah_siswa" id="jumlah_siswa" value="36" min="1" required
                                            class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                                            placeholder="36">
                                    </div>
                                </div>

                                <div class="mt-6 pt-4 border-t border-dark-100 flex items-center justify-end gap-3">
                                    <button type="button" onclick="closeModal('modal-inputkelas')"
                                        class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 font-medium transition-colors duration-200 text-sm">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center gap-2 text-sm">
                                        <i class='bx bx-save text-lg'></i>
                                        Simpan Rombel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Edit Rombel --}}
        <div id="modal-editkelas" class="fixed inset-0 z-[60] hidden">
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editkelas')"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl fade-in">
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-edit-alt text-primary-600 text-xl'></i>
                                Edit Data Rombel
                            </h3>
                            <button type="button" onclick="closeModal('modal-editkelas')"
                                class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>
                        <div class="px-6 py-5" id="loadeditformkelas"></div>
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
