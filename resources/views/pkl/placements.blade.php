@extends('layouts.app')

@section('title', 'Penempatan Siswa PKL')
@section('page-title', 'Penempatan Siswa PKL')
@section('page-subtitle', 'Distribusi siswa ke dunia usaha/industri mitra beserta penugasan guru pembimbing sekolah.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Penempatan Siswa PKL</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Distribusi siswa ke dunia usaha/industri mitra beserta penugasan guru pembimbing sekolah.</p>
        </div>
        @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']))
        <button type="button" onclick="document.getElementById('modalTambahPenempatan').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Plotting Siswa PKL
        </button>
        @endif
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KELAS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">PERUSAHAAN MITRA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">GURU PEMBIMBING</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">PERIODE</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">STATUS</th>
                        @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']))
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right w-24">AKSI</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($placements as $pl)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-900 text-xs sm:text-sm block">{{ $pl->siswa->nama ?? 'Siswa' }}</span>
                                <span class="text-[11px] text-slate-400 font-normal">NIS: {{ $pl->siswa->nis ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-800 text-xs sm:text-sm">
                                {{ $pl->siswa->kelas->nama_kelas ?? '-' }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-900 hover:text-[#003FA9] transition block text-xs sm:text-sm">{{ $pl->partner->nama_dudi ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400 font-normal">{{ $pl->partner->kota ?? '' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-slate-800 font-medium text-xs sm:text-sm">{{ $pl->guruPembimbing->name ?? 'Belum Ditugaskan' }}</span>
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-600 font-mono">
                                {{ $pl->tanggal_mulai ? $pl->tanggal_mulai->format('d M Y') : '' }} s/d {{ $pl->tanggal_selesai ? $pl->tanggal_selesai->format('d M Y') : '' }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $pl->status === 'active' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }}">
                                    {{ strtoupper($pl->status) }}
                                </span>
                            </td>
                            @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']))
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                        data-placement="{{ json_encode([
                                            'id' => $pl->id,
                                            'siswa_id' => $pl->siswa_id,
                                            'siswa_nama' => $pl->siswa->nama ?? null,
                                            'jurusan_id' => $pl->siswa?->kelas?->jurusan_id,
                                            'kelas_id' => $pl->siswa?->kelas_id,
                                            'pkl_dudi_partner_id' => $pl->pkl_dudi_partner_id,
                                            'guru_pembimbing_id' => $pl->guru_pembimbing_id,
                                            'tanggal_mulai' => $pl->tanggal_mulai?->format('Y-m-d'),
                                            'tanggal_selesai' => $pl->tanggal_selesai?->format('Y-m-d'),
                                            'status' => $pl->status,
                                            'catatan' => $pl->catatan,
                                        ]) }}"
                                        onclick="openEditPlacement(this.dataset.placement)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                        title="Edit Penempatan">
                                        <i class='bx bx-edit text-lg'></i>
                                    </button>
                                    <form method="POST" action="{{ route('pkl.placements.destroy', $pl->id) }}"
                                          data-confirm="Batalkan/hapus penempatan {{ $pl->siswa->nama ?? 'siswa ini' }}? Jika sudah ada riwayat logbook/presensi, sistem akan mengubah status menjadi WITHDRAWN (riwayat tetap tersimpan)."
                                          data-confirm-title="Hapus Penempatan PKL"
                                          data-confirm-danger="true"
                                          class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Hapus Penempatan">
                                            <i class='bx bx-trash text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']) ? 7 : 6 }}" class="py-12 text-center text-slate-400">
                                <i class='bx bx-briefcase text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Belum ada penempatan siswa PKL</p>
                                <p class="text-xs text-slate-400 mt-0.5">Plotting siswa ke mitra DU/DI untuk memulai penugasan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($placements->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{ $placements->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('modals')
<!-- Modal Plotting Siswa -->
@if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']))
<div id="modalTambahPenempatan" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalTambahPenempatan').classList.add('hidden')"></div>

    {{-- Modal Card --}}
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-2xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">

            {{-- Header --}}
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Plotting Siswa ke Mitra DU/DI</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Penempatan Siswa Praktik Kerja Lapangan (PKL)</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalTambahPenempatan').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('pkl.placements.store') }}" id="formTambahPenempatan">
                @csrf
                <div class="px-8 py-6 space-y-4">
                    @php $capJ = auth()->user()->guru?->activeKaprogAssignment?->jurusan_id ?? (auth()->user()->role === 'kaprog' ? auth()->user()->jurusan_id : null); @endphp
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">1. Pilih Jurusan <span class="text-rose-500">*</span></label>
                        <select id="selJurusan" onchange="onJurusanChange(this.value)" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none" {{ $capJ ? 'disabled' : 'required' }}>
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach($jurusans as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="jurusan_id" id="hdnJurusan" value="{{ $capJ ?? ($jurusans->count() === 1 ? $jurusans->first()->id : '') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">2. Rombel Kelas 12 <span class="text-rose-500">*</span></label>
                        <select name="kelas_id" id="selRombel" onchange="onRombelChange(this.value)" class="w-full h-11 text-sm rounded-xl border-0 bg-slate-100 px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none disabled:text-slate-400" disabled>
                            <option value="">-- Pilih jurusan dulu --</option>
                        </select>
                    </div>

                    <div>
                        <x-searchable-select
                            name="siswa_ids"
                            id="selSiswaMulti"
                            label="3. Pilih Siswa (Bisa Pilih Banyak)"
                            placeholder="-- Pilih rombel terlebih dahulu --"
                            searchPlaceholder="Cari siswa berdasarkan nama, NIS, atau NISN..."
                            endpoint="{{ route('pkl.options.siswa') }}"
                            :multiple="true"
                            :required="true"
                            :disabled="true"
                            dependsOn="selRombel"
                            dependsParam="kelas_id"
                            hint="Mendukung multi-select, pencarian, dan pagination server-side"
                            emptyMessage="Tidak ada siswa kelas 12 yang eligible di rombel ini."
                            :perPage="15"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">4. Mitra Industri <span class="text-rose-500">*</span></label>
                        <select name="pkl_dudi_partner_id" id="selMitra" class="w-full h-11 text-sm rounded-xl border-0 bg-slate-100 px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none disabled:text-slate-400" disabled required>
                            <option value="">-- Pilih jurusan dulu --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Guru Pembimbing Sekolah</label>
                        <select name="guru_pembimbing_id" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">-- Tanpa Pembimbing / Nanti --</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_selesai" required
                                class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Khusus</label>
                        <textarea name="catatan" rows="2" placeholder="Catatan shift kerja, divisi magang, dll..."
                            class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalTambahPenempatan').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        <i class='bx bx-check text-lg'></i>
                        <span>Simpan Penempatan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modal Edit Penempatan --}}
@if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kaprog']))
<div id="modalEditPenempatan" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalEditPenempatan').classList.add('hidden')"></div>
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="relative w-full max-w-2xl bg-[#EFF5FF] rounded-[28px] shadow-2xl border border-blue-100/60 overflow-hidden">
            <div class="px-8 pt-7 pb-4 border-b border-blue-100/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Penempatan PKL</h2>
                        <p class="text-xs font-semibold text-[#003FA9] mt-1">Perbarui mitra, pembimbing, periode & status penempatan</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalEditPenempatan').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <form method="POST" id="formEditPenempatan" action="">
                @csrf @method('PUT')
                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Siswa</label>
                        <input type="text" id="edit_siswa_display" readonly class="w-full h-11 text-sm rounded-xl border-0 bg-slate-100 px-4 text-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Mitra Industri <span class="text-rose-500">*</span></label>
                        <select name="pkl_dudi_partner_id" id="edit_selMitra" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">-- Memuat mitra... --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Guru Pembimbing Sekolah</label>
                        <select name="guru_pembimbing_id" id="edit_selGuru" class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                            <option value="">-- Tanpa Pembimbing --</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Status <span class="text-rose-500">*</span></label>
                            <select name="status" id="edit_status" required class="w-full h-11 text-sm rounded-xl border-0 bg-[#E2EDFF] px-4 focus:ring-2 focus:ring-[#003FA9] appearance-none">
                                <option value="active">ACTIVE (berjalan)</option>
                                <option value="completed">COMPLETED (selesai)</option>
                                <option value="withdrawn">WITHDRAWN (dibatalkan)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Khusus</label>
                        <textarea name="catatan" id="edit_catatan" rows="2" class="w-full text-sm rounded-xl border-0 bg-[#E2EDFF] p-4 focus:ring-2 focus:ring-[#003FA9] resize-none"></textarea>
                    </div>
                </div>
                <div class="px-8 py-5 border-t border-blue-100/60 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('modalEditPenempatan').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-[#003FA9] hover:bg-blue-50 transition-colors">Batal</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#003FA9] text-white text-sm font-bold hover:bg-[#00297A] transition-colors shadow-lg shadow-blue-500/25">
                        <i class='bx bx-check text-lg'></i><span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endpush

@push('scripts')
<script>
const PKL_BASE = '{{ url("/pkl") }}';

function setPklOptions(sel, items, placeholder, selectedId) {
    sel.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = ''; ph.textContent = placeholder;
    sel.appendChild(ph);
    items.forEach(it => {
        const o = document.createElement('option');
        o.value = it.id;
        o.textContent = it.label;
        if (String(selectedId) === String(it.id)) o.selected = true;
        sel.appendChild(o);
    });
}

async function pklFetch(url) {
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

function fillMitraLabels(list) {
    return list.map(m => ({
        id: m.id,
        label: m.nama + ' (' + (m.kota || '-') + ')' + (m.kuota ? ' — sisa ' + m.sisa + '/' + m.kuota : '')
    }));
}

async function loadRombel(selJurusanId, prefillKelas, prefillSiswa) {
    const selR = document.getElementById('selRombel');
    const selM = document.getElementById('selMitra');
    if (!selR) return;
    setOptionsDown(selM);
    if (!selJurusanId) {
        setPklOptions(selR, [], '-- Pilih jurusan dulu --');
        selR.disabled = true;
        selR.dispatchEvent(new Event('change'));
        return;
    }
    selR.disabled = true;
    setPklOptions(selR, [], 'Memuat rombel...');
    try {
        const j = await pklFetch(PKL_BASE + '/options/rombel?jurusan_id=' + selJurusanId);
        if (!j.data.length) {
            setPklOptions(selR, [], j.message || 'Tidak ada rombel kelas 12');
            selR.disabled = true;
            selR.dispatchEvent(new Event('change'));
            return;
        }
        setPklOptions(selR, j.data.map(k => ({ id: k.id, label: k.nama })), '-- Pilih Rombel --', prefillKelas);
        selR.disabled = false;
        selR.dispatchEvent(new Event('change'));
        if (prefillKelas) await onRombelChange(prefillKelas, prefillSiswa);
        const jm = await pklFetch(PKL_BASE + '/options/mitra?jurusan_id=' + selJurusanId);
        setPklOptions(selM, fillMitraLabels(jm.data), '-- Pilih Mitra --');
        selM.disabled = !jm.data.length;
        if (!jm.data.length) setPklOptions(selM, [], jm.message || 'Tidak ada mitra tersedia');
    } catch (e) {
        setPklOptions(selR, [], 'Gagal memuat data, coba lagi');
    }
}

function setOptionsDown(selM) {
    if (selM) { selM.disabled = true; setPklOptions(selM, [], '-- Tunggu --'); }
    window.dispatchEvent(new CustomEvent('myskanic-select:reset', {
        detail: { name: 'siswa_ids', disabled: true }
    }));
}

function onJurusanChange(val) {
    const h = document.getElementById('hdnJurusan');
    if (h && !document.getElementById('selJurusan').disabled) h.value = val || '';
    setOptionsDown(document.getElementById('selMitra'));
    loadRombel(val);
}

async function onRombelChange(val, prefillSiswa) {
    const selR = document.getElementById('selRombel');
    if (selR) {
        selR.dispatchEvent(new Event('change'));
    }
}

function openEditPlacement(raw) {
    const p = typeof raw === 'string' ? JSON.parse(raw) : raw;
    document.getElementById('formEditPenempatan').action = PKL_BASE + '/penempatan/' + p.id;
    document.getElementById('edit_siswa_display').value = (p.siswa_nama || 'Siswa #' + p.siswa_id);
    document.getElementById('edit_tanggal_mulai').value = p.tanggal_mulai || '';
    document.getElementById('edit_tanggal_selesai').value = p.tanggal_selesai || '';
    document.getElementById('edit_catatan').value = p.catatan || '';
    document.getElementById('edit_status').value = p.status || 'active';
    document.getElementById('edit_selGuru').value = p.guru_pembimbing_id || '';
    (async () => {
        const sel = document.getElementById('edit_selMitra');
        setPklOptions(sel, [], 'Memuat mitra...');
        try {
            const j = await pklFetch(PKL_BASE + '/options/mitra' + (p.jurusan_id ? '?jurusan_id=' + p.jurusan_id : ''));
            setPklOptions(sel, fillMitraLabels(j.data), '-- Pilih Mitra --', p.pkl_dudi_partner_id);
        } catch (e) { setPklOptions(sel, [], 'Gagal memuat mitra'); }
    })();
    document.getElementById('modalEditPenempatan').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const selJ = document.getElementById('selJurusan');
    if (!selJ) return;
    const hidden = document.getElementById('hdnJurusan');
    const fixed = hidden ? hidden.value : '';
    if (fixed) {
        if (!selJ.disabled) { selJ.value = fixed; }
        loadRombel(fixed);
    }
});
</script>
@endpush
