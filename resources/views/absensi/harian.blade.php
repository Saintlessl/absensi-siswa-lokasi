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

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NIS / NAMA SISWA</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">GENDER</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KELAS</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">STATUS ABSENSI (HARI INI)</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">JAM / INFO</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-20">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($siswas as $s)
                                @php
                                    $absen = $s->absensi->first();
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-4 text-xs font-medium text-slate-400 text-center">{{ $siswas->firstItem() + $loop->index }}</td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 w-9 h-9 rounded-full overflow-hidden bg-[#EFF5FF] border border-blue-100 flex items-center justify-center font-bold text-xs shadow-xs">
                                                @if ($s->photo_url)
                                                    <img src="{{ $s->photo_url }}" alt="{{ $s->nama }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-[#003FA9] uppercase">{{ substr($s->nama, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-xs sm:text-sm font-bold text-slate-900">{{ $s->nama }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono">NIS: {{ $s->nis }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-xs font-semibold text-slate-800 text-center">
                                        {{ $s->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </td>
                                    <td class="py-4 px-4 text-xs sm:text-sm font-bold text-slate-900 font-mono">
                                        {{ $s->kelas->nama_kelas }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if ($absen)
                                            @php $status = strtolower($absen->status); @endphp
                                            @if ($status == 'hadir')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white">
                                                    Hadir
                                                </span>
                                            @elseif($status == 'izin')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#003FA9] text-white">
                                                    Izin
                                                </span>
                                            @elseif($status == 'sakit')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white">
                                                    Sakit
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-600 text-white">
                                                    Alfa
                                                </span>
                                            @endif
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                                Belum Absen
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if ($absen && $absen->jam_masuk)
                                            <span class="font-mono text-xs font-bold text-slate-800">
                                                {{ date('H:i', strtotime($absen->jam_masuk)) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <button type="button"
                                            onclick="openEditAbsensi({{ $s->id }}, '{{ $absen ? $absen->status : '' }}', '{{ $absen ? addslashes($absen->keterangan) : '' }}', '{{ addslashes($s->nama) }}')"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition" title="Edit Kehadiran">
                                            <i class='bx bx-edit text-lg'></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i class='bx bx-user-x text-4xl mb-2 text-slate-300'></i>
                                        <p class="font-semibold text-sm text-slate-600">Tidak ada data siswa</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Tidak ditemukan siswa pada filter ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($siswas->hasPages())
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                        {{ $siswas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('modals')
        {{-- Modal Edit Absensi (Exact Design System) --}}
        <div id="modal-editabsensi" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeModal('modal-editabsensi')"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                <div class="relative w-full max-w-md rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Absensi Siswa</h2>
                            <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                                Perbarui Kehadiran Harian Siswa
                            </span>
                        </div>
                        <button type="button" onclick="closeModal('modal-editabsensi')"
                            class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                    <form action="{{ route('absensi.harian.update') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="siswa_id" id="edit_siswa_id">

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Siswa</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class='bx bx-user text-slate-400 text-lg'></i>
                                </div>
                                <input type="text" id="edit_nama_siswa" readonly
                                    class="block w-full pl-10 pr-4 h-11 rounded-xl bg-blue-100/50 border-none text-slate-700 text-sm focus:outline-none font-medium cursor-not-allowed">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status Kehadiran <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="status" id="edit_status" required
                                    class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                                    <option value="Hadir">Hadir</option>
                                    <option value="Izin">Izin</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Alfa">Alfa</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan (Opsional)</label>
                            <textarea name="keterangan" id="edit_keterangan" rows="3"
                                class="block w-full p-3.5 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition resize-none"
                                placeholder="Ketik alasan perubahan..."></textarea>
                        </div>

                        {{-- Actions --}}
                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#D8E6FC]">
                            <button type="button" onclick="closeModal('modal-editabsensi')"
                                class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] hover:bg-[#d4e4fc] text-[#003FA9] font-bold text-sm transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#00338d] text-white font-bold text-sm shadow-md shadow-blue-500/20 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
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

