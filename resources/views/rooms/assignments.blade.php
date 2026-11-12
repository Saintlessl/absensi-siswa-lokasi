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
            <h2 class="text-xl font-medium text-dark-800">Alokasi Ruangan & Rolling Kelas</h2>
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
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rombel Belajar</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ruangan Fisik</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Periode Berlaku</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jadwal Hari & Jam</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Tahun Ajaran</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Status</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($assignments as $a)
                        @php
                            $today = \Carbon\Carbon::today();
                            $isActiveNow = (!$a->starts_on || $a->starts_on->lte($today)) && (!$a->ends_on || $a->ends_on->gte($today));
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-800">{{ $a->kelas->nama_kelas }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $a->kelas->jurusan->nama_jurusan ?? '-' }} &bull; Wali: {{ $a->kelas->wali_kelas ?? '-' }}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                    <i class='bx bx-building text-[#003FA9]'></i>
                                    <span>{{ $a->room->name }}</span>
                                    <span class="text-xs font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $a->room->code }}</span>
                                </div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $a->room->building ?? 'Gedung Utama' }} (Kapasitas {{ $a->room->capacity ?? 36 }})</div>
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-700">
                                <div class="font-semibold">{{ $a->starts_on->format('d M Y') }}</div>
                                <div class="text-slate-400 mt-0.5">
                                    s/d {{ $a->ends_on ? $a->ends_on->format('d M Y') : 'Berjalan (Permanen)' }}
                                </div>
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-700">
                                <div class="font-semibold">{{ $a->day_of_week ?? 'Setiap Hari Kerja' }}</div>
                                <div class="text-slate-400 mt-0.5 font-mono">
                                    {{ $a->start_time && $a->end_time ? $a->start_time . ' - ' . $a->end_time : 'Sepanjang Hari' }}
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center font-mono text-xs font-bold text-slate-700">
                                {{ $a->tahun_ajaran }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($isActiveNow)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">
                                        Aktif Sekarang
                                    </span>
                                @elseif($a->ends_on && $a->ends_on->lt($today))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">
                                        Akan Datang
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                <form action="{{ route('rooms.assignments.destroy', $a) }}" method="POST"
                                    data-confirm="Hapus alokasi rombel {{ $a->kelas->nama_kelas }} di {{ $a->room->name }}?"
                                    data-confirm-title="Hapus Alokasi Ruangan"
                                    data-confirm-danger="true">
                                    @csrf
                                    <button type="submit"
                                        class="delete-confirm p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                        title="Hapus Alokasi">
                                        <i class='bx bx-trash text-base'></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <i class='bx bx-transfer text-2xl text-slate-400'></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600">Belum ada alokasi ruangan aktif</p>
                                    <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Tambah Alokasi Ruangan" untuk menjadwalkan kelas ke ruangan fisik.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>

@endsection

@push('modals')
    {{-- Modal Tambah Alokasi Ruangan (Wizard) --}}
    @include('rooms.modal-wizard-assignment')
@endpush

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
