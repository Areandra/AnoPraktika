{{-- components/modals/submission.blade.php --}}
<div id="submissionModal"
     class="hidden fixed inset-0 flex items-center justify-center p-4 z-50"
     style="background: rgba(6,11,24,0.85); backdrop-filter: blur(8px);">
    <div class="w-full max-w-md rounded-2xl border border-slate-700/40 shadow-2xl overflow-hidden"
         style="background: #0d1424;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                     style="background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.2);">
                    <i data-lucide="upload-cloud" class="w-4 h-4 text-emerald-400"></i>
                </div>
                <h2 class="text-sm font-bold text-white">Kumpul Berkas Tugas</h2>
            </div>
            <button onclick="toggleModal('submissionModal')"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-700/50 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6">
            <form action="{{ route('submissions.store', $activeAssignment->id) }}" method="POST"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf

                @if ($activeAssignment->type === 'module')
                    {{-- Word File --}}
                    <div x-data="{ file: null, hover: false }">
                        <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-2">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-blue-400"></i>
                            Berkas Word Laporan (.docx)
                        </label>
                        <label class="group flex flex-col items-center justify-center w-full h-24 rounded-xl border-2 border-dashed cursor-pointer transition"
                               :style="file ? 'border-color: rgba(59,130,246,0.5); background: rgba(59,130,246,0.05)' : (hover ? 'border-color: rgba(59,130,246,0.4); background: rgba(59,130,246,0.04)' : 'border-color: rgba(51,65,85,0.8); background: rgba(6,11,24,0.5)')"
                               @mouseenter="hover = true"
                               @mouseleave="hover = false">
                            <i data-lucide="file-up" class="w-6 h-6 mb-1.5 transition" :class="file ? 'text-blue-400' : 'text-slate-600 group-hover:text-blue-400'"></i>
                            <span class="text-xs transition text-center px-4 truncate w-full" :class="file ? 'text-blue-400 font-semibold' : 'text-slate-600 group-hover:text-slate-400'" x-text="file ? file.name : 'Klik untuk pilih file .docx'">Klik untuk pilih file .docx</span>
                            <input type="file" name="word_file" accept=".docx" class="hidden" required @change="file = $event.target.files.length ? $event.target.files[0] : null">
                        </label>
                    </div>

                    {{-- PDF File --}}
                    <div x-data="{ file: null, hover: false }">
                        <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-2">
                            <i data-lucide="file-scan" class="w-3.5 h-3.5 text-red-400"></i>
                            Berkas PDF Laporan (.pdf)
                        </label>
                        <label class="group flex flex-col items-center justify-center w-full h-24 rounded-xl border-2 border-dashed cursor-pointer transition"
                               :style="file ? 'border-color: rgba(239,68,68,0.5); background: rgba(239,68,68,0.05)' : (hover ? 'border-color: rgba(239,68,68,0.4); background: rgba(239,68,68,0.04)' : 'border-color: rgba(51,65,85,0.8); background: rgba(6,11,24,0.5)')"
                               @mouseenter="hover = true"
                               @mouseleave="hover = false">
                            <i data-lucide="file-up" class="w-6 h-6 mb-1.5 transition" :class="file ? 'text-red-400' : 'text-slate-600 group-hover:text-red-400'"></i>
                            <span class="text-xs transition text-center px-4 truncate w-full" :class="file ? 'text-red-400 font-semibold' : 'text-slate-600 group-hover:text-slate-400'" x-text="file ? file.name : 'Klik untuk pilih file .pdf'">Klik untuk pilih file .pdf</span>
                            <input type="file" name="pdf_file" accept=".pdf" class="hidden" required @change="file = $event.target.files.length ? $event.target.files[0] : null">
                        </label>
                    </div>
                @else
                    {{-- Attachment --}}
                    <div x-data="{ file: null, hover: false }">
                        <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-2">
                            <i data-lucide="paperclip" class="w-3.5 h-3.5 text-indigo-400"></i>
                            Attachment File (Zip / Markdown / Code)
                        </label>
                        <label class="group flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed cursor-pointer transition"
                               :style="file ? 'border-color: rgba(99,102,241,0.5); background: rgba(99,102,241,0.05)' : (hover ? 'border-color: rgba(99,102,241,0.4); background: rgba(99,102,241,0.04)' : 'border-color: rgba(51,65,85,0.8); background: rgba(6,11,24,0.5)')"
                               @mouseenter="hover = true"
                               @mouseleave="hover = false">
                            <i data-lucide="upload-cloud" class="w-7 h-7 mb-1.5 transition" :class="file ? 'text-indigo-400' : 'text-slate-600 group-hover:text-indigo-400'"></i>
                            <span class="text-xs transition font-medium text-center px-4 truncate w-full" :class="file ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300'" x-text="file ? file.name : 'Klik untuk pilih file'">Klik untuk pilih file</span>
                            <span class="text-[10px] text-slate-700 mt-0.5" x-show="!file">.zip · .md · .js · .py · dll</span>
                            <input type="file" name="attachment_file" class="hidden" required @change="file = $event.target.files.length ? $event.target.files[0] : null">
                        </label>
                    </div>
                @endif

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                    style="background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 4px 16px rgba(5,150,105,0.25);"
                    onmouseover="this.style.opacity='0.85'"
                    onmouseout="this.style.opacity='1'">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Pengumpulan
                </button>
            </form>
        </div>
    </div>
</div>
