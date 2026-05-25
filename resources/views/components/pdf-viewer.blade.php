{{-- resources/views/components/pdf-viewer.blade.php --}}

{{-- Modal Komentar (hanya untuk asisten) --}}
@if ($viewMode === 'assistant_assignment_review')
    <div id="commentModal"
        class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[60]">
        <div class="bg-[#151c2c] rounded-2xl border border-gray-700 p-6 w-96 shadow-2xl space-y-4">
            <h3 class="text-sm font-bold text-white">Tambah Komentar Anotasi</h3>
            <p class="text-[10px] text-gray-400">
                Titik: <span id="comment-coord-info" class="font-mono text-amber-400"></span>
            </p>
            <textarea id="comment-text" rows="3" placeholder="Tulis komentar atau instruksi revisi..."
                class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-white text-[11px] focus:outline-none focus:border-amber-500/50"></textarea>
            <div class="flex justify-end gap-2">
                <button onclick="closeCommentModal()"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-xl transition text-xs">
                    Batal
                </button>
                <button id="save-comment-btn"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl transition text-xs font-bold">
                    Simpan
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Tooltip global --}}
{{-- <div id="annotation-tooltip"
    class="absolute hidden bg-gray-900 text-white text-[11px] p-2 rounded shadow-lg z-50 max-w-[200px] pointer-events-none whitespace-pre-wrap">
</div> --}}

{{-- ================================================ --}}
{{-- TAMPILAN ASISTEN (REVIEW) --}}
{{-- ================================================ --}}
@if ($viewMode === 'assistant_assignment_review')
    <div class="p-4 bg-[#111827] border-b border-gray-800 flex justify-between items-center z-10 shadow-md">
        <div>
            <h2 class="text-sm font-bold text-white">{{ $activeAssignment->title }}</h2>
            <p class="text-[10px] text-gray-400 font-mono">
                Batas Waktu: {{ $activeAssignment->deadline->format('d M Y, H:i') }} WITA
            </p>
        </div>
        <a href="?practicum_id={{ $activePracticum->id }}&assignment_id={{ $activeAssignment->id }}&show_users=true"
            class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-3 py-1.5 rounded-lg font-medium transition">
            <i class="ri-user-shared-line"></i> Lihat Anggota Kelas
        </a>
    </div>

    <div class="flex-1 flex overflow-hidden bg-[#0a0f1d]">
        {{-- Panel Kiri: Daftar Pengumpulan --}}
        <div class="w-64 border-r border-gray-800 bg-[#111827]/60 flex flex-col justify-between p-4 space-y-4">
            <div class="overflow-y-auto scrollbar-none">
                <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Daftar Pengumpulan</h3>
                <div id="submission-list" class="space-y-2 pr-1">
                    @if ($centerData['submissions'])
                        @foreach ($centerData['submissions'] as $sub)
                            <span class="text-[9px] font-mono text-blue-400 font-semibold">
                                {{ $sub->student->identifier }}
                            </span>

                            <h4 class="font-bold text-white truncate max-w-[120px]">
                                {{ $sub->student->name }}
                            </h4>
                            @foreach ($sub->versions as $v)
                                @php
                                    $rawCoords = $v->annotation_coordinates ?? '[]';
                                    $savedAnnotations = json_decode($rawCoords, true) ?? [];
                                    $savedNotes = $v->assistant_notes ?? '';
                                    $base64Notes = base64_encode($savedNotes);
                                @endphp
                                <div
                                    class="bg-[#111827] p-3 rounded-xl border border-gray-800 flex flex-col gap-2 transition hover:border-gray-700">
                                    <div class="flex flex-col justify-between items-center">
                                        <div class="flex w-full justify-between items-center">
                                            <div>
                                                <span class="font-semibold text-white text-[11px]">Versi
                                                    #{{ $v->version_number }}</span>
                                                <p class="text-[9px] text-gray-500 font-mono">
                                                    {{ $v->created_at->format('d M Y, H:i') }} WITA
                                                </p>
                                            </div>
                                            <span
                                                class="text-[9px] px-1.5 py-0.5 rounded font-bold
                                            {{ $v->is_format_valid ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                                {{ $v->is_format_valid ? 'Valid' : 'Struktur Salah' }}
                                            </span>
                                        </div>

                                        {{-- Tampilkan detail log validasi jika ada dan format tidak valid --}}
                                        @php
                                            $validationLogs = json_decode($v->system_validation_logs, true);
                                        @endphp
                                        @if (!$v->is_format_valid && !empty($validationLogs))
                                            <div
                                                class="mt-2 text-[10px] text-red-400 bg-red-500/5 p-2 rounded-lg border border-red-500/20 space-y-1">
                                                <p class="font-semibold text-red-300">Detail Kesalahan Format:
                                                </p>
                                                @foreach ($validationLogs as $key => $message)
                                                    <div class="flex gap-1">
                                                        <span>•</span>
                                                        <span>{{ $message }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @if ($activeAssignment->type === 'module')
                                        <button
                                            onclick="loadPdfViewer(
                                                '/storage/{{ $v->pdf_file_path }}',
                                                {{ $v->id }},
                                                {{ json_encode($savedAnnotations) }},
                                                '{{ $base64Notes }}'
                                            )"
                                            class="w-full text-center bg-emerald-600/10 hover:bg-emerald-600 text-emerald-400 hover:text-white py-1.5 rounded-lg transition text-[10px] font-semibold flex items-center justify-center gap-1">
                                            <i class="ri-file-search-line"></i> Lihat Hasil Review
                                        </button>
                                    @else
                                        <button
                                            onclick='loadTaskViewer(
                                                "{{ $v->attachment }}",
                                                "{{ basename($v->attachment) }}"
                                            )'
                                            class="w-full text-center bg-blue-600/10 hover:bg-blue-600 text-blue-400 hover:text-white py-1.5 rounded-lg transition text-[10px] font-semibold flex items-center justify-center gap-1">
                                            <i class="ri-code-box-line"></i>
                                            Lihat Tugas
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Panel Anotasi --}}
            <div id="annotation-panel" class="hidden border-t border-gray-800 pt-4 mt-auto">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">
                        <i class="ri-chat-private-line"></i> Lembar Penilaian
                    </h3>
                    <span id="target-page-indicator" class="text-[9px] text-gray-500 font-mono">Halaman -</span>
                </div>
                <form id="form-review-asprak" method="POST" action="" class="space-y-3">
                    @csrf
                    <input type="hidden" id="annotation_coordinates" name="annotation_coordinates" value="[]">
                    <div>
                        <label class="block text-gray-400 mb-1 font-semibold">Catatan / Koreksi Ringkas:</label>
                        <textarea id="assistant_notes" name="assistant_notes" rows="4"
                            placeholder="Tulis instruksi ringkas atau rincian revisi disini..."
                            class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-white text-[11px] focus:outline-none focus:border-amber-500/50"
                            required></textarea>
                    </div>
                    <div class="bg-[#0f1524] p-2 rounded-xl border border-gray-800 text-[10px] space-y-1">
                        <span class="text-gray-500 block font-mono">Titik Komentar Halaman Ini:</span>
                        <div id="list-coordinates-log"
                            class="text-amber-400 font-mono max-h-20 overflow-y-auto space-y-0.5">
                            <span class="text-gray-600 italic">Belum ada titik komentar diletakkan.</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <button type="submit" name="status" value="revision"
                            class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2 rounded-xl transition text-[10px]">
                            Tolak & Revisi
                        </button>
                        <button type="submit" name="status" value="approved"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-xl transition text-[10px]">
                            ACC Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Panel Kanan: PDF --}}
        <div class="flex-1 flex flex-col bg-[#111625] overflow-hidden relative">

            @if ($activeAssignment->type === 'module')
                <x-pdf-toolbar />
                <x-pdf-canvas :disable-click="false" />
            @else
                <div id="task-viewer-container" class="flex-1 overflow-hidden bg-[#0b1120]">
                    <div class="h-full flex items-center justify-center text-gray-500">
                        Pilih file tugas untuk ditampilkan.
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ================================================ --}}
    {{-- TAMPILAN MAHASISWA --}}
    {{-- ================================================ --}}
