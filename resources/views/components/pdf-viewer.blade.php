{{-- resources/views/components/pdf-viewer.blade.php --}}

{{-- ======================================================== --}}
{{-- Modal Komentar (hanya untuk asisten)                      --}}
{{-- ======================================================== --}}
@if ($viewMode === 'assistant_assignment_review')
    <div id="commentModal"
         class="hidden fixed inset-0 flex items-center justify-center z-[60]"
         style="background: rgba(6,11,24,0.85); backdrop-filter: blur(8px);">
        <div class="w-96 rounded-2xl border border-slate-700/50 shadow-2xl p-6 space-y-4"
             style="background: #0d1424;">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                     style="background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.2);">
                    <i data-lucide="message-square-plus" class="w-4 h-4 text-amber-400"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Tambah Komentar Anotasi</h3>
                    <p class="text-xs text-slate-500">
                        Titik: <span id="comment-coord-info" class="font-mono text-amber-400"></span>
                    </p>
                </div>
            </div>
            <textarea id="comment-text" rows="3" placeholder="Tulis komentar atau instruksi revisi..."
                class="w-full rounded-xl px-3 py-2.5 text-sm text-white resize-none transition"
                style="background: rgba(6,11,24,0.8); border: 1px solid rgba(51,65,85,0.8); outline: none;"
                onfocus="this.style.borderColor='rgba(99,102,241,0.5)'"
                onblur="this.style.borderColor='rgba(51,65,85,0.8)'"></textarea>
            <div class="flex justify-end gap-2">
                <button onclick="closeCommentModal()"
                    class="px-4 py-2 text-xs font-medium text-slate-400 rounded-xl border border-slate-700/60 hover:bg-slate-700/40 hover:text-white transition">
                    Batal
                </button>
                <button id="save-comment-btn"
                    class="px-4 py-2 text-xs font-semibold text-white rounded-xl transition flex items-center gap-1.5"
                    style="background: linear-gradient(135deg, #d97706, #b45309);">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Simpan
                </button>
            </div>
        </div>
    </div>
@endif


{{-- ======================================================== --}}
{{-- TAMPILAN ASISTEN (REVIEW)                                 --}}
{{-- ======================================================== --}}
@if ($viewMode === 'assistant_assignment_review')

<div class="flex-1 flex flex-col h-full relative" x-data="{ assessmentDrawerOpen: true }">

    {{-- Top Header --}}
    <div class="flex-shrink-0 flex items-center justify-between px-5 py-3 border-b border-slate-800/60 bg-[#0d1424] z-10">
        <div class="min-w-0">
            <h2 class="text-sm font-bold text-white truncate">{{ $activeAssignment->title }}</h2>
            <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5 font-mono">
                <i data-lucide="clock" class="w-3 h-3"></i>
                Batas: {{ $activeAssignment->deadline->format('d M Y, H:i') }} WITA
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="?practicum_id={{ $activePracticum->id }}&assignment_id={{ $activeAssignment->id }}&show_users=true"
                class="hidden md:flex items-center gap-1.5 text-xs font-medium text-slate-400 hover:text-white transition px-3 py-2 rounded-xl border border-slate-700/40 hover:bg-slate-700/40 flex-shrink-0">
                <i data-lucide="users" class="w-3.5 h-3.5"></i> Anggota
            </a>
            <button @click="assessmentDrawerOpen = !assessmentDrawerOpen"
                class="flex items-center gap-1.5 text-xs font-medium text-white transition px-3 py-2 rounded-xl border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 flex-shrink-0"
                :class="assessmentDrawerOpen ? 'bg-indigo-500/20 border-indigo-500/50' : ''">
                <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-indigo-400"></i>
                <span class="hidden sm:inline">Panel Penilaian</span>
            </button>
        </div>
    </div>

    <div class="flex-1 flex overflow-hidden relative" style="background: #080e1c;">

        {{-- AREA UTAMA (100% PDF / Tugas) --}}
        <div class="flex-1 flex flex-col overflow-hidden relative z-0">
            @if ($activeAssignment->type === 'module')
                <x-pdf-toolbar />
                <x-pdf-canvas :disable-click="false" />
            @else
                <div id="task-viewer-container" class="flex-1 overflow-hidden" style="background: #060b18;">
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 select-none">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3 border border-slate-800/60"
                             style="background: rgba(59,130,246,0.06);">
                            <i data-lucide="code-2" class="w-7 h-7 text-blue-500/40"></i>
                        </div>
                        <p class="text-sm text-slate-600">Pilih file tugas dari panel penilaian untuk ditampilkan.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- LACI MELAYANG (FLOATING DRAWER): Daftar Pengumpulan & Penilaian --}}
        <div class="absolute inset-y-0 right-0 z-30 w-80 bg-[#0d1424] border-l border-slate-800/60 shadow-2xl flex flex-col transform transition-transform duration-300 ease-in-out"
             :class="assessmentDrawerOpen ? 'translate-x-0' : 'translate-x-full'">

            {{-- Close Button for Mobile Drawer --}}
            <button @click="assessmentDrawerOpen = false" class="absolute top-3 right-3 p-1.5 rounded-lg text-slate-400 hover:text-white bg-slate-800/50 transition z-50">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

            {{-- Submission List --}}
            <div class="flex-1 overflow-y-auto p-4 pt-10">
                <div class="flex items-center gap-1.5 mb-3 px-1">
                    <i data-lucide="inbox" class="w-3.5 h-3.5 text-slate-500"></i>
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Daftar Pengumpulan</h3>
                </div>

                <div id="submission-list" class="space-y-3">
                    @if ($centerData['submissions'])
                        @foreach ($centerData['submissions'] as $sub)
                            <div class="rounded-xl border border-slate-800/60 overflow-hidden"
                                 style="background: rgba(6,11,24,0.5);">
                                {{-- Student Header --}}
                                <div class="px-3 py-2 border-b border-slate-800/40 flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white text-[9px] font-bold uppercase flex-shrink-0">
                                        {{ substr($sub->student->name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-white truncate">{{ $sub->student->name }}</p>
                                        <p class="text-[10px] text-indigo-400 font-mono">{{ $sub->student->identifier }}</p>
                                    </div>
                                </div>

                                {{-- Versions --}}
                                <div class="p-2 space-y-1.5">
                                    @foreach ($sub->versions as $v)
                                        @php
                                            $rawCoords = $v->annotation_coordinates ?? '[]';
                                            $savedAnnotations = json_decode($rawCoords, true) ?? [];
                                            $savedNotes = $v->assistant_notes ?? '';
                                            $base64Notes = base64_encode($savedNotes);
                                            $validationLogs = json_decode($v->system_validation_logs, true);
                                        @endphp
                                        <div class="rounded-xl p-2.5 border border-slate-700/30 space-y-2"
                                             style="background: rgba(13,20,36,0.8);">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="text-xs font-semibold text-white">Versi #{{ $v->version_number }}</span>
                                                    <p class="text-[10px] text-slate-600 font-mono">{{ $v->created_at->format('d M Y, H:i') }}</p>
                                                </div>
                                                <span class="text-[9px] px-1.5 py-0.5 rounded-md font-bold font-mono
                                                    {{ $v->is_format_valid
                                                        ? 'bg-emerald-500/12 text-emerald-400 border border-emerald-500/20'
                                                        : 'bg-red-500/12 text-red-400 border border-red-500/20' }}">
                                                    {{ $v->is_format_valid ? 'Valid' : 'Invalid' }}
                                                </span>
                                            </div>

                                            @if ($activeAssignment->type === 'module')
                                                <button
                                                    onclick="loadPdfViewer(
                                                        '/storage/{{ $v->pdf_file_path }}',
                                                        {{ $v->id }},
                                                        {{ json_encode($savedAnnotations) }},
                                                        '{{ $base64Notes }}',
                                                        {{ json_encode($validationLogs) }}
                                                    )"
                                                    class="w-full text-center py-1.5 rounded-lg transition text-[10px] font-semibold flex items-center justify-center gap-1.5 text-emerald-400 hover:text-white"
                                                    style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2);"
                                                    onmouseover="this.style.background='rgba(16,185,129,0.2)'"
                                                    onmouseout="this.style.background='rgba(16,185,129,0.08)'">
                                                    <i data-lucide="file-search" class="w-3.5 h-3.5"></i> Periksa Laporan
                                                </button>
                                            @else
                                                <button
                                                    onclick='loadTaskViewer("{{ $v->attachment }}", "{{ basename($v->attachment) }}")'
                                                    class="w-full text-center py-1.5 rounded-lg transition text-[10px] font-semibold flex items-center justify-center gap-1.5 text-blue-400 hover:text-white"
                                                    style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2);"
                                                    onmouseover="this.style.background='rgba(59,130,246,0.2)'"
                                                    onmouseout="this.style.background='rgba(59,130,246,0.08)'">
                                                    <i data-lucide="code-2" class="w-3.5 h-3.5"></i> Lihat Tugas
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Annotation / Review Panel --}}
            <div id="annotation-panel" class="hidden border-t border-slate-800/60 p-4">
                <div class="flex items-center justify-between mb-2.5">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="pen-tool" class="w-3.5 h-3.5 text-amber-400"></i>
                        <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider">Lembar Penilaian</h3>
                    </div>
                    <span id="target-page-indicator" class="text-[10px] text-slate-600 font-mono">Hal -</span>
                </div>

                <form id="form-review-asprak" method="POST" action="" class="space-y-2.5">
                    @csrf
                    <input type="hidden" id="annotation_coordinates" name="annotation_coordinates" value="[]">

                    <div>
                        <textarea id="assistant_notes" name="assistant_notes" rows="3"
                            placeholder="Tulis instruksi ringkas revisi..."
                            class="w-full rounded-xl px-3 py-2 text-xs text-white resize-none transition"
                            style="background: rgba(6,11,24,0.8); border: 1px solid rgba(51,65,85,0.8); outline: none;"
                            onfocus="this.style.borderColor='rgba(245,158,11,0.4)'"
                            onblur="this.style.borderColor='rgba(51,65,85,0.8)'"
                            required></textarea>
                    </div>

                    {{-- Annotation Log --}}
                    <div class="rounded-xl p-2.5 border border-slate-800/60" style="background: rgba(6,11,24,0.6);">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <i data-lucide="map-pin" class="w-3 h-3 text-slate-600"></i>
                            <span class="text-[10px] text-slate-600 font-mono">Titik Komentar Hal Ini:</span>
                        </div>
                        <div id="list-coordinates-log" class="text-amber-400 font-mono max-h-24 overflow-y-auto space-y-1">
                            <span class="text-slate-700 italic text-[10px]">Belum ada titik komentar.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="submit" name="status" value="revision"
                            class="flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold text-white transition"
                            style="background: linear-gradient(135deg, #d97706, #b45309);"
                            onmouseover="this.style.opacity='0.85'"
                            onmouseout="this.style.opacity='1'">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Revisi
                        </button>
                        <button type="submit" name="status" value="approved"
                            class="flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold text-white transition"
                            style="background: linear-gradient(135deg, #059669, #047857);"
                            onmouseover="this.style.opacity='0.85'"
                            onmouseout="this.style.opacity='1'">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> ACC
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================== --}}
{{-- TAMPILAN MAHASISWA                                        --}}
{{-- ======================================================== --}}
@elseif($viewMode === 'student_assignment_view')

