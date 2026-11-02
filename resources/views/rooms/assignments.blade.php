@extends('layouts.app')

@section('title', 'Alokasi & Rolling Ruangan')
@section('page-title', 'Alokasi & Rolling Ruangan')
@section('page-subtitle', 'Penjadwalan penggunaan ruangan fisik oleh rombongan belajar')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><span class="text-dark-500">Akademik</span></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Alokasi Ruangan</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dark-800">Alokasi Ruangan & Rolling Kelas</h2>
            <p class="text-sm text-dark-400 mt-1">
                Atur rombongan belajar yang menempati setiap ruangan fisik. Sistem memvalidasi agar tidak terjadi bentrok ruangan maupun rombel ganda.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('rooms.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 text-sm font-medium rounded-xl shadow-sm transition-all duration-200">
                <i class='bx bx-building-house text-lg text-primary-600'></i>
                <span>Data Ruangan Fisik</span>
            </a>
            <button onclick="openModal('modal-addassignment')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200">
                <i class='bx bx-plus text-lg'></i>
                <span>Tambah Alokasi Ruangan</span>
            </button>
        </div>
    </div>
@endsection

@section('content')
    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-emerald-800 text-sm font-semibold">
            <div class="flex items-center gap-2">
                <i class='bx bx-check-circle text-lg text-emerald-600'></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class='bx bx-x text-lg'></i></button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between text-rose-800 text-sm font-semibold">
            <div class="flex items-center gap-2">
                <i class='bx bx-error-circle text-lg text-rose-600'></i>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i class='bx bx-x text-lg'></i></button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-4 mb-6 bg-white">
        <form action="{{ route('rooms.assignments') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="w-full sm:w-64">
                <label class="block text-xs font-medium text-dark-600 mb-1">Filter Ruangan Fisik</label>
                <select name="room_id"
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="">Semua Ruangan</option>
                    @foreach ($rooms as $r)
                        <option value="{{ $r->id }}" {{ $room_id == $r->id ? 'selected' : '' }}>
                            {{ $r->name }} ({{ $r->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-64">
                <label class="block text-xs font-medium text-dark-600 mb-1">Filter Rombel</label>
                <select name="kelas_id"
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="">Semua Rombel</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                <i class='bx bx-filter-alt'></i> Filter
            </button>
            @if ($room_id || $kelas_id)
                <a href="{{ route('rooms.assignments') }}"
                    class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Assignments Table --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-dark-200 bg-dark-50/70 text-[11px] font-bold text-dark-500 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Rombel Belajar</th>
                        <th class="px-6 py-3.5">Ruangan Fisik</th>
                        <th class="px-6 py-3.5">Periode Berlaku</th>
                        <th class="px-6 py-3.5">Jadwal Hari & Jam</th>
                        <th class="px-6 py-3.5 text-center">Tahun Ajaran</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/60 text-sm">
                    @forelse ($assignments as $a)
                        @php
                            $today = \Carbon\Carbon::today();
                            $isActiveNow = (!$a->starts_on || $a->starts_on->lte($today)) && (!$a->ends_on || $a->ends_on->gte($today));
                        @endphp
                        <tr class="hover:bg-dark-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-dark-900">{{ $a->kelas->nama_kelas }}</div>
                                <div class="text-xs text-dark-500 mt-0.5">
                                    {{ $a->kelas->jurusan->nama_jurusan ?? '-' }} &bull; Wali: {{ $a->kelas->wali_kelas ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-dark-900 flex items-center gap-1.5">
                                    <i class='bx bx-building text-primary-600'></i>
                                    <span>{{ $a->room->name }}</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-dark-100 text-dark-600">{{ $a->room->code }}</span>
                                </div>
                                <div class="text-xs text-dark-500 mt-0.5">{{ $a->room->building ?? 'Gedung Utama' }} (Kapasitas {{ $a->room->capacity ?? 36 }})</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-dark-700">
                                <div class="font-semibold">{{ $a->starts_on->format('d M Y') }}</div>
                                <div class="text-dark-400 mt-0.5">
                                    s/d {{ $a->ends_on ? $a->ends_on->format('d M Y') : 'Berjalan (Permanen)' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-dark-700">
                                <div class="font-semibold">{{ $a->day_of_week ?? 'Setiap Hari Kerja' }}</div>
                                <div class="text-dark-400 mt-0.5 font-mono">
                                    {{ $a->start_time && $a->end_time ? $a->start_time . ' - ' . $a->end_time : 'Sepanjang Hari' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs font-bold text-dark-700">
                                {{ $a->tahun_ajaran }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($isActiveNow)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif Sekarang
                                    </span>
                                @elseif($a->ends_on && $a->ends_on->lt($today))
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-dark-100 text-dark-500">
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Akan Datang
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('rooms.assignments.destroy', $a) }}" method="POST" onsubmit="return confirm('Hapus alokasi rombel {{ $a->kelas->nama_kelas }} di {{ $a->room->name }}?')">
                                    @csrf
                                    <button type="submit"
                                        class="w-8 h-8 rounded-lg border border-dark-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 inline-flex items-center justify-center text-dark-600 transition"
                                        title="Hapus Alokasi">
                                        <i class='bx bx-trash text-base'></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-dark-400">
                                <i class='bx bx-transfer text-4xl mb-2'></i>
                                <p class="text-sm font-semibold text-dark-600">Belum ada alokasi ruangan aktif</p>
                                <p class="text-xs text-dark-400 mt-1">Gunakan tombol "Tambah Alokasi Ruangan" untuk menjadwalkan kelas ke ruangan fisik.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())
            <div class="px-6 py-4 border-t border-dark-200">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tambah Alokasi Ruangan --}}
    <div id="modal-addassignment" class="fixed inset-0 z-50 hidden bg-dark-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-dark-200">
            <div class="px-6 py-4 border-b border-dark-200 flex items-center justify-between">
                <h3 class="font-bold text-dark-900 text-base">Alokasikan Rombel ke Ruangan</h3>
                <button type="button" onclick="closeModal('modal-addassignment')" class="text-dark-400 hover:text-dark-600"><i class='bx bx-x text-2xl'></i></button>
            </div>
            <form action="{{ route('rooms.assignments.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Ruangan Fisik <span class="text-rose-500">*</span></label>
                        <select name="room_id" required
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Rombel Belajar <span class="text-rose-500">*</span></label>
                        <select name="kelas_id" required
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                            <option value="">-- Pilih Rombel --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="tahun_ajaran" value="2026/2027" required
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Hari (Opsional)</label>
                        <select name="day_of_week"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                            <option value="">Setiap Hari Kerja</option>
                            <option value="Monday">Senin</option>
                            <option value="Tuesday">Selasa</option>
                            <option value="Wednesday">Rabu</option>
                            <option value="Thursday">Kamis</option>
                            <option value="Friday">Jumat</option>
                            <option value="Saturday">Sabtu</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Mulai Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="starts_on" value="{{ date('Y-m-d') }}" required
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Selesai Tanggal (Opsional)</label>
                        <input type="date" name="ends_on"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Jam Mulai (Opsional)</label>
                        <input type="time" name="start_time"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1">Jam Selesai (Opsional)</label>
                        <input type="time" name="end_time"
                            class="w-full px-3 py-2 border border-dark-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2 border-t border-dark-200">
                    <button type="button" onclick="closeModal('modal-addassignment')"
                        class="px-4 py-2 border border-dark-200 rounded-xl text-sm font-semibold text-dark-600 hover:bg-dark-50">Batal</button>
                    <button type="submit"
                        class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-primary-500/30">Simpan Alokasi</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-addassignment');
            }
        });
    </script>
@endpush
