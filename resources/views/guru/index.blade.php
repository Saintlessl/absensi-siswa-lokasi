@extends('layouts.app')

@section('title', 'Data Guru')
@section('page-title', 'Data Guru')
@section('page-subtitle', 'Manajemen Data Guru')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Data Guru</li>
@endsection

@section('content')
    {{-- Flash Notifications --}}
    @if(session('activation_url'))
        <div class="mb-5 rounded-2xl bg-blue-50 p-4 border border-blue-200 text-sm text-[#003FA9] shadow-sm">
            <div class="flex items-center justify-between font-bold">
                <span class="flex items-center gap-2">
                    <i class='bx bx-link-external text-base'></i> Tautan Aktivasi Akun Guru Siap Dibagikan
                </span>
                <button type="button" onclick="this.closest('.mb-5').remove()" class="text-blue-400 hover:text-[#003FA9]">
                    <i class='bx bx-x text-lg'></i>
                </button>
            </div>
            <p class="mt-1 text-xs text-blue-700">Salin tautan ini dan kirimkan kepada guru untuk mengatur password akunnya sendiri secara mandiri:</p>
            <div class="mt-2 flex items-center gap-2">
                <input class="w-full rounded-xl border border-blue-200 bg-white px-3 py-2 font-mono text-xs text-slate-800 select-all" readonly value="{{ session('activation_url') }}" id="activation_url_copy" onclick="this.select()">
                <button type="button" onclick="copyActivationUrl()"
                    class="h-9 px-4 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold whitespace-nowrap inline-flex items-center gap-1.5 transition">
                    <i class='bx bx-copy'></i>
                    <span>Salin</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Main Container Card (Matching Data Siswa Reference 1) --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100">
        {{-- Card Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Daftar Guru</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola data pengajar, wali kelas, dan pimpinan program keahlian.</p>
            </div>
            <div class="flex items-center gap-2.5 self-end sm:self-auto">
                <a href="{{ route('guru.workload') }}"
                    class="h-11 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-xs inline-flex items-center gap-2 transition"
                    title="Beban Kerja Mengajar Guru">
                    <i class='bx bx-pie-chart-alt-2 text-base text-[#003FA9]'></i>
                    <span class="hidden sm:inline">Beban Kerja</span>
                </a>
                <button type="button" onclick="openModal('modal-inputguru')"
                    class="h-11 inline-flex items-center gap-2 px-4 py-2.5 bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold rounded-xl shadow-md shadow-blue-900/10 transition-all">
                    <i class='bx bx-plus text-base'></i>
                    <span>Tambah Data</span>
                </button>
            </div>
        </div>

        {{-- Search & Filter Bar (Matching Data Siswa layout) --}}
        <form action="{{ route('guru') }}" method="GET" class="mb-6">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-end gap-3">
                {{-- Cari Guru Input --}}
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Cari Guru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class='bx bx-search text-base'></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            class="w-full h-11 pl-10 pr-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-medium placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                            placeholder="Nama / NIP / NUPTK / Email...">
                    </div>
                </div>

                {{-- Status Kepegawaian Filter --}}
                <div class="w-full sm:w-44">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Kepegawaian</label>
                    <div class="relative">
                        <select name="status_kepegawaian"
                            class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                            <option value="">Semua Status</option>
                            <option value="PNS" {{ ($status_kepegawaian ?? '') == 'PNS' ? 'selected' : '' }}>PNS</option>
                            <option value="PPPK" {{ ($status_kepegawaian ?? '') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                            <option value="GTT/Honorer" {{ ($status_kepegawaian ?? '') == 'GTT/Honorer' ? 'selected' : '' }}>GTT / Honorer</option>
                            <option value="Tetap Yayasan" {{ ($status_kepegawaian ?? '') == 'Tetap Yayasan' ? 'selected' : '' }}>Tetap Yayasan</option>
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                {{-- Status Akun Filter --}}
                <div class="w-full sm:w-36">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Status Akun</label>
                    <div class="relative">
                        <select name="status_akun"
                            class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                            <option value="">Semua Akun</option>
                            <option value="active" {{ ($status_akun ?? '') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="pending" {{ ($status_akun ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="suspended" {{ ($status_akun ?? '') == 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                            <option value="deactivated" {{ ($status_akun ?? '') == 'deactivated' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                {{-- Tanggung Jawab Tambahan Filter --}}
                <div class="w-full sm:w-48">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5">Penugasan</label>
                    <div class="relative">
                        <select name="additional_duty"
                            class="w-full h-11 px-3.5 pr-8 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-[#003FA9] outline-none transition appearance-none">
                            <option value="">Semua Penugasan</option>
                            <option value="homeroom" {{ ($additional_duty ?? '') == 'homeroom' ? 'selected' : '' }}>Wali Kelas</option>
                            <option value="kaprog" {{ ($additional_duty ?? '') == 'kaprog' ? 'selected' : '' }}>KAPROG</option>
                            <option value="both" {{ ($additional_duty ?? '') == 'both' ? 'selected' : '' }}>Wali Kelas & KAPROG</option>
                            <option value="pure" {{ ($additional_duty ?? '') == 'pure' ? 'selected' : '' }}>Guru Mapel Murni</option>
                        </select>
                        <i class='bx bx-chevron-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-sm'></i>
                    </div>
                </div>

                {{-- Actions: Filter & Reset --}}
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="h-11 px-5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm transition">
                        <i class='bx bx-filter-alt text-sm'></i>
                        <span>Filter</span>
                    </button>
                    @if ($search || $status_kepegawaian || $status_akun || ($additional_duty ?? ''))
                        <a href="{{ route('guru') }}"
                            class="h-11 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold inline-flex items-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table Data Guru (Matching Data Siswa: Clean typography, no chips, no translucent borders) --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">IDENTITAS (NIP / NUPTK)</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NAMA LENGKAP</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">JENIS KELAMIN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">MATA PELAJARAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">PENUGASAN & STATUS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KONTAK</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-28">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($guru as $g)
                        @php
                            $user = $g->user;
                            $activeHomeroom = $g->activeHomeroomAssignment;
                            $activeSubjectsCount = $g->activeSubjectAssignments->count();
                            $isKaprog = $g->is_kaprog;
                            $kaprogJurusan = $g->kaprog_jurusan;

                            // Resolve clean subject list
                            $activeMapels = $g->activeSubjectAssignments->map(fn($sa) => $sa->mataPelajaran?->nama_mapel ?: $sa->mataPelajaran?->kode_mapel)->filter()->unique();
                            $mapelText = $activeMapels->isNotEmpty() ? $activeMapels->take(2)->implode(', ') . ($activeMapels->count() > 2 ? ' +' . ($activeMapels->count() - 2) : '') : ($g->mapel ?: '-');

                            // Resolve duty list
                            $dutyList = [];
                            if ($activeHomeroom) {
                                $dutyList[] = 'Wali ' . $activeHomeroom->kelas->nama_kelas;
                            }
                            if ($isKaprog && $kaprogJurusan) {
                                $dutyList[] = 'KAPROG ' . $kaprogJurusan->kode_jurusan;
                            }
                            if ($activeSubjectsCount > 0) {
                                $dutyList[] = $activeSubjectsCount . ' Rombel Mapel';
                            }
                            if (empty($dutyList)) {
                                $dutyList[] = 'Guru Mapel';
                            }

                            $accountStatus = $user->status ?? 'active';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- 1. No --}}
                            <td class="py-4 px-4 text-xs font-medium text-slate-400 text-center">
                                {{ $guru->firstItem() + $loop->index }}
                            </td>

                            {{-- 2. Identitas (NIP / NUPTK) --}}
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900 font-mono text-xs sm:text-sm">{{ $g->nip ?: '-' }}</div>
                                <div class="text-[11px] text-slate-400 font-normal">NUPTK: {{ $g->nuptk ?: '-' }}</div>
                            </td>

                            {{-- 3. Nama Lengkap & Email --}}
                            <td class="py-4 px-4">
                                <a href="{{ route('guru.show', $g->id) }}" class="font-bold text-slate-900 hover:text-[#003FA9] transition block">
                                    {{ $g->nama }}
                                </a>
                                @if($user?->email)
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $user->email }}</div>
                                @endif
                            </td>

                            {{-- 4. Jenis Kelamin (Clean text, no chip) --}}
                            <td class="py-4 px-4 font-semibold text-slate-800 text-xs sm:text-sm">
                                {{ $g->jenis_kelamin === 'L' ? 'Laki-laki' : ($g->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                            </td>

                            {{-- 5. Mata Pelajaran (Clean text, no chip) --}}
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-900 text-xs sm:text-sm">{{ $mapelText }}</div>
                                <div class="text-[11px] text-slate-400">{{ $g->status_kepegawaian ?: 'GTT / Honorer' }}</div>
                            </td>

                            {{-- 6. Penugasan & Status Akun --}}
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-800 text-xs sm:text-sm">{{ implode(' • ', $dutyList) }}</div>
                                <div class="text-[11px] font-bold mt-0.5 inline-flex items-center gap-1.5 {{ $accountStatus === 'active' ? 'text-emerald-600' : ($accountStatus === 'pending' ? 'text-amber-600' : 'text-slate-500') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $accountStatus === 'active' ? 'bg-emerald-500' : ($accountStatus === 'pending' ? 'bg-amber-500' : 'bg-slate-400') }}"></span>
                                    <span>{{ ucfirst($accountStatus) }}</span>
                                </div>
                            </td>

                            {{-- 7. Kontak --}}
                            <td class="py-4 px-4">
                                @if($g->telepon)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $g->telepon) }}" target="_blank"
                                       class="text-[#003FA9] hover:underline font-bold text-xs inline-flex items-center gap-1 font-mono">
                                        {{ $g->telepon }}
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- 8. Aksi (Minimalist icons matching Data Siswa) --}}
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('guru.show', $g->id) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Detail & Kelola Penugasan">
                                        <i class='bx bx-info-circle text-lg'></i>
                                    </a>
                                    <button type="button" onclick="editGuru('{{ $g->id }}')"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Edit Data Guru">
                                        <i class='bx bx-edit text-lg'></i>
                                    </button>
                                    <form action="{{ route('guru.delete', $g->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus data guru {{ $g->nama }} beserta akun autentikasinya?')">
                                        @csrf
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Hapus Guru">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class='bx bx-user text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Belum ada data guru</p>
                                <p class="text-xs text-slate-400 mt-0.5">Tidak ditemukan data guru yang sesuai filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($guru->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{ $guru->links() }}
            </div>
        @endif
    </div>
@endsection

@push('modals')
    {{-- Multi-Step Wizard Modal for Guru (Exact Design System) --}}
    @include('guru.modal-wizard')

    {{-- Modal Edit Guru (Design System) --}}
    <div id="modal-editguru" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
            onclick="closeModal('modal-editguru')"></div>
        <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
            <div class="relative w-full max-w-2xl rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Data Guru</h2>
                        <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                            Perbarui Profil, Mapel & Akun Guru
                        </span>
                    </div>
                    <button type="button" onclick="closeModal('modal-editguru')"
                        class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                <div id="loadeditform">
                    <div class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-[#003FA9] border-t-transparent"></div>
                        <p class="text-xs text-slate-500 mt-2">Memuat formulir...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function editGuru(id) {
        openModal('modal-editguru');
        document.getElementById('loadeditform').innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-[#003FA9] border-t-transparent"></div>
                <p class="text-xs text-slate-500 mt-2">Memuat formulir...</p>
            </div>
        `;

        fetch('{{ route("guru.edit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('loadeditform').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loadeditform').innerHTML = `
                <div class="text-center py-6 text-rose-600 text-xs">
                    Gagal memuat formulir edit guru. Silakan coba lagi.
                </div>
            `;
        });
    }

    function copyActivationUrl() {
        const copyInput = document.getElementById('activation_url_copy');
        if (copyInput) {
            copyInput.select();
            navigator.clipboard.writeText(copyInput.value);
            Swal.fire({
                title: 'Berhasil Disalin!',
                text: 'Tautan aktivasi akun guru siap dibagikan.',
                icon: 'success',
                confirmButtonColor: '#003FA9',
                customClass: {
                    popup: 'font-inter rounded-2xl',
                    confirmButton: 'rounded-xl text-xs px-5 py-2'
                }
            });
        }
    }
</script>
@endpush
