{{-- components/modals/assignment.blade.php --}}
<div id="assignmentModal"
     class="hidden fixed inset-0 flex items-center justify-center p-4 z-50"
     style="background: rgba(6,11,24,0.85); backdrop-filter: blur(8px);">
    <div class="w-full max-w-md rounded-2xl border border-slate-700/40 shadow-2xl overflow-hidden"
         style="background: #0d1424;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                     style="background: rgba(59,130,246,0.12); border: 1px solid rgba(59,130,246,0.2);">
                    <i data-lucide="file-plus-2" class="w-4 h-4 text-blue-400"></i>
                </div>
                <h2 class="text-sm font-bold text-white">Rilis Modul / Tugas Baru</h2>
            </div>
            <button onclick="toggleModal('assignmentModal')"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-700/50 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6">
            <form action="{{ route('assignments.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="practicum_id" value="{{ $activePracticum->id }}">

                {{-- Judul --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-1.5">
                        <i data-lucide="type" class="w-3.5 h-3.5"></i> Judul Tugas
                    </label>
                    <input type="text" name="title" placeholder="Contoh: Modul 2: Transformasi 3D"
                        class="w-full rounded-xl px-3 py-2.5 text-sm text-white transition"
                        style="background: rgba(6,11,24,0.8); border: 1px solid rgba(51,65,85,0.8); outline: none;"
                        onfocus="this.style.borderColor='rgba(59,130,246,0.5)'"
                        onblur="this.style.borderColor='rgba(51,65,85,0.8)'"
                        required>
                </div>

                {{-- Tipe --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-1.5">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i> Tipe Instrumen
                    </label>
                    <select name="type"
                        class="w-full rounded-xl px-3 py-2.5 text-sm text-white transition"
                        style="background: rgba(6,11,24,0.8); border: 1px solid rgba(51,65,85,0.8); outline: none;">
                        <option value="module" style="background:#0d1424">📄 Modul Praktikum (Auto-Read PDF)</option>
                        <option value="task" style="background:#0d1424">💻 Tugas Tambahan (Attachment Canvas)</option>
                    </select>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-1.5">
                        <i data-lucide="align-left" class="w-3.5 h-3.5"></i> Deskripsi Instruksi
                    </label>
                    <textarea name="description" rows="3" placeholder="Tulis instruksi pengerjaan..."
                        class="w-full rounded-xl px-3 py-2.5 text-sm text-white resize-none transition"
                        style="background: rgba(6,11,24,0.8); border: 1px solid rgba(51,65,85,0.8); outline: none;"
                        onfocus="this.style.borderColor='rgba(59,130,246,0.5)'"
                        onblur="this.style.borderColor='rgba(51,65,85,0.8)'"></textarea>
                </div>

                {{-- Deadline --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-1.5">
                        <i data-lucide="calendar-clock" class="w-3.5 h-3.5"></i> Batas Waktu Pengumpulan
                    </label>
                    <input type="datetime-local" name="deadline"
                        class="w-full rounded-xl px-3 py-2.5 text-sm text-white transition"
                        style="background: rgba(6,11,24,0.8); border: 1px solid rgba(51,65,85,0.8); outline: none; color-scheme: dark;"
                        onfocus="this.style.borderColor='rgba(59,130,246,0.5)'"
                        onblur="this.style.borderColor='rgba(51,65,85,0.8)'"
                        required>
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold text-white transition mt-2"
                    style="background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 16px rgba(59,130,246,0.25);"
                    onmouseover="this.style.opacity='0.85'"
                    onmouseout="this.style.opacity='1'">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Terbitkan Sekarang
                </button>
            </form>
        </div>
    </div>
</div>
