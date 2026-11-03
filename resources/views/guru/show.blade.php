@extends('layouts.app')

@section('title', 'Detail Guru - ' . $guru->nama)
@section('page-title', 'Detail & Manajemen Guru')
@section('page-subtitle', 'Hub pusat pengelolaan profil, akun autentikasi, penugasan mata pelajaran, wali kelas, KAPROG, dan beban kerja')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('guru') }}" class="text-dark-500 hover:text-primary-600 transition">Data Guru</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">{{ $guru->nama }}</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru') }}"
                class="w-10 h-10 rounded-xl bg-white border border-dark-200 hover:bg-dark-50 text-dark-600 flex items-center justify-center transition shadow-sm">
                <i class='bx bx-arrow-back text-lg'></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-dark-800">{{ $guru->nama }}</h2>
                <p class="text-xs text-dark-400">NIP: {{ $guru->nip ?? '-' }} &bull; NUPTK: {{ $guru->nuptk ?? '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.workload') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 text-xs font-semibold rounded-xl shadow-sm transition">
                <i class='bx bx-pie-chart-alt-2 text-primary-600 text-base'></i>
                <span>Lihat Beban Kerja Sekolah</span>
            </a>
            <form action="{{ route('guru.delete', $guru->id) }}" method="POST"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru beserta akun autentikasinya? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-xl transition">
                    <i class='bx bx-trash text-base'></i>
                    <span>Hapus Guru</span>
                </button>
            </form>
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

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between text-rose-800 text-sm font-semibold">
            <div class="flex items-center gap-2">
                <i class='bx bx-error-circle text-lg text-rose-600'></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i class='bx bx-x text-lg'></i></button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold">
            <div class="flex items-center gap-2 mb-1">
                <i class='bx bx-error-circle text-lg text-rose-600'></i>
                <span>Terdapat kesalahan pada formulir:</span>
            </div>
            <ul class="list-disc pl-5 text-xs text-rose-700 space-y-0.5 font-normal">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('activation_url'))
        <div class="mb-6 p-4 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-900 text-sm shadow-sm">
            <div class="flex items-center justify-between font-bold mb-1">
                <span class="flex items-center gap-2">
                    <i class='bx bx-link-external text-lg text-indigo-600'></i> Tautan Aktivasi Akun Guru Siap Dibagikan
                </span>
                <button onclick="this.parentElement.parentElement.remove()" class="text-indigo-500 hover:text-indigo-700"><i class='bx bx-x text-lg'></i></button>
            </div>
            <p class="text-xs text-indigo-700 mb-2">Bagikan tautan berikut kepada <strong>{{ $guru->nama }}</strong> untuk mengaktivasi akun dan membuat password secara mandiri:</p>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ session('activation_url') }}" id="activation_url_copy"
                    class="w-full text-xs font-mono bg-white border border-indigo-200 rounded-lg px-3 py-2 text-dark-800 select-all">
                <button type="button" onclick="copyActivationUrl()"
                    class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold whitespace-nowrap flex items-center gap-1.5 transition">
                    <i class='bx bx-copy'></i> Salin Link
                </button>
            </div>
        </div>
    @endif

    {{-- Teacher Overview Banner Card --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-white mb-6 shadow-sm">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="relative w-20 h-20 rounded-2xl overflow-hidden bg-primary-100 border-2 border-white shadow-md flex-shrink-0 flex items-center justify-center">
                    @if($guru->photo_path && file_exists(public_path('storage/' . $guru->photo_path)))
                        <img src="{{ asset('storage/' . $guru->photo_path) }}" alt="{{ $guru->nama }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-2xl font-bold text-primary-700">
                            {{ strtoupper(substr($guru->nama, 0, 2)) }}
                        </span>
                    @endif
                    <span class="absolute bottom-1 right-1 w-3.5 h-3.5 rounded-full border-2 border-white {{ $guru->user?->status === 'active' ? 'bg-emerald-500' : ($guru->user?->status === 'pending' ? 'bg-amber-400' : 'bg-rose-500') }}"></span>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <h1 class="text-2xl font-extrabold text-dark-900">{{ $guru->nama }}</h1>
                        @php
                            $statusAkun = $guru->user?->status ?? 'active';
                        @endphp
                        @if($statusAkun === 'active')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Akun Aktif
                            </span>
                        @elseif($statusAkun === 'pending')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu Aktivasi
                            </span>
                        @elseif($statusAkun === 'suspended')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 border border-rose-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Ditangguhkan
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Dinonaktifkan
                            </span>
                        @endif

                        @if($guru->status_kepegawaian)
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                                {{ $guru->status_kepegawaian }}
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-dark-500">
                        <span class="flex items-center gap-1"><i class='bx bx-envelope text-sm text-dark-400'></i> {{ $guru->user?->email ?? '-' }}</span>
                        <span class="flex items-center gap-1"><i class='bx bx-phone text-sm text-dark-400'></i> {{ $guru->telepon ?? '-' }}</span>
                        <span class="flex items-center gap-1"><i class='bx bx-user-pin text-sm text-dark-400'></i> {{ $guru->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>

                    {{-- Multi-assignment badges --}}
                    <div class="flex flex-wrap items-center gap-1.5 mt-2.5">
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-[11px] font-medium flex items-center gap-1">
                            <i class='bx bx-book-open text-xs'></i>
                            <span>{{ $guru->activeSubjectAssignments->count() }} Mapel Diajar</span>
                        </span>

                        @if($guru->activeHomeroomAssignment)
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[11px] font-medium flex items-center gap-1">
                                <i class='bx bx-id-card text-xs'></i>
                                <span>Wali Kelas {{ $guru->activeHomeroomAssignment->kelas->nama_kelas }}</span>
                            </span>
                        @else
                            <span class="px-2 py-0.5 bg-dark-50 text-dark-400 border border-dark-200 rounded-lg text-[11px] font-medium">
                                Bukan Wali Kelas
                            </span>
                        @endif

                        @if($guru->is_kaprog)
                            <span class="px-2 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg text-[11px] font-semibold flex items-center gap-1">
                                <i class='bx bx-award text-xs'></i>
                                <span>KAPROG {{ $guru->kaprog_jurusan->nama_jurusan ?? '' }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick Workload Indicator --}}
            <div class="bg-dark-50/70 border border-dark-100 rounded-2xl p-4 flex items-center gap-4 min-w-[240px]">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl flex-shrink-0 {{ $workload['status'] === 'optimal' ? 'bg-emerald-100 text-emerald-700' : ($workload['status'] === 'underload' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                    <i class='bx bx-time-five'></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Beban Kerja</span>
                        <span class="text-[11px] font-bold {{ $workload['status'] === 'optimal' ? 'text-emerald-700' : ($workload['status'] === 'underload' ? 'text-amber-700' : 'text-rose-700') }}">
                            {{ $workload['status_label'] }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-2xl font-black text-dark-900">{{ $workload['total_jp'] }}</span>
                        <span class="text-xs text-dark-400 font-semibold">JP / minggu</span>
                    </div>
                    <div class="w-full bg-dark-200 rounded-full h-1.5 mt-1.5 overflow-hidden">
                        <div class="h-1.5 rounded-full {{ $workload['status'] === 'optimal' ? 'bg-emerald-500' : ($workload['status'] === 'underload' ? 'bg-amber-500' : 'bg-rose-500') }}"
                            style="width: {{ min(100, ($workload['total_jp'] / 36) * 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Multi-Tab Navigation Hub --}}
    <div x-data="{ activeTab: 'profil' }" class="space-y-6">
        {{-- Tab Bar --}}
        <div class="flex items-center gap-2 border-b border-dark-200/80 overflow-x-auto pb-px">
            <button type="button" @click="activeTab = 'profil'"
                :class="activeTab === 'profil' ? 'border-primary-600 text-primary-600 font-bold bg-primary-50/50' : 'border-transparent text-dark-500 hover:text-dark-800 font-medium'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 flex items-center gap-2 transition whitespace-nowrap">
                <i class='bx bx-user text-base'></i>
                <span>Profil & Biodata</span>
            </button>

            <button type="button" @click="activeTab = 'akun'"
                :class="activeTab === 'akun' ? 'border-primary-600 text-primary-600 font-bold bg-primary-50/50' : 'border-transparent text-dark-500 hover:text-dark-800 font-medium'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 flex items-center gap-2 transition whitespace-nowrap">
                <i class='bx bx-shield-quarter text-base'></i>
                <span>Akun & Keamanan</span>
            </button>

            <button type="button" @click="activeTab = 'mapel'"
                :class="activeTab === 'mapel' ? 'border-primary-600 text-primary-600 font-bold bg-primary-50/50' : 'border-transparent text-dark-500 hover:text-dark-800 font-medium'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 flex items-center gap-2 transition whitespace-nowrap">
                <i class='bx bx-book-bookmark text-base'></i>
                <span>Penugasan Mapel</span>
                <span class="px-1.5 py-0.2 bg-dark-100 text-dark-700 text-[10px] font-semibold rounded-full">{{ $guru->subjectAssignments->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'wali_kelas'"
                :class="activeTab === 'wali_kelas' ? 'border-primary-600 text-primary-600 font-bold bg-primary-50/50' : 'border-transparent text-dark-500 hover:text-dark-800 font-medium'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 flex items-center gap-2 transition whitespace-nowrap">
                <i class='bx bx-id-card text-base'></i>
                <span>Wali Kelas</span>
                @if($guru->activeHomeroomAssignment)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </button>

            <button type="button" @click="activeTab = 'kaprog'"
                :class="activeTab === 'kaprog' ? 'border-primary-600 text-primary-600 font-bold bg-primary-50/50' : 'border-transparent text-dark-500 hover:text-dark-800 font-medium'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 flex items-center gap-2 transition whitespace-nowrap">
                <i class='bx bx-award text-base'></i>
                <span>Jabatan KAPROG</span>
                @if($guru->is_kaprog)
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                @endif
            </button>

            <button type="button" @click="activeTab = 'jadwal'"
                :class="activeTab === 'jadwal' ? 'border-primary-600 text-primary-600 font-bold bg-primary-50/50' : 'border-transparent text-dark-500 hover:text-dark-800 font-medium'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 flex items-center gap-2 transition whitespace-nowrap">
                <i class='bx bx-calendar-event text-base'></i>
                <span>Beban Kerja & Jadwal</span>
                <span class="px-1.5 py-0.2 bg-primary-100 text-primary-700 text-[10px] font-bold rounded-full">{{ $workload['total_jp'] }} JP</span>
            </button>
        </div>

        {{-- TAB 1: PROFIL & BIODATA --}}
        <div x-show="activeTab === 'profil'" class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-white shadow-sm">
            <h3 class="text-base font-bold text-dark-800 mb-1">Biodata Guru</h3>
            <p class="text-xs text-dark-400 mb-6">Informasi personal guru yang tercatat dalam data kepegawaian sekolah.</p>

            <form action="{{ route('guru.update', $guru->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Nama Lengkap & Gelar *</label>
                        <input type="text" name="nama" value="{{ old('nama', $guru->nama) }}" required
                            class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Email Pengguna *</label>
                        <input type="email" name="email" value="{{ old('email', $guru->user?->email) }}" required
                            class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}" placeholder="Boleh dikosongkan jika belum memiliki NIP"
                            class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                        <span class="text-[10px] text-dark-400">Opsional bagi guru honorer/non-PNS.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">NUPTK</label>
                        <input type="text" name="nuptk" value="{{ old('nuptk', $guru->nuptk) }}" placeholder="Nomor Unik Pendidik dan Tenaga Kependidikan"
                            class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" required
                            class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                            <option value="L" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Status Kepegawaian</label>
                        <select name="status_kepegawaian"
                            class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Pilih Status Kepegawaian --</option>
                            @foreach(['PNS', 'PPPK', 'GTT / Honorer', 'Guru Tetap Yayasan', 'Guru Pengganti'] as $st)
                                <option value="{{ $st }}" {{ old('status_kepegawaian', $guru->status_kepegawaian) === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="telepon" value="{{ old('telepon', $guru->telepon) }}" placeholder="Contoh: 081234567890"
                            class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Ganti Foto Profil</label>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2 text-dark-800 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1.5">Alamat Tempat Tinggal</label>
                    <textarea name="alamat" rows="2" placeholder="Alamat lengkap domisili guru..."
                        class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">{{ old('alamat', $guru->alamat) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1.5">Catatan Kepegawaian</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan internal sekolah terkait guru..."
                        class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">{{ old('catatan', $guru->catatan) }}</textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm transition">
                        <i class='bx bx-save text-base'></i>
                        <span>Simpan Perubahan Biodata</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- TAB 2: AKUN & KEAMANAN --}}
        <div x-show="activeTab === 'akun'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Status Akun Control --}}
                <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-white shadow-sm">
                    <h3 class="text-base font-bold text-dark-800 mb-1">Status Akun Login</h3>
                    <p class="text-xs text-dark-400 mb-4">Ubah hak akses login akun guru ke aplikasi.</p>

                    <form action="{{ route('guru.status', $guru->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-2">Pilih Status Akun:</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-dark-100 hover:bg-dark-50 cursor-pointer">
                                    <input type="radio" name="status" value="active" {{ $guru->user?->status === 'active' ? 'checked' : '' }}
                                        class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <p class="text-xs font-bold text-emerald-800">Aktif (Active)</p>
                                        <p class="text-[11px] text-dark-400">Guru dapat login secara normal dan mengakses dashboard.</p>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-xl border border-dark-100 hover:bg-dark-50 cursor-pointer">
                                    <input type="radio" name="status" value="pending" {{ $guru->user?->status === 'pending' ? 'checked' : '' }}
                                        class="text-amber-600 focus:ring-amber-500">
                                    <div>
                                        <p class="text-xs font-bold text-amber-800">Menunggu Aktivasi (Pending)</p>
                                        <p class="text-[11px] text-dark-400">Akun menunggu guru membuka link aktivasi dan membuat password.</p>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-xl border border-dark-100 hover:bg-dark-50 cursor-pointer">
                                    <input type="radio" name="status" value="suspended" {{ $guru->user?->status === 'suspended' ? 'checked' : '' }}
                                        class="text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <p class="text-xs font-bold text-rose-800">Ditangguhkan (Suspended)</p>
                                        <p class="text-[11px] text-dark-400">Guru diblokir sementara dari login ke aplikasi.</p>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-xl border border-dark-100 hover:bg-dark-50 cursor-pointer">
                                    <input type="radio" name="status" value="deactivated" {{ $guru->user?->status === 'deactivated' ? 'checked' : '' }}
                                        class="text-gray-600 focus:ring-gray-500">
                                    <div>
                                        <p class="text-xs font-bold text-gray-800">Dinonaktifkan (Deactivated)</p>
                                        <p class="text-[11px] text-dark-400">Akun dinonaktifkan secara permanen (misal: mutasi / pensiun).</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full py-2.5 bg-dark-800 hover:bg-dark-900 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition">
                                <i class='bx bx-check-shield text-base'></i>
                                <span>Perbarui Status Akun</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Password & Activation Link Section --}}
                <div class="space-y-6">
                    {{-- Reset Password Manual --}}
                    <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-white shadow-sm">
                        <h3 class="text-base font-bold text-dark-800 mb-1">Setel Password Langsung</h3>
                        <p class="text-xs text-dark-400 mb-4">Ubah password guru secara langsung oleh administrator.</p>

                        <form action="{{ route('guru.update', $guru->id) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="nama" value="{{ $guru->nama }}">
                            <input type="hidden" name="email" value="{{ $guru->user?->email }}">
                            <input type="hidden" name="jenis_kelamin" value="{{ $guru->jenis_kelamin }}">

                            <div>
                                <label class="block text-xs font-semibold text-dark-700 mb-1.5">Password Baru (Minimal 8 Karakter)</label>
                                <input type="password" name="password" minlength="8" required placeholder="Masukkan kata sandi baru..."
                                    class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition">
                                <i class='bx bx-key text-base'></i>
                                <span>Simpan Password Baru</span>
                            </button>
                        </form>
                    </div>

                    {{-- Generate Activation Link Invitation --}}
                    <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-white shadow-sm">
                        <h3 class="text-base font-bold text-dark-800 mb-1">Undangan Aktivasi Mandiri</h3>
                        <p class="text-xs text-dark-400 mb-4">Buat tautan undangan aman agar guru dapat mengatur kata sandi secara mandiri (berlaku 72 jam).</p>

                        <form action="{{ route('guru.aktivasi.regenerate', $guru->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition">
                                <i class='bx bx-link-external text-base'></i>
                                <span>Buat Tautan Aktivasi Baru</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: PENUGASAN MATA PELAJARAN --}}
        <div x-show="activeTab === 'mapel'" class="space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-dark-800">Penugasan Mata Pelajaran (Subject Assignments)</h3>
                    <p class="text-xs text-dark-400">Daftar mata pelajaran dan rombongan belajar yang ditugaskan kepada {{ $guru->nama }}.</p>
                </div>
                <button type="button" onclick="openModal('modal-add-subject')"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                    <i class='bx bx-plus text-base'></i>
                    <span>Tugaskan Mapel Baru</span>
                </button>
            </div>

            <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-dark-50/60 border-b border-dark-100 text-[11px] font-bold text-dark-500 uppercase tracking-wider">
                                <th class="px-5 py-3.5">Mata Pelajaran</th>
                                <th class="px-5 py-3.5">Rombel / Kelas</th>
                                <th class="px-5 py-3.5">Tahun Ajaran</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-100/60">
                            @forelse($guru->subjectAssignments as $sa)
                                <tr class="hover:bg-dark-50/30 transition">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                                <i class='bx bx-book'></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-dark-800">{{ $sa->mataPelajaran->nama_mapel ?? '-' }}</p>
                                                <p class="text-[10px] text-dark-400">{{ $sa->mataPelajaran->kode_mapel ?? '' }} &bull; {{ $sa->mataPelajaran->kelompok ?? 'Umum' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="font-bold text-dark-800">{{ $sa->kelas->nama_kelas ?? '-' }}</span>
                                        <p class="text-[10px] text-dark-400">{{ $sa->kelas->jurusan->nama_jurusan ?? '' }}</p>
                                    </td>
                                    <td class="px-5 py-4 font-mono font-medium text-dark-600">
                                        {{ $sa->tahun_ajaran }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sa->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-dark-100 text-dark-500' }}">
                                            {{ $sa->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <form action="{{ route('guru.removeSubject', $sa->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin melepaskan penugasan mapel ini dari guru?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 text-xs text-rose-600 hover:text-rose-800 font-semibold hover:bg-rose-50 rounded-lg transition">
                                                <i class='bx bx-trash'></i> Lepas
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-dark-400">
                                        <i class='bx bx-book-open text-3xl mb-2 text-dark-300'></i>
                                        <p>Belum ada penugasan mata pelajaran yang tercatat untuk guru ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 4: PENUGASAN WALI KELAS --}}
        <div x-show="activeTab === 'wali_kelas'" class="space-y-6">
            {{-- Active Homeroom Status Banner --}}
            @if($guru->activeHomeroomAssignment)
                <div class="glass-card rounded-2xl border border-emerald-200 bg-emerald-50/50 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-2xl flex-shrink-0 shadow-md shadow-emerald-500/20">
                            <i class='bx bx-id-card'></i>
                        </div>
                        <div>
                            <span class="px-2 py-0.5 bg-emerald-200 text-emerald-900 rounded-md text-[10px] font-bold uppercase tracking-wider">
                                Wali Kelas Aktif Saat Ini
                            </span>
                            <h3 class="text-xl font-black text-emerald-950 mt-1">
                                {{ $guru->activeHomeroomAssignment->kelas->nama_kelas }}
                            </h3>
                            <p class="text-xs text-emerald-700">
                                Jurusan {{ $guru->activeHomeroomAssignment->kelas->jurusan->nama_jurusan ?? '-' }} &bull; Tahun Ajaran {{ $guru->activeHomeroomAssignment->tahun_ajaran }}
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('guru.removeHomeroom', $guru->activeHomeroomAssignment->id) }}" method="POST"
                        onsubmit="return confirm('Lepaskan tugas wali kelas dari rombel ini? Riwayat penugasan akan tetap tersimpan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition shadow-sm">
                            <i class='bx bx-x-circle'></i> Lepaskan Tugas Wali Kelas
                        </button>
                    </form>
                </div>
            @else
                <div class="glass-card rounded-2xl border border-dark-200/50 bg-dark-50/50 p-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-dark-200 text-dark-500 flex items-center justify-center text-xl flex-shrink-0">
                        <i class='bx bx-id-card'></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-dark-800">Belum Ditugaskan Sebagai Wali Kelas</h4>
                        <p class="text-xs text-dark-400">Guru ini saat ini tidak memegang tanggung jawab rombongan belajar binaan.</p>
                    </div>
                </div>
            @endif

            {{-- Form Penugasan / Penggantian Wali Kelas --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-white shadow-sm">
                <h3 class="text-base font-bold text-dark-800 mb-1">Tugaskan Sebagai Wali Kelas</h3>
                <p class="text-xs text-dark-400 mb-4">Menetapkan rombel binaan baru akan otomatis menonaktifkan penugasan wali kelas sebelumnya dengan tetap menjaga rekam jejak histori.</p>

                <form action="{{ route('guru.assignHomeroom', $guru->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1.5">Pilih Rombongan Belajar (Kelas) *</label>
                            <select name="kelas_id" required
                                class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Pilih Rombel --</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama_kelas }} ({{ $k->jurusan->singkatan ?? $k->jurusan->nama_jurusan }})
                                        @if($k->wali_kelas) - Saat ini: {{ $k->wali_kelas }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1.5">Tahun Ajaran *</label>
                            <input type="text" name="tahun_ajaran" value="{{ $tahunAjaran }}" required
                                class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-dark-700 mb-1.5">Catatan SK Penugasan / Keterangan</label>
                        <input type="text" name="catatan" placeholder="Contoh: SK Kepala Sekolah No. 421/2026 tanggal 15 Juli 2026"
                            class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm transition">
                            <i class='bx bx-check text-base'></i>
                            <span>Simpan Penugasan Wali Kelas</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Histori Penugasan Wali Kelas --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-white shadow-sm">
                <h4 class="text-sm font-bold text-dark-800 mb-3">Histori Penugasan Wali Kelas</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-dark-50/60 border-b border-dark-100 text-[11px] font-bold text-dark-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Rombel</th>
                                <th class="px-4 py-3">Tahun Ajaran</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Catatan</th>
                                <th class="px-4 py-3">Tanggal Ditugaskan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-100/60">
                            @forelse($guru->homeroomAssignments as $ha)
                                <tr class="hover:bg-dark-50/30 transition">
                                    <td class="px-4 py-3 font-bold text-dark-800">{{ $ha->kelas->nama_kelas ?? '-' }}</td>
                                    <td class="px-4 py-3 font-mono text-dark-600">{{ $ha->tahun_ajaran }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $ha->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-dark-100 text-dark-500' }}">
                                            {{ $ha->is_active ? 'Aktif' : 'Selesai' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-dark-500">{{ $ha->catatan ?? '-' }}</td>
                                    <td class="px-4 py-3 text-dark-400 font-mono">{{ $ha->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-dark-400">
                                        Belum ada riwayat penugasan wali kelas sebelumnya.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 5: JABATAN & KAPROG --}}
        <div x-show="activeTab === 'kaprog'" class="space-y-6">
            @if($guru->is_kaprog)
                <div class="glass-card rounded-2xl border border-purple-200 bg-purple-50/50 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-2xl flex-shrink-0 shadow-md shadow-purple-500/20">
                            <i class='bx bx-award'></i>
                        </div>
                        <div>
                            <span class="px-2 py-0.5 bg-purple-200 text-purple-900 rounded-md text-[10px] font-bold uppercase tracking-wider">
                                Kepala Program Keahlian (KAPROG) Aktif
                            </span>
                            <h3 class="text-xl font-black text-purple-950 mt-1">
                                {{ $guru->kaprog_jurusan->nama_jurusan ?? '-' }}
                            </h3>
                            <p class="text-xs text-purple-700">
                                Memiliki wewenang manajerial akademik jurusan, data siswa kejuruan, dan alokasi/rolling ruangan kejuruan.
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('guru.removeKaprog', $guru->id) }}" method="POST"
                        onsubmit="return confirm('Lepaskan jabatan Kepala Program Keahlian (KAPROG) dari guru ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition shadow-sm">
                            <i class='bx bx-x-circle'></i> Lepaskan Jabatan KAPROG
                        </button>
                    </form>
                </div>
            @else
                <div class="glass-card rounded-2xl border border-dark-200/50 bg-dark-50/50 p-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-dark-200 text-dark-500 flex items-center justify-center text-xl flex-shrink-0">
                        <i class='bx bx-award'></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-dark-800">Bukan Kepala Program Keahlian</h4>
                        <p class="text-xs text-dark-400">Guru ini saat ini belum ditugaskan sebagai KAPROG untuk jurusan manapun.</p>
                    </div>
                </div>

                {{-- Form Tugaskan KAPROG --}}
                <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-white shadow-sm">
                    <h3 class="text-base font-bold text-dark-800 mb-1">Tugaskan Sebagai Kepala Program Keahlian (KAPROG)</h3>
                    <p class="text-xs text-dark-400 mb-4">Menjadikan guru ini sebagai pimpinan program keahlian. Akun guru akan tetap satu, namun memperoleh hak akses menu KAPROG secara dinamis.</p>

                    <form action="{{ route('guru.assignKaprog', $guru->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-dark-700 mb-1.5">Pilih Program Keahlian / Jurusan *</label>
                            <select name="jurusan_id" required
                                class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($jurusans as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm transition">
                                <i class='bx bx-check text-base'></i>
                                <span>Tetapkan Sebagai KAPROG</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        {{-- TAB 6: BEBAN KERJA & JADWAL MENGAJAR --}}
        <div x-show="activeTab === 'jadwal'" class="space-y-6">
            {{-- Metrics Cards Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white">
                    <div class="flex items-center justify-between text-dark-400 text-xs mb-1 font-semibold">
                        <span>Total Jam Pelajaran</span>
                        <i class='bx bx-time text-base text-primary-600'></i>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black text-dark-900">{{ $workload['total_jp'] }}</span>
                        <span class="text-xs text-dark-500 font-bold">JP / mgg</span>
                    </div>
                    <p class="text-[10px] text-dark-400 mt-1">Standar: 1 JP = 45 Menit</p>
                </div>

                <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white">
                    <div class="flex items-center justify-between text-dark-400 text-xs mb-1 font-semibold">
                        <span>Jam Kerja Riil</span>
                        <i class='bx bx-stopwatch text-base text-indigo-600'></i>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black text-dark-900">{{ $workload['clock_hours'] }}</span>
                        <span class="text-xs text-dark-500 font-bold">Jam / mgg</span>
                    </div>
                    <p class="text-[10px] text-dark-400 mt-1">Total durasi: {{ $workload['total_minutes'] }} menit</p>
                </div>

                <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white">
                    <div class="flex items-center justify-between text-dark-400 text-xs mb-1 font-semibold">
                        <span>Total Sesi & Rombel</span>
                        <i class='bx bx-chalkboard text-base text-emerald-600'></i>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-dark-900">{{ $workload['total_sessions'] }}</span>
                        <span class="text-xs text-dark-500 font-bold">Sesi &bull; {{ $workload['rombels_taught'] }} Rombel</span>
                    </div>
                    <p class="text-[10px] text-dark-400 mt-1">{{ $workload['subjects_taught'] }} Mata Pelajaran Berbeda</p>
                </div>

                <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white">
                    <div class="flex items-center justify-between text-dark-400 text-xs mb-1 font-semibold">
                        <span>Status Beban Guru</span>
                        <i class='bx bx-check-shield text-base text-amber-600'></i>
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $workload['status'] === 'optimal' ? 'bg-emerald-100 text-emerald-800' : ($workload['status'] === 'underload' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                            {{ $workload['status_label'] }}
                        </span>
                    </div>
                    <p class="text-[10px] text-dark-400 mt-1">Target Ideal: 24 - 36 JP / minggu</p>
                </div>
            </div>

            {{-- Weekly Teaching Schedule Table --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
                <div class="p-5 border-b border-dark-100/60 flex items-center justify-between bg-dark-50/40">
                    <div>
                        <h4 class="text-sm font-bold text-dark-800">Jadwal Mengajar Mingguan</h4>
                        <p class="text-xs text-dark-400">Ruangan fisik disinkronkan secara dinamis berdasarkan alokasi rolling ruangan aktif.</p>
                    </div>
                    <a href="{{ route('jadwal') }}"
                        class="px-3 py-1.5 bg-white border border-dark-200 text-dark-700 hover:bg-dark-50 rounded-xl text-xs font-semibold transition">
                        Kelola di Jadwal Utama
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-dark-50/60 border-b border-dark-100 text-[11px] font-bold text-dark-500 uppercase tracking-wider">
                                <th class="px-5 py-3.5">Hari</th>
                                <th class="px-5 py-3.5">Waktu</th>
                                <th class="px-5 py-3.5">Mata Pelajaran</th>
                                <th class="px-5 py-3.5">Rombel (Kelas)</th>
                                <th class="px-5 py-3.5">Ruangan Fisik Terkini</th>
                                <th class="px-5 py-3.5">Durasi (JP)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-100/60">
                            @forelse($liveSchedules as $js)
                                <tr class="hover:bg-dark-50/30 transition">
                                    <td class="px-5 py-3.5 font-bold text-dark-800">
                                        <span class="px-2.5 py-1 bg-dark-100 rounded-lg text-dark-700 text-xs">
                                            {{ $js->hari }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-dark-600 font-semibold">
                                        {{ \Carbon\Carbon::parse($js->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($js->jam_selesai)->format('H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 font-semibold text-dark-800">
                                        {{ $js->mataPelajaran->nama_mapel ?? $js->mata_pelajaran }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="font-bold text-primary-700">{{ $js->kelas->nama_kelas ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($js->live_room)
                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[11px] font-bold flex items-center gap-1.5 w-max">
                                                <i class='bx bx-building-house'></i>
                                                {{ $js->live_room->name }} ({{ $js->live_room->code }})
                                            </span>
                                        @else
                                            <span class="text-dark-400 italic text-[11px]">Belum Dialokasikan</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 font-mono font-bold text-dark-700">
                                        @php
                                            $m = \Carbon\Carbon::parse($js->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($js->jam_selesai));
                                            $jp = round($m / 45, 1);
                                        @endphp
                                        {{ $jp }} JP
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-dark-400">
                                        Belum ada jadwal mengajar yang diatur untuk guru ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Penugasan Mapel --}}
    <div id="modal-add-subject" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/50 backdrop-blur-sm hidden">
        <div class="glass-card rounded-2xl border border-dark-200/50 bg-white w-full max-w-lg p-6 shadow-xl">
            <div class="flex items-center justify-between border-b border-dark-100 pb-4 mb-4">
                <h3 class="text-base font-bold text-dark-800">Tugaskan Mata Pelajaran Baru</h3>
                <button type="button" onclick="closeModal('modal-add-subject')" class="text-dark-400 hover:text-dark-600">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <form action="{{ route('guru.assignSubject', $guru->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1.5">Mata Pelajaran *</label>
                    <select name="mata_pelajaran_id" required
                        class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mataPelajarans as $mp)
                            <option value="{{ $mp->id }}">{{ $mp->nama_mapel }} ({{ $mp->kode_mapel }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1.5">Rombongan Belajar (Kelas) *</label>
                    <select name="kelas_id" required
                        class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Pilih Rombel --</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }} ({{ $k->jurusan->singkatan ?? $k->jurusan->nama_jurusan }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1.5">Tahun Ajaran *</label>
                    <input type="text" name="tahun_ajaran" value="{{ $tahunAjaran }}" required
                        class="w-full text-xs rounded-xl border-dark-200 bg-white px-3 py-2.5 text-dark-800 focus:border-primary-500 focus:ring-primary-500">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-dark-100">
                    <button type="button" onclick="closeModal('modal-add-subject')"
                        class="px-4 py-2 border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 rounded-xl text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold transition">
                        Simpan Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function copyActivationUrl() {
        const copyInput = document.getElementById('activation_url_copy');
        if (copyInput) {
            copyInput.select();
            navigator.clipboard.writeText(copyInput.value);
            alert('Tautan aktivasi berhasil disalin ke clipboard!');
        }
    }
</script>
@endpush
