{{-- components/main-content.blade.php --}}
@if ($viewMode === 'empty')
    <div class="flex-1 flex flex-col items-center justify-center text-center p-8 select-none">
        <div class="relative mb-6">
            <div class="w-20 h-20 rounded-3xl flex items-center justify-center border border-slate-700/30"
                style="background: linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(59,130,246,0.05) 100%);">
                <i data-lucide="sparkles" class="w-9 h-9 text-indigo-500/60"
                    style="animation: pulse 3s ease-in-out infinite;"></i>
            </div>
            <div
                class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-indigo-500/20 border border-indigo-500/30 animate-ping">
            </div>
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
    <x-pdf-viewer :active-practicum="$activePracticum" :view-mode="$viewMode" :active-assignment="$activeAssignment" :center-data="$centerData" :role-in-active-practicum="$roleInActivePracticum" />
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
                    $colors = [
                        'from-violet-500 to-indigo-600',
                        'from-blue-500 to-cyan-600',
                        'from-emerald-500 to-teal-600',
                        'from-rose-500 to-pink-600',
                        'from-amber-500 to-orange-600',
                    ];
                    $colorClass = $colors[crc32($u->identifier) % count($colors)];
                    // Cek status user ini dari tabel pivot
                    $status = $u->pivot->status ?? 'joined';
                @endphp

                <div
                    class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-800/60 bg-slate-900/30 hover:bg-slate-800/30 transition">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-xl bg-gradient-to-br {{ $colorClass }} flex items-center justify-center font-bold text-white text-xs uppercase flex-shrink-0">
                            {{ substr($u->name, 0, 2) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">{{ $u->name }}</h4>
                            <p class="text-xs text-slate-500 font-mono">{{ $u->identifier }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Badge Role & Status --}}
                        @if ($status === 'request')
                            <span
                                class="text-[10px] uppercase font-mono font-bold px-2 py-1 rounded-lg bg-yellow-500/12 text-yellow-400 border border-yellow-500/20">
                                Pending
                            </span>
                        @else
                            <span
                                class="text-[10px] uppercase font-mono font-bold px-2 py-1 rounded-lg {{ $u->pivot->role === 'assistant' ? 'bg-amber-500/12 text-amber-400 border border-amber-500/20' : 'bg-blue-500/12 text-blue-400 border border-blue-500/20' }}">
                                {{ $u->pivot->role === 'assistant' ? 'Asprak' : 'Mahasiswa' }}
                            </span>
                        @endif

                        {{-- Tombol Action: Tampil jika user yang sedang login bukan dirinya sendiri (dan opsional: jika login sebagai asprak) --}}
                        @if (Auth::id() !== $u->id)
                            <div class="flex items-center gap-1 border-l border-slate-700/50 pl-3 ml-1">
                                {{-- Tombol Terima (Hanya muncul jika status masih request) --}}
                                @if ($status === 'request')
                                    <form
                                        action="{{ route('practicum.accept', ['practicum_id' => $activePracticum['id'], 'user_id' => $u->id]) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 transition-colors"
                                            title="Terima">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2.5"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                {{-- Tombol Kick/Tolak --}}
                                <form
                                    action="{{ route('practicum.kick', ['practicum_id' => $activePracticum['id'], 'user_id' => $u->id]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin mengeluarkan/menolak mahasiswa ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/20 transition-colors"
                                        title="{{ $status === 'request' ? 'Tolak' : 'Kick' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
