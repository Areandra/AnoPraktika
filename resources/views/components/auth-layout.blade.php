<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Authentication' }}</title>

    {{-- Hubungkan Font Inter & Remix Icon untuk mempercantik tampilan --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

{{-- Latar belakang luar diubah menjadi gelap pekat (Cyber Dark Blue) --}}
<body class="min-h-screen bg-[#0b0f19] flex items-center justify-center p-4 antialiased">
    
    {{-- Frame utama diubah dari bg-white menjadi bg-[#111827] dengan border tipis elegan --}}
    <div class="max-w-5xl w-full bg-[#111827] rounded-[28px] border border-gray-800/80 shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[650px]">

        {{-- SISI KIRI: Form Area (Sekarang menyatu sempurna tanpa sekat putih) --}}
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-[#111827]">
            <div class="w-full max-w-sm mx-auto">
                
                {{-- Alert Error Modifikasi Tema Gelap --}}
                @error('custom_message')
                    <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-400 font-mono flex items-center gap-2">
                        <i class="ri-alert-line text-base"></i>
                        {{ $message }}
                    </div>
                @enderror
                
                {{-- Alert Success Modifikasi Tema Gelap --}}
                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-400 font-mono flex items-center gap-2">
                        <i class="ri-checkbox-circle-line text-base"></i>
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Slot Form Login / Register masuk di sini --}}
                {{ $slot }}
                
            </div>
        </div>

        {{-- SISI KANAN: Ilustrasi bertema Premium Dark Gradient --}}
        <div class="hidden md:flex w-1/2 bg-gradient-to-br from-[#1e293b] via-[#0f172a] to-[#020617] items-center justify-center relative overflow-hidden border-l border-gray-800/50">
            
            {{-- Grid Pattern yang Lebih Halus dan Elegan --}}
            <div class="absolute inset-0 opacity-[0.05] bg-[linear-gradient(to_right,#808080_1px,transparent_1px),linear-gradient(to_bottom,#808080_1px,transparent_1px)] bg-[size:24px_24px]"></div>
            
            {{-- Glow Effect di Latar Belakang --}}
            <div class="absolute w-72 h-72 bg-blue-500/10 rounded-full blur-[80px] -top-10 -right-10"></div>
            <div class="absolute w-72 h-72 bg-emerald-500/5 rounded-full blur-[100px] -bottom-10 -left-10"></div>

            {{-- Komponen Logo/Branding di Sisi Bawah --}}
            <div class="absolute p-8 bottom-0 flex items-center gap-2 font-bold text-lg tracking-wide text-white/90">
                <span>AnoPraktika</span>
                <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
            </div>

            {{-- Objek Ilustrasi 3D Sederhana (Sudah Disesuaikan Warnanya) --}}
            <div class="relative z-10 w-56 h-56 bg-gradient-to-tr from-blue-600 to-blue-500 rounded-[32px] shadow-2xl shadow-blue-500/20 rotate-20 flex items-center justify-center border-[8px] border-slate-900/50 group hover:rotate-12 transition-all duration-500 ease-out hover:rotate-6 hover:scale-105">
                
                <div class="w-20 h-20 rounded-full bg-emerald-400 flex items-center justify-center shadow-inner relative">
                    <div class="w-6 h-6 rounded-full bg-slate-950"></div>
                </div>
                
                {{-- Garis X Dekoratif --}}
                <div class="absolute w-1 h-14 bg-white/20 rounded-full left-1/2 top-1/2 -translate-y-1/2 ml-12 rotate-45"></div>
                <div class="absolute w-1 h-14 bg-white/20 rounded-full left-1/2 top-1/2 -translate-y-1/2 ml-12 -rotate-45"></div>
            </div>
            
        </div>

    </div>
</body>

</html>