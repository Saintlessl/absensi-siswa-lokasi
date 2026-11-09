@extends('layouts.app')

@section('title', 'Manajemen Biometrik Wajah')
@section('page-title', 'Manajemen Biometrik Wajah')
@section('page-subtitle', 'Monitoring template biometrik siswa, anti-spoofing, dan audit log verifikasi')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Biometrik Wajah</li>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- 4 Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card rounded-2xl border border-dark-200/50 p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-dark-400 uppercase tracking-wider">Total Siswa</p>
                    <h3 class="text-2xl font-black text-dark-800 mt-1">{{ $totalSiswa }}</h3>
                    <p class="text-[11px] text-dark-500 mt-0.5">Seluruh 33 Kelas</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl font-bold">
                    <i class='bx bx-group'></i>
                </div>
            </div>

            <div class="glass-card rounded-2xl border border-dark-200/50 p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-dark-400 uppercase tracking-wider">Terdaftar Biometrik</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $totalRegistered }}</h3>
                    <p class="text-[11px] text-emerald-700 mt-0.5">{{ $totalSiswa > 0 ? round(($totalRegistered / $totalSiswa) * 100) : 0 }}% terverifikasi</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                    <i class='bx bx-check-shield'></i>
                </div>
            </div>

            <div class="glass-card rounded-2xl border border-dark-200/50 p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-dark-400 uppercase tracking-wider">Belum Terdaftar</p>
                    <h3 class="text-2xl font-black text-amber-600 mt-1">{{ $totalUnregistered }}</h3>
                    <p class="text-[11px] text-amber-700 mt-0.5">Perlu registrasi wajah</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold">
                    <i class='bx bx-user-x'></i>
                </div>
            </div>

            <div class="glass-card rounded-2xl border border-dark-200/50 p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-dark-400 uppercase tracking-wider">Percobaan Mencurigakan</p>
                    <h3 class="text-2xl font-black text-rose-600 mt-1">{{ $totalSuspicious }}</h3>
                    <p class="text-[11px] text-rose-700 mt-0.5">Hari ini (Gagal Match/Spoof)</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl font-bold">
                    <i class='bx bx-shield-x'></i>
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="flex items-center gap-2 border-b border-dark-200">
            <a href="{{ route('admin.biometrik', ['tab' => 'students']) }}"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition-all flex items-center gap-2 {{ $activeTab === 'students' ? 'border-primary-600 text-primary-600' : 'border-transparent text-dark-500 hover:text-dark-800' }}">
                <i class='bx bx-user-check text-lg'></i>
                <span>Status Biometrik Siswa</span>
            </a>
            <a href="{{ route('admin.biometrik', ['tab' => 'logs']) }}"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition-all flex items-center gap-2 {{ $activeTab === 'logs' ? 'border-primary-600 text-primary-600' : 'border-transparent text-dark-500 hover:text-dark-800' }}">
                <i class='bx bx-receipt text-lg'></i>
                <span>Audit Log Verifikasi</span>
                @if ($totalSuspicious > 0)
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                @endif
            </a>
        </div>

        {{-- TAB 1: DAFTAR BIOMETRIK SISWA --}}
        @if ($activeTab === 'students')
            <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
                {{-- Filter Bar --}}
                <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/40">
                    <form action="{{ route('admin.biometrik') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <input type="hidden" name="tab" value="students">
                        <div>
                            <label class="block text-xs font-medium text-dark-600 mb-1">Cari Siswa</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                                placeholder="Nama atau NIS...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-dark-600 mb-1">Filter Kelas</label>
                            <select name="kelas_id" class="w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-dark-600 mb-1">Status Biometrik</label>
                            <select name="status_biometrik" class="w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none">
                                <option value="">Semua Status</option>
                                <option value="enrolled" {{ in_array(request('status_biometrik'), ['enrolled', 'registered']) ? 'selected' : '' }}>Enrolled (Aktif)</option>
                                <option value="not_enrolled" {{ in_array(request('status_biometrik'), ['not_enrolled', 'unregistered']) ? 'selected' : '' }}>Belum Enroll</option>
                                <option value="needs_re_enrollment" {{ request('status_biometrik') === 'needs_re_enrollment' ? 'selected' : '' }}>Wajib Enroll Ulang</option>
                                <option value="suspended" {{ request('status_biometrik') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 h-[38px]">
                                <i class='bx bx-filter-alt'></i> Filter
                            </button>
                            @if (request()->hasAny(['search', 'kelas_id', 'status_biometrik']))
                                <a href="{{ route('admin.biometrik', ['tab' => 'students']) }}" class="py-2 px-3 rounded-xl border border-dark-200 text-dark-600 hover:bg-dark-100 text-xs font-medium h-[38px] flex items-center">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-dark-50/50 border-b border-dark-200/50 text-xs font-semibold text-dark-500 uppercase tracking-wider">
                                <th class="px-6 py-4 w-12 text-center">No</th>
                                <th class="px-6 py-4">Siswa</th>
                                <th class="px-6 py-4">Kelas & Jurusan</th>
                                <th class="px-6 py-4 text-center">Status Biometrik</th>
                                <th class="px-6 py-4 text-center">Kualitas Template</th>
                                <th class="px-6 py-4">Tgl Registrasi</th>
                                <th class="px-6 py-4 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-200/50 text-sm">
                            @forelse ($siswas as $s)
                                <tr class="hover:bg-dark-50/50 transition-colors">
                                    <td class="px-6 py-4 text-center text-dark-500 font-medium">
                                        {{ $siswas->firstItem() + $loop->index }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($s->faceBiometric && $u = image_url($s->faceBiometric->sample_photo))
                                                <img src="{{ $u }}"
                                                    class="w-10 h-10 rounded-xl object-cover border border-dark-200 shadow-sm" loading="lazy">
                                            @else
                                                <div class="w-10 h-10 rounded-xl bg-dark-100 text-dark-500 flex items-center justify-center font-bold text-xs">
                                                    {{ strtoupper(substr($s->nama, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-bold text-dark-800 leading-tight">{{ $s->nama }}</p>
                                                <p class="text-xs text-dark-500">NIS: {{ $s->nis }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-dark-800">{{ $s->kelas->nama_kelas ?? '-' }}</p>
                                        <p class="text-xs text-dark-400">{{ $s->kelas->jurusan->kode_jurusan ?? '' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $bStatus = $s->biometric_status ?? ($s->faceBiometric && $s->faceBiometric->is_active ? 'enrolled' : 'not_enrolled');
                                        @endphp
                                        @if ($bStatus === 'enrolled')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class='bx bx-check-shield'></i> Enrolled
                                            </span>
                                        @elseif ($bStatus === 'needs_re_enrollment')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-orange-50 text-orange-700 border border-orange-200">
                                                <i class='bx bx-refresh'></i> Re-Enroll
                                            </span>
                                        @elseif ($bStatus === 'suspended')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <i class='bx bx-block'></i> Suspended
                                            </span>
                                        @elseif ($bStatus === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class='bx bx-loader-alt'></i> Pending
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i class='bx bx-time'></i> Belum Enroll
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($s->faceBiometric && $s->faceBiometric->quality_score)
                                            <span class="font-bold text-emerald-600 text-xs">{{ round($s->faceBiometric->quality_score) }}%</span>
                                        @else
                                            <span class="text-dark-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs text-dark-600">
                                        {{ $s->faceBiometric && ($s->faceBiometric->enrolled_at ?? $s->faceBiometric->registered_at) ? ($s->faceBiometric->enrolled_at ?? $s->faceBiometric->registered_at)->translatedFormat('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Bantu Pendaftaran Wajah via Perangkat Sekolah / Lab --}}
                                            @if ($bStatus !== 'enrolled')
                                                <a href="{{ route('admin.biometrik.assisted_enroll', ['id' => $s->id]) }}"
                                                    title="Bantu Pendaftaran Wajah (Perangkat Sekolah / Lab)"
                                                    class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                                                    <i class='bx bx-desktop text-base'></i>
                                                </a>
                                            @endif

                                            {{-- Minta Re-Enroll (Admin & Superadmin) --}}
                                            @if ($s->faceBiometric && $s->biometric_status === 'enrolled')
                                                <form action="{{ route('admin.biometrik.re_enroll', ['id' => $s->id]) }}" method="POST" class="inline"
                                                    onsubmit="return confirm(@js('Minta siswa ' . $s->nama . ' untuk mendaftarkan ulang wajahnya saat login berikutnya?'));">
                                                    @csrf
                                                    <button type="submit" title="Minta Registrasi Ulang Wajah"
                                                        class="p-2 rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-100 transition-colors">
                                                        <i class='bx bx-refresh text-base'></i>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Toggle Suspend / Aktifkan (Admin & Superadmin) --}}
                                            @if ($s->faceBiometric)
                                                <form action="{{ route('admin.biometrik.toggle_suspend', ['id' => $s->id]) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('{{ $s->biometric_status === 'suspended' ? 'Aktifkan kembali biometrik siswa ini?' : 'Suspend biometrik siswa ini?' }}');">
                                                    @csrf
                                                    <button type="submit" title="{{ $s->biometric_status === 'suspended' ? 'Aktifkan Biometrik' : 'Suspend Biometrik' }}"
                                                        class="p-2 rounded-lg {{ $s->biometric_status === 'suspended' ? 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' : 'text-slate-600 bg-slate-100 hover:bg-slate-200' }} transition-colors">
                                                        <i class='bx {{ $s->biometric_status === 'suspended' ? 'bx-play-circle' : 'bx-pause-circle' }} text-base'></i>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Reset Permanen (Superadmin Only) --}}
                                            @if (Auth::user()->role === 'superadmin' && $s->faceBiometric)
                                                <form action="{{ route('admin.biometrik.reset', ['id' => $s->id]) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin mereset biometrik wajah siswa ini? Data biometrik akan dihapus dan siswa wajib merekam ulang wajahnya.');">
                                                    @csrf
                                                    <button type="submit" title="Reset Biometrik Permanen (Superadmin Only)"
                                                        class="p-2 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 transition-colors">
                                                        <i class='bx bx-reset text-base'></i>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Presensi Manual Fallback --}}
                                            <button type="button" onclick='openManualOverrideModal({{ $s->id }}, @js($s->nama))'
                                                title="Presensi Manual (Kasus Darurat/Disabilitas)"
                                                class="p-2 rounded-lg text-primary-600 bg-primary-50 hover:bg-primary-100 transition-colors">
                                                <i class='bx bx-edit text-base'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-dark-400 text-sm">
                                        Tidak ada data siswa yang cocok dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($siswas->hasPages())
                    <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/20">
                        {{ $siswas->links() }}
                    </div>
                @endif
            </div>
        @endif

        {{-- TAB 2: AUDIT LOG VERIFIKASI --}}
        @if ($activeTab === 'logs')
            <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
                {{-- Filter Log Bar --}}
                <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/40">
                    <form action="{{ route('admin.biometrik') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <input type="hidden" name="tab" value="logs">
                        <div>
                            <label class="block text-xs font-medium text-dark-600 mb-1">Filter Status Log</label>
                            <select name="log_status" class="w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none">
                                <option value="">Semua Status Log</option>
                                <option value="success" {{ request('log_status') === 'success' ? 'selected' : '' }}>Sukses Verifikasi</option>
                                <option value="failed_match" {{ request('log_status') === 'failed_match' ? 'selected' : '' }}>Gagal Match (Beda Orang / Titip Absen)</option>
                                <option value="failed_liveness" {{ request('log_status') === 'failed_liveness' ? 'selected' : '' }}>Gagal Liveness (Indikasi Spoof)</option>
                                <option value="failed_location" {{ request('log_status') === 'failed_location' ? 'selected' : '' }}>Gagal Lokasi GPS</option>
                                <option value="manual_override" {{ request('log_status') === 'manual_override' ? 'selected' : '' }}>Presensi Manual</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-dark-600 mb-1">Tanggal</label>
                            <input type="date" name="log_date" value="{{ request('log_date') }}"
                                class="w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold transition-all h-[38px] flex items-center justify-center gap-1.5">
                                <i class='bx bx-filter-alt'></i> Terapkan
                            </button>
                            @if (request()->hasAny(['log_status', 'log_date']))
                                <a href="{{ route('admin.biometrik', ['tab' => 'logs']) }}" class="py-2 px-3 rounded-xl border border-dark-200 text-dark-600 hover:bg-dark-100 text-xs font-medium h-[38px] flex items-center">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-dark-50/50 border-b border-dark-200/50 text-xs font-semibold text-dark-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Waktu</th>
                                <th class="px-6 py-4">Siswa</th>
                                <th class="px-6 py-4 text-center">Sesi</th>
                                <th class="px-6 py-4 text-center">Hasil Status</th>
                                <th class="px-6 py-4 text-center">Kemiripan / Jarak</th>
                                <th class="px-6 py-4">Catatan / Alasan</th>
                                <th class="px-6 py-4">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-200/50 text-xs">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-dark-50/50 transition-colors {{ in_array($log->status, ['failed_match', 'failed_liveness']) ? 'bg-rose-50/30' : '' }}">
                                    <td class="px-6 py-4 text-dark-500 whitespace-nowrap">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-dark-800">{{ $log->siswa->nama ?? 'Siswa' }}</p>
                                        <p class="text-dark-400 text-[11px]">{{ $log->siswa->kelas->nama_kelas ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $log->session_type === 'masuk' ? 'bg-emerald-100 text-emerald-800' : ($log->session_type === 'pulang' ? 'bg-primary-100 text-primary-800' : 'bg-purple-100 text-purple-800') }}">
                                            {{ $log->session_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($log->status === 'success')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Sukses
                                            </span>
                                        @elseif ($log->status === 'failed_match')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="Beda Orang / Titip Absen">
                                                Wajah Beda
                                            </span>
                                        @elseif ($log->status === 'failed_liveness')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200" title="Indikasi Foto/Video">
                                                Liveness Gagal
                                            </span>
                                        @elseif ($log->status === 'manual_override')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Manual
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-dark-100 text-dark-700">
                                                {{ $log->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono">
                                        @if ($log->confidence_score !== null)
                                            <span class="font-bold {{ $log->confidence_score >= 70 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ round($log->confidence_score, 1) }}%
                                            </span>
                                            <span class="text-dark-400 text-[10px]">({{ round($log->euclidean_distance, 3) }})</span>
                                        @else
                                            <span class="text-dark-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-dark-600 max-w-xs truncate" title="{{ $log->failure_reason }}">
                                        {{ $log->failure_reason ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-dark-400 text-[11px]">
                                        {{ $log->ip_address ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-dark-400 text-sm">
                                        Belum ada rekaman audit log verifikasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/20">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>

    {{-- MODAL PRESENSI MANUAL --}}
    <div id="modalManualOverride" class="fixed inset-0 z-[70] hidden bg-dark-950/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-dark-100">
                <h4 class="font-bold text-dark-900 text-base flex items-center gap-2">
                    <i class='bx bx-edit text-primary-600 text-xl'></i> Presensi Manual Fallback
                </h4>
                <button type="button" onclick="closeManualOverrideModal()" class="text-dark-400 hover:text-dark-700 text-xl">
                    <i class='bx bx-x'></i>
                </button>
            </div>

            <form action="{{ route('admin.biometrik.override') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="siswa_id" id="overrideSiswaId">

                <div>
                    <label class="block text-xs font-semibold text-dark-600 mb-1">Nama Siswa</label>
                    <input type="text" id="overrideSiswaNama" readonly class="w-full px-3 py-2 rounded-xl bg-dark-50 border border-dark-200 text-sm text-dark-800 font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-dark-600 mb-1">Sesi Presensi</label>
                        <select name="tipe" required class="w-full px-3 py-2 rounded-xl border border-dark-200 text-sm">
                            <option value="masuk">Absen Masuk</option>
                            <option value="pulang">Absen Pulang</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-dark-600 mb-1">Status Kehadiran</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-xl border border-dark-200 text-sm">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-dark-600 mb-1">Alasan Verifikasi Manual (Wajib Audit) <span class="text-rose-500">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Kamera ponsel siswa rusak fisik / verifikasi wajah terkendala disabilitas dengan persetujuan wali kelas..."
                        class="w-full p-3 rounded-xl border border-dark-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeManualOverrideModal()" class="px-4 py-2 rounded-xl border border-dark-200 text-xs font-semibold text-dark-600 hover:bg-dark-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-md shadow-primary-500/30">
                        Simpan Presensi Manual
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openManualOverrideModal(id, nama) {
            document.getElementById('overrideSiswaId').value = id;
            document.getElementById('overrideSiswaNama').value = nama;
            document.getElementById('modalManualOverride').classList.remove('hidden');
        }

        function closeManualOverrideModal() {
            document.getElementById('modalManualOverride').classList.add('hidden');
        }
    </script>
@endpush
