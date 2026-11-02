@extends('layouts.app')

@section('title', 'Data Ruangan')
@section('page-title', 'Data Ruangan')
@section('page-subtitle', 'Kelola ruangan fisik sekolah, gedung, kapasitas, dan QR code permanen')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><span class="text-dark-500">Pengaturan</span></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Data Ruangan</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dark-800">Manajemen Ruangan Fisik</h2>
            <p class="text-sm text-dark-400 mt-1">
                Data ruangan fisik dipisahkan dari rombel belajar, memungkinkan rolling kelas tanpa ganti QR Code.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('rooms.assignments') }}"
                class="inline-flex items-center gap-2 px-4 py-2 border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 text-sm font-medium rounded-xl shadow-sm transition-all duration-200">
                <i class='bx bx-transfer text-lg text-primary-600'></i>
                <span>Alokasi & Rolling Ruangan</span>
            </a>
            <button onclick="openModal('modal-addroom')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200">
                <i class='bx bx-plus text-lg'></i>
                <span>Tambah Ruangan</span>
            </button>
        </div>
    </div>
@endsection

@section('content')
    {{-- Flash Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-emerald-800 text-sm font-semibold">
            <div class="flex items-center gap-2">
                <i class='bx bx-check-circle text-lg text-emerald-600'></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class='bx bx-x text-lg'></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between text-rose-800 text-sm font-semibold">
            <div class="flex items-center gap-2">
                <i class='bx bx-error-circle text-lg text-rose-600'></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i class='bx bx-x text-lg'></i></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="glass-card rounded-2xl border border-dark-200/50 p-5 bg-white flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary-50 border border-primary-200 flex items-center justify-center text-primary-600 text-2xl flex-shrink-0">
                <i class='bx bx-building-house'></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-dark-500">Total Ruangan Fisik</p>
                <h3 class="text-2xl font-extrabold text-dark-900 mt-0.5">{{ $totalRooms }}</h3>
            </div>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-5 bg-white flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 text-2xl flex-shrink-0">
                <i class='bx bx-check-circle'></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-dark-500">Ruangan Aktif</p>
                <h3 class="text-2xl font-extrabold text-dark-900 mt-0.5">{{ $activeRooms }}</h3>
            </div>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-5 bg-white flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600 text-2xl flex-shrink-0">
                <i class='bx bx-laptop'></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-dark-500">Laboratorium / Bengkel</p>
                <h3 class="text-2xl font-extrabold text-dark-900 mt-0.5">{{ $labRooms }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-4 mb-6 bg-white">
        <form action="{{ route('rooms.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1 w-full">
                <label class="block text-xs font-medium text-dark-600 mb-1">Cari Ruangan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bx-search text-dark-400'></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                        placeholder="Nama ruangan, kode (misal: R-201, Gedung Barat)...">
                </div>
            </div>

            @if($buildings->isNotEmpty())
                <div class="w-full sm:w-60">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Filter Gedung</label>
                    <select name="building"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Gedung</option>
                        @foreach ($buildings as $b)
                            <option value="{{ $b }}" {{ $building == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="submit"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                <i class='bx bx-filter-alt'></i> Filter
            </button>
            @if ($search || $building)
                <a href="{{ route('rooms.index') }}"
                    class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Room Table --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-dark-200 bg-dark-50/70 text-[11px] font-bold text-dark-500 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Nama & Jenis Ruangan</th>
                        <th class="px-6 py-3.5">Kode Ruangan</th>
                        <th class="px-6 py-3.5">Gedung / Lokasi</th>
                        <th class="px-6 py-3.5 text-center">Kapasitas</th>
                        <th class="px-6 py-3.5">Alokasi Rombel Aktif</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-center">QR Code</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/60 text-sm">
                    @forelse ($rooms as $room)
                        @php
                            $activeAssignment = $room->assignments->first();
                            $activeKelas = $activeAssignment?->kelas;
                        @endphp
                        <tr class="hover:bg-dark-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-dark-900">{{ $room->name }}</div>
                                <div class="text-xs text-dark-500 flex items-center gap-2 mt-0.5">
                                    <span class="px-2 py-0.5 rounded bg-dark-100 text-dark-600 text-[10px] font-medium">{{ $room->type ?? 'Ruang Teori' }}</span>
                                    @if($room->description)
                                        <span class="truncate max-w-[200px]" title="{{ $room->description }}">{{ $room->description }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-dark-800 text-xs">
                                <span class="px-2 py-1 bg-dark-100 rounded-md border border-dark-200">{{ $room->code }}</span>
                            </td>
                            <td class="px-6 py-4 text-dark-600 text-xs">
                                <i class='bx bx-building mr-1 text-dark-400'></i>{{ $room->building ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-dark-800 text-xs">
                                {{ $room->capacity ?? '-' }} <span class="text-dark-400 font-normal">siswa</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($activeKelas)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary-50 text-primary-800 border border-primary-200 text-xs font-semibold">
                                        <i class='bx bx-door-open text-primary-600'></i>
                                        <span>{{ $activeKelas->nama_kelas }}</span>
                                    </div>
                                    <p class="text-[10px] text-dark-400 mt-0.5">s/d {{ $activeAssignment->ends_on ? $activeAssignment->ends_on->format('d/m/Y') : 'Berjalan' }}</p>
                                @else
                                    <span class="text-xs text-dark-400 italic">Belum dialokasikan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($room->is_active)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-dark-100 text-dark-500">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" onclick="showQrModal('{{ $room->id }}', '{{ $room->name }}', '{{ $room->code }}', '{{ $room->qr_token }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-dark-50 hover:bg-dark-100 text-dark-700 border border-dark-200 text-xs font-semibold transition">
                                    <i class='bx bx-qr-scan text-sm text-primary-600'></i>
                                    <span>Lihat QR</span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="openEditRoomModal({{ json_encode($room) }})"
                                        class="w-8 h-8 rounded-lg border border-dark-200 hover:bg-primary-50 hover:text-primary-600 hover:border-primary-200 flex items-center justify-center text-dark-600 transition"
                                        title="Edit Ruangan">
                                        <i class='bx bx-edit text-base'></i>
                                    </button>

                                    <form action="{{ route('rooms.regenerate', $room) }}" method="POST" onsubmit="return confirm('Regenerate QR Code untuk {{ $room->name }}? QR Code sebelumnya tidak akan bisa digunakan lagi!')">
                                        @csrf
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg border border-dark-200 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 flex items-center justify-center text-dark-600 transition"
                                            title="Regenerate QR Token">
                                            <i class='bx bx-refresh text-base'></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Hapus data ruangan {{ $room->name }}?')">
                                        @csrf
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg border border-dark-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 flex items-center justify-center text-dark-600 transition"
                                            title="Hapus Ruangan">
                                            <i class='bx bx-trash text-base'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-dark-400">
                                <i class='bx bx-building-house text-4xl mb-2'></i>
                                <p class="text-sm font-semibold text-dark-600">Belum ada data ruangan fisik</p>
                                <p class="text-xs text-dark-400 mt-1">Gunakan tombol "Tambah Ruangan" di atas untuk menambahkan ruangan baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rooms->hasPages())
            <div class="px-6 py-4 border-t border-dark-200">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tambah Ruangan --}}
    <div id="modal-addroom" class="fixed inset-0 z-50 hidden bg-dark-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-dark-200">
            <div class="px-6 py-4 border-b border-dark-200 flex items-center justify-between">
                <h3 class="font-bold text-dark-900 text-base">Tambah Ruangan Baru</h3>
                <button type="button" onclick="closeModal('modal-addroom')" class="text-dark-400 hover:text-dark-600"><i class='bx bx-x text-2xl'></i></button>
            </div>
            <form action="{{ route('rooms.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Nama Ruangan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Ruangan 201, Lab Komputer 1"
                        class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Kode Ruangan <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" required placeholder="Contoh: R-201, LAB-01"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Kapasitas (Siswa)</label>
                        <input type="number" name="capacity" min="1" placeholder="36"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Gedung / Wilayah</label>
                        <input type="text" name="building" placeholder="Contoh: Gedung A, Lantai 2"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Jenis / Tipe</label>
                        <input type="text" name="type" placeholder="Contoh: Ruang Teori, Lab, Bengkel"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Deskripsi / Fasilitas (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Fasilitas AC, Projector, Smart TV, dsb."
                        class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="add_is_active" value="1" checked
                        class="w-4 h-4 text-primary-600 rounded border-dark-300 focus:ring-primary-500">
                    <label for="add_is_active" class="text-xs font-semibold text-dark-700">Ruangan Aktif untuk Presensi</label>
                </div>
                <div class="pt-4 flex items-center justify-end gap-2 border-t border-dark-200">
                    <button type="button" onclick="closeModal('modal-addroom')"
                        class="px-4 py-2 border border-dark-200 rounded-xl text-sm font-semibold text-dark-600 hover:bg-dark-50">Batal</button>
                    <button type="submit"
                        class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-primary-500/30">Simpan Ruangan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Ruangan --}}
    <div id="modal-editroom" class="fixed inset-0 z-50 hidden bg-dark-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-dark-200">
            <div class="px-6 py-4 border-b border-dark-200 flex items-center justify-between">
                <h3 class="font-bold text-dark-900 text-base">Edit Data Ruangan</h3>
                <button type="button" onclick="closeModal('modal-editroom')" class="text-dark-400 hover:text-dark-600"><i class='bx bx-x text-2xl'></i></button>
            </div>
            <form id="frmEditRoom" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Nama Ruangan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Kode Ruangan <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" id="edit_code" required
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Kapasitas (Siswa)</label>
                        <input type="number" name="capacity" id="edit_capacity" min="1"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Gedung / Wilayah</label>
                        <input type="text" name="building" id="edit_building"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Jenis / Tipe</label>
                        <input type="text" name="type" id="edit_type"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Deskripsi / Fasilitas (Opsional)</label>
                    <textarea name="description" id="edit_description" rows="2"
                        class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                        class="w-4 h-4 text-primary-600 rounded border-dark-300 focus:ring-primary-500">
                    <label for="edit_is_active" class="text-xs font-semibold text-dark-700">Ruangan Aktif untuk Presensi</label>
                </div>
                <div class="pt-4 flex items-center justify-end gap-2 border-t border-dark-200">
                    <button type="button" onclick="closeModal('modal-editroom')"
                        class="px-4 py-2 border border-dark-200 rounded-xl text-sm font-semibold text-dark-600 hover:bg-dark-50">Batal</button>
                    <button type="submit"
                        class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-primary-500/30">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Preview QR Code --}}
    <div id="modal-previewqr" class="fixed inset-0 z-50 hidden bg-dark-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center border border-dark-200">
            <h3 class="font-extrabold text-dark-900 text-lg" id="modal_qr_name">Ruangan</h3>
            <p class="text-xs text-dark-500 font-mono mt-0.5" id="modal_qr_code">R-000</p>

            <div class="my-4 p-4 bg-white border-2 border-dark-900 rounded-2xl inline-block mx-auto shadow-inner">
                <div id="modal_qrcode_container"></div>
            </div>

            <p class="text-[10px] text-dark-400 font-mono break-all bg-dark-50 p-2 rounded border border-dark-200" id="modal_qr_token"></p>

            <div class="mt-4 flex items-center justify-center gap-2">
                <button type="button" id="btnDownloadModalQr"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5 transition">
                    <i class='bx bx-download text-sm'></i> Unduh QR PNG
                </button>
                <button type="button" onclick="closeModal('modal-previewqr')"
                    class="px-4 py-2 border border-dark-200 hover:bg-dark-50 text-dark-600 text-xs font-semibold rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function openEditRoomModal(room) {
            document.getElementById('frmEditRoom').action = `/ruangan/${room.id}`;
            document.getElementById('edit_name').value = room.name || '';
            document.getElementById('edit_code').value = room.code || '';
            document.getElementById('edit_capacity').value = room.capacity || '';
            document.getElementById('edit_building').value = room.building || '';
            document.getElementById('edit_type').value = room.type || '';
            document.getElementById('edit_description').value = room.description || '';
            document.getElementById('edit_is_active').checked = !!room.is_active;
            openModal('modal-editroom');
        }

        let modalQrInstance = null;
        function showQrModal(id, name, code, token) {
            document.getElementById('modal_qr_name').textContent = name;
            document.getElementById('modal_qr_code').textContent = code;
            document.getElementById('modal_qr_token').textContent = token;

            const container = document.getElementById('modal_qrcode_container');
            container.innerHTML = '';
            modalQrInstance = new QRCode(container, {
                text: token,
                width: 180,
                height: 180,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            document.getElementById('btnDownloadModalQr').onclick = function() {
                const canvas = container.querySelector('canvas');
                const img = container.querySelector('img');
                const src = canvas ? canvas.toDataURL('image/png') : (img ? img.src : '');
                if (src) {
                    const a = document.createElement('a');
                    a.href = src;
                    a.download = `QR_Ruangan_${name.replace(/\s+/g, '_')}.png`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            };

            openModal('modal-previewqr');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-addroom');
                closeModal('modal-editroom');
                closeModal('modal-previewqr');
            }
        });
    </script>
@endpush
