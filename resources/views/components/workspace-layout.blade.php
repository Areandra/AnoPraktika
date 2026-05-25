<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ano Praktika - Workspace</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    @stack('styles')
</head>

<body class="bg-[#0b0f19] text-gray-200 min-h-screen flex overflow-hidden font-sans text-xs">
    {{-- BLOK RESPONS STATUS SERVER (Gaya Dark Mode) --}}
    <div class="absolute top-4 left-4 right-4 z-50 pointer-events-none flex flex-col gap-2 max-w-md ml-auto">
        @if (session('success'))
            <div
                class="pointer-events-auto flex items-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-950/80 px-4 py-3 text-emerald-400 shadow-lg backdrop-blur-md">
                <i class="ri-checkbox-circle-line text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div
                class="pointer-events-auto flex flex-col gap-1 rounded-xl border border-rose-500/30 bg-rose-950/80 px-4 py-3 text-rose-400 shadow-lg backdrop-blur-md">
                <div class="flex items-center gap-2 font-bold">
                    <i class="ri-error-warning-line text-sm"></i>
                    <span>Terdapat kesalahan pengisian data:</span>
                </div>
                <ul class="list-disc list-inside pl-2 text-[11px] opacity-90">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Sidebar Kiri --}}
    <div class="w-64 bg-[#111827] border-r border-gray-800 flex flex-col justify-between">
        {{ $sidebarLeft ?? '' }}
    </div>

    {{-- Konten Utama --}}
    <div class="flex-1 bg-[#0f172a] flex flex-col justify-between overflow-hidden relative">
        {{ $mainContent ?? '' }}
    </div>

    {{-- Sidebar Kanan --}}
    <div class="w-80 bg-[#111827] border-l border-gray-800 flex flex-col justify-between p-4">
        {{ $sidebarRight ?? '' }}
    </div>

    {{-- Modals --}}
    {{ $modals ?? '' }}

    @stack('scripts')

</body>

</html>
