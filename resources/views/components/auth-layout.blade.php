<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Authentication' }} — AnoPraktika</title>
    <meta name="description" content="AnoPraktika — Platform manajemen praktikum digital berbasis AI">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        body { background: #060b18; }

        .auth-orb-1 {
            position: absolute; width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(99,102,241,0.18) 0%, transparent 70%);
            border-radius: 50%; top: -80px; right: -80px;
            animation: orbFloat1 8s ease-in-out infinite;
        }
        .auth-orb-2 {
            position: absolute; width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(16,185,129,0.12) 0%, transparent 70%);
            border-radius: 50%; bottom: -60px; left: -60px;
            animation: orbFloat2 10s ease-in-out infinite;
        }
        .auth-orb-3 {
            position: absolute; width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%);
            border-radius: 50%; top: 40%; left: 30%;
            animation: orbFloat3 12s ease-in-out infinite;
        }
        @keyframes orbFloat1 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, 20px); }
        }
        @keyframes orbFloat2 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(20px, -15px); }
        }
        @keyframes orbFloat3 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-10px, 10px) scale(1.05); }
        }

        .grid-pattern {
            background-image: linear-gradient(to right, rgba(148,163,184,0.04) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(148,163,184,0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .hero-card {
            background: linear-gradient(135deg, #6366f1 0%, #3b82f6 50%, #06b6d4 100%);
            box-shadow: 0 30px 80px -10px rgba(99,102,241,0.35);
        }
        .hero-card-inner {
            animation: heroFloat 6s ease-in-out infinite;
        }
        @keyframes heroFloat {
            0%, 100% { transform: translateY(0px) rotate(12deg); }
            50% { transform: translateY(-10px) rotate(10deg); }
        }

        .input-field {
            background: rgba(15,23,42,0.6);
            border: 1px solid rgba(51,65,85,0.8);
            color: #f1f5f9;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: rgba(99,102,241,0.6);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        .input-field::placeholder { color: #475569; }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            box-shadow: 0 4px 20px rgba(99,102,241,0.3);
            transition: all 0.2s;
        }
        .btn-primary:hover {
            box-shadow: 0 6px 28px rgba(99,102,241,0.45);
            transform: translateY(-1px);
        }
        .btn-primary:active { transform: translateY(0) scale(0.98); }

        [x-cloak] { display: none !important; }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-[#060b18] flex items-center justify-center p-4 antialiased">

    <div class="max-w-5xl w-full bg-[#0d1424] rounded-[28px] border border-slate-800/60 shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[640px]">

        {{-- LEFT: Form Area --}}
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-[#0d1424]">
            <div class="w-full max-w-sm mx-auto">

                {{-- Logo --}}
                <div class="flex items-center gap-2 mb-8">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                        <i data-lucide="layers" class="w-4 h-4 text-white"></i>
                    </div>
                    <span class="font-bold text-base text-white tracking-tight">AnoPraktika</span>
                </div>

                @error('custom_message')
                    <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/8 px-4 py-3 text-sm text-red-400 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                        {{ $message }}
                    </div>
                @enderror

                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-emerald-500/20 bg-emerald-500/8 px-4 py-3 text-sm text-emerald-400 flex items-center gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
                        {{ session('success') }}
                    </div>
                @endif

                {{ $slot }}

            </div>
        </div>

        {{-- RIGHT: Illustration --}}
        <div class="hidden md:flex w-1/2 relative items-center justify-center overflow-hidden border-l border-slate-800/40"
             style="background: linear-gradient(135deg, #0f1a2e 0%, #0a0f1d 50%, #060b18 100%);">

            <div class="grid-pattern absolute inset-0"></div>
            <div class="auth-orb-1"></div>
            <div class="auth-orb-2"></div>
            <div class="auth-orb-3"></div>

            {{-- Hero Card --}}
            <div class="hero-card-inner relative z-10 flex flex-col items-center gap-6">
                <div class="hero-card w-52 h-52 rounded-[28px] flex items-center justify-center border-4 border-white/10">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center border border-white/20">
                            <i data-lucide="file-check-2" class="w-7 h-7 text-white"></i>
                        </div>
                        <div class="space-y-1.5">
                            <div class="w-28 h-2 bg-white/25 rounded-full"></div>
                            <div class="w-20 h-2 bg-white/15 rounded-full"></div>
                            <div class="w-24 h-2 bg-white/15 rounded-full"></div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <p class="text-white/90 font-semibold text-sm">Platform Praktikum Digital</p>
                    <p class="text-white/40 text-xs mt-1 font-mono">AI-Powered Format Validation</p>
                </div>
            </div>

            {{-- Branding bottom --}}
            <div class="absolute bottom-6 left-0 right-0 flex items-center justify-center gap-2">
                <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></div>
                <span class="text-white/30 text-xs font-mono">v1.0 · AnoPraktika System</span>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
    </script>
</body>
</html>