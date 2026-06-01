{{-- components/sidebar-left.blade.php --}}
<div class="flex flex-col h-full">

    @php
        $parts = preg_split('/\s+/', trim($user->name));
    @endphp

    {{-- Top: User Info --}}
    <div class="p-4 border-b border-slate-800/60">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/30 border border-slate-700/30">
            <div class="relative flex-shrink-0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm uppercase"
                    style="background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%); box-shadow: 0 4px 12px rgba(99,102,241,0.3);">
                    {{ count($parts) >= 2
                        ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                        : strtoupper(substr($parts[0] ?? '', 0, 2)) }}
                </div>
                <div
                    class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-400 border-2 border-[#0d1424] rounded-full">
                </div>
            </div>
            <div class="overflow-hidden min-w-0">
                <h4 class="font-semibold text-white truncate text-sm">{{ $user->name }}</h4>
                <p class="text-xs text-slate-500 font-mono truncate">{{ $user->identifier }}</p>
            </div>
        </div>
    </div>

    {{-- Middle: Practicum List --}}
    <div class="flex-1 overflow-y-auto p-4">

        {{-- Section Header --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-1.5">
                <i data-lucide="layout-list" class="w-3 h-3 text-slate-500"></i>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Praktikum</span>
            </div>
            <button onclick="toggleModal('practicumModal')"
                class="w-6 h-6 rounded-lg flex items-center justify-center transition"
                style="background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.25);"
                onmouseover="this.style.background='rgba(99,102,241,0.3)'"
                onmouseout="this.style.background='rgba(99,102,241,0.15)'">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-indigo-400"></i>
            </button>
        </div>

        <nav class="space-y-1">
            @forelse($myPracticums as $p)
                <a href="?practicum_id={{ $p->id }}"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl border transition group
                    {{ request('practicum_id') == $p->id
                        ? 'sidebar-item-active text-white'
                        : 'border-transparent text-slate-400 sidebar-item-hover hover:text-slate-200' }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <i data-lucide="folder-open"
                            class="w-4 h-4 flex-shrink-0 transition
                           {{ request('practicum_id') == $p->id ? 'text-indigo-400' : 'text-slate-600 group-hover:text-slate-400' }}"></i>
                        <span class="truncate text-xs font-medium">{{ $p->name }}</span>
                    </div>
                    <span
                        class="flex-shrink-0 text-[9px] px-1.5 py-0.5 rounded-md font-mono font-semibold uppercase tracking-wide
                        {{ $p->pivot->role === 'assistant'
                            ? 'bg-amber-500/12 text-amber-400 border border-amber-500/20'
                            : 'bg-blue-500/12 text-blue-400 border border-blue-500/20' }}">
                        {{ $p->pivot->role === 'assistant' ? 'Asprak' : 'Mhs' }}
                    </span>
                </a>
            @empty
                <div class="py-6 text-center">
                    <i data-lucide="inbox" class="w-8 h-8 text-slate-700 mx-auto mb-2"></i>
                    <p class="text-xs text-slate-600">Belum ada praktikum.</p>
                    <p class="text-xs text-slate-700 mt-0.5">Klik <span class="text-indigo-500">+</span> untuk
                        bergabung.</p>
                </div>
            @endforelse
        </nav>
    </div>

    {{-- Bottom: Logout --}}
    <div class="p-4 border-t border-slate-800/60">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-semibold transition text-red-400 border border-red-500/15 bg-red-500/6 hover:bg-red-500/15 hover:border-red-500/30 hover:text-red-300">
                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                Keluar Sesi
            </button>
        </form>
    </div>
</div>
