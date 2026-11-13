@extends('layouts.app')

@section('title', 'Master Pelanggaran & Prestasi')
@section('page-title', 'Master Standar Pelanggaran & Prestasi')
@section('page-subtitle', 'Pusat pengelolaan master jenis pelanggaran tata tertib, apresiasi prestasi, dan bobot poin standar.')

@section('content')
<div class="space-y-6">
    {{-- Header & Sub-navigation Tabs --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Master Pelanggaran & Prestasi</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola standar pelanggaran dan prestasi siswa beserta bobot poin default.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('discipline.index') }}" class="px-3.5 py-2 rounded-xl bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition border border-slate-200 shadow-sm flex items-center gap-1.5">
                <i class='bx bx-list-ul text-sm text-slate-400'></i>
                <span>Catatan Kedisiplinan</span>
            </a>
            <a href="{{ route('discipline.master.index') }}" class="px-3.5 py-2 rounded-xl bg-[#003FA9] text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <i class='bx bx-cog text-sm'></i>
                <span>Master Data</span>
            </a>
            <a href="{{ route('counseling.index') }}" class="px-3.5 py-2 rounded-xl bg-white text-purple-700 text-xs font-bold hover:bg-purple-50 transition border border-purple-200 shadow-sm flex items-center gap-1.5">
                <i class='bx bx-conversation text-sm text-purple-500'></i>
                <span>Sesi Konseling BK</span>
            </a>
            <button type="button" onclick="openTambahModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#003FA9] text-white text-xs font-bold hover:bg-[#00297A] transition shadow-md shadow-blue-500/20">
                <i class='bx bx-plus text-base'></i>
                <span>Tambah Master Data</span>
            </button>
        </div>
    </div>

    {{-- 4 Summary Statistic Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Card Total Master --}}
        <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Standar</span>
                <div class="text-2xl font-black text-[#003FA9] mt-0.5">{{ $stats['total_master'] }}</div>
                <span class="text-[10px] text-blue-600 font-medium">Pelanggaran & Prestasi</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#003FA9] flex items-center justify-center">
                <i class='bx bx-library text-xl'></i>
            </div>
        </div>

        {{-- Card Pelanggaran Aktif --}}
        <div class="bg-white rounded-2xl p-4 border border-rose-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pelanggaran Aktif</span>
                <div class="text-2xl font-black text-rose-600 mt-0.5">{{ $stats['total_pelanggaran_aktif'] }}</div>
                <span class="text-[10px] text-rose-500 font-medium">Tata Tertib Sekolah</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i class='bx bx-error-circle text-xl'></i>
            </div>
        </div>

        {{-- Card Prestasi Aktif --}}
        <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Prestasi Aktif</span>
                <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ $stats['total_prestasi_aktif'] }}</div>
                <span class="text-[10px] text-emerald-600 font-medium">Apresiasi & Penghargaan</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class='bx bx-trophy text-xl'></i>
            </div>
        </div>

        {{-- Card Master Nonaktif --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Master Nonaktif</span>
                <div class="text-2xl font-black text-slate-600 mt-0.5">{{ $stats['total_nonaktif'] }}</div>
                <span class="text-[10px] text-slate-400 font-medium">Diarsipkan / Nonaktif</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <i class='bx bx-archive text-xl'></i>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('discipline.master.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama standar atau deskripsi..."
                    class="w-full text-sm rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] px-3.5 py-2 transition">
            </div>
            <div>
                <select name="tipe" class="w-full text-sm rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] px-3.5 py-2 transition">
                    <option value="">Semua Jenis (Pelanggaran & Prestasi)</option>
                    <option value="pelanggaran" {{ request('tipe') == 'pelanggaran' ? 'selected' : '' }}>Pelanggaran</option>
                    <option value="prestasi" {{ request('tipe') == 'prestasi' ? 'selected' : '' }}>Prestasi</option>
                </select>
            </div>
            <div>
                <select name="status" class="w-full text-sm rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] px-3.5 py-2 transition">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-[#003FA9] hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">Filter</button>
                <a href="{{ route('discipline.master.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition flex items-center justify-center">Reset</a>
            </div>
        </form>
    </div>

    {{-- Master Data Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">No</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nama Standar & Keterangan</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-36">Jenis</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-32 text-center">Poin Default</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-28 text-center">Status</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($categories as $index => $cat)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ !$cat->is_active ? 'opacity-60 bg-slate-50/30' : '' }}">
                            <td class="py-4 px-4 text-center font-bold text-slate-400 text-xs">
                                {{ $categories->firstItem() + $index }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                    <span>{{ $cat->nama }}</span>
                                    @if(!$cat->is_active)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">Nonaktif</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $cat->deskripsi ?: 'Tidak ada keterangan khusus.' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                @if($cat->tipe === 'prestasi')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-sm shadow-emerald-500/20">
                                        <i class='bx bx-trophy text-sm'></i>
                                        <span>Prestasi</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-600 text-white shadow-sm shadow-rose-500/20">
                                        <i class='bx bx-error text-sm'></i>
                                        <span>Pelanggaran</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($cat->default_poin < 0)
                                    <span class="inline-block px-3 py-1 rounded-xl text-xs font-extrabold bg-rose-100 text-rose-800">
                                        {{ $cat->formatted_default_poin }} Poin
                                    </span>
                                @elseif($cat->default_poin > 0)
                                    <span class="inline-block px-3 py-1 rounded-xl text-xs font-extrabold bg-emerald-100 text-emerald-800">
                                        {{ $cat->formatted_default_poin }} Poin
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 rounded-xl text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                                        0 Poin
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($cat->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Edit Button --}}
                                    <button type="button"
                                        onclick="openEditModal({{ json_encode($cat) }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Edit Master Data">
                                        <i class='bx bx-edit text-lg'></i>
                                    </button>

                                    {{-- Toggle Status Button --}}
                                    <form method="POST" action="{{ route('discipline.master.toggle-status', $cat->id) }}" class="inline" id="toggle-form-{{ $cat->id }}">
                                        @csrf
                                        <button type="button"
                                            onclick="confirmToggleStatus('toggle-form-{{ $cat->id }}', '{{ addslashes($cat->nama) }}', {{ $cat->is_active ? 'false' : 'true' }})"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition"
                                            title="{{ $cat->is_active ? 'Nonaktifkan Master' : 'Aktifkan Master' }}">
                                            <i class='bx {{ $cat->is_active ? "bx-toggle-right text-emerald-600" : "bx-toggle-left text-slate-400" }} text-xl'></i>
                                        </button>
                                    </form>

                                    {{-- Delete Button --}}
                                    <form method="POST" action="{{ route('discipline.master.destroy', $cat->id) }}" class="inline" id="delete-form-{{ $cat->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="confirmDeleteMaster('delete-form-{{ $cat->id }}', '{{ addslashes($cat->nama) }}')"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Hapus Master">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <i class='bx bx-library text-2xl text-slate-400'></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600">Belum ada master data</p>
                                    <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Tambah Master Data" untuk mendefinisikan standar baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('modals')
{{-- Modal Tambah Master Data --}}
<div id="modalTambahMaster" class="hidden fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeTambahModal()"></div>
    <div class="relative w-full max-w-xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-hidden my-auto z-10">
        <div class="px-8 pt-6 pb-4 border-b border-blue-100/60 shrink-0 bg-[#EFF5FF]">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Tambah Master Data Standar</h2>
                    <p class="text-xs font-semibold text-[#003FA9] mt-0.5">Kategori Pelanggaran Tata Tertib atau Penghargaan Prestasi</p>
                </div>
                <button type="button" onclick="closeTambahModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('discipline.master.store') }}" id="formTambahMaster" class="flex flex-col flex-1 overflow-hidden min-h-0">
            @csrf
            <div class="px-8 py-5 space-y-4 overflow-y-auto flex-1">
                {{-- Jenis Pilihan: Pelanggaran vs Prestasi --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Standar <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-rose-400 has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/50 transition">
                            <input type="radio" name="tipe" value="pelanggaran" checked onchange="onTipeChange('tambah', this.value)" class="text-rose-600 focus:ring-rose-500">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class='bx bx-error text-rose-600 text-sm'></i>
                                <span>Pelanggaran</span>
                            </span>
                        </label>
                        <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-emerald-400 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 transition">
                            <input type="radio" name="tipe" value="prestasi" onchange="onTipeChange('tambah', this.value)" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class='bx bx-trophy text-emerald-600 text-sm'></i>
                                <span>Prestasi</span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Nama Standar --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Standar <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: Terlambat Masuk Jam Pertama / Juara 1 LKS Kota"
                        class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                </div>

                {{-- Poin Default --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Poin Default <span class="text-rose-500">*</span>
                        <span class="font-normal text-slate-400 text-[11px] ml-1">(Pelanggaran biasanya negatif seperti -5, Prestasi biasanya positif seperti +50)</span>
                    </label>
                    <input type="number" name="default_poin" id="tambah_default_poin" value="-5" required
                        class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]"
                        oninput="checkPointNotice('tambah')">
                    <div id="tambah_poin_warning" class="hidden mt-1.5 p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center gap-1.5">
                        <i class='bx bx-info-circle text-sm text-amber-600'></i>
                        <span id="tambah_poin_warning_text"></span>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi / Keterangan Standar</label>
                    <textarea name="deskripsi" rows="3" placeholder="Uraikan kriteria, aturan spesifik, atau rincian pembinaan terkait standar ini..."
                        class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                </div>

                {{-- Status Aktif --}}
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="tambah_is_active" value="1" checked
                        class="w-4 h-4 rounded text-[#003FA9] focus:ring-[#003FA9]">
                    <label for="tambah_is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                        Aktifkan standar ini untuk digunakan pada pencatatan BK baru
                    </label>
                </div>
            </div>

            <div class="px-8 py-4 border-t border-blue-100/60 shrink-0 bg-[#EFF5FF] flex items-center justify-between">
                <button type="button" onclick="closeTambahModal()" class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                    <i class='bx bx-check text-lg'></i>
                    <span>Simpan Standar</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Master Data --}}
<div id="modalEditMaster" class="hidden fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="relative w-full max-w-xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-hidden my-auto z-10">
        <div class="px-8 pt-6 pb-4 border-b border-blue-100/60 shrink-0 bg-[#EFF5FF]">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Master Data Standar</h2>
                    <p class="text-xs font-semibold text-[#003FA9] mt-0.5">Perubahan tidak akan mengubah catatan historis yang telah tersimpan</p>
                </div>
                <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>
        </div>

        <form method="POST" action="" id="formEditMaster" class="flex flex-col flex-1 overflow-hidden min-h-0">
            @csrf
            @method('PUT')
            <div class="px-8 py-5 space-y-4 overflow-y-auto flex-1">
                {{-- Jenis Pilihan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Standar <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/50 transition">
                            <input type="radio" name="tipe" id="edit_tipe_pelanggaran" value="pelanggaran" onchange="onTipeChange('edit', this.value)" class="text-rose-600 focus:ring-rose-500">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class='bx bx-error text-rose-600 text-sm'></i>
                                <span>Pelanggaran</span>
                            </span>
                        </label>
                        <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 transition">
                            <input type="radio" name="tipe" id="edit_tipe_prestasi" value="prestasi" onchange="onTipeChange('edit', this.value)" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class='bx bx-trophy text-emerald-600 text-sm'></i>
                                <span>Prestasi</span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Nama Standar --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Standar <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" id="edit_nama" required
                        class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]">
                </div>

                {{-- Poin Default --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Poin Default <span class="text-rose-500">*</span>
                        <span class="font-normal text-slate-400 text-[11px] ml-1">(Dapat bernilai negatif, nol, atau positif)</span>
                    </label>
                    <input type="number" name="default_poin" id="edit_default_poin" required
                        class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9]"
                        oninput="checkPointNotice('edit')">
                    <div id="edit_poin_warning" class="hidden mt-1.5 p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center gap-1.5">
                        <i class='bx bx-info-circle text-sm text-amber-600'></i>
                        <span id="edit_poin_warning_text"></span>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi / Keterangan Standar</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3"
                        class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                </div>

                {{-- Status Aktif --}}
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                        class="w-4 h-4 rounded text-[#003FA9] focus:ring-[#003FA9]">
                    <label for="edit_is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                        Master aktif (muncul pada form pencatatan BK)
                    </label>
                </div>
            </div>

            <div class="px-8 py-4 border-t border-blue-100/60 shrink-0 bg-[#EFF5FF] flex items-center justify-between">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                    <i class='bx bx-check text-lg'></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openTambahModal() {
        document.getElementById('modalTambahMaster').classList.remove('hidden');
        checkPointNotice('tambah');
    }

    function closeTambahModal() {
        document.getElementById('modalTambahMaster').classList.add('hidden');
    }

    function openEditModal(cat) {
        document.getElementById('formEditMaster').action = '{{ url('/kedisiplinan/master') }}/' + cat.id;
        document.getElementById('edit_nama').value = cat.nama || '';
        document.getElementById('edit_default_poin').value = cat.default_poin;
        document.getElementById('edit_deskripsi').value = cat.deskripsi || '';
        document.getElementById('edit_is_active').checked = !!cat.is_active;

        if (cat.tipe === 'prestasi') {
            document.getElementById('edit_tipe_prestasi').checked = true;
        } else {
            document.getElementById('edit_tipe_pelanggaran').checked = true;
        }

        document.getElementById('modalEditMaster').classList.remove('hidden');
        checkPointNotice('edit');
    }

    function closeEditModal() {
        document.getElementById('modalEditMaster').classList.add('hidden');
    }

    function onTipeChange(prefix, value) {
        const poinInput = document.getElementById(prefix + '_default_poin');
        if (value === 'prestasi') {
            if (parseInt(poinInput.value) <= 0) {
                poinInput.value = 25;
            }
        } else {
            if (parseInt(poinInput.value) >= 0) {
                poinInput.value = -5;
            }
        }
        checkPointNotice(prefix);
    }

    function checkPointNotice(prefix) {
        const tipe = document.querySelector(`input[name="tipe"]:checked`) ? document.querySelector(`input[name="tipe"]:checked`).value : 'pelanggaran';
        const poin = parseInt(document.getElementById(prefix + '_default_poin').value);
        const warningBox = document.getElementById(prefix + '_poin_warning');
        const warningText = document.getElementById(prefix + '_poin_warning_text');

        if (isNaN(poin)) {
            warningBox.classList.add('hidden');
            return;
        }

        if (tipe === 'pelanggaran' && poin > 0) {
            warningText.innerText = 'Perhatian: Anda memasukkan poin positif untuk jenis Pelanggaran. Pelanggaran lazimnya bernilai negatif (pengurang skor).';
            warningBox.classList.remove('hidden');
        } else if (tipe === 'prestasi' && poin < 0) {
            warningText.innerText = 'Perhatian: Anda memasukkan poin negatif untuk jenis Prestasi. Prestasi lazimnya bernilai positif (penambah merit).';
            warningBox.classList.remove('hidden');
        } else {
            warningBox.classList.add('hidden');
        }
    }

    function confirmToggleStatus(formId, nama, willActivate) {
        const actionText = willActivate ? 'mengaktifkan' : 'menonaktifkan';
        const statusText = willActivate ? 'Master data akan kembali muncul di form pencatatan BK.' : 'Master data ini tidak akan muncul pada pencatatan BK baru, tetapi riwayat lama tetap terjaga utuh.';

        Swal.fire({
            title: `Yakin ingin ${actionText}?`,
            text: `Standar: "${nama}". ${statusText}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#003FA9',
            cancelButtonColor: '#94A3B8',
            confirmButtonText: `Ya, ${actionText.charAt(0).toUpperCase() + actionText.slice(1)}!`,
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

    function confirmDeleteMaster(formId, nama) {
        Swal.fire({
            title: 'Hapus Master Data?',
            text: `Apakah Anda yakin ingin menghapus standar "${nama}"? Jika standar sudah digunakan pada catatan siswa, sistem akan menolak penghapusan demi menjaga integritas data.`,
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
</script>
@endpush
