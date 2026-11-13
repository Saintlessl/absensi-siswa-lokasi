@extends('layouts.app')

@section('title', 'Kedisiplinan & Prestasi Siswa')
@section('page-title', 'Kedisiplinan & Rekor Prestasi Siswa')
@section('page-subtitle', 'Pencatatan pelanggaran tata tertib sekolah, poin kedisiplinan, dan penghargaan prestasi.')

@section('content')
<div class="space-y-6">
    {{-- Header & Sub-navigation Tabs --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Kedisiplinan & Rekor Prestasi Siswa</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Pencatatan multi-item pelanggaran tata tertib sekolah, akumulasi poin, dan apresiasi prestasi siswa.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('discipline.index') }}" class="px-3.5 py-2 rounded-xl bg-[#003FA9] text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <i class='bx bx-list-ul text-sm'></i>
                <span>Catatan Kedisiplinan</span>
            </a>
            <a href="{{ route('discipline.master.index') }}" class="px-3.5 py-2 rounded-xl bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition border border-slate-200 shadow-sm flex items-center gap-1.5">
                <i class='bx bx-cog text-sm text-slate-400'></i>
                <span>Master Data</span>
            </a>
            <a href="{{ route('counseling.index') }}" class="px-3.5 py-2 rounded-xl bg-white text-purple-700 text-xs font-bold hover:bg-purple-50 transition border border-purple-200 shadow-sm flex items-center gap-1.5">
                <i class='bx bx-conversation text-sm text-purple-500'></i>
                <span>Sesi Konseling BK</span>
            </a>
            <button type="button" onclick="openCatatModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#003FA9] text-white text-xs font-bold hover:bg-[#00297A] transition shadow-md shadow-blue-500/20">
                <i class='bx bx-plus text-base'></i>
                <span>Catat Pelanggaran / Prestasi</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-2xl flex items-center gap-2 font-medium shadow-sm">
            <i class='bx bx-check-circle text-lg text-emerald-600 shrink-0'></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl flex items-center gap-2 font-medium shadow-sm">
            <i class='bx bx-error-circle text-lg text-rose-600 shrink-0'></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- 4 Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-rose-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pelanggaran</span>
                <div class="text-2xl font-black text-rose-600 mt-0.5">{{ $stats['total_pelanggaran'] }}</div>
                <span class="text-[10px] text-rose-500 font-medium">{{ $stats['total_item_pelanggaran'] }} item kasus tercatat</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i class='bx bx-error text-xl'></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Prestasi</span>
                <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ $stats['total_prestasi'] }}</div>
                <span class="text-[10px] text-emerald-600 font-medium">{{ $stats['total_item_prestasi'] }} item prestasi diraih</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class='bx bx-trophy text-xl'></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Poin Tercatat</span>
                <div class="text-2xl font-black text-[#003FA9] mt-0.5">
                    {{ $stats['poin_tercatat'] > 0 ? '+' . $stats['poin_tercatat'] : $stats['poin_tercatat'] }}
                </div>
                <span class="text-[10px] text-blue-600 font-medium">Kumulatif Poin Catatan</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003FA9] flex items-center justify-center">
                <i class='bx bx-calculator text-xl'></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Perlu Tindak Lanjut</span>
                <div class="text-2xl font-black text-amber-600 mt-0.5">{{ $stats['kasus_aktif'] }}</div>
                <span class="text-[10px] text-amber-600 font-medium">Status Kasus Terbuka</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class='bx bx-time-five text-xl'></i>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('discipline.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, nama siswa, atau standar..."
                    class="w-full text-sm rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] px-3.5 py-2 transition">
            </div>
            <div>
                <select name="tipe" class="w-full text-sm rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] px-3.5 py-2 transition">
                    <option value="">Semua Tipe Catatan</option>
                    <option value="pelanggaran" {{ request('tipe') == 'pelanggaran' ? 'selected' : '' }}>Pelanggaran Tata Tertib</option>
                    <option value="prestasi" {{ request('tipe') == 'prestasi' ? 'selected' : '' }}>Prestasi & Penghargaan</option>
                </select>
            </div>
            <div>
                <select name="kelas_id" class="w-full text-sm rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] px-3.5 py-2 transition">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('kelas_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-[#003FA9] hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">Filter</button>
                <a href="{{ route('discipline.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition flex items-center justify-center">Reset</a>
            </div>
        </form>
    </div>

    {{-- Records Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-24">Tanggal</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siswa & Rombel</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kategori & Rincian Item</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-28">Total Poin</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-32">Dicatat Oleh</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-36">Status</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4 font-semibold text-slate-700 whitespace-nowrap text-xs">
                                {{ $rec->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full overflow-hidden bg-blue-50 border border-blue-100 shrink-0 flex items-center justify-center shadow-xs">
                                        @if($rec->siswa?->photo_url)
                                            <img src="{{ $rec->siswa->photo_url }}" alt="{{ $rec->siswa->nama }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-[10px] font-bold text-[#003FA9]">{{ strtoupper(substr($rec->siswa->nama ?? 'S', 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-800 block truncate">{{ $rec->siswa->nama ?? 'Siswa' }}</span>
                                        <span class="text-xs text-slate-400 mt-0.5 block truncate">NIS: {{ $rec->siswa->nis ?? '-' }} • {{ $rec->siswa->kelas->nama_kelas ?? 'Kelas' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                    @if($rec->effective_type === 'prestasi')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white uppercase tracking-wider">
                                            <i class='bx bx-trophy text-xs'></i>
                                            <span>Prestasi</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white uppercase tracking-wider">
                                            <i class='bx bx-error text-xs'></i>
                                            <span>Pelanggaran</span>
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class='bx bx-list-check'></i>
                                        <span>{{ $rec->items->count() > 0 ? $rec->items->count() . ' Item' : '1 Item' }}</span>
                                    </span>
                                </div>

                                <div class="font-bold text-slate-800 text-sm">{{ $rec->judul }}</div>
                                <div class="text-xs text-slate-500 line-clamp-1 mt-0.5">{{ $rec->deskripsi }}</div>

                                {{-- Inline Item Preview Tags --}}
                                @if($rec->items->count() > 0)
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($rec->items->take(3) as $it)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] bg-slate-50 border border-slate-200 text-slate-700">
                                                <span>{{ Str::limit($it->nama_standar, 25) }}</span>
                                                <span class="font-bold {{ $it->poin < 0 ? 'text-rose-600' : 'text-emerald-600' }}">({{ $it->formatted_poin }})</span>
                                            </span>
                                        @endforeach
                                        @if($rec->items->count() > 3)
                                            <span class="text-[10px] font-bold text-slate-400 self-center">+{{ $rec->items->count() - 3 }} lainnya</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($rec->poin < 0)
                                    <span class="inline-block px-3 py-1 rounded-xl text-xs font-black bg-rose-100 text-rose-800">
                                        {{ $rec->poin }} Poin
                                    </span>
                                @elseif($rec->poin > 0)
                                    <span class="inline-block px-3 py-1 rounded-xl text-xs font-black bg-emerald-100 text-emerald-800">
                                        +{{ $rec->poin }} Poin
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 rounded-xl text-xs font-black bg-slate-100 text-slate-600 border border-slate-200">
                                        0 Poin
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-500">
                                <span class="font-semibold text-slate-700 block">{{ $rec->recordedBy->name ?? 'Sistem' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $rec->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold 
                                    {{ $rec->status === 'resolved' ? 'bg-emerald-600 text-white' : ($rec->status === 'verified' ? 'bg-[#003FA9] text-white' : 'bg-amber-500 text-white') }}">
                                    {{ strtoupper($rec->status) }}
                                </span>
                                @if($rec->tindak_lanjut)
                                    <div class="text-[11px] text-slate-500 mt-1 line-clamp-1">Tindak: {{ $rec->tindak_lanjut }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    {{-- Detail Modal Button --}}
                                    <button type="button" onclick="viewRecordDetail({{ $rec->id }})"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition" title="Lihat Rincian Multi-Item">
                                        <i class='bx bx-show text-lg'></i>
                                    </button>

                                    {{-- Update Status Button --}}
                                    <button type="button" onclick="openStatusModal({{ $rec->id }}, '{{ $rec->status }}', '{{ addslashes($rec->tindak_lanjut ?? '') }}')"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Update Status Kasus">
                                        <i class='bx bx-check-shield text-lg'></i>
                                    </button>

                                    {{-- Delete Record Button --}}
                                    <form method="POST" action="{{ route('discipline.destroy', $rec->id) }}" class="inline" id="del-rec-{{ $rec->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDeleteRecord('del-rec-{{ $rec->id }}', '{{ addslashes($rec->judul) }}')"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Catatan">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <i class='bx bx-shield-quarter text-2xl text-slate-400'></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600">Belum ada catatan</p>
                                    <p class="text-xs text-slate-400 mt-1">Belum ada catatan pelanggaran atau prestasi siswa yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('modals')
{{-- MODAL CATAT KEDISIPLINAN & PRESTASI (MULTI-ITEM BUILDER + AJAX SEARCHABLE PICKER) --}}
<div id="modalCatatDisiplin" class="hidden fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4 sm:p-6"
     x-data="bkRecordManager()">

    {{-- Overlay --}}
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeCatatModal()"></div>

    {{-- Modal Card with Fixed Height Constraint & Smooth Scroll --}}
    <div class="relative w-full max-w-3xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-hidden my-auto z-10">

        {{-- Header (Fixed Top) --}}
        <div class="px-8 pt-6 pb-4 border-b border-blue-100/60 shrink-0 bg-[#EFF5FF]">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Catat Kedisiplinan & Prestasi</h2>
                    <p class="text-xs font-semibold text-[#003FA9] mt-0.5">Pencatatan Multi-Item Pelanggaran Tata Tertib atau Apresiasi Prestasi Siswa</p>
                </div>
                <button type="button" @click="closeCatatModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>
        </div>

        {{-- Form Container --}}
        <form method="POST" action="{{ route('discipline.store') }}" enctype="multipart/form-data" @submit="validateBeforeSubmit($event)"
              class="flex flex-col flex-1 overflow-hidden min-h-0">
            @csrf
            
            {{-- Scrollable Form Body --}}
            <div class="px-8 py-5 space-y-4 overflow-y-auto flex-1">
                {{-- 1. Pilih Siswa (Searchable with Photo & Live Details) --}}
                <div>
                    <x-searchable-select
                        name="siswa_id"
                        id="discipline_siswa_id"
                        label="1. Pilih Siswa"
                        endpoint="{{ route('options.select.siswa') }}"
                        placeholder="-- Pilih Siswa Terkait --"
                        searchPlaceholder="Ketik nama siswa, NIS, atau NISN..."
                        :required="true"
                    />
                </div>

                {{-- 2. Tanggal & Judul Catatan --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Kejadian <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required
                            class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Judul / Perihal <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul" required placeholder="Contoh: Terlambat & Seragam Tidak Lengkap / Juara LKS Web"
                            class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                    </div>
                </div>

                {{-- 3. INTERACTIVE MULTI-ITEM BUILDER AREA --}}
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-800">
                                3. Daftar Standar / Item BK
                                <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-[11px] text-slate-400 font-medium">Satu catatan dapat memuat beberapa item pelanggaran atau beberapa item prestasi sekaligus.</p>
                        </div>

                        {{-- Active Record Type Indicator --}}
                        <template x-if="recordType">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider text-white"
                                      :class="recordType === 'prestasi' ? 'bg-emerald-600' : 'bg-rose-600'">
                                    <i class='bx' :class="recordType === 'prestasi' ? 'bx-trophy' : 'bx-error'"></i>
                                    <span x-text="recordType === 'prestasi' ? 'CATATAN PRESTASI' : 'CATATAN PELANGGARAN'"></span>
                                </span>
                                <button type="button" @click="resetRecordTypePrompt()" class="text-xs font-bold text-slate-400 hover:text-rose-600 transition" title="Reset Semua Item">
                                    Reset
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Hidden Form Inputs for Native Submission --}}
                    <template x-for="(item, idx) in selectedItems" :key="item.id + '_' + idx">
                        <div>
                            <input type="hidden" :name="'items[' + idx + '][category_id]'" :value="item.id">
                            <input type="hidden" :name="'items[' + idx + '][poin]'" :value="item.poin">
                            <input type="hidden" :name="'items[' + idx + '][keterangan]'" :value="item.keterangan || ''">
                        </div>
                    </template>

                    {{-- EMPTY STATE: STEP 1 (PILIH STANDAR PERTAMA) --}}
                    <template x-if="selectedItems.length === 0">
                        <div class="border-2 border-dashed border-blue-200/80 rounded-2xl p-6 text-center bg-[#F8FAFC]">
                            <div class="w-12 h-12 rounded-full bg-blue-50 text-[#003FA9] flex items-center justify-center mx-auto mb-3">
                                <i class='bx bx-list-plus text-2xl'></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-700">Pilih Kategori Standar Pertama</h4>
                            <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto">
                                Item pertama yang Anda pilih akan menentukan jenis catatan (Pelanggaran atau Prestasi).
                            </p>
                            <button type="button" @click="openPickerModal('')"
                                class="mt-3.5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#003FA9] text-white text-xs font-bold hover:bg-[#00297A] transition shadow-md shadow-blue-500/20">
                                <i class='bx bx-search-alt text-base'></i>
                                <span>Pilih Standar Pertama (Pelanggaran / Prestasi)</span>
                            </button>
                        </div>
                    </template>

                    {{-- POPULATED STATE: TABLE OF CHOSEN ITEMS --}}
                    <template x-if="selectedItems.length > 0">
                        <div class="space-y-3">
                            <div class="overflow-hidden rounded-xl border border-slate-100">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
                                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                                            <th class="py-2.5 px-3">Nama Standar</th>
                                            <th class="py-2.5 px-3 w-28 text-center">Poin</th>
                                            <th class="py-2.5 px-3">Keterangan Khusus (Opsional)</th>
                                            <th class="py-2.5 px-3 w-12 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(item, idx) in selectedItems" :key="item.id + '_' + idx">
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="py-3 px-3 text-center font-bold text-slate-400" x-text="idx + 1"></td>
                                                <td class="py-3 px-3">
                                                    <div class="font-bold text-slate-800" x-text="item.nama"></div>
                                                    <div class="text-[10px] text-slate-400 line-clamp-1" x-text="item.deskripsi || '-'"></div>
                                                </td>
                                                <td class="py-3 px-3 text-center">
                                                    <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-extrabold"
                                                          :class="item.poin < 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'"
                                                          x-text="(item.poin > 0 ? '+' : '') + item.poin + ' Poin'">
                                                    </span>
                                                </td>
                                                <td class="py-2 px-3">
                                                    <input type="text" x-model="item.keterangan" placeholder="Catatan spesifik item ini..."
                                                        class="w-full h-8 text-xs rounded-lg border-slate-200 bg-slate-50 px-2.5 focus:bg-white focus:ring-1 focus:ring-[#003FA9]">
                                                </td>
                                                <td class="py-3 px-3 text-center">
                                                    <button type="button" @click="removeItem(idx)" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus item ini">
                                                        <i class='bx bx-trash text-base'></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Total Calculation Bar & Dynamic Action Button --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                                {{-- Dynamic Add Button --}}
                                <div>
                                    <button type="button" @click="openPickerModal(recordType)"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm"
                                        :class="recordType === 'prestasi' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200'">
                                        <i class='bx bx-plus text-sm'></i>
                                        <span x-text="recordType === 'prestasi' ? '+ Tambah Prestasi' : '+ Tambah Pelanggaran'"></span>
                                    </button>
                                </div>

                                {{-- Total Points Counter --}}
                                <div class="flex items-center gap-3 bg-[#EFF5FF] px-4 py-2 rounded-xl border border-slate-100">
                                    <span class="text-xs text-slate-600 font-semibold">Total Item: <span class="font-bold text-slate-800" x-text="selectedItems.length + ' Item'"></span></span>
                                    <span class="text-slate-300">|</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs text-slate-600 font-semibold">Total Poin:</span>
                                        <span class="text-sm font-black"
                                              :class="totalPoin < 0 ? 'text-rose-600' : (totalPoin > 0 ? 'text-emerald-600' : 'text-slate-600')"
                                              x-text="(totalPoin > 0 ? '+' : '') + totalPoin + ' Poin'">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- 4. Deskripsi Lengkap Kronologi --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">4. Deskripsi Lengkap Kejadian <span class="text-rose-500">*</span></label>
                    <textarea name="deskripsi" rows="3" required placeholder="Uraikan detail kronologi kejadian, tempat, waktu, atau rincian pembinaan yang telah dilakukan..."
                        class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                </div>

                {{-- 5. Bukti Dokumen / Foto --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">5. Bukti Foto / Surat / Sertifikat (Opsional)</label>
                    <input type="file" name="bukti"
                        class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#E2EDFF] file:text-[#003FA9] hover:file:bg-blue-200">
                </div>
            </div>

            {{-- Footer (Fixed Bottom) --}}
            <div class="px-8 py-4 border-t border-blue-100/60 bg-[#EFF5FF] shrink-0 flex items-center justify-between">
                <button type="button" @click="closeCatatModal()"
                    class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                    <i class='bx bx-check text-lg'></i>
                    <span>Simpan Catatan BK</span>
                </button>
            </div>
        </form>
    </div>

    {{-- SEARCHABLE AJAX DATA PICKER MODAL (SERVER-SIDE PAGINATION) --}}
    <div x-show="pickerOpen" x-cloak
         class="fixed inset-0 z-[80] overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="pickerOpen = false"></div>

        <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden z-10 flex flex-col max-h-[85vh] my-auto">
            {{-- Picker Header --}}
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 shrink-0">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900" x-text="pickerFilterType === 'pelanggaran' ? 'Pilih Standar Pelanggaran' : (pickerFilterType === 'prestasi' ? 'Pilih Standar Prestasi' : 'Pilih Standar Pelanggaran / Prestasi')"></h3>
                        <p class="text-[11px] text-slate-500 font-medium">Pencarian server-side master standar aktif</p>
                    </div>
                    <button type="button" @click="pickerOpen = false" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition">
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>

                {{-- Search Input with Debounce --}}
                <div class="relative">
                    <i class='bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                    <input type="text" x-model="pickerSearch" @input.debounce.300ms="fetchCategories(1)"
                        placeholder="Ketik nama pelanggaran, prestasi, atau kata kunci..."
                        class="w-full h-10 pl-10 pr-4 text-xs rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-[#003FA9] focus:border-[#003FA9]">
                </div>
            </div>

            {{-- Picker Results List (Scrollable) --}}
            <div class="p-4 overflow-y-auto flex-1 space-y-2">
                <template x-if="pickerLoading">
                    <div class="py-12 text-center text-slate-400 flex flex-col items-center justify-center">
                        <div class="w-6 h-6 border-2 border-[#003FA9] border-t-transparent rounded-full animate-spin mb-2"></div>
                        <span class="text-xs font-semibold">Memuat data master...</span>
                    </div>
                </template>

                <template x-if="!pickerLoading && pickerItems.length === 0">
                    <div class="py-10 text-center text-slate-400">
                        <i class='bx bx-search-alt text-3xl mb-1 text-slate-300'></i>
                        <p class="text-xs font-semibold text-slate-600">Tidak ada standar yang sesuai.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Coba kata kunci pencarian lain.</p>
                    </div>
                </template>

                <template x-if="!pickerLoading && pickerItems.length > 0">
                    <div class="space-y-2">
                        <template x-for="cat in pickerItems" :key="cat.id">
                            <div class="p-3.5 rounded-2xl border border-slate-100 hover:border-blue-300 hover:bg-[#EFF5FF]/40 transition flex items-center justify-between gap-3 group">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase"
                                              :class="cat.tipe === 'prestasi' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
                                              x-text="cat.tipe">
                                        </span>
                                        <span class="font-extrabold text-xs"
                                              :class="cat.poin < 0 ? 'text-rose-600' : 'text-emerald-600'"
                                              x-text="(cat.poin > 0 ? '+' : '') + cat.poin + ' Poin'">
                                        </span>
                                    </div>
                                    <h5 class="text-xs font-bold text-slate-800" x-text="cat.nama"></h5>
                                    <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1" x-text="cat.deskripsi || '-'"></p>
                                </div>

                                <button type="button" @click="selectItem(cat)"
                                    class="px-3.5 py-1.5 rounded-xl bg-[#003FA9] text-white text-xs font-bold hover:bg-[#00297A] transition shadow-sm group-hover:scale-105">
                                    Pilih
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Picker Footer Pagination --}}
            <div class="p-4 border-t border-slate-100 bg-slate-50 shrink-0 flex items-center justify-between text-xs text-slate-500">
                <div>
                    <span>Halaman <strong x-text="pickerCurrentPage"></strong> dari <strong x-text="pickerLastPage"></strong></span>
                    <span class="text-slate-400 ml-1">(Total <span x-text="pickerTotal"></span> standar)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="fetchCategories(pickerCurrentPage - 1)" :disabled="pickerCurrentPage <= 1"
                        class="px-3 py-1 rounded-lg border border-slate-200 bg-white font-bold hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed">
                        Prev
                    </button>
                    <button type="button" @click="fetchCategories(pickerCurrentPage + 1)" :disabled="pickerCurrentPage >= pickerLastPage"
                        class="px-3 py-1 rounded-lg border border-slate-200 bg-white font-bold hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL CATATAN BK (MULTI-ITEM INSPECTION) --}}
<div id="modalDetailDisiplin" class="hidden fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeDetailModal()"></div>
    <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-hidden my-auto z-10">
        <div class="px-8 pt-6 pb-4 border-b border-slate-100 shrink-0 flex items-center justify-between bg-slate-50/50">
            <div>
                <span id="dtl_badge_tipe" class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider text-white bg-rose-600">PELANGGARAN</span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight mt-1" id="dtl_judul">Detail Catatan</h2>
                <p class="text-xs text-slate-500 font-medium" id="dtl_sub">NIS - Siswa - Tanggal</p>
            </div>
            <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition">
                <i class='bx bx-x text-2xl'></i>
            </button>
        </div>

        <div class="px-8 py-5 space-y-4 overflow-y-auto flex-1">
            {{-- Student & Record Info Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-[#EFF5FF] p-4 rounded-2xl border border-slate-100 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Siswa</span>
                    <span class="font-extrabold text-slate-800" id="dtl_siswa_nama">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Kelas</span>
                    <span class="font-bold text-slate-800" id="dtl_siswa_kelas">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Tanggal</span>
                    <span class="font-bold text-slate-800" id="dtl_tanggal">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Poin</span>
                    <span class="font-black text-sm" id="dtl_total_poin">-</span>
                </div>
            </div>

            {{-- Child Items Table (Snapshot Records) --}}
            <div>
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class='bx bx-list-ul text-sm text-[#003FA9]'></i>
                    <span>Rincian Standar Snapshot</span>
                </h4>
                <div class="overflow-hidden rounded-xl border border-slate-100">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="py-2.5 px-3 w-8 text-center">No</th>
                                <th class="py-2.5 px-3">Standar</th>
                                <th class="py-2.5 px-3 w-28 text-center">Poin Snapshot</th>
                                <th class="py-2.5 px-3">Keterangan Khusus</th>
                            </tr>
                        </thead>
                        <tbody id="dtl_items_body" class="divide-y divide-slate-100">
                            {{-- Populated by JS --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Chronicled Description --}}
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Kronologi Kejadian / Pembinaan</span>
                <div class="p-3.5 bg-slate-50 rounded-xl text-xs text-slate-700 border border-slate-100 whitespace-pre-line" id="dtl_deskripsi">
                    -
                </div>
            </div>

            {{-- Status & Follow up --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Status Penanganan</span>
                    <span class="font-extrabold text-slate-800" id="dtl_status">-</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Dicatat Oleh</span>
                    <span class="font-extrabold text-slate-800" id="dtl_recorded_by">-</span>
                </div>
            </div>

            {{-- Tindak lanjut note --}}
            <div id="dtl_tindak_box" class="p-3.5 bg-amber-50 rounded-xl text-xs text-amber-900 border border-amber-200 hidden">
                <span class="font-bold block">Tindak Lanjut Sekolah:</span>
                <span id="dtl_tindak_text">-</span>
            </div>

            {{-- Attached Evidence Link --}}
            <div id="dtl_bukti_box" class="hidden">
                <a id="dtl_bukti_link" href="#" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 text-[#003FA9] text-xs font-bold hover:bg-blue-100 transition border border-blue-200">
                    <i class='bx bx-file text-base'></i>
                    <span>Lihat Berkas Bukti / Sertifikat</span>
                </a>
            </div>
        </div>

        <div class="px-8 py-4 border-t border-slate-100 bg-slate-50 shrink-0 text-right">
            <button type="button" onclick="closeDetailModal()" class="px-5 py-2 rounded-full text-xs font-bold bg-[#003FA9] text-white hover:bg-[#00297A] transition">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL UPDATE STATUS KASUS --}}
<div id="modalUpdateStatus" class="hidden fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeStatusModal()"></div>
    <div class="relative w-full max-w-lg bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-hidden my-auto z-10">
        <div class="px-8 pt-6 pb-4 border-b border-blue-100/60 shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Update Status Penanganan</h2>
                    <p class="text-xs font-semibold text-[#003FA9] mt-0.5">Tindak Lanjut & Verifikasi Kasus Siswa</p>
                </div>
                <button type="button" onclick="closeStatusModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>
        </div>

        <form id="formUpdateStatus" method="POST" action="" class="flex flex-col flex-1 overflow-hidden min-h-0">
            @csrf
            <div class="px-8 py-5 space-y-4 overflow-y-auto flex-1">
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

            <div class="px-8 py-4 border-t border-blue-100/60 shrink-0 bg-[#EFF5FF] flex items-center justify-between">
                <button type="button" onclick="closeStatusModal()" class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                    <i class='bx bx-check text-lg'></i>
                    <span>Simpan Status</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openCatatModal() {
        document.getElementById('modalCatatDisiplin').classList.remove('hidden');
    }

    function closeCatatModal() {
        document.getElementById('modalCatatDisiplin').classList.add('hidden');
    }

    function openStatusModal(id, currentStatus, currentAction) {
        document.getElementById('formUpdateStatus').action = '{{ url('/kedisiplinan') }}/' + id + '/status';
        document.getElementById('selectStatus').value = currentStatus === 'resolved' ? 'resolved' : 'verified';
        document.getElementById('textareaTindakLanjut').value = currentAction || '';
        document.getElementById('modalUpdateStatus').classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('modalUpdateStatus').classList.add('hidden');
    }

    function confirmDeleteRecord(formId, judul) {
        Swal.fire({
            title: 'Hapus Catatan Kedisiplinan?',
            text: `Yakin ingin menghapus catatan "${judul}"? Tindakan ini akan menghapus seluruh item rincian di dalamnya.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E11D48',
            cancelButtonColor: '#94A3B8',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-xl font-bold px-5 py-2.5',
                cancelButton: 'rounded-xl font-bold px-5 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    function viewRecordDetail(recordId) {
        fetch('{{ url('/kedisiplinan') }}/' + recordId + '/detail')
            .then(res => res.json())
            .then(data => {
                document.getElementById('dtl_judul').innerText = data.judul;
                document.getElementById('dtl_sub').innerText = (data.siswa.nama || 'Siswa') + ' • ' + (data.siswa.kelas || '-') + ' • ' + data.tanggal_id;
                document.getElementById('dtl_siswa_nama').innerText = data.siswa.nama;
                document.getElementById('dtl_siswa_kelas').innerText = data.siswa.kelas;
                document.getElementById('dtl_tanggal').innerText = data.tanggal_id;
                document.getElementById('dtl_total_poin').innerText = data.formatted_poin + ' Poin';
                document.getElementById('dtl_total_poin').className = 'font-black text-sm ' + (data.poin < 0 ? 'text-rose-600' : 'text-emerald-600');
                document.getElementById('dtl_deskripsi').innerText = data.deskripsi || '-';
                document.getElementById('dtl_status').innerText = (data.status || 'RECORDED').toUpperCase();
                document.getElementById('dtl_recorded_by').innerText = data.recorded_by || 'Sistem';

                const badge = document.getElementById('dtl_badge_tipe');
                if (data.tipe === 'prestasi') {
                    badge.innerText = 'PRESTASI';
                    badge.className = 'px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider text-white bg-emerald-600';
                } else {
                    badge.innerText = 'PELANGGARAN';
                    badge.className = 'px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider text-white bg-rose-600';
                }

                // Items Table
                const tbody = document.getElementById('dtl_items_body');
                tbody.innerHTML = '';
                if (data.items && data.items.length > 0) {
                    data.items.forEach(it => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-slate-50/50';
                        tr.innerHTML = `
                            <td class="py-2.5 px-3 text-center font-bold text-slate-400">${it.no}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-800">${it.nama_standar}</td>
                            <td class="py-2.5 px-3 text-center font-extrabold ${it.poin < 0 ? 'text-rose-600' : 'text-emerald-600'}">${it.formatted_poin} Poin</td>
                            <td class="py-2.5 px-3 text-slate-600">${it.keterangan || '-'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-slate-400">Tidak ada rincian item standar.</td></tr>`;
                }

                // Tindak lanjut
                const tindakBox = document.getElementById('dtl_tindak_box');
                if (data.tindak_lanjut) {
                    document.getElementById('dtl_tindak_text').innerText = data.tindak_lanjut;
                    tindakBox.classList.remove('hidden');
                } else {
                    tindakBox.classList.add('hidden');
                }

                // Evidence Link
                const buktiBox = document.getElementById('dtl_bukti_box');
                if (data.bukti_path) {
                    document.getElementById('dtl_bukti_link').href = data.bukti_path;
                    buktiBox.classList.remove('hidden');
                } else {
                    buktiBox.classList.add('hidden');
                }

                document.getElementById('modalDetailDisiplin').classList.remove('hidden');
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal memuat rincian data catatan kedisiplinan.', 'error');
            });
    }

    function closeDetailModal() {
        document.getElementById('modalDetailDisiplin').classList.add('hidden');
    }

    // Alpine component for multi-item BK management
    function bkRecordManager() {
        return {
            recordType: null, // 'pelanggaran' or 'prestasi'
            selectedItems: [],
            pickerOpen: false,
            pickerSearch: '',
            pickerFilterType: '',
            pickerCurrentPage: 1,
            pickerLastPage: 1,
            pickerTotal: 0,
            pickerLoading: false,
            pickerItems: [],

            get totalPoin() {
                return this.selectedItems.reduce((acc, item) => acc + parseInt(item.poin || 0), 0);
            },

            openPickerModal(filterType = '') {
                this.pickerFilterType = filterType || this.recordType || '';
                this.pickerSearch = '';
                this.pickerOpen = true;
                this.fetchCategories(1);
            },

            fetchCategories(page = 1) {
                if (page < 1) return;
                this.pickerLoading = true;
                this.pickerCurrentPage = page;

                const params = new URLSearchParams({
                    page: page,
                    per_page: 15,
                    only_active: 1
                });

                if (this.pickerSearch.trim()) {
                    params.append('q', this.pickerSearch.trim());
                }

                if (this.pickerFilterType) {
                    params.append('tipe', this.pickerFilterType);
                }

                fetch('{{ route('options.select.discipline-categories') }}?' + params.toString())
                    .then(res => res.json())
                    .then(res => {
                        this.pickerItems = res.data || [];
                        if (res.pagination) {
                            this.pickerCurrentPage = res.pagination.current_page;
                            this.pickerLastPage = res.pagination.last_page;
                            this.pickerTotal = res.pagination.total;
                        }
                    })
                    .catch(err => console.error(err))
                    .finally(() => {
                        this.pickerLoading = false;
                    });
            },

            selectItem(cat) {
                // Determine or verify record type
                if (this.selectedItems.length === 0) {
                    this.recordType = cat.tipe;
                } else if (this.recordType !== cat.tipe) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Jenis Tidak Sesuai',
                        text: `Catatan ini bertipe "${this.recordType.toUpperCase()}". Anda tidak dapat mencampurkan "${cat.tipe.toUpperCase()}" dalam satu catatan.`,
                        confirmButtonColor: '#003FA9',
                        customClass: { popup: 'rounded-3xl' }
                    });
                    return;
                }

                // Add item with snapshot poin
                this.selectedItems.push({
                    id: cat.id,
                    nama: cat.nama,
                    tipe: cat.tipe,
                    poin: parseInt(cat.poin || cat.default_poin),
                    deskripsi: cat.deskripsi || '',
                    keterangan: ''
                });

                this.pickerOpen = false;
            },

            removeItem(index) {
                this.selectedItems.splice(index, 1);
                if (this.selectedItems.length === 0) {
                    this.recordType = null;
                }
            },

            resetRecordTypePrompt() {
                Swal.fire({
                    title: 'Reset Seluruh Item?',
                    text: 'Semua standar yang telah dipilih akan dihapus dan Anda dapat memilih jenis catatan baru.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#E11D48',
                    cancelButtonColor: '#94A3B8',
                    confirmButtonText: 'Ya, Reset',
                    cancelButtonText: 'Batal',
                    customClass: { popup: 'rounded-3xl' }
                }).then((res) => {
                    if (res.isConfirmed) {
                        this.selectedItems = [];
                        this.recordType = null;
                    }
                });
            },

            validateBeforeSubmit(e) {
                if (this.selectedItems.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Standar Belum Dipilih',
                        text: 'Silakan pilih minimal 1 item standar pelanggaran atau prestasi sebelum menyimpan catatan BK.',
                        confirmButtonColor: '#003FA9',
                        customClass: { popup: 'rounded-3xl' }
                    });
                }
            }
        };
    }
</script>
@endpush
