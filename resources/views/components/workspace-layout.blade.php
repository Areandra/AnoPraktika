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
