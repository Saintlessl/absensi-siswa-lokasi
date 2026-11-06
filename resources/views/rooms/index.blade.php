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

@endsection

@push('modals')
{{-- Modal Tambah Ruangan (Wizard) --}}
    @include('rooms.modal-wizard')

    {{-- Modal Edit Ruangan (Exact Design System) --}}
    <div id="modal-editroom" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
            onclick="closeModal('modal-editroom')"></div>
        <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
            <div class="relative w-full max-w-2xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Data Ruangan</h2>
                        <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                            Perbarui Spesifikasi, Kapasitas & Gedung
                        </span>
                    </div>
                    <button type="button" onclick="closeModal('modal-editroom')"
                        class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                <form id="frmEditRoom" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">Nama Ruangan <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="edit_name" required
                            class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                            placeholder="Contoh: Lab Komputer 1, Ruang 201">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">Kode Ruangan <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" id="edit_code" required
                                class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm font-mono font-bold uppercase focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                placeholder="Contoh: LAB-01">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">Kapasitas (Siswa)</label>
                            <input type="number" name="capacity" id="edit_capacity" min="1" max="200"
                                class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                placeholder="36">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">Gedung / Lokasi</label>
                            <input type="text" name="building" id="edit_building"
                                class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                placeholder="Contoh: Gedung A Lantai 2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">Jenis / Tipe</label>
                            <input type="text" name="type" id="edit_type"
                                class="w-full h-11 px-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                                placeholder="Contoh: Ruang Teori, Lab, Bengkel">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#1E293B] mb-1.5">Fasilitas & Keterangan</label>
                        <textarea name="description" id="edit_description" rows="2"
                            class="w-full p-4 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                            placeholder="Fasilitas AC, Projector, PC, sound system..."></textarea>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-3 cursor-pointer select-none bg-white/70 p-3.5 rounded-xl border border-blue-100">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                                class="w-5 h-5 text-[#003FA9] rounded border-blue-300 focus:ring-[#003FA9]">
                            <div>
                                <span class="text-sm font-bold text-[#0F172A] block">Ruangan Aktif</span>
                                <span class="text-xs text-[#64748B]">Dapat digunakan untuk presensi QR code</span>
                            </div>
                        </label>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#D8E6FC] flex items-center justify-between">
                        <button type="button" onclick="closeModal('modal-editroom')"
                            class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] text-[#003FA9] font-semibold text-sm hover:bg-[#d4e4fc] transition-colors focus:outline-none">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] text-white font-semibold text-sm hover:bg-[#00338d] shadow-md shadow-blue-500/20 transition-all focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Preview QR Code (Exact Design System) --}}
    <div id="modal-previewqr" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
            onclick="closeModal('modal-previewqr')"></div>
        <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-center">
            <div class="relative w-full max-w-sm rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full">
                        QR Code Presensi
                    </span>
                    <button type="button" onclick="closeModal('modal-previewqr')"
                        class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <h3 class="font-extrabold text-[#0F172A] text-xl" id="modal_qr_name">Ruangan</h3>
                <p class="text-xs text-[#003FA9] font-mono font-bold mt-1" id="modal_qr_code">R-000</p>

                <div class="my-5 p-4 bg-white rounded-2xl inline-block mx-auto shadow-md border border-blue-100">
                    <div id="modal_qrcode_container"></div>
                </div>

                <p class="text-[10px] text-slate-500 font-mono break-all bg-[#E2EDFF] p-2.5 rounded-xl border border-blue-200/50" id="modal_qr_token"></p>

                <div class="mt-6 flex items-center justify-center gap-3">
                    <button type="button" id="btnDownloadModalQr"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#003FA9] hover:bg-[#00338d] text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Unduh QR PNG
                    </button>
                    <button type="button" onclick="closeModal('modal-previewqr')"
                        class="px-5 py-2.5 bg-[#E2EDFF] hover:bg-[#d4e4fc] text-[#003FA9] text-xs font-bold rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush



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
