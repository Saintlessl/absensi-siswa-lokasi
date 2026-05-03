@extends('layouts.app')

@section('title', 'Absensi Harian Kelas')
@section('page-title', 'Absensi Harian')
@section('page-subtitle', 'Pantau kehadiran siswa di kelas Anda hari ini')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Absensi Harian</li>
@endsection

@section('content')
    <div class="flex flex-col gap-6">

        {{-- Panel Jadwal & Info (SEKARANG DI ATAS) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Card Hari Ini --}}
            <div class="md:col-span-1">
                <div class="glass-card rounded-2xl border border-dark-200/50 p-6 h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
                            <i class='bx bx-calendar-event text-2xl'></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-dark-500 uppercase tracking-widest">Hari Ini</p>
                            <h3 class="text-lg font-bold text-dark-800">{{ $hariIniStr }}</h3>
                        </div>
                    </div>
                    <p class="text-sm text-dark-600">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                </div>
            </div>

            {{-- Card Kelas Hari Ini --}}
            <div class="md:col-span-1">
                <div class="glass-card rounded-2xl border border-dark-200/50 p-6 h-full">
                    <h4 class="text-sm font-bold text-dark-800 mb-3 flex items-center gap-2">
                        <i class='bx bx-chalkboard text-primary-500'></i> Kelas Anda Hari Ini:
                    </h4>

                    @if ($jadwalHariIni->isEmpty())
                        <div class="p-3 bg-dark-50 rounded-xl border border-dark-100 text-center">
                            <p class="text-sm text-dark-500">Tidak ada jadwal hari ini.</p>
                        </div>
                    @else
                        <ul class="space-y-2">
                            @foreach ($jadwalHariIni as $jdwl)
                                <li
                                    class="p-2 bg-white border border-dark-100 rounded-xl flex justify-between items-center">
                                    <p class="text-sm font-bold text-dark-800">{{ $jdwl->kelas->nama_kelas }}</p>
                                    <span
                                        class="px-2 py-0.5 bg-dark-50 rounded text-[10px] text-dark-500">{{ $jdwl->mata_pelajaran }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Card Informasi --}}
            <div class="md:col-span-1">
                <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-primary-50/30 h-full">
                    <h4 class="text-sm font-bold text-dark-800 mb-2 flex items-center gap-2">
                        <i class='bx bx-info-circle text-primary-600'></i> Informasi
                    </h4>
                    <p class="text-xs text-dark-600 leading-relaxed">
                        Sistem hanya menampilkan daftar siswa yang berasal dari kelas yang terdaftar pada jadwal aktual Anda
                        hari ini.
                    </p>
                </div>
            </div>
        </div>

        {{-- Panel Daftar Siswa (SEKARANG DI BAWAH) --}}
        <div class="w-full">
            <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
                <div
                    class="px-6 py-5 border-b border-dark-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-dark-50/30">
                    <div>
                        <h3 class="text-lg font-bold text-dark-800">Daftar Kehadiran Siswa</h3>
                        <p class="text-sm text-dark-500 mt-1">Status rekam kehadiran Real-Time</p>
                    </div>
                    <div
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-dark-200 rounded-xl text-sm font-medium text-dark-600 shadow-sm">
                        <i class='bx bxs-user-detail text-primary-500'></i>
                        {{ $siswas->total() }} Siswa Aktif
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-dark-50/50 border-b border-dark-200/50">
                                <th
                                    class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-12 text-center">
                                    No</th>
                                <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">NIS /
                                    Nama Siswa</th>
                                <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Gender
                                </th>
                                <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Kelas
                                </th>
                                <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Status
                                    Absensi (Hari Ini)</th>
                                <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Jam /
                                    Info</th>
                                <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-200/50">
                            @forelse ($siswas as $s)
                                @php
                                    $absen = $s->absensi->first();
                                @endphp
                                <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                                    <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $siswas->firstItem() + $loop->index }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xs uppercase">
                                                {{ substr($s->nama, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-dark-800">{{ $s->nama }}</p>
                                                <p class="text-xs text-dark-500 font-mono">{{ $s->nis }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-dark-600">
                                        {{ $s->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-dark-700">
                                        {{ $s->kelas->nama_kelas }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($absen)
                                            @php $status = strtolower($absen->status); @endphp
                                            @if ($status == 'hadir')
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <i class='bx bx-check-shield text-sm'></i> Hadir
                                                </span>
                                            @elseif($status == 'izin')
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                                    <i class='bx bx-envelope text-sm'></i> Izin
                                                </span>
                                            @elseif($status == 'sakit')
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <i class='bx bx-plus-medical text-sm'></i> Sakit
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <i class='bx bx-x-circle text-sm'></i> Alfa
                                                </span>
                                            @endif
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-dark-100 text-dark-600 border border-dark-200">
                                                <i class='bx bx-time text-sm'></i> Belum Absen
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($absen && $absen->jam_masuk)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded bg-dark-50 text-xs font-mono text-dark-600 border border-dark-100">
                                                <i class='bx bx-time-five'></i>
                                                {{ date('H:i', strtotime($absen->jam_masuk)) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-dark-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button type="button"
                                            onclick="openEditAbsensi({{ $s->id }}, '{{ $absen ? $absen->status : '' }}', '{{ $absen ? addslashes($absen->keterangan) : '' }}', '{{ addslashes($s->nama) }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-50 text-primary-600 hover:bg-primary-100 transition-colors" title="Edit Kehadiran">
                                            <i class='bx bx-edit-alt text-lg'></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-dark-500">Tidak ada siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($siswas->hasPages())
                    <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                        {{ $siswas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('modals')
        {{-- Modal Edit Absensi --}}
        <div id="modal-editabsensi" class="fixed inset-0 z-[60] hidden">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editabsensi')"></div>

            {{-- Modal Panel --}}
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md fade-in">
                        {{-- Header --}}
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-edit-alt text-primary-600 text-xl'></i>
                                Edit Absensi
                            </h3>
                            <button type="button" onclick="closeModal('modal-editabsensi')"
                                class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="px-6 py-5">
                            <form action="{{ route('absensi.harian.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="siswa_id" id="edit_siswa_id">

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-dark-700 mb-1.5">Nama Siswa</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class='bx bx-user text-dark-400 text-lg'></i>
                                        </div>
                                        <input type="text" id="edit_nama_siswa" readonly
                                            class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 text-dark-800 focus:outline-none transition-all duration-200">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-dark-700 mb-1.5">Status Kehadiran</label>
                                    <select name="status" id="edit_status" required
                                        class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                                        <option value="Hadir">Hadir</option>
                                        <option value="Izin">Izin</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Alfa">Alfa</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-dark-700 mb-1.5">Keterangan (Opsional)</label>
                                    <textarea name="keterangan" id="edit_keterangan" rows="3"
                                        class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                        placeholder="Ketik alasan perubahan..."></textarea>
                                </div>

                                {{-- Actions --}}
                                <div class="mt-6 flex items-center justify-end gap-3">
                                    <button type="button" onclick="closeModal('modal-editabsensi')"
                                        class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 bg-white hover:bg-dark-50 hover:text-dark-800 font-medium transition-colors duration-200">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center gap-2">
                                        <i class='bx bx-save text-lg'></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
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
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function openEditAbsensi(siswaId, status, keterangan, namaSiswa) {
            document.getElementById('edit_siswa_id').value = siswaId;
            document.getElementById('edit_nama_siswa').value = namaSiswa;

            if (status !== '') {
                let selectStatus = document.getElementById('edit_status');
                for (let i = 0; i < selectStatus.options.length; i++) {
                    if (selectStatus.options[i].value.toLowerCase() === status.toLowerCase()) {
                        selectStatus.selectedIndex = i;
                        break;
                    }
                }
            } else {
                document.getElementById('edit_status').value = 'Hadir';
            }

            document.getElementById('edit_keterangan').value = keterangan && keterangan !== '-' ? keterangan : '';

            openModal('modal-editabsensi');
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-editabsensi');
            }
        });
    </script>
@endpush