<div class="flex-1 flex flex-col h-full relative" x-data="{ assessmentDrawerOpen: true }">

    {{-- Top Header --}}
    <div class="flex-shrink-0 flex items-center justify-between px-5 py-3 border-b border-slate-800/60 bg-[#0d1424] z-10">
        <div class="min-w-0">
            <h2 class="text-sm font-bold text-white truncate">{{ $activeAssignment->title }}</h2>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="text-xs text-slate-500">Status:</span>
                @php $status = $centerData['my_submission']->status ?? null; @endphp
                <span class="text-xs font-bold uppercase font-mono
                    {{ $status === 'approved' ? 'text-emerald-400' : ($status === 'revision' ? 'text-amber-400' : 'text-blue-400') }}">
                    {{ $status ?? 'Belum Mengumpul' }}
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="hidden sm:flex flex-shrink-0 items-center gap-1.5 px-3 py-1.5 rounded-xl border
                {{ $status === 'approved' ? 'border-emerald-500/20 bg-emerald-500/8' : ($status === 'revision' ? 'border-amber-500/20 bg-amber-500/8' : 'border-blue-500/20 bg-blue-500/8') }}">
                <i data-lucide="{{ $status === 'approved' ? 'check-circle-2' : ($status === 'revision' ? 'rotate-ccw' : 'clock') }}"
                   class="w-3.5 h-3.5 {{ $status === 'approved' ? 'text-emerald-400' : ($status === 'revision' ? 'text-amber-400' : 'text-blue-400') }}"></i>
                <span class="text-xs font-mono font-semibold
                    {{ $status === 'approved' ? 'text-emerald-400' : ($status === 'revision' ? 'text-amber-400' : 'text-blue-400') }}">
                    {{ $status === 'approved' ? 'Disetujui' : ($status === 'revision' ? 'Perlu Revisi' : 'Menunggu') }}
                </span>
            </div>
            <button @click="assessmentDrawerOpen = !assessmentDrawerOpen"
                class="flex items-center gap-1.5 text-xs font-medium text-white transition px-3 py-2 rounded-xl border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 flex-shrink-0"
                :class="assessmentDrawerOpen ? 'bg-indigo-500/20 border-indigo-500/50' : ''">
                <i data-lucide="history" class="w-3.5 h-3.5 text-indigo-400"></i>
                <span class="hidden sm:inline">Riwayat Tugas</span>
            </button>
        </div>
    </div>

    <div class="flex-1 flex overflow-hidden relative" style="background: #080e1c;">

        {{-- AREA UTAMA (100% PDF / Tugas) --}}
        <div class="flex-1 flex flex-col overflow-hidden relative z-0">
            @if ($activeAssignment->type === 'module')
                <x-pdf-toolbar />
                <x-pdf-canvas :disable-click="true" />
            @else
                <div id="task-viewer-container" class="flex-1 overflow-hidden" style="background: #060b18;">
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 select-none">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3 border border-slate-800/60"
                             style="background: rgba(59,130,246,0.06);">
                            <i data-lucide="code-2" class="w-7 h-7 text-blue-500/40"></i>
                        </div>
                        <p class="text-sm text-slate-600">Pilih file tugas untuk ditampilkan.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- LACI MELAYANG (FLOATING DRAWER): Riwayat & Instruksi --}}
        <div class="absolute inset-y-0 right-0 z-30 w-80 bg-[#0d1424] border-l border-slate-800/60 shadow-2xl flex flex-col transform transition-transform duration-300 ease-in-out"
             :class="assessmentDrawerOpen ? 'translate-x-0' : 'translate-x-full'">

            {{-- Close Button --}}
            <button @click="assessmentDrawerOpen = false" class="absolute top-3 right-3 p-1.5 rounded-lg text-slate-400 hover:text-white bg-slate-800/50 transition z-50">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

            <div class="flex-1 overflow-y-auto p-4 pt-10 space-y-4">

                {{-- Deskripsi Instruksi --}}
                <div class="rounded-xl p-3 border border-slate-800/50" style="background: rgba(6,11,24,0.5);">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-slate-500"></i>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Instruksi</h3>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ $activeAssignment->description ?? 'Tidak ada deskripsi instruksi tambahan.' }}
                    </p>
                </div>

                {{-- Riwayat Versi --}}
                @if ($centerData['my_submission'])
                    <div>
                        <div class="flex items-center gap-1.5 mb-2 px-1">
                            <i data-lucide="history" class="w-3.5 h-3.5 text-slate-500"></i>
                            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Riwayat Unggahan</h3>
                        </div>

                        <div class="space-y-2">
                            @foreach ($centerData['my_submission']->versions as $v)
                                @php
                                    $rawCoords = $v->annotation_coordinates ?? '[]';
                                    $savedAnnotations = json_decode($rawCoords, true) ?? [];
                                    $savedNotes = $v->assistant_notes ?? '';
                                    $base64Notes = base64_encode($savedNotes);
                                    $validationLogs = json_decode($v->system_validation_logs, true);
                                @endphp
                                <div class="rounded-xl p-2.5 border border-slate-700/30 space-y-2"
                                     style="background: rgba(13,20,36,0.8);">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-xs font-semibold text-white">Versi #{{ $v->version_number }}</span>
                                            <p class="text-[10px] text-slate-600 font-mono">{{ $v->created_at->format('d M Y, H:i') }}</p>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.5 rounded-md font-bold font-mono
                                            {{ $v->is_format_valid
                                                ? 'bg-emerald-500/12 text-emerald-400 border border-emerald-500/20'
                                                : 'bg-red-500/12 text-red-400 border border-red-500/20' }}">
                                            {{ $v->is_format_valid ? 'Valid' : 'Invalid' }}
                                        </span>
                                    </div>

                                    @if ($activeAssignment->type === 'module')
                                        <button
                                            onclick="loadPdfViewerStudent(
                                                '/storage/{{ $v->pdf_file_path }}',
                                                {{ json_encode($savedAnnotations) }},
                                                '{{ $base64Notes }}',
                                                {{ json_encode($validationLogs) }}
                                            )"
                                            class="w-full text-center py-1.5 rounded-lg transition text-[10px] font-semibold flex items-center justify-center gap-1.5 text-emerald-400 hover:text-white"
                                            style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2);"
                                            onmouseover="this.style.background='rgba(16,185,129,0.2)'"
                                            onmouseout="this.style.background='rgba(16,185,129,0.08)'">
                                            <i data-lucide="file-search" class="w-3.5 h-3.5"></i> Lihat Hasil Review
                                        </button>
                                    @else
                                        <button
                                            onclick='loadTaskViewer("{{ $v->attachment }}", "{{ basename($v->attachment) }}")'
                                            class="w-full text-center py-1.5 rounded-lg transition text-[10px] font-semibold flex items-center justify-center gap-1.5 text-blue-400 hover:text-white"
                                            style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2);"
                                            onmouseover="this.style.background='rgba(59,130,246,0.2)'"
                                            onmouseout="this.style.background='rgba(59,130,246,0.08)'">
                                            <i data-lucide="code-2" class="w-3.5 h-3.5"></i> Lihat Tugas
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Catatan Asprak --}}
            <div id="student-notes-panel" class="hidden border-t border-slate-800/60 p-4">
                <div class="rounded-xl border border-amber-500/15 overflow-hidden"
                     style="background: rgba(120,53,15,0.08);">
                    <div class="flex items-center gap-1.5 px-3 py-2 border-b border-amber-500/10">
                        <i data-lucide="message-square" class="w-3.5 h-3.5 text-amber-400"></i>
                        <h4 class="text-xs font-bold text-amber-400 uppercase tracking-wider">Catatan Asprak</h4>
                    </div>
                    <div id="student-notes-content"
                         class="text-xs text-slate-300 font-mono leading-relaxed p-3 max-h-32 overflow-y-auto whitespace-pre-wrap">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FAB Upload (jika belum ACC) --}}
    @if (!$centerData['my_submission'] || $centerData['my_submission']->status !== 'approved')
        <button onclick="toggleModal('submissionModal')"
            class="absolute bottom-6 right-6 w-12 h-12 text-white rounded-2xl flex items-center justify-center shadow-2xl transition hover:scale-110 z-50"
            style="background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 8px 32px rgba(99,102,241,0.4);">
            <i data-lucide="upload" class="w-5 h-5"></i>
        </button>
    @endif

</div>
@endif
