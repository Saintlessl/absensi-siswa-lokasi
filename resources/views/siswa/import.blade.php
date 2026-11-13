@extends('layouts.app')

@section('title', 'Import Data Siswa — MySkanic')
@section('page-title', 'Import Data Siswa & Riwayat Akademik')
@section('page-subtitle', 'Modul Import Excel Resmi MySkanic.')

@section('content')
<div class="space-y-6" x-data="studentImportWizard()">
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#003EA7] via-[#002D7A] to-[#001D4D] p-6 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-blue-200 text-xs font-semibold mb-2">
                    <i class='bx bx-file-blank'></i>
                    <span>Modul Import Excel Resmi MySkanic</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-normal tracking-tight">Import Data Siswa & Riwayat Akademik</h1>
                <p class="text-blue-100/80 text-sm mt-1 max-w-2xl">
                    Unggah data siswa aktif, siswa lama, pindahan, maupun alumni secara terverifikasi. Sistem otomatis memvalidasi relasi rombel, jurusan, angkatan, dan tahun ajaran tanpa mengarang histori.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('siswa.import.template') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-[#003EA7] font-semibold text-sm shadow hover:bg-blue-50 transition-all">
                    <i class='bx bx-download text-lg'></i>
                    <span>Unduh Template Excel</span>
                </a>
                <a href="{{ route('siswa') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-sm backdrop-blur transition-all">
                    <i class='bx bx-arrow-back text-lg'></i>
                    <span>Kembali ke Data Siswa</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Wizard Step Indicator --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
        <div class="grid grid-cols-6 gap-2">
            <template x-for="(stepName, idx) in steps" :key="idx">
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl transition-all"
                    :class="currentStep === (idx + 1) ? 'bg-blue-50 border border-blue-200' : (currentStep > (idx + 1) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-400')">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                        :class="currentStep === (idx + 1) ? 'bg-[#003EA7] text-white' : (currentStep > (idx + 1) ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500')"
                        x-text="currentStep > (idx + 1) ? '✓' : (idx + 1)"></div>
                    <span class="text-xs font-semibold truncate" :class="currentStep === (idx + 1) ? 'text-[#003EA7]' : ''" x-text="stepName"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Step 1: Upload File --}}
    <div x-show="currentStep === 1" x-cloak class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-6">
        <div class="max-w-2xl mx-auto text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-[#003EA7] flex items-center justify-center mx-auto text-3xl">
                <i class='bx bx-cloud-upload'></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">Langkah 1: Unggah Berkas Excel Siswa</h3>
                <p class="text-sm text-slate-500 mt-1">Gunakan template resmi MySkanic format .xlsx atau .xls dengan batas ukuran maksimal 10MB.</p>
            </div>

            {{-- Import Type Selection --}}
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 text-left">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tipe Kategori Import</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all"
                        :class="importType === 'campuran' ? 'bg-blue-50/80 border-[#003EA7] text-[#003EA7] font-semibold' : 'bg-white border-slate-200 text-slate-600'">
                        <input type="radio" value="campuran" x-model="importType" class="hidden">
                        <i class='bx bx-shuffle text-base'></i>
                        <span class="text-xs">Campuran (Auto)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all"
                        :class="importType === 'aktif' ? 'bg-blue-50/80 border-[#003EA7] text-[#003EA7] font-semibold' : 'bg-white border-slate-200 text-slate-600'">
                        <input type="radio" value="aktif" x-model="importType" class="hidden">
                        <i class='bx bx-user-check text-base'></i>
                        <span class="text-xs">Siswa Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all"
                        :class="importType === 'alumni' ? 'bg-blue-50/80 border-[#003EA7] text-[#003EA7] font-semibold' : 'bg-white border-slate-200 text-slate-600'">
                        <input type="radio" value="alumni" x-model="importType" class="hidden">
                        <i class='bx bxs-graduation text-base'></i>
                        <span class="text-xs">Alumni / Lulus</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all"
                        :class="importType === 'historis' ? 'bg-blue-50/80 border-[#003EA7] text-[#003EA7] font-semibold' : 'bg-white border-slate-200 text-slate-600'">
                        <input type="radio" value="historis" x-model="importType" class="hidden">
                        <i class='bx bx-history text-base'></i>
                        <span class="text-xs">Siswa Lama/Pindah</span>
                    </label>
                </div>
            </div>

            {{-- Dropzone Area --}}
            <div class="border-2 border-dashed border-slate-300 rounded-2xl p-8 hover:border-[#003EA7] transition-all bg-slate-50/50 cursor-pointer"
                @dragover.prevent=""
                @drop.prevent="handleFileDrop($event)"
                @click="$refs.fileInput.click()">
                <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" accept=".xlsx,.xls" class="hidden">
                <i class='bx bxs-file-doc text-5xl text-[#003EA7]/70 mb-3 block'></i>
                <span class="text-sm font-semibold text-slate-700 block" x-text="selectedFile ? selectedFile.name : 'Klik untuk memilih file atau seret file Excel ke sini'"></span>
                <span class="text-xs text-slate-400 mt-1 block" x-text="selectedFile ? (selectedFile.size / 1024).toFixed(1) + ' KB' : 'Mendukung XLSX dan XLS (Maks. 10MB)'"></span>
            </div>

            <div class="flex justify-center gap-3 pt-2">
                <button type="button" @click="uploadAndValidate()" :disabled="!selectedFile || isUploading"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003EA7] hover:bg-[#002D7A] text-white font-semibold text-sm shadow disabled:opacity-50 transition-all">
                    <i class='bx' :class="isUploading ? 'bx-loader-alt bx-spin' : 'bx-right-arrow-alt'"></i>
                    <span x-text="isUploading ? 'Memvalidasi Berkas...' : 'Lanjutkan ke Validasi & Mapping'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Step 2: Mapping Kolom (Overview) --}}
    <div x-show="currentStep === 2" x-cloak class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-6">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Langkah 2: Konfirmasi Pemetaan Kolom Excel</h3>
            <p class="text-sm text-slate-500 mt-1">Sistem MySkanic otomatis mencocokkan kolom template standar dengan struktur database.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                <span class="text-xs text-slate-400 font-semibold block uppercase">Identitas Siswa</span>
                <span class="text-sm font-bold text-slate-700 block mt-1">NISN, NIS, Nama, Gender, TTL</span>
                <span class="text-xs text-emerald-600 mt-1 block flex items-center gap-1"><i class='bx bx-check'></i> Terpetakan otomatis</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                <span class="text-xs text-slate-400 font-semibold block uppercase">Tahun Masuk & Kohort</span>
                <span class="text-sm font-bold text-slate-700 block mt-1">T.A. Masuk, Jurusan, No. Angkatan</span>
                <span class="text-xs text-emerald-600 mt-1 block flex items-center gap-1"><i class='bx bx-check'></i> Terpetakan otomatis</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                <span class="text-xs text-slate-400 font-semibold block uppercase">Enrollment & Rombel</span>
                <span class="text-sm font-bold text-slate-700 block mt-1">Tingkat, Nama Rombel, T.A. Enrollment</span>
                <span class="text-xs text-emerald-600 mt-1 block flex items-center gap-1"><i class='bx bx-check'></i> Terpetakan otomatis</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                <span class="text-xs text-slate-400 font-semibold block uppercase">Status & Jalur</span>
                <span class="text-sm font-bold text-slate-700 block mt-1">Status (Aktif/Lulus/Pindah), Jalur, Asal</span>
                <span class="text-xs text-emerald-600 mt-1 block flex items-center gap-1"><i class='bx bx-check'></i> Terpetakan otomatis</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                <span class="text-xs text-slate-400 font-semibold block uppercase">Akun & Kredensial</span>
                <span class="text-sm font-bold text-slate-700 block mt-1">Email Siswa (Password ter-hash otomatis)</span>
                <span class="text-xs text-emerald-600 mt-1 block flex items-center gap-1"><i class='bx bx-check'></i> Keamanan Terjamin</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                <span class="text-xs text-slate-400 font-semibold block uppercase">Data Orang Tua</span>
                <span class="text-sm font-bold text-slate-700 block mt-1">Nama Orang Tua, Telepon, Alamat</span>
                <span class="text-xs text-emerald-600 mt-1 block flex items-center gap-1"><i class='bx bx-check'></i> Terpetakan otomatis</span>
            </div>
        </div>

        <div class="flex justify-between items-center pt-4 border-t border-slate-100">
            <button type="button" @click="currentStep = 1" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-all">
                Kembali ke Berkas
            </button>
            <button type="button" @click="currentStep = 3" class="px-6 py-2.5 rounded-xl bg-[#003EA7] hover:bg-[#002D7A] text-white font-semibold text-sm shadow transition-all">
                Lanjutkan ke Preview Validasi
            </button>
        </div>
    </div>

    {{-- Step 3: Preview & Validasi --}}
    <div x-show="currentStep === 3" x-cloak class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Langkah 3: Preview & Validasi Integritas Data</h3>
                <p class="text-sm text-slate-500 mt-1">Pemeriksaan duplikasi NIS/NISN, kesesuaian jurusan rombel, dan ketersediaan tahun ajaran.</p>
            </div>
            {{-- Metrics Badges --}}
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold flex items-center gap-2">
                    <span>Total Baris:</span>
                    <span class="text-sm font-black" x-text="validationResult.total_rows || 0"></span>
                </div>
                <div class="px-4 py-2 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center gap-2">
                    <i class='bx bx-check-circle text-base'></i>
                    <span>Siap Diimpor:</span>
                    <span class="text-sm font-black" x-text="validationResult.valid_count || 0"></span>
                </div>
                <div class="px-4 py-2 rounded-xl bg-rose-100 text-rose-800 text-xs font-bold flex items-center gap-2">
                    <i class='bx bx-error-circle text-base'></i>
                    <span>Bermasalah:</span>
                    <span class="text-sm font-black" x-text="validationResult.error_count || 0"></span>
                </div>
            </div>
        </div>

        {{-- Error Details Accordion (If Any) --}}
        <template x-if="validationResult.errors && validationResult.errors.length > 0">
            <div class="p-4 rounded-xl bg-rose-50/70 border border-rose-200 space-y-3">
                <div class="flex items-center gap-2 text-rose-800 font-bold text-sm">
                    <i class='bx bx-error text-lg'></i>
                    <span>Ditemukan <span x-text="validationResult.errors.length"></span> Baris Bermasalah (Akan Dilewati):</span>
                </div>
                <div class="max-h-48 overflow-y-auto space-y-2 pr-2">
                    <template x-for="err in validationResult.errors" :key="err.row">
                        <div class="text-xs bg-white p-2.5 rounded-lg border border-rose-200 flex items-start gap-2 text-slate-700">
                            <span class="font-bold text-rose-600 shrink-0">Baris <span x-text="err.row"></span> (NIS: <span x-text="err.nis || '-'"></span>):</span>
                            <span class="text-slate-600" x-text="err.messages.join('; ')"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- Valid Preview Table --}}
        <div>
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampel Data Valid yang Akan Diproses (Menampilkan maks. 50 data):</h4>
            <div class="border border-slate-100 rounded-2xl overflow-x-auto max-h-96">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-white border-b border-slate-100 sticky top-0">
                        <tr>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">No</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NISN / NIS</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">L/P</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">T.A. Masuk</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tingkat & Rombel</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Siswa</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">T.A. Enrollment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <template x-for="(row, idx) in validationResult.valid_rows" :key="idx">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 text-slate-400 font-mono text-center text-xs" x-text="idx + 1"></td>
                                <td class="py-4 px-4 font-mono font-medium text-slate-700 text-xs">
                                    <span class="font-bold text-slate-800" x-text="row.nis"></span>
                                    <span x-show="row.nisn" class="text-slate-400 block text-[10px]" x-text="'NISN: ' + row.nisn"></span>
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-800" x-text="row.nama"></td>
                                <td class="py-4 px-4 text-center font-semibold text-xs" x-text="row.jenis_kelamin"></td>
                                <td class="py-4 px-4 font-medium text-[#003FA9]" x-text="row.tahun_ajaran_masuk"></td>
                                <td class="py-4 px-4 font-medium text-slate-700">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-slate-100 font-semibold text-xs text-slate-700" x-text="'Kelas ' + row.tingkat"></span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                        :class="row.status === 'aktif' ? 'bg-emerald-600 text-white' : 'bg-purple-600 text-white'"
                                        x-text="row.status.toUpperCase()"></span>
                                </td>
                                <td class="py-4 px-4 text-slate-600 font-medium text-xs" x-text="row.tahun_ajaran_enr"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-between items-center pt-4 border-t border-slate-100">
            <button type="button" @click="currentStep = 2" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-all">
                Kembali ke Mapping
            </button>
            <button type="button" @click="currentStep = 4" :disabled="!validationResult.valid_count"
                class="px-6 py-2.5 rounded-xl bg-[#003EA7] hover:bg-[#002D7A] text-white font-semibold text-sm shadow disabled:opacity-50 transition-all">
                Lanjutkan ke Konfirmasi Eksekusi
            </button>
        </div>
    </div>

    {{-- Step 4: Konfirmasi --}}
    <div x-show="currentStep === 4" x-cloak class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-6">
        <div class="max-w-xl mx-auto text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto text-3xl">
                <i class='bx bx-check-shield'></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">Langkah 4: Konfirmasi Eksekusi Import</h3>
                <p class="text-sm text-slate-500 mt-1">Periksa kembali ringkasan import sebelum data disimpan secara permanen ke database.</p>
            </div>

            <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 text-left space-y-3">
                <div class="flex justify-between text-sm py-1 border-b border-slate-200">
                    <span class="text-slate-500">Nama Berkas:</span>
                    <span class="font-bold text-slate-800 font-mono" x-text="validationResult.file_name"></span>
                </div>
                <div class="flex justify-between text-sm py-1 border-b border-slate-200">
                    <span class="text-slate-500">Kategori Import:</span>
                    <span class="font-bold text-[#003EA7] uppercase" x-text="importType"></span>
                </div>
                <div class="flex justify-between text-sm py-1 border-b border-slate-200">
                    <span class="text-slate-500">Jumlah Siswa Siap Diimpor:</span>
                    <span class="font-bold text-emerald-600 text-base" x-text="validationResult.valid_count + ' Siswa'"></span>
                </div>
                <div class="flex justify-between text-sm py-1">
                    <span class="text-slate-500">Jumlah Baris Dilewati (Bermasalah):</span>
                    <span class="font-bold text-rose-600" x-text="validationResult.error_count + ' Baris'"></span>
                </div>
            </div>

            <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl text-left text-xs text-blue-800 flex items-start gap-2">
                <i class='bx bx-info-circle text-base text-[#003EA7] shrink-0 mt-0.5'></i>
                <span>Sistem menggunakan <strong>Database Transaction</strong> atomik. Setiap siswa akan otomatis dibuatkan akun dengan password terenkripsi aman dan di-enroll sesuai snapshot wali kelas rombel terkait.</span>
            </div>

            <div class="flex justify-center gap-3 pt-3">
                <button type="button" @click="currentStep = 3" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-all">
                    Batal / Tinjau Kembali
                </button>
                <button type="button" @click="executeImport()" :disabled="isExecuting"
                    class="inline-flex items-center gap-2 px-7 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-all">
                    <i class='bx' :class="isExecuting ? 'bx-loader-alt bx-spin' : 'bx-check-double'"></i>
                    <span x-text="isExecuting ? 'Sedang Memproses Import...' : 'Konfirmasi & Eksekusi Sekarang'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Step 5 & 6: Hasil Import --}}
    <div x-show="currentStep === 6" x-cloak class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 text-center space-y-6">
        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-4xl shadow-inner">
            <i class='bx bx-check-circle'></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-slate-800">Proses Import Berhasil Diselesaikan!</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">Data siswa, akun pengguna, snapshot wali kelas, dan enrollment akademik telah tercatat ke dalam sistem.</p>
        </div>

        <div class="grid grid-cols-3 gap-4 max-w-lg mx-auto">
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                <span class="text-xs text-emerald-600 font-bold block">BERHASIL</span>
                <span class="text-2xl font-black text-emerald-700 mt-1 block" x-text="executionResult.successful_rows || 0"></span>
            </div>
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-center">
                <span class="text-xs text-rose-600 font-bold block">GAGAL / DILEWATI</span>
                <span class="text-2xl font-black text-rose-700 mt-1 block" x-text="executionResult.failed_rows || 0"></span>
            </div>
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-center">
                <span class="text-xs text-[#003EA7] font-bold block">TOTAL DIPROSES</span>
                <span class="text-2xl font-black text-[#003EA7] mt-1 block" x-text="executionResult.total_rows || 0"></span>
            </div>
        </div>

        <div class="flex justify-center gap-3 pt-4">
            <a :href="executionResult.report_url" x-show="executionResult.report_url" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition-all">
                <i class='bx bx-download text-base'></i>
                <span>Unduh Laporan Hasil Import (.xlsx)</span>
            </a>
            <a href="{{ route('siswa') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003EA7] hover:bg-[#002D7A] text-white text-sm font-bold shadow transition-all">
                <i class='bx bx-group text-base'></i>
                <span>Lihat Data Siswa Sekarang</span>
            </a>
        </div>
    </div>

    {{-- Audit Log Riwayat Import Terakhir --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class='bx bx-history text-xl text-[#003EA7]'></i>
                <h3 class="font-bold text-slate-800 text-base">Riwayat Batch Import Terakhir</h3>
            </div>
            <span class="text-xs text-slate-400">10 batch terakhir tercatat</span>
        </div>

        <div class="border border-slate-200 rounded-xl overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-2.5">ID Batch</th>
                        <th class="px-3 py-2.5">Nama Berkas</th>
                        <th class="px-3 py-2.5">Tipe</th>
                        <th class="px-3 py-2.5">Hasil</th>
                        <th class="px-3 py-2.5">Status</th>
                        <th class="px-3 py-2.5">Petugas</th>
                        <th class="px-3 py-2.5">Waktu</th>
                        <th class="px-3 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentBatches as $b)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-3 py-2.5 font-mono font-bold text-slate-700">#{{ $b->id }}</td>
                            <td class="px-3 py-2.5 font-medium text-slate-800">{{ $b->file_name }}</td>
                            <td class="px-3 py-2.5 uppercase font-bold text-blue-700">{{ $b->import_type }}</td>
                            <td class="px-3 py-2.5">
                                <span class="text-emerald-700 font-bold">{{ $b->successful_rows }} sukses</span>
                                @if($b->failed_rows > 0)
                                    <span class="text-rose-600 font-bold ml-1">/ {{ $b->failed_rows }} gagal</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $b->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ strtoupper($b->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-slate-600">{{ $b->user?->name ?? 'System' }}</td>
                            <td class="px-3 py-2.5 text-slate-400 font-mono">{{ $b->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <a href="{{ route('siswa.import.report', $b->id) }}" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-semibold">
                                    <i class='bx bx-download'></i> Laporan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-slate-400">Belum ada riwayat batch import yang tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function studentImportWizard() {
    return {
        currentStep: 1,
        steps: ['Upload Berkas', 'Mapping Kolom', 'Validasi Data', 'Konfirmasi', 'Proses', 'Hasil Selesai'],
        importType: 'campuran',
        selectedFile: null,
        isUploading: false,
        isExecuting: false,
        importToken: null,
        validationResult: {
            total_rows: 0,
            valid_count: 0,
            error_count: 0,
            valid_rows: [],
            errors: [],
            file_name: '',
        },
        executionResult: {},

        handleFileSelect(e) {
            if (e.target.files.length) {
                this.selectedFile = e.target.files[0];
            }
        },

        handleFileDrop(e) {
            if (e.dataTransfer.files.length) {
                this.selectedFile = e.dataTransfer.files[0];
            }
        },

        uploadAndValidate() {
            if (!this.selectedFile) return;

            this.isUploading = true;
            const formData = new FormData();
            formData.append('excel_file', this.selectedFile);
            formData.append('import_type', this.importType);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("siswa.import.preview") }}', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.isUploading = false;
                if (data.success) {
                    this.importToken = data.import_token;
                    this.validationResult = data;
                    this.currentStep = 3; // Jump directly to preview validation
                } else {
                    window.MySkanicAlert ? MySkanicAlert.error(data.message || 'Gagal memvalidasi berkas.', 'Validasi Berkas Gagal') : alert(data.message || 'Gagal memvalidasi berkas.');
                }
            })
            .catch(err => {
                this.isUploading = false;
                window.MySkanicAlert ? MySkanicAlert.error('Terjadi kesalahan jaringan saat memvalidasi berkas.') : alert('Terjadi kesalahan jaringan saat memvalidasi berkas.');
            });
        },

        executeImport() {
            if (!this.importToken) return;

            this.isExecuting = true;
            fetch('{{ route("siswa.import.confirm") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ import_token: this.importToken })
            })
            .then(res => res.json())
            .then(data => {
                this.isExecuting = false;
                if (data.success) {
                    this.executionResult = data;
                    this.currentStep = 6;
                } else {
                    window.MySkanicAlert ? MySkanicAlert.error(data.message || 'Gagal mengeksekusi import.', 'Import Gagal') : alert(data.message || 'Gagal mengeksekusi import.');
                }
            })
            .catch(err => {
                this.isExecuting = false;
                window.MySkanicAlert ? MySkanicAlert.error('Terjadi kesalahan jaringan saat mengeksekusi import.') : alert('Terjadi kesalahan jaringan saat mengeksekusi import.');
            });
        }
    };
}
</script>
@endsection
