<div class="p-4">
    <div class="flex items-center gap-3 bg-[#1f2937]/50 p-3 rounded-xl border border-gray-800/80 mb-6">
        <div
            class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold uppercase">
            {{ substr($user->name, 0, 2) }}
        </div>
        <div class="overflow-hidden">
            <h4 class="font-bold text-white truncate">{{ $user->name }}</h4>
            <p class="text-[10px] text-gray-400 font-mono truncate">{{ $user->identifier }}</p>
        </div>
    </div>

    <div
        class="flex items-center justify-between text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-3 px-1">
        <span>Daftar Praktikum</span>
        <button onclick="toggleModal('practicumModal')"
            class="w-5 h-5 bg-blue-600/20 hover:bg-blue-600 text-blue-400 hover:text-white rounded flex items-center justify-center transition">
            <i class="ri-add-line text-sm"></i>
        </button>
    </div>

    <nav class="space-y-1 overflow-y-auto max-h-[60vh]">
        @forelse($myPracticums as $p)
            <a href="?practicum_id={{ $p->id }}"
                class="flex items-center justify-between p-3 rounded-xl border border-transparent transition {{ request('practicum_id') == $p->id ? 'bg-blue-600/10 border-blue-500/30 text-white font-semibold' : 'hover:bg-[#1f2937]/40 text-gray-400' }}">
                <div class="flex items-center gap-2.5 truncate">
                    <i
                        class="ri-folder-open-fill {{ request('practicum_id') == $p->id ? 'text-blue-400' : 'text-gray-500' }}"></i>
                    <span class="truncate">{{ $p->name }}</span>
                </div>
                <span
                    class="text-[9px] px-1.5 py-0.5 rounded font-mono uppercase {{ $p->pivot->role === 'assistant' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-gray-800 text-gray-400' }}">
                    {{ $p->pivot->role }}
                </span>
            </a>
        @empty
            <p class="text-gray-500 text-[11px] px-1 py-4">Belum bergabung dengan praktikum manapun.</p>
        @endforelse
    </nav>
</div>

<div class="p-4 border-t border-gray-800">
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
            class="w-full bg-red-500/10 hover:bg-red-600 border border-red-500/20 hover:border-transparent text-red-400 hover:text-white p-2.5 rounded-xl transition flex items-center justify-center gap-2 font-medium">
            <i class="ri-logout-box-r-line"></i> Keluar Sesi
        </button>
    </form>
</div>
