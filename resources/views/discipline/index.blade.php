@extends('layouts.app')

@section('title', 'Kedisiplinan & Prestasi Siswa')
@section('page-title', 'Kedisiplinan & Rekor Prestasi Siswa')
@section('page-subtitle', 'Pencatatan pelanggaran tata tertib sekolah, poin kedisiplinan, dan penghargaan prestasi.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Kedisiplinan & Rekor Prestasi Siswa</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Pencatatan pelanggaran tata tertib sekolah, poin kedisiplinan, dan penghargaan prestasi siswa.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('counseling.index') }}" class="px-3.5 py-2.5 rounded-xl bg-purple-50 text-purple-700 text-xs font-bold hover:bg-purple-100 transition border border-purple-200">
                Sesi Konseling BK
            </a>
            <button type="button" onclick="document.getElementById('modalCatatDisiplin').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Pelanggaran / Prestasi
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-rose-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pelanggaran</span>
                <div class="text-2xl font-black text-rose-600 mt-0.5">{{ $stats['total_pelanggaran'] }}</div>
                <span class="text-[10px] text-rose-500 font-medium">Kasus Tata Tertib</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Prestasi</span>
                <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ $stats['total_prestasi'] }}</div>
                <span class="text-[10px] text-emerald-600 font-medium">Penghargaan Diraih</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Poin Tercatat</span>
                <div class="text-2xl font-black text-blue-700 mt-0.5">{{ $stats['poin_tercatat'] }}</div>
                <span class="text-[10px] text-blue-600 font-medium">Kumulatif Poin</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Perlu Tindak Lanjut</span>
                <div class="text-2xl font-black text-amber-600 mt-0.5">{{ $stats['kasus_aktif'] }}</div>
                <span class="text-[10px] text-amber-600 font-medium">Status Terbuka</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('discipline.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau nama siswa..." class="w-full text-xs rounded-xl border-slate-200">
            </div>
            <div>
                <select name="tipe" class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">Semua Tipe Catatan</option>
                    <option value="pelanggaran" {{ request('tipe') == 'pelanggaran' ? 'selected' : '' }}>Pelanggaran Tata Tertib</option>
                    <option value="prestasi" {{ request('tipe') == 'prestasi' ? 'selected' : '' }}>Prestasi & Penghargaan</option>
                </select>
            </div>
            <div>
                <select name="kelas_id" class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('kelas_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition">Filter</button>
                <a href="{{ route('discipline.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Siswa</th>
                        <th class="py-3 px-4">Kategori & Judul</th>
                        <th class="py-3 px-4">Poin</th>
                        <th class="py-3 px-4">Dicatat Oleh</th>
                        <th class="py-3 px-4">Status & Tindak Lanjut</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($records as $rec)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-3 px-4 font-bold text-slate-700 whitespace-nowrap">
                                {{ $rec->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $rec->siswa->nama ?? 'Siswa' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $rec->siswa->kelas->nama_kelas ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold uppercase mb-1
                                    {{ $rec->category->tipe === 'prestasi' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $rec->category->nama ?? $rec->category->tipe }}
                                </span>
                                <div class="font-semibold text-slate-800">{{ $rec->judul }}</div>
                                <div class="text-[11px] text-slate-500 line-clamp-1">{{ $rec->deskripsi }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-black {{ $rec->category->tipe === 'prestasi' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $rec->category->tipe === 'prestasi' ? '+' : '-' }}{{ $rec->poin }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-[11px] text-slate-500">
                                {{ $rec->recordedBy->name ?? 'Sistem' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $rec->status === 'resolved' ? 'bg-emerald-50 text-emerald-700' : ($rec->status === 'verified' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ strtoupper($rec->status) }}
                                </span>
                                @if($rec->tindak_lanjut)
                                    <div class="text-[10px] text-slate-500 mt-0.5">Tindak: {{ $rec->tindak_lanjut }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <button type="button" onclick="openStatusModal({{ $rec->id }}, '{{ $rec->status }}', '{{ addslashes($rec->tindak_lanjut ?? '') }}')" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition">
                                    Update
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada catatan kedisiplinan atau prestasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('modals')
<!-- Modal Catat Disiplin/Prestasi -->
<div id="modalCatatDisiplin" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalCatatDisiplin').classList.add('hidden')"></div>

    {{-- Modal Card --}}
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-2xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">

            {{-- Header --}}
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Catat Kedisiplinan & Prestasi</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Pencatatan Poin Pelanggaran atau Penghargaan Siswa</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalCatatDisiplin').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('discipline.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Siswa <span class="text-rose-500">*</span></label>
                        <select name="siswa_id" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">-- Pilih Siswa Terkait --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kelas->nama_kelas ?? 'Kelas' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori Pelanggaran / Prestasi <span class="text-rose-500">*</span></label>
                        <select name="discipline_category_id" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">-- Pilih Kategori Standar --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">[{{ strtoupper($cat->tipe) }}] {{ $cat->nama }} (Default {{ $cat->default_poin }} poin)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Kejadian <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Bobot Poin Khusus (Opsional)</label>
                            <input type="number" name="poin" placeholder="Kosongkan jika default"
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Judul / Perihal <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul" required placeholder="Contoh: Terlambat Masuk Jam Pertama / Juara 1 Web Tech LKS"
                            class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Lengkap Kejadian <span class="text-rose-500">*</span></label>
                        <textarea name="deskripsi" rows="3" required placeholder="Uraikan detail kronologi kejadian atau rincian kejuaraan yang diraih..."
                            class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Bukti Foto / Surat / Sertifikat (Opsional)</label>
                        <input type="file" name="bukti"
                            class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#E2EDFF] file:text-[#003FA9] hover:file:bg-blue-200">
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalCatatDisiplin').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        <i class='bx bx-check text-lg'></i>
                        <span>Simpan Catatan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div id="modalUpdateStatus" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalUpdateStatus').classList.add('hidden')"></div>

    {{-- Modal Card --}}
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-lg bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">

            {{-- Header --}}
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Update Status Penanganan</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Tindak Lanjut & Verifikasi Kasus</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalUpdateStatus').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <form id="formUpdateStatus" method="POST" action="">
                @csrf
                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Penanganan <span class="text-rose-500">*</span></label>
                        <select name="status" id="selectStatus" required
                            class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="verified">Diverifikasi (Verified)</option>
                            <option value="resolved">Kasus Selesai (Resolved)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Tindak Lanjut</label>
                        <textarea name="tindak_lanjut" id="textareaTindakLanjut" rows="3"
                            placeholder="Contoh: Telah diberikan pembinaan lisan dan tugas kebersihan lab"
                            class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalUpdateStatus').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        <i class='bx bx-check text-lg'></i>
                        <span>Simpan Status</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openStatusModal(id, currentStatus, currentAction) {
        document.getElementById('formUpdateStatus').action = '{{ url('/kedisiplinan') }}/' + id + '/status';
        document.getElementById('selectStatus').value = currentStatus === 'resolved' ? 'resolved' : 'verified';
        document.getElementById('textareaTindakLanjut').value = currentAction || '';
        document.getElementById('modalUpdateStatus').classList.remove('hidden');
    }
</script>
@endpush

