@if ($viewMode === 'empty')
    <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
        <i class="ri-shining-fill text-4xl text-gray-700 animate-pulse mb-3"></i>
        <h3 class="text-sm font-bold text-gray-400">Selamat Datang di Workspace</h3>
        <p class="text-gray-500 mt-1 max-w-xs">Silakan pilih kelas praktikum di panel kiri atau klik tanda tambah untuk
            membuat/join praktikum baru.</p>
    </div>
@elseif($viewMode === 'practicum_selected')
    <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
        <i class="ri-layout-grid-line text-4xl text-blue-500/30 mb-3"></i>
        <h3 class="text-sm font-bold text-white">{{ $activePracticum->name }}</h3>
        <p class="text-gray-400 mt-1">Silakan pilih salah satu modul praktikum atau tugas di panel kanan untuk memulai
            aktivitas.</p>
    </div>
@elseif(in_array($viewMode, ['assistant_assignment_review', 'student_assignment_view']))
    <x-pdf-viewer :active-practicum="$activePracticum" :view-mode="$viewMode" :active-assignment="$activeAssignment" :center-data="$centerData" :role-in-active-practicum="$roleInActivePracticum" />
@elseif($viewMode === 'list_users')
    <div class="p-4 bg-[#111827] border-b border-gray-800">
        <h2 class="text-sm font-bold text-white">Daftar Anggota Kelas</h2>
        <p class="text-[10px] text-gray-400">Total User Terdaftar Dalam Praktikum</p>
    </div>
    <div class="flex-1 overflow-y-auto p-4 space-y-2">
        @foreach ($centerData['users'] as $u)
            <div class="bg-[#111827] p-3 rounded-xl border border-gray-800 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div
                        class="w-7 h-7 bg-gray-800 text-gray-300 rounded-full flex items-center justify-center font-bold font-mono">
                        {{ substr($u->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-white">{{ $u->name }}</h4>
                        <p class="text-[10px] text-gray-500 font-mono">{{ $u->identifier }}</p>
                    </div>
                </div>
                <span
                    class="text-[9px] uppercase font-mono tracking-wider font-bold px-2 py-0.5 rounded {{ $u->pivot->role === 'assistant' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-gray-800 text-gray-400' }}">
                    {{ $u->pivot->role }}
                </span>
            </div>
        @endforeach
    </div>
@endif
