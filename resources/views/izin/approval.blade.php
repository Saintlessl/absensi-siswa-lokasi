@extends('settings.layout')

@php
    $settingsTitle = 'Persetujuan Izin Siswa & Guru — MySkanic';
    $settingsPageTitle = 'Persetujuan Izin';
    $settingsPageSubtitle = 'Verifikasi dan kelola permohonan dispensasi kehadiran siswa dan pendidik';
@endphp

@section('settings-content')
<div class="space-y-6">

    {{-- Top Filter & Segmented Control Card --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-100 space-y-4">
        
        {{-- Status Tabs (Segmented Control) --}}
        <div class="flex items-center justify-between flex-wrap gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-1.5 overflow-x-auto p-1 bg-slate-100/80 rounded-2xl">
                @php
                    $tabs = [
                        'pending'   => ['label' => 'Sedang Menunggu', 'icon' => 'bx-time-five'],
                        'approved'  => ['label' => 'Disetujui',       'icon' => 'bx-check-circle'],
                        'rejected'  => ['label' => 'Ditolak',         'icon' => 'bx-x-circle'],
                        'cancelled' => ['label' => 'Dibatalkan',      'icon' => 'bx-block'],
                        'all'       => ['label' => 'Semua',           'icon' => 'bx-list-ul'],
                    ];
                @endphp

                @foreach($tabs as $tabKey => $tabData)
                    <a href="{{ route('settings.izin.approval', array_merge(request()->query(), ['status' => $tabKey])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-150 inline-flex items-center gap-1.5 whitespace-nowrap {{ $status === $tabKey ? 'bg-white text-[#003EA7] shadow-sm font-extrabold' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class='bx {{ $tabData['icon'] }}'></i>
                        <span>{{ $tabData['label'] }}</span>
                        @if($tabKey === 'pending' && $pendingCount > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'pending' ? 'bg-[#003EA7] text-white' : 'bg-amber-500 text-white' }}">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- Applicant Type Filter (Siswa / Guru) --}}
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-400 mr-1 hidden sm:inline">Pemohon:</span>
                <div class="flex items-center gap-1 p-1 bg-slate-100/80 rounded-xl text-xs font-bold">
                    <a href="{{ route('settings.izin.approval', array_merge(request()->query(), ['tipe' => 'all'])) }}"
                       class="px-2.5 py-1 rounded-lg transition {{ $tipe === 'all' ? 'bg-white text-[#003EA7] shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                    <a href="{{ route('settings.izin.approval', array_merge(request()->query(), ['tipe' => 'siswa'])) }}"
                       class="px-2.5 py-1 rounded-lg transition inline-flex items-center gap-1 {{ $tipe === 'siswa' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Siswa
                        @if($pendingSiswaCount > 0)
                            <span class="px-1 py-0.2 rounded-full text-[9px] {{ $tipe === 'siswa' ? 'bg-white text-blue-700' : 'bg-blue-100 text-blue-700' }}">{{ $pendingSiswaCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('settings.izin.approval', array_merge(request()->query(), ['tipe' => 'guru'])) }}"
                       class="px-2.5 py-1 rounded-lg transition inline-flex items-center gap-1 {{ $tipe === 'guru' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Guru
                        @if($pendingGuruCount > 0)
                            <span class="px-1 py-0.2 rounded-full text-[9px] {{ $tipe === 'guru' ? 'bg-white text-indigo-700' : 'bg-indigo-100 text-indigo-700' }}">{{ $pendingGuruCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        {{-- Search Input --}}
        <form method="GET" action="{{ route('settings.izin.approval') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="tipe" value="{{ $tipe }}">

            <div class="relative flex-1 w-full">
                <i class='bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari pemohon berdasarkan nama, NIS siswa, atau NIP guru..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit"
                    class="w-full sm:w-auto px-4 py-2.5 bg-[#003EA7] hover:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                    <i class='bx bx-filter-alt'></i> Terapkan
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('settings.izin.approval', ['status' => $status, 'tipe' => $tipe]) }}"
                        class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Submissions Table Card --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
        @if($izins->isEmpty())
            <div class="py-14 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-200/60 flex items-center justify-center mx-auto mb-3 text-slate-400 text-2xl">
                    <i class='bx bx-task-x'></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Tidak Ada Pengajuan</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Tidak ditemukan data pengajuan izin pada status dan kriteria filter yang dipilih.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 border-collapse">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100 font-bold">
                        <tr>
                            <th class="px-4 py-3.5">Pemohon</th>
                            <th class="px-4 py-3.5">Kategori</th>
                            <th class="px-4 py-3.5">Jenis Izin</th>
                            <th class="px-4 py-3.5">Periode / Durasi</th>
                            <th class="px-4 py-3.5">Keterangan</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($izins as $izin)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- Pemohon --}}
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs shadow-xs flex-shrink-0 {{ $izin->applicant_type === 'guru' ? 'bg-indigo-50 text-indigo-700' : 'bg-blue-50 text-[#003EA7]' }}">
                                            @if($izin->applicant_type === 'guru' && $izin->guru && $izin->guru->photo_path && file_exists(public_path('storage/' . $izin->guru->photo_path)))
                                                <img src="{{ asset('storage/' . $izin->guru->photo_path) }}" alt="{{ $izin->applicant_name }}" class="w-full h-full object-cover rounded-xl">
                                            @elseif($izin->applicant_type === 'siswa' && $izin->siswa && $izin->siswa->photo_path && file_exists(public_path('storage/' . $izin->siswa->photo_path)))
                                                <img src="{{ asset('storage/' . $izin->siswa->photo_path) }}" alt="{{ $izin->applicant_name }}" class="w-full h-full object-cover rounded-xl">
                                            @else
                                                <span>{{ strtoupper(substr($izin->applicant_name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block leading-tight text-sm">
                                                {{ $izin->applicant_name }}
                                            </span>
                                            <span class="text-[11px] text-slate-400 mt-0.5 block">
                                                {{ $izin->applicant_identifier }} • {{ $izin->applicant_detail }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kategori Pemohon --}}
                                <td class="px-4 py-4">
                                    @if($izin->applicant_type === 'guru')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800">
                                            <i class='bx bx-id-card'></i> GURU
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#EFF5FF] text-[#003EA7]">
                                            <i class='bx bx-user'></i> SISWA
                                        </span>
                                    @endif
                                </td>

                                {{-- Jenis Izin --}}
                                <td class="px-4 py-4 font-semibold text-slate-800">
                                    {{ $izin->jenis_label }}
                                </td>

                                {{-- Periode / Durasi --}}
                                <td class="px-4 py-4">
                                    <span class="font-mono text-slate-700 block">
                                        {{ $izin->tanggal_mulai ? $izin->tanggal_mulai->format('d/m/Y') : $izin->tanggal->format('d/m/Y') }}
                                        @if($izin->tanggal_selesai && $izin->tanggal_selesai->ne($izin->tanggal_mulai ?? $izin->tanggal))
                                            - {{ $izin->tanggal_selesai->format('d/m/Y') }}
                                        @endif
                                    </span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">
                                        {{ $izin->durasi_label }}
                                    </span>
                                </td>

                                {{-- Keterangan & Lampiran --}}
                                <td class="px-4 py-4 max-w-xs">
                                    <p class="line-clamp-2 text-slate-700 leading-snug" title="{{ $izin->keterangan }}">
                                        {{ $izin->keterangan }}
                                    </p>
                                    @if($izin->lampiran_url)
                                        <a href="{{ $izin->lampiran_url }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-[11px] text-[#003EA7] font-semibold hover:underline mt-1">
                                            <i class='bx bx-paperclip'></i> Lampiran File
                                        </a>
                                    @endif

                                    @if($izin->keputusan_catatan)
                                        <p class="text-[10px] text-slate-500 mt-1 italic">
                                            Catatan: "{{ $izin->keputusan_catatan }}"
                                        </p>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-4">
                                    @if($izin->status === 'pending')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Menunggu
                                        </span>
                                    @elseif($izin->status === 'approved')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Disetujui
                                        </span>
                                        @if($izin->decidedBy)
                                            <span class="block text-[10px] text-slate-400 mt-0.5">Oleh: {{ $izin->decidedBy->name }}</span>
                                        @endif
                                    @elseif($izin->status === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            Ditolak
                                        </span>
                                        @if($izin->decidedBy)
                                            <span class="block text-[10px] text-slate-400 mt-0.5">Oleh: {{ $izin->decidedBy->name }}</span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                            Dibatalkan
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-4 text-right">
                                    @php
                                        $canDecideThis = ($izin->applicant_type === 'siswa' && $canApproveSiswa) ||
                                                         ($izin->applicant_type === 'guru' && $canApproveGuru);
                                    @endphp

                                    @if($izin->status === 'pending' && $canDecideThis)
                                        <div class="inline-flex items-center gap-1.5">
                                            {{-- Setujui Button --}}
                                            <button type="button" onclick="promptDecision('{{ $izin->id }}', 'approve', '{{ addslashes($izin->applicant_name) }}', '{{ $izin->jenis_label }}')"
                                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition inline-flex items-center gap-1">
                                                <i class='bx bx-check text-base'></i> Setujui
                                            </button>

                                            {{-- Tolak Button --}}
                                            <button type="button" onclick="promptDecision('{{ $izin->id }}', 'reject', '{{ addslashes($izin->applicant_name) }}', '{{ $izin->jenis_label }}')"
                                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 transition inline-flex items-center gap-1">
                                                <i class='bx bx-x text-base'></i> Tolak
                                            </button>
                                        </div>

                                        {{-- Hidden forms for action submissions --}}
                                        <form id="approve-form-{{ $izin->id }}" action="{{ route('izin.approve', $izin->id) }}" method="POST" class="hidden">
                                            @csrf
                                            <input type="hidden" name="catatan" id="approve-catatan-{{ $izin->id }}">
                                        </form>

                                        <form id="reject-form-{{ $izin->id }}" action="{{ route('izin.reject', $izin->id) }}" method="POST" class="hidden">
                                            @csrf
                                            <input type="hidden" name="catatan" id="reject-catatan-{{ $izin->id }}">
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">
                                            {{ $izin->decided_at?->format('d/m/Y H:i') ?? '-' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $izins->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    function promptDecision(izinId, action, applicantName, jenisLabel) {
        const isApprove = action === 'approve';
        
        Swal.fire({
            title: isApprove ? 'Setujui Permohonan Izin?' : 'Tolak Permohonan Izin?',
            html: `
                <div class="text-left text-xs text-slate-600 mb-3 space-y-1">
                    <p>Pemohon: <strong>${applicantName}</strong></p>
                    <p>Jenis Izin: <strong>${jenisLabel}</strong></p>
                </div>
                <div class="text-left">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        ${isApprove ? 'Catatan Persetujuan (Opsional):' : 'Alasan Penolakan <span class="text-rose-500">*</span>:'}
                    </label>
                    <textarea id="swal-decision-catatan" class="swal2-textarea !text-xs !w-full !m-0 !p-2.5" rows="2" placeholder="${isApprove ? 'Contoh: Disetujui, harap menjaga kesehatan...' : 'Contoh: Tugas mendesak di sekolah, mohon hadir...'}"></textarea>
                </div>
            `,
            icon: isApprove ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: isApprove ? '#059669' : '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: isApprove ? 'Ya, Setujui Izin' : 'Tolak Izin',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const catatan = document.getElementById('swal-decision-catatan').value;
                if (!isApprove && !catatan.trim()) {
                    Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                    return false;
                }
                return catatan;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (isApprove) {
                    const input = document.getElementById(`approve-catatan-${izinId}`);
                    if (input) input.value = result.value || 'Disetujui';
                    document.getElementById(`approve-form-${izinId}`).submit();
                } else {
                    const input = document.getElementById(`reject-catatan-${izinId}`);
                    if (input) input.value = result.value;
                    document.getElementById(`reject-form-${izinId}`).submit();
                }
            }
        });
    }
</script>
@endpush
@endsection
