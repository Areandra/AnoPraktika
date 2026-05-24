@if ($activePracticum)
    <div>
        <div class="border-b border-gray-800 pb-4 mb-4">
            <span class="text-[10px] font-mono text-gray-500 uppercase tracking-wide">Info Praktikum</span>
            <h2 class="text-base font-bold text-white mt-1">{{ $activePracticum->name }}</h2>
            <p class="text-[11px] text-gray-400 mt-0.5">Tahun Akademik: {{ $activePracticum->academic_year }}</p>

            <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                <div class="bg-[#1f2937]/50 p-2 border border-gray-800 rounded-xl">
                    <span class="text-gray-400 block text-[9px] uppercase">Students</span>
                    <span class="text-sm font-mono font-bold text-white">{{ $activePracticum->total_students }}</span>
                </div>
                <div class="bg-[#1f2937]/50 p-2 border border-gray-800 rounded-xl">
                    <span class="text-gray-400 block text-[9px] uppercase">Modul</span>
                    <span class="text-sm font-mono font-bold text-blue-400">{{ $activePracticum->total_modules }}</span>
                </div>
                <div class="bg-[#1f2937]/50 p-2 border border-gray-800 rounded-xl">
                    <span class="text-gray-400 block text-[9px] uppercase">Tugas</span>
                    <span class="text-sm font-mono font-bold text-amber-400">{{ $activePracticum->total_tasks }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mb-3">
            <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Daftar Modul & Tugas</h3>
            @if ($roleInActivePracticum === 'assistant')
                <button onclick="toggleModal('assignmentModal')"
                    class="text-blue-400 hover:text-white transition text-xs font-bold">+ Rilis</button>
            @endif
        </div>

        <div class="space-y-1.5 overflow-y-auto max-h-[50vh]">
            @forelse($activePracticum->assignments()->get() as $asm)
                <a href="?practicum_id={{ $activePracticum->id }}&assignment_id={{ $asm->id }}"
                    class="flex items-center justify-between p-3 rounded-xl border transition {{ request('assignment_id') == $asm->id ? 'bg-[#1f2937] border-gray-700 text-white font-medium shadow-md' : 'bg-[#151c2c]/40 border-transparent text-gray-400 hover:bg-[#1f2937]/30' }}">
                    <div class="flex items-center gap-2.5 truncate">
                        <i
                            class="ri-file-text-line text-base {{ $asm->type === 'module' ? 'text-blue-400' : 'text-amber-400' }}"></i>
                        <span class="truncate">{{ $asm->title }}</span>
                    </div>
                    <i class="ri-arrow-right-s-line text-gray-600"></i>
                </a>
            @empty
                <p class="text-gray-600 text-center py-6">Belum ada modul atau tugas dirilis.</p>
            @endforelse
        </div>
    </div>
@else
    <div class="flex-1 flex items-center justify-center text-center text-gray-600">
        Pilih praktikum untuk memuat instrumen panel.
    </div>
@endif
