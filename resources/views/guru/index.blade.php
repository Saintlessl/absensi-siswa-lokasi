@extends('layouts.app')

@section('title', 'Data Guru')
@section('page-title', 'Data Guru')
@section('page-subtitle', 'Manajemen profil, akun autentikasi, dan penugasan guru')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><span class="text-dark-500">Manajemen Sekolah</span></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Data Guru</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dark-800">Manajemen Data Guru</h2>
            <p class="text-sm text-dark-400 mt-1">Satu akun guru terintegrasi untuk Guru Mata Pelajaran, Wali Kelas, dan KAPROG.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.workload') }}"
                class="inline-flex items-center gap-2 px-4 py-2 border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 text-sm font-medium rounded-xl shadow-sm transition-all duration-200">
                <i class='bx bx-pie-chart-alt-2 text-lg text-primary-600'></i>
                <span>Beban Kerja Guru</span>
            </a>
            <button onclick="openModal('modal-inputguru')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200">
                <i class='bx bx-plus text-lg'></i>
                <span>Tambah Guru Baru</span>
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

    @if(session('activation_url'))
        <div class="mb-6 p-4 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-900 text-sm">
            <div class="flex items-center justify-between font-bold mb-1">
                <span class="flex items-center gap-2">
                    <i class='bx bx-link-external text-lg text-indigo-600'></i> Tautan Aktivasi Akun Guru Siap Dibagikan
                </span>
                <button onclick="this.parentElement.parentElement.remove()" class="text-indigo-500 hover:text-indigo-700"><i class='bx bx-x text-lg'></i></button>
            </div>
            <p class="text-xs text-indigo-700 mb-2">Salin tautan ini dan kirimkan kepada guru untuk membuat password akunnya sendiri secara mandiri:</p>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ session('activation_url') }}" id="activation_url_copy"
                    class="w-full text-xs font-mono bg-white border border-indigo-200 rounded-lg px-3 py-2 text-dark-800 select-all">
                <button type="button" onclick="copyActivationUrl()"
                    class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold whitespace-nowrap flex items-center gap-1 transition">
                    <i class='bx bx-copy'></i> Salin
                </button>
            </div>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class='bx bx-user'></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-dark-400">Total Guru</p>
                <h3 class="text-2xl font-extrabold text-dark-900 mt-0.5">{{ $totalGurus }}</h3>
            </div>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class='bx bx-check-circle'></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-dark-400">Guru Aktif</p>
                <h3 class="text-2xl font-extrabold text-dark-900 mt-0.5">{{ $activeGurus }}</h3>
            </div>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class='bx bx-id-card'></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-dark-400">Wali Kelas Aktif</p>
                <h3 class="text-2xl font-extrabold text-dark-900 mt-0.5">{{ $homeroomGurus }}</h3>
            </div>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class='bx bx-briefcase'></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-dark-400">KAPROG</p>
                <h3 class="text-2xl font-extrabold text-dark-900 mt-0.5">{{ $kaprogGurus }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-4 mb-6 bg-white shadow-sm">
        <form action="{{ route('guru') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <label class="block text-xs font-medium text-dark-600 mb-1">Cari Guru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class='bx bx-search text-dark-400'></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                        placeholder="Nama, NIP, NUPTK, email...">
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-dark-600 mb-1">Status Kepegawaian</label>
                <select name="status_kepegawaian"
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="">Semua Status</option>
                    <option value="PNS" {{ $status_kepegawaian == 'PNS' ? 'selected' : '' }}>PNS</option>
                    <option value="PPPK" {{ $status_kepegawaian == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                    <option value="GTT/Honorer" {{ $status_kepegawaian == 'GTT/Honorer' ? 'selected' : '' }}>GTT / Honorer</option>
                    <option value="Tetap Yayasan" {{ $status_kepegawaian == 'Tetap Yayasan' ? 'selected' : '' }}>Tetap Yayasan</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-dark-600 mb-1">Status Akun</label>
                <select name="status_akun"
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="">Semua Akun</option>
                    <option value="active" {{ $status_akun == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ $status_akun == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="suspended" {{ $status_akun == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="deactivated" {{ $status_akun == 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-medium text-dark-600 mb-1">Tanggung Jawab Tambahan</label>
                <select name="additional_duty"
                    class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    <option value="">Semua Guru</option>
                    <option value="homeroom" {{ ($additional_duty ?? '') == 'homeroom' ? 'selected' : '' }}>Dengan Tugas Wali Kelas</option>
                    <option value="kaprog" {{ ($additional_duty ?? '') == 'kaprog' ? 'selected' : '' }}>Dengan Tugas KAPROG</option>
                    <option value="both" {{ ($additional_duty ?? '') == 'both' ? 'selected' : '' }}>Wali Kelas & KAPROG Sekaligus</option>
                    <option value="pure" {{ ($additional_duty ?? '') == 'pure' ? 'selected' : '' }}>Hanya Mengajar Mapel (Tanpa Tugas Tambahan)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-1.5">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                @if ($search || $status_kepegawaian || $status_akun || ($additional_duty ?? ''))
                    <a href="{{ route('guru') }}"
                        class="px-3 py-2 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Teacher Table --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-dark-50/70 border-b border-dark-200 text-[11px] font-bold text-dark-500 uppercase tracking-wider">
                        <th class="px-6 py-3.5 w-14 text-center">No</th>
                        <th class="px-6 py-3.5">Profil Guru</th>
                        <th class="px-6 py-3.5">NIP & NUPTK</th>
                        <th class="px-6 py-3.5">Kepegawaian</th>
                        <th class="px-6 py-3.5">Penugasan & Tanggung Jawab</th>
                        <th class="px-6 py-3.5 text-center">Status Akun</th>
                        <th class="px-6 py-3.5 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/60 text-sm">
                    @forelse ($guru as $g)
                        @php
                            $user = $g->user;
                            $activeHomeroom = $g->activeHomeroomAssignment;
                            $activeSubjectsCount = $g->activeSubjectAssignments->count();
                            $isKaprog = $g->is_kaprog;
                            $kaprogJurusan = $g->kaprog_jurusan;

                            $accountStatus = $user->status ?? 'active';
                            $statusBadge = match($accountStatus) {
                                'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'suspended' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'deactivated' => 'bg-dark-100 text-dark-600 border-dark-200',
                                default => 'bg-dark-100 text-dark-600'
                            };
                        @endphp
                        <tr class="hover:bg-dark-50/50 transition-colors">
                            <td class="px-6 py-4 text-center text-xs text-dark-500">{{ $guru->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs flex-shrink-0 overflow-hidden shadow-sm">
                                        @if($g->photo_url)
                                            <img src="{{ $g->photo_url }}" alt="{{ $g->nama }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($g->nama, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('guru.show', $g->id) }}" class="font-bold text-dark-900 hover:text-primary-600 transition">
                                                {{ $g->nama }}
                                            </a>
                                            @if ($g->jenis_kelamin === 'L')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200" title="Laki-laki">
                                                    L
                                                </span>
                                            @elseif ($g->jenis_kelamin === 'P')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-pink-50 text-pink-700 border border-pink-200" title="Perempuan">
                                                    P
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200" title="Jenis kelamin belum diatur">
                                                    <i class='bx bx-info-circle text-[11px]'></i> Belum diatur
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-dark-400 flex items-center gap-1.5 mt-0.5">
                                            <span><i class='bx bx-envelope text-xs'></i> {{ $user->email ?? '-' }}</span>
                                            @if($g->telepon)
                                                <span>&bull;</span>
                                                <span><i class='bx bxl-whatsapp text-emerald-600'></i> {{ $g->telepon }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono">
                                <div><span class="text-dark-400">NIP:</span> <span class="font-bold text-dark-800">{{ $g->nip ?? '-' }}</span></div>
                                <div class="mt-0.5"><span class="text-dark-400">NUPTK:</span> <span class="text-dark-700">{{ $g->nuptk ?? '-' }}</span></div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <span class="inline-block px-2.5 py-1 rounded-md bg-dark-100 font-semibold text-dark-700 border border-dark-200">
                                    {{ $g->status_kepegawaian ?? 'GTT/Honorer' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 items-center">
                                    {{-- Wali Kelas Badge --}}
                                    @if($activeHomeroom)
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1">
                                            <i class='bx bx-id-card'></i> Wali {{ $activeHomeroom->kelas->nama_kelas }}
                                        </span>
                                    @endif

                                    {{-- KAPROG Badge --}}
                                    @if($isKaprog && $kaprogJurusan)
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-purple-50 text-purple-800 border border-purple-200 flex items-center gap-1">
                                            <i class='bx bx-briefcase'></i> KAPROG {{ $kaprogJurusan->kode_jurusan }}
                                        </span>
                                    @endif

                                    {{-- Subject Teacher Count --}}
                                    @if($activeSubjectsCount > 0)
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-primary-50 text-primary-700 border border-primary-200 flex items-center gap-1">
                                            <i class='bx bx-book-open'></i> {{ $activeSubjectsCount }} Penugasan Mapel
                                        </span>
                                    @else
                                        <span class="text-[11px] text-dark-400 italic">Belum ada penugasan mapel</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold border uppercase tracking-wider {{ $statusBadge }}">
                                    {{ $accountStatus }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="editGuru('{{ $g->id }}')"
                                        class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-[#003FA9] border border-blue-200 hover:bg-blue-100 text-xs font-semibold flex items-center gap-1 transition"
                                        title="Edit Cepat Data Guru">
                                        <i class='bx bx-edit text-sm'></i>
                                        <span>Edit</span>
                                    </button>
                                    <a href="{{ route('guru.show', $g->id) }}"
                                        class="px-2.5 py-1.5 rounded-lg bg-primary-50 text-primary-700 border border-primary-200 hover:bg-primary-100 text-xs font-semibold flex items-center gap-1 transition"
                                        title="Detail Profil & Kelola Penugasan">
                                        <i class='bx bx-slider text-sm'></i>
                                        <span>Kelola</span>
                                    </a>

                                    <form action="{{ route('guru.delete', ['id' => $g->id]) }}" method="POST" onsubmit="return confirm('Hapus data guru {{ $g->nama }} beserta seluruh penugasannya?')">
                                        @csrf
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg border border-dark-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 inline-flex items-center justify-center text-dark-500 transition"
                                            title="Hapus Guru">
                                            <i class='bx bx-trash text-base'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-dark-400">
                                <i class='bx bx-user-x text-4xl mb-2 text-dark-300'></i>
                                <p class="text-sm font-semibold text-dark-700">Tidak ada data guru yang cocok</p>
                                <p class="text-xs text-dark-400 mt-1">Gunakan tombol "Tambah Guru Baru" di atas untuk mendaftarkan akun pengajar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($guru->hasPages())
            <div class="px-6 py-4 border-t border-dark-200">
                {{ $guru->links() }}
            </div>
        @endif
    </div>
@endsection

@push('modals')
    {{-- Multi-Step Wizard Modal for Guru (Exact Design System) --}}
    @include('guru.modal-wizard')

    {{-- Modal Edit Guru (Exact Design System) --}}
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
                document.body.classList.add('overflow-hidden');
                setTimeout(() => {
                    const scrollBody = document.getElementById('modal_guru_scroll_body');
                    if (scrollBody) {
                        scrollBody.scrollTop = 0;
                    }
                    const outerScroll = modal.querySelector('.overflow-y-auto');
                    if (outerScroll) {
                        outerScroll.scrollTop = 0;
                    }
                }, 20);
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-inputguru');
            }
        });

        function togglePasswordMode(mode) {
            const wrapper = document.getElementById('wrapper_direct_password');
            const input = document.getElementById('field_direct_password');
            if (mode === 'direct') {
                wrapper.classList.remove('hidden');
                input.required = true;
            } else {
                wrapper.classList.add('hidden');
                input.required = false;
                input.value = '';
            }
        }

        function previewPhoto(input) {
            const container = document.getElementById('preview_guru_photo');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    container.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                container.innerHTML = `<i class='bx bx-user text-3xl text-dark-300'></i>`;
            }
        }

        function selectAllClasses(checked) {
            document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = checked);
        }

        function toggleHomeroomSelect(checked) {
            const wrap = document.getElementById('wrapper_homeroom_select');
            const field = document.getElementById('field_homeroom_kelas_id');
            if (checked) {
                wrap.classList.remove('hidden');
                field.required = true;
            } else {
                wrap.classList.add('hidden');
                field.required = false;
                field.value = '';
            }
        }

        function toggleKaprogSelect(checked) {
            const wrap = document.getElementById('wrapper_kaprog_select');
            const field = document.getElementById('field_kaprog_jurusan_id');
            if (checked) {
                wrap.classList.remove('hidden');
                field.required = true;
            } else {
                wrap.classList.add('hidden');
                field.required = false;
                field.value = '';
            }
        }

        function copyActivationUrl() {
            const input = document.getElementById('activation_url_copy');
            if (!input) return;
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                alert('Tautan aktivasi berhasil disalin ke clipboard!');
            });
        }
    </script>

        function editGuru(id) {
            document.getElementById('loadeditform').innerHTML =
                '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-[#003FA9] border-t-transparent"></div><p class="text-xs text-slate-500 mt-2">Memuat data guru...</p></div>';
            openModal('modal-editguru');

            fetch('/guru/edit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.text())
            .then(html => {
                document.getElementById('loadeditform').innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('loadeditform').innerHTML = '<p class="text-rose-500 text-xs text-center py-4">Gagal memuat formulir edit.</p>';
            });
        }
    @endpush
