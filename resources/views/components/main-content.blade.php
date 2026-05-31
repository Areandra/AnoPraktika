{{-- components/main-content.blade.php --}}
@if ($viewMode === 'empty')
    <div class="flex-1 flex flex-col items-center justify-center text-center p-8 select-none">
        <div class="relative mb-6">
            <div class="w-20 h-20 rounded-3xl flex items-center justify-center border border-slate-700/30"
                 style="background: linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(59,130,246,0.05) 100%);">
                <i data-lucide="sparkles" class="w-9 h-9 text-indigo-500/60" style="animation: pulse 3s ease-in-out infinite;"></i>
            </div>
            <div class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-indigo-500/20 border border-indigo-500/30 animate-ping"></div>
        </div>
        <h3 class="text-base font-bold text-slate-300 mb-2">Selamat Datang di Workspace</h3>
        <p class="text-sm text-slate-600 max-w-xs leading-relaxed">
            Pilih kelas praktikum di panel kiri, atau klik
            <span class="inline-flex items-center gap-0.5 text-indigo-400 font-mono">
                <i data-lucide="plus" class="w-3 h-3"></i>
            </span>
            untuk membuat atau bergabung ke praktikum baru.
        </p>
    </div>

@elseif($viewMode === 'practicum_selected')
    <div class="flex-1 flex flex-col items-center justify-center text-center p-8 select-none">
        <div class="mb-6 w-20 h-20 rounded-3xl flex items-center justify-center border border-indigo-500/20"
             style="background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(59,130,246,0.06) 100%);">
            <i data-lucide="layout-grid" class="w-9 h-9 text-indigo-400/60"></i>
        </div>
        <h3 class="text-base font-bold text-white mb-2">{{ $activePracticum->name }}</h3>
        <p class="text-sm text-slate-500 max-w-xs leading-relaxed">
            Pilih salah satu modul atau tugas di panel kanan untuk memulai aktivitas.
        </p>
    </div>

@elseif(in_array($viewMode, ['assistant_assignment_review', 'student_assignment_view']))
    <x-pdf-viewer
        :active-practicum="$activePracticum"
        :view-mode="$viewMode"
        :active-assignment="$activeAssignment"
        :center-data="$centerData"
        :role-in-active-practicum="$roleInActivePracticum" />

@elseif($viewMode === 'list_users')
    {{-- Header --}}
    <div class="flex-shrink-0 flex items-center justify-between px-5 py-3.5 bg-[#0d1424] border-b border-slate-800/60">
        <div>
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-indigo-400"></i>
                Daftar Anggota Kelas
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Total {{ count($centerData['users']) }} anggota terdaftar</p>
        </div>
    </div>

    {{-- User List --}}
    <div class="flex-1 overflow-y-auto p-4">
        <div class="space-y-2">
            @foreach ($centerData['users'] as $u)
                @php
                    $colors = ['from-violet-500 to-indigo-600', 'from-blue-500 to-cyan-600', 'from-emerald-500 to-teal-600', 'from-rose-500 to-pink-600', 'from-amber-500 to-orange-600'];
                    $colorClass = $colors[crc32($u->identifier) % count($colors)];
                @endphp
                <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-800/60 bg-slate-900/30 hover:bg-slate-800/30 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br {{ $colorClass }} flex items-center justify-center font-bold text-white text-xs uppercase flex-shrink-0">
                            {{ substr($u->name, 0, 2) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">{{ $u->name }}</h4>
                            <p class="text-xs text-slate-500 font-mono">{{ $u->identifier }}</p>
                        </div>
                    </div>
                    <span class="text-[10px] uppercase font-mono font-bold px-2 py-1 rounded-lg
                        {{ $u->pivot->role === 'assistant'
                            ? 'bg-amber-500/12 text-amber-400 border border-amber-500/20'
                            : 'bg-blue-500/12 text-blue-400 border border-blue-500/20' }}">
                        {{ $u->pivot->role === 'assistant' ? 'Asprak' : 'Mahasiswa' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
@endif
