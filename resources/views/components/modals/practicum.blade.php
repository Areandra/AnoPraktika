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
        <form action="{{ route('practicums.store') }}" method="POST" class="space-y-3">
            @csrf
            <label class="block font-bold text-[10px] text-gray-400 uppercase">BUAT KELAS BARU (ASSISTANT)</label>
            <input type="text" name="name" placeholder="Nama Praktikum (cth: Grafika Komputer)"
                class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-white focus:outline-none"
                required>
            <input type="text" name="academic_year" placeholder="Tahun Akademik (cth: 2026/2027)"
                class="w-full bg-[#0f1524] border border-gray-700 rounded-xl p-2.5 text-white focus:outline-none"
                required>
            <button type="submit"
                class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold p-2.5 rounded-xl transition">
                Buat Kelas Praktikum
            </button>
        </form>
    </div>
</div>
