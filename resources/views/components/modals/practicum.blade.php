{{-- components/modals/practicum.blade.php --}}
<div id="practicumModal"
     class="hidden fixed inset-0 flex items-center justify-center p-4 z-50"
     style="background: rgba(6,11,24,0.85); backdrop-filter: blur(8px);">
    <div class="w-full max-w-md rounded-2xl border border-slate-700/40 shadow-2xl overflow-hidden"
         style="background: #0d1424;">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                     style="background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.2);">
                    <i data-lucide="git-branch" class="w-4 h-4 text-indigo-400"></i>
                </div>
                <h2 class="text-sm font-bold text-white">Opsi Kelas Praktikum</h2>
            </div>
            <button onclick="toggleModal('practicumModal')"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-700/50 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">

            {{-- JOIN SECTION --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-5 h-5 rounded-md bg-blue-500/15 flex items-center justify-center text-blue-400 flex-shrink-0">
                        <i data-lucide="log-in" class="w-3 h-3"></i>
                    </span>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gabung Kelas (Mahasiswa)</label>
                </div>
                <form action="{{ route('practicums.join') }}" method="POST" class="flex gap-2">
                    @csrf
                    <select name="practicum_id"
                        class="flex-1 rounded-xl px-3 py-2.5 text-sm text-white transition"
                        style="background: rgba(6,11,24,0.8); border: 1px solid rgba(51,65,85,0.8); outline: none;">
                        @foreach ($availablePracticums as $ap)
                            <option value="{{ $ap->id }}" style="background: #0d1424;">
                                {{ $ap->name }} ({{ $ap->academic_year }})
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-white flex-shrink-0 transition"
                        style="background: linear-gradient(135deg, #3b82f6, #2563eb);"
                        onmouseover="this.style.opacity='0.85'"
                        onmouseout="this.style.opacity='1'">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i> Join
                    </button>
                </form>
            </div>

            {{-- Divider --}}
            <div class="flex items-center gap-3">
                <div class="flex-1 border-t border-slate-800"></div>
                <span class="text-xs text-slate-600 font-semibold uppercase tracking-widest">atau</span>
                <div class="flex-1 border-t border-slate-800"></div>
            </div>

            {{-- CREATE SECTION --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-5 h-5 rounded-md bg-amber-500/15 flex items-center justify-center text-amber-400 flex-shrink-0">
                        <i data-lucide="plus-circle" class="w-3 h-3"></i>
                    </span>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Buat Kelas Baru (Asisten)</label>
                </div>

                <form action="{{ route('practicums.store') }}" method="POST" class="space-y-3">
                    @csrf

                    <input type="text" name="name" placeholder="Nama Praktikum (cth: Grafika Komputer)"
                        class="w-full rounded-xl px-3 py-2.5 text-sm text-white transition"
                        style="background: rgba(6,11,24,0.8); border: 1px solid rgba(51,65,85,0.8); outline: none;"
                        onfocus="this.style.borderColor='rgba(245,158,11,0.4)'"
                        onblur="this.style.borderColor='rgba(51,65,85,0.8)'"
                        required value="{{ old('name') }}">

                    <input type="text" name="academic_year" placeholder="Tahun Akademik (cth: 2026/2027)"
                        class="w-full rounded-xl px-3 py-2.5 text-sm text-white transition"
                        style="background: rgba(6,11,24,0.8); border: 1px solid rgba(51,65,85,0.8); outline: none;"
                        onfocus="this.style.borderColor='rgba(245,158,11,0.4)'"
                        onblur="this.style.borderColor='rgba(51,65,85,0.8)'"
                        required value="{{ old('academic_year') }}">

                    {{-- Format Rules --}}
                    <div class="rounded-xl border border-slate-800/60 overflow-hidden" style="background: rgba(6,11,24,0.5);">
                        <div class="flex items-center gap-2 px-3 py-2 border-b border-slate-800/60">
                            <i data-lucide="settings-2" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Standar Format Dokumen</span>
                        </div>
                        <div class="p-3 space-y-3">
                            {{-- Margins --}}
                            <div>
                                <label class="block text-[10px] text-slate-500 font-semibold mb-2 uppercase tracking-wide">Margin (cm)</label>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach(['required_margin_top_cm' => ['Atas','4.0'], 'required_margin_bottom_cm' => ['Bawah','3.0'], 'required_margin_left_cm' => ['Kiri','4.0'], 'required_margin_right_cm' => ['Kanan','3.0']] as $name => [$label, $default])
                                        <div>
                                            <label class="block text-[9px] text-slate-600 mb-1">{{ $label }}</label>
                                            <input type="number" step="0.1" name="{{ $name }}" value="{{ $default }}"
                                                class="w-full rounded-lg px-2 py-1.5 text-xs text-white text-center transition"
                                                style="background: rgba(13,20,36,0.8); border: 1px solid rgba(51,65,85,0.6); outline: none;"
                                                onfocus="this.style.borderColor='rgba(245,158,11,0.4)'"
                                                onblur="this.style.borderColor='rgba(51,65,85,0.6)'"
                                                required>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            {{-- Font & Spacing --}}
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-[9px] text-slate-600 mb-1">Font Name</label>
                                    <select name="required_font_name"
                                        class="w-full rounded-lg px-2 py-1.5 text-xs text-white transition"
                                        style="background: rgba(13,20,36,0.8); border: 1px solid rgba(51,65,85,0.6); outline: none;">
                                        <option value="Times New Roman" style="background:#0d1424" selected>Times New Roman</option>
                                        <option value="Arial" style="background:#0d1424">Arial</option>
                                        <option value="Calibri" style="background:#0d1424">Calibri</option>
                                        <option value="Helvetica" style="background:#0d1424">Helvetica</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[9px] text-slate-600 mb-1">Font Size (pt)</label>
                                    <input type="number" name="required_font_size" value="12" min="8" max="24"
                                        class="w-full rounded-lg px-2 py-1.5 text-xs text-white text-center transition"
                                        style="background: rgba(13,20,36,0.8); border: 1px solid rgba(51,65,85,0.6); outline: none;"
                                        onfocus="this.style.borderColor='rgba(245,158,11,0.4)'"
                                        onblur="this.style.borderColor='rgba(51,65,85,0.6)'"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-[9px] text-slate-600 mb-1">Line Spacing</label>
                                    <select name="required_line_spacing"
                                        class="w-full rounded-lg px-2 py-1.5 text-xs text-white transition"
                                        style="background: rgba(13,20,36,0.8); border: 1px solid rgba(51,65,85,0.6); outline: none;">
                                        <option value="1.0" style="background:#0d1424">1.0 Single</option>
                                        <option value="1.15" style="background:#0d1424">1.15</option>
                                        <option value="1.5" selected style="background:#0d1424">1.5</option>
                                        <option value="2.0" style="background:#0d1424">2.0 Double</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                        style="background: linear-gradient(135deg, #d97706, #b45309); box-shadow: 0 4px 16px rgba(217,119,6,0.25);"
                        onmouseover="this.style.opacity='0.85'"
                        onmouseout="this.style.opacity='1'">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        Buat Kelas Praktikum
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
