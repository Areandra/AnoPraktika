<div id="assignmentModal"
    class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="w-full max-w-md bg-[#151c2c] rounded-2xl border border-gray-800 p-6 shadow-2xl space-y-4">
        <div class="flex justify-between items-center border-b border-gray-800 pb-3">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Rilis Modul / Tugas Baru</h2>
            <button onclick="toggleModal('assignmentModal')" class="text-gray-500 hover:text-white">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <form action="{{ route('assignments.store') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="practicum_id" value="{{ $activePracticum->id }}">
            <div>
                <label class="block text-gray-400 mb-1">Judul Tugas</label>
                <input type="text" name="title" placeholder="Contoh: Modul 2: Transformasi 3D"
                    class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-white focus:outline-none"
                    required>
            </div>
            <div>
                <label class="block text-gray-400 mb-1">Tipe Instrumen</label>
                <select name="type"
                    class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-white focus:outline-none">
                    <option value="module">Modul Praktikum (Auto-Read PDF)</option>
                    <option value="task">Tugas Tambahan (Attachment Canvas)</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-400 mb-1">Deskripsi Instruksi</label>
                <textarea name="description" rows="3" placeholder="Tulis instruksi pengerjaan..."
                    class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-white focus:outline-none"></textarea>
            </div>
            <div>
                <label class="block text-gray-400 mb-1">Batas Waktu Pengumpulan</label>
                <input type="datetime-local" name="deadline"
                    class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-white focus:outline-none"
                    required>
            </div>
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold p-2.5 rounded-xl transition">
                Terbitkan Sekarang
            </button>
        </form>
    </div>
</div>