@elseif($viewMode === 'student_assignment_view')
    <div class="p-4 bg-[#111827] border-b border-gray-800 flex justify-between items-center z-10 shadow-md">
        <div>
            <h2 class="text-sm font-bold text-white">{{ $activeAssignment->title }}</h2>
            <p class="text-[10px] text-gray-400">
                Status Tugas:
                <span
                    class="font-bold uppercase
                    {{ ($centerData['my_submission']->status ?? '') === 'approved' ? 'text-emerald-400' : (($centerData['my_submission']->status ?? '') === 'revision' ? 'text-amber-400' : 'text-blue-400') }}">
                    {{ $centerData['my_submission']->status ?? 'Belum Mengumpul' }}
                </span>
            </p>
        </div>
    </div>

    <div class="flex-1 flex overflow-hidden bg-[#0a0f1d]">
        {{-- Panel Kiri: Riwayat Versi --}}
        <div
            class="w-64 border-r border-gray-800 bg-[#111827]/60 flex flex-col justify-between scrollbar-none overflow-y-auto p-4 space-y-4">
            <div class="space-y-4 flex-1 overflow-y-auto scrollbar-none pr-1">
                <div class="bg-[#1e293b]/40 p-3 rounded-xl border border-gray-800">
                    <h3 class="font-bold text-gray-300 uppercase tracking-wider text-[9px] mb-1">Instruksi Deskripsi
                    </h3>
                    <p class="text-gray-400 leading-relaxed text-[11px]">
                        {{ $activeAssignment->description ?? 'Tidak ada deskripsi instruksi tambahan.' }}
                    </p>
                </div>

                @if ($centerData['my_submission'])
                    <div>
                        <h3 class="font-bold text-white text-[11px] mb-2"><i class="ri-history-line"></i> Riwayat
                            Unggahan Versimu:</h3>
                        <div class="space-y-2">
                            @foreach ($centerData['my_submission']->versions as $v)
                                @php
                                    $rawCoords = $v->annotation_coordinates ?? '[]';
                                    $savedAnnotations = json_decode($rawCoords, true) ?? [];
                                    $savedNotes = $v->assistant_notes ?? '';
                                    $base64Notes = base64_encode($savedNotes);
                                @endphp
                                <div
                                    class="bg-[#111827] p-3 rounded-xl border border-gray-800 flex flex-col gap-2 transition hover:border-gray-700">
                                    <div class="flex flex-col justify-between items-center">
                                        <div class="flex w-full justify-between items-center">
                                            <div>
                                                <span class="font-semibold text-white text-[11px]">Versi
                                                    #{{ $v->version_number }}</span>
                                                <p class="text-[9px] text-gray-500 font-mono">
                                                    {{ $v->created_at->format('d M Y, H:i') }} WITA
                                                </p>
                                            </div>
                                            <span
                                                class="text-[9px] px-1.5 py-0.5 rounded font-bold
                                            {{ $v->is_format_valid ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                                {{ $v->is_format_valid ? 'Valid' : 'Struktur Salah' }}
                                            </span>
                                        </div>

                                        {{-- Tampilkan detail log validasi jika ada dan format tidak valid --}}
                                        @php
                                            $validationLogs = json_decode($v->system_validation_logs, true);
                                        @endphp
                                        @if (!$v->is_format_valid && !empty($validationLogs))
                                            <div
                                                class="mt-2 text-[10px] text-red-400 bg-red-500/5 p-2 rounded-lg border border-red-500/20 space-y-1">
                                                <p class="font-semibold text-red-300">Detail Kesalahan Format:</p>
                                                @foreach ($validationLogs as $key => $message)
                                                    <div class="flex gap-1">
                                                        <span>•</span>
                                                        <span>{{ $message }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @if ($activeAssignment->type === 'module')
                                        <button
                                            onclick="loadPdfViewerStudent(
                                            '/storage/{{ $v->pdf_file_path }}',
                                            {{ json_encode($savedAnnotations) }},
                                            '{{ $base64Notes }}'
                                        )"
                                            class="w-full text-center bg-emerald-600/10 hover:bg-emerald-600 text-emerald-400 hover:text-white py-1.5 rounded-lg transition text-[10px] font-semibold flex items-center justify-center gap-1">
                                            <i class="ri-file-search-line"></i> Lihat Hasil Review
                                        </button>
                                    @else
                                        <button
                                            onclick='loadTaskViewer(
                                                "{{ $v->attachment }}",
                                                "{{ basename($v->attachment) }}"
                                            )'
                                            class="w-full text-center bg-blue-600/10 hover:bg-blue-600 text-blue-400 hover:text-white py-1.5 rounded-lg transition text-[10px] font-semibold flex items-center justify-center gap-1">
                                            <i class="ri-code-box-line"></i>
                                            Lihat Tugas
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div id="student-notes-panel"
                class="hidden border-t border-gray-800 pt-3 mt-auto bg-[#0f1524]/60 p-2.5 rounded-xl border border-gray-800">
                <h4 class="text-[10px] font-bold text-amber-400 uppercase tracking-wider mb-1">
                    <i class="ri-feedback-line"></i> Ringkasan Catatan Asprak:
                </h4>
                <div id="student-notes-content"
                    class="text-gray-300 text-[11px] whitespace-pre-wrap max-h-32 overflow-y-auto font-sans bg-[#0a0f1d] p-2 rounded-lg border border-gray-800">
                </div>
            </div>
        </div>

        {{-- Panel Kanan: PDF --}}
        <div class="flex-1 flex flex-col bg-[#111625] overflow-hidden relative">

            @if ($activeAssignment->type === 'module')
                <x-pdf-toolbar />
                <x-pdf-canvas :disable-click="false" />
            @else
                <div id="task-viewer-container" class="flex-1 overflow-hidden bg-[#0b1120]">
                    <div class="h-full flex items-center justify-center text-gray-500">
                        Pilih file tugas untuk ditampilkan.
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Tombol unggah jika belum ACC --}}
    @if (!$centerData['my_submission'] || $centerData['my_submission']->status !== 'approved')
        <button onclick="toggleModal('submissionModal')"
            class="absolute bottom-6 right-6 w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center shadow-2xl transition transform hover:scale-110 z-50">
            <i class="ri-add-line text-2xl"></i>
        </button>
    @endif
@endif
