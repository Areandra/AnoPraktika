{{-- components/sidebar-right.blade.php --}}
@if ($activePracticum)
    <div class="flex flex-col h-full">

        {{-- Practicum Info Header --}}
        <div class="p-4 border-b border-slate-800/60">
            <span class="text-[10px] font-mono text-slate-600 uppercase tracking-widest">Info Praktikum</span>
            <h2 class="text-sm font-bold text-white mt-1 leading-tight">{{ $activePracticum->name }}</h2>
            <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                <i data-lucide="calendar" class="w-3 h-3"></i>
                TA {{ $activePracticum->academic_year }}
            </p>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-3 gap-2 mt-4">
                <div class="rounded-xl p-2.5 text-center border"
                     style="background: rgba(59,130,246,0.06); border-color: rgba(59,130,246,0.15);">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-blue-400 mx-auto mb-1"></i>
                    <span class="block text-[10px] text-slate-500">Mahasiswa</span>
                    <span class="block text-base font-bold font-mono text-blue-300">{{ $activePracticum->total_students }}</span>
                </div>
                <div class="rounded-xl p-2.5 text-center border"
                     style="background: rgba(99,102,241,0.06); border-color: rgba(99,102,241,0.15);">
                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-indigo-400 mx-auto mb-1"></i>
                    <span class="block text-[10px] text-slate-500">Modul</span>
                    <span class="block text-base font-bold font-mono text-indigo-300">{{ $activePracticum->total_modules }}</span>
                </div>
                <div class="rounded-xl p-2.5 text-center border"
                     style="background: rgba(245,158,11,0.06); border-color: rgba(245,158,11,0.15);">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-amber-400 mx-auto mb-1"></i>
                    <span class="block text-[10px] text-slate-500">Tugas</span>
                    <span class="block text-base font-bold font-mono text-amber-300">{{ $activePracticum->total_tasks }}</span>
                </div>
            </div>
        </div>

        {{-- Module & Task List --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-1.5">
                    <i data-lucide="layers" class="w-3 h-3 text-slate-500"></i>
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Modul &amp; Tugas</h3>
                </div>
                @if ($roleInActivePracticum === 'assistant')
                    <button onclick="toggleModal('assignmentModal')"
                        class="flex items-center gap-1 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition px-2 py-1 rounded-lg hover:bg-indigo-500/10">
                        <i data-lucide="plus" class="w-3 h-3"></i> Rilis
                    </button>
                @endif
            </div>

            <div class="space-y-1.5">
                @forelse($activePracticum->assignments()->get() as $asm)
                    <a href="?practicum_id={{ $activePracticum->id }}&assignment_id={{ $asm->id }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl border transition group
                        {{ request('assignment_id') == $asm->id
                            ? 'bg-slate-800/60 border-slate-700/60 text-white'
                            : 'border-transparent text-slate-400 sidebar-item-hover hover:text-slate-200' }}">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="flex-shrink-0 w-6 h-6 rounded-lg flex items-center justify-center
                                {{ $asm->type === 'module'
                                    ? 'bg-indigo-500/12 text-indigo-400'
                                    : 'bg-amber-500/12 text-amber-400' }}">
                                <i data-lucide="{{ $asm->type === 'module' ? 'file-text' : 'code-2' }}" class="w-3 h-3"></i>
                            </div>
                            <span class="truncate text-xs font-medium">{{ $asm->title }}</span>
                        </div>
                        <i data-lucide="chevron-right"
                           class="w-3.5 h-3.5 flex-shrink-0 text-slate-700 group-hover:text-slate-500 transition"></i>
                    </a>
                @empty
                    <div class="py-6 text-center">
                        <i data-lucide="package-open" class="w-8 h-8 text-slate-700 mx-auto mb-2"></i>
                        <p class="text-xs text-slate-600">Belum ada modul atau tugas dirilis.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@else
    <div class="flex-1 flex flex-col items-center justify-center text-center p-6 gap-3">
        <div class="w-12 h-12 rounded-2xl bg-slate-800/50 flex items-center justify-center border border-slate-700/40">
            <i data-lucide="panel-right" class="w-6 h-6 text-slate-600"></i>
        </div>
        <p class="text-xs text-slate-600 leading-relaxed max-w-[160px]">
            Pilih praktikum untuk memuat panel instrumen.
        </p>
    </div>
@endif
