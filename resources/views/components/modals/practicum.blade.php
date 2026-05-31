<div id="practicumModal"
    class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="w-full max-w-md bg-[#151c2c] rounded-2xl border border-gray-800 p-6 shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b border-gray-800 pb-3">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">
                <i class="ri-git-repository-line"></i> Opsi Kelas Praktikum
            </h2>
            <button onclick="toggleModal('practicumModal')" class="text-gray-500 hover:text-white">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <form action="{{ route('practicums.join') }}" method="POST" class="space-y-3">
            @csrf
            <label class="block font-bold text-[10px] text-gray-400 uppercase">GABUNG KELAS YANG TERSEDIA
                (STUDENT)</label>
            <div class="flex gap-2">
                <select name="practicum_id"
                    class="flex-1 bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-white focus:outline-none">
                    @foreach ($availablePracticums as $ap)
                        <option value="{{ $ap->id }}">{{ $ap->name }} ({{ $ap->academic_year }})</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 rounded-xl transition">Join</button>
            </div>
        </form>
        <div class="relative flex py-2 items-center">
            <div class="flex-grow border-t border-gray-800"></div>
            <span class="flex-shrink mx-4 text-gray-600 font-bold text-[9px] uppercase">ATAU</span>
            <div class="flex-grow border-t border-gray-800"></div>
        </div>
        <form action="{{ route('practicums.store') }}" method="POST" class="space-y-4">
            @csrf
            <label class="block font-bold text-[10px] text-gray-400 uppercase tracking-wider">BUAT KELAS BARU
                (ASSISTANT)</label>

            <div class="space-y-2">
                <input type="text" name="name" placeholder="Nama Praktikum (cth: Grafika Komputer)"
                    class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-sm text-white focus:outline-none focus:border-amber-500 transition"
                    required value="{{ old('name') }}">
                <input type="text" name="academic_year" placeholder="Tahun Akademik (cth: 2026/2027)"
                    class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-sm text-white focus:outline-none focus:border-amber-500 transition"
                    required value="{{ old('academic_year') }}">
            </div>

            <div class="border-t border-gray-800/60 my-2"></div>

            <div class="space-y-3">
                <label class="block font-bold text-[9px] text-amber-500 uppercase tracking-wider">
                    <i class="ri-settings-4-line"></i> Standar Format Dokumen (Rules)
                </label>

                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <label class="block text-[8px] text-gray-400 font-semibold mb-1">Margin Atas (cm)</label>
                        <input type="number" step="0.1" name="required_margin_top_cm" value="4.0"
                            class="w-full bg-[#0f1524] border border-gray-700 rounded-lg p-2 text-xs text-white focus:outline-none focus:border-amber-500 text-center"
                            required>
                    </div>
                    <div>
                        <label class="block text-[8px] text-gray-400 font-semibold mb-1">Bawah (cm)</label>
                        <input type="number" step="0.1" name="required_margin_bottom_cm" value="3.0"
                            class="w-full bg-[#0f1524] border border-gray-700 rounded-lg p-2 text-xs text-white focus:outline-none focus:border-amber-500 text-center"
                            required>
                    </div>
                    <div>
                        <label class="block text-[8px] text-gray-400 font-semibold mb-1">Kiri (cm)</label>
                        <input type="number" step="0.1" name="required_margin_left_cm" value="4.0"
                            class="w-full bg-[#0f1524] border border-gray-700 rounded-lg p-2 text-xs text-white focus:outline-none focus:border-amber-500 text-center"
                            required>
                    </div>
                    <div>
                        <label class="block text-[8px] text-gray-400 font-semibold mb-1">Kanan (cm)</label>
                        <input type="number" step="0.1" name="required_margin_right_cm" value="3.0"
                            class="w-full bg-[#0f1524] border border-gray-700 rounded-lg p-2 text-xs text-white focus:outline-none focus:border-amber-500 text-center"
                            required>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[8px] text-gray-400 font-semibold mb-1">Font Name</label>
                        <select name="required_font_name"
                            class="w-full bg-[#0f1524] border border-gray-700 rounded-lg p-2 text-xs text-white focus:outline-none focus:border-amber-500">
                            <option value="Times New Roman" selected>Times New Roman</option>
                            <option value="Arial">Arial</option>
                            <option value="Calibri">Calibri</option>
                            <option value="Helvetica">Helvetica</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[8px] text-gray-400 font-semibold mb-1">Font Size (pt)</label>
                        <input type="number" name="required_font_size" value="12" min="8" max="24"
                            class="w-full bg-[#0f1524] border border-gray-700 rounded-lg p-2 text-xs text-white focus:outline-none focus:border-amber-500 text-center"
                            required>
                    </div>
                    <div>
                        <label class="block text-[8px] text-gray-400 font-semibold mb-1">Line Spacing</label>
                        <select name="required_line_spacing"
                            class="w-full bg-[#0f1524] border border-gray-700 rounded-lg p-2 text-xs text-white focus:outline-none focus:border-amber-500">
                            <option value="1.0">1.0 (Single)</option>
                            <option value="1.15">1.15</option>
                            <option value="1.5" selected>1.5</option>
                            <option value="2.0">2.0 (Double)</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold p-2.5 rounded-xl transition shadow-lg shadow-amber-900/20">
                Buat Kelas Praktikum
            </button>
        </form>
    </div>
</div>
