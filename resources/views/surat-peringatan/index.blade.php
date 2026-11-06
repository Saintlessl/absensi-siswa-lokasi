@extends('layouts.app')

@section('title', 'Surat Peringatan')
@section('page-title', 'Surat Peringatan')
@section('page-subtitle', 'Kelola dan terbitkan surat peringatan siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Surat Peringatan</li>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Action Bar --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 p-6">
            <div class="flex flex-col lg:flex-row items-start lg:items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-dark-800">Daftar Surat Peringatan</h2>
                    <p class="text-sm text-dark-400 mt-1">SP yang diterbitkan otomatis oleh sistem maupun manual oleh guru/admin.</p>
                </div>
                <button onclick="openModal('modal-tambah-sp')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-500/20 hover:-translate-y-0.5 transition-all duration-200 text-sm">
                    <i class='bx bx-plus-circle text-lg'></i> Terbitkan SP Manual
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 p-4">
            <form action="{{ route('surat-peringatan') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Cari Siswa</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama/NIS..."
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                </div>
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Jenis SP</label>
                    <select name="jenis_sp"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua</option>
                        <option value="SP1" {{ request('jenis_sp') == 'SP1' ? 'selected' : '' }}>SP1</option>
                        <option value="SP2" {{ request('jenis_sp') == 'SP2' ? 'selected' : '' }}>SP2</option>
                        <option value="SP3" {{ request('jenis_sp') == 'SP3' ? 'selected' : '' }}>SP3</option>
                    </select>
                </div>
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-dark-600 mb-1">Kelas</label>
                    <select name="id_kelas"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('id_kelas') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-dark-50/50 border-b border-dark-200/50">
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-14 text-center">No</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Jenis</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">No. Surat</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-200/50">
                        @forelse ($suratPeringatans as $sp)
                            <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                                <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $suratPeringatans->firstItem() + $loop->index }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-dark-800">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs shadow-sm">
                                            {{ strtoupper(substr($sp->siswa->nama ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold">{{ $sp->siswa->nama ?? '-' }}</p>
                                            <p class="text-[10px] text-dark-400">{{ $sp->siswa->nis ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-dark-600">{{ $sp->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    @php
                                        $spColors = [
                                            'SP1' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'SP2' => 'bg-orange-50 text-orange-700 border-orange-200',
                                            'SP3' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold border {{ $spColors[$sp->jenis_sp] ?? '' }}">
                                        {{ $sp->jenis_sp }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-dark-600 font-mono text-xs">{{ $sp->nomor_surat }}</td>
                                <td class="px-6 py-4 text-sm text-dark-600">{{ \Carbon\Carbon::parse($sp->tanggal_terbit)->isoFormat('D MMM Y') }}</td>
                                <td class="px-6 py-4 text-sm text-dark-500 max-w-[200px] truncate">{{ $sp->keterangan ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('surat-peringatan.cetak', $sp->id) }}" target="_blank"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-primary-600 bg-primary-50 hover:bg-primary-100 transition-colors"
                                            title="Cetak SP">
                                            <i class='bx bx-printer text-lg'></i>
                                        </a>
                                        @if (Auth::user()->role == 'admin')
                                            <form action="{{ route('surat-peringatan.delete', $sp->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="button"
                                                    class="delete-sp inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 transition-colors"
                                                    title="Hapus SP">
                                                    <i class='bx bx-trash text-lg'></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 mb-4 rounded-full bg-dark-100 flex items-center justify-center">
                                            <i class='bx bx-file text-3xl text-dark-400'></i>
                                        </div>
                                        <h3 class="text-sm font-medium text-dark-900">Belum ada Surat Peringatan</h3>
                                        <p class="mt-1 text-sm text-dark-500">SP akan otomatis terbit saat siswa mencapai batas Alpha.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($suratPeringatans->hasPages())
                <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                    {{ $suratPeringatans->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Tambah SP Manual --}}
    @push('modals')
        <div id="modal-tambah-sp" class="fixed inset-0 z-[60] hidden">
            <div class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm" onclick="closeModal('modal-tambah-sp')"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg fade-in">
                        <div class="bg-dark-50/50 px-6 py-4 border-b border-dark-100 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-dark-800 flex items-center gap-2">
                                <i class='bx bx-file-plus text-rose-500 text-xl'></i> Terbitkan SP Manual
                            </h3>
                            <button type="button" onclick="closeModal('modal-tambah-sp')" class="text-dark-400 hover:text-dark-600 transition-colors">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>
                        <form action="{{ route('surat-peringatan.store') }}" method="POST">
                            @csrf
                            <div class="px-6 py-5 space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-dark-700 mb-2">Pilih Siswa <span class="text-rose-500">*</span></label>
                                    <select name="siswa_id" required
                                        class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                                        <option value="">-- Pilih Siswa --</option>
                                        @php $allSiswa = \App\Models\Siswa::with('kelas')->orderBy('nama')->get(); @endphp
                                        @foreach ($allSiswa as $s)
                                            <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->nis }}) — {{ $s->kelas->nama_kelas ?? '-' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-dark-700 mb-2">Jenis SP <span class="text-rose-500">*</span></label>
                                    <select name="jenis_sp" required
                                        class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                                        <option value="SP1">SP1 — Peringatan Pertama</option>
                                        <option value="SP2">SP2 — Peringatan Kedua</option>
                                        <option value="SP3">SP3 — Peringatan Terakhir</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-dark-700 mb-2">Keterangan</label>
                                    <textarea name="keterangan" rows="3"
                                        class="block w-full px-4 py-3 border border-dark-200 rounded-xl bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                                        placeholder="Alasan penerbitan SP..."></textarea>
                                </div>
                            </div>
                            <div class="bg-dark-50/50 px-6 py-4 border-t border-dark-100 flex justify-end gap-3">
                                <button type="button" onclick="closeModal('modal-tambah-sp')"
                                    class="px-4 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-rose-500 to-rose-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/20 transition-all text-sm">
                                    <i class='bx bx-check'></i> Terbitkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endpush
@endsection

@push('scripts')
<script>
    function openModal(modalId) {
        document.getElementById(modalId)?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(modalId) {
        document.getElementById(modalId)?.classList.add('hidden');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal('modal-tambah-sp'); });

    document.querySelectorAll('.delete-sp').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'Hapus Surat Peringatan?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: { popup: 'font-inter rounded-2xl', confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });
</script>
@endpush
