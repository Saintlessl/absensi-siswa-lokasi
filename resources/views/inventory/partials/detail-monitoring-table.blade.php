<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-slate-100">
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NAMA PEMINJAM</th>
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">ROLE</th>
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">JURUSAN</th>
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KODE UNIT</th>
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TGL PINJAM</th>
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">BATAS KEMBALI</th>
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TGL KEMBALI AKTUAL</th>
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">STATUS</th>
                <th class="py-3.5 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-24">AKSI</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
            @foreach($borrowings as $b)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    {{-- 1. No --}}
                    <td class="py-3.5 px-4 text-xs font-semibold text-slate-400 text-center">
                        {{ $loop->iteration }}
                    </td>

                    {{-- 2. Nama Peminjam --}}
                    <td class="py-3.5 px-4">
                        <div class="font-bold text-slate-900 text-xs sm:text-sm">
                            {{ $b->user?->name ?? ($b->siswa?->nama ?? ($b->peminjamGuru?->nama ?? '-')) }}
                        </div>
                        <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                            @if($b->siswa)
                                NIS: {{ $b->siswa->nis ?? '-' }}
                            @elseif($b->peminjamGuru)
                                NIP: {{ $b->peminjamGuru->nip ?? '-' }}
                            @else
                                ID: #{{ $b->user_id }}
                            @endif
                        </div>
                        @if($b->keperluan)
                            <div class="text-[11px] text-slate-500 italic mt-0.5 line-clamp-1" title="{{ $b->keperluan }}">
                                {{ $b->keperluan }}
                            </div>
                        @endif
                    </td>

                    {{-- 3. Role --}}
                    <td class="py-3.5 px-4">
                        @if($b->siswa)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                Siswa
                            </span>
                        @elseif($b->peminjamGuru)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-100">
                                Guru
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600">
                                Umum
                            </span>
                        @endif
                    </td>

                    {{-- 4. Jurusan / Kelas --}}
                    <td class="py-3.5 px-4">
                        <div class="text-xs font-semibold text-slate-700">
                            {{ $b->siswa?->kelas?->jurusan?->nama_jurusan ?? ($b->siswa?->kelas?->nama_kelas ?? ($b->item?->jurusan?->nama_jurusan ?? 'Umum')) }}
                        </div>
                        @if($b->siswa?->kelas)
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $b->siswa->kelas->nama_kelas }}</div>
                        @endif
                    </td>

                    {{-- 5. Kode Unit --}}
                    <td class="py-3.5 px-4">
                        @if($b->borrowingUnits->isNotEmpty())
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                @foreach($b->borrowingUnits as $bu)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#EFF5FF] text-[#003FA9] font-mono font-bold text-[11px] border border-blue-100">
                                        {{ $bu->unit?->kode_unit ?: 'Unit #' . $bu->inventory_unit_id }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-slate-500 font-mono">{{ $b->jumlah }} Unit</span>
                        @endif
                    </td>

                    {{-- 6. Tgl Pinjam --}}
                    <td class="py-3.5 px-4 text-xs font-mono font-medium text-slate-600">
                        {{ $b->tanggal_pinjam ? $b->tanggal_pinjam->format('d/m/Y') : '-' }}
                    </td>

                    {{-- 7. Batas Pengembalian --}}
                    <td class="py-3.5 px-4 text-xs font-mono font-medium {{ $b->is_overdue ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                        {{ $b->rencana_kembali ? $b->rencana_kembali->format('d/m/Y') : '-' }}
                    </td>

                    {{-- 8. Tgl Pengembalian Aktual --}}
                    <td class="py-3.5 px-4 text-xs font-mono font-medium text-slate-600">
                        {{ $b->tanggal_kembali ? $b->tanggal_kembali->format('d/m/Y') : '-' }}
                    </td>

                    {{-- 9. Status --}}
                    <td class="py-3.5 px-4">
                        @php $badge = $b->status_badge; @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badge['bg'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </td>

                    {{-- 10. Aksi --}}
                    <td class="py-3.5 px-4 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']) && in_array($b->status, ['borrowed', 'approved']))
                                <a href="{{ route('inventory.borrowings', ['search' => $b->id]) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-[#EFF5FF] text-[#003FA9] hover:bg-blue-100 transition shadow-2xs"
                                   title="Proses Pengembalian">
                                    <i class='bx bx-check-shield text-sm'></i>
                                    <span>Kembalikan</span>
                                </a>
                            @else
                                <a href="{{ route('inventory.borrowings', ['search' => $b->id]) }}"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-[#EFF5FF] transition"
                                   title="Detail Peminjaman">
                                    <i class='bx bx-show text-base'></i>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
