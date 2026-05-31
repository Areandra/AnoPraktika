<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnoPraktika — Workspace</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- PDF.js --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf_viewer.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    {{-- Prism.js Syntax Highlighting --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>

    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono, code, pre { font-family: 'JetBrains Mono', monospace; }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }

        .sidebar-item-active {
            background: linear-gradient(135deg, rgba(99,102,241,0.12) 0%, rgba(59,130,246,0.08) 100%);
            border-left: 2px solid #6366f1;
        }
        .sidebar-item-hover:hover {
            background: rgba(30,41,59,0.5);
        }
        .glass-panel {
            background: rgba(13,20,36,0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .toast-slide {
            animation: toastIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(100%) scale(0.9); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }
        .toast-out {
            animation: toastOut 0.3s ease-in forwards;
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0) scale(1); }
            to   { opacity: 0; transform: translateX(100%) scale(0.9); }
        }
        [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
</head>

<body class="bg-[#060b18] text-slate-200 h-screen overflow-hidden flex flex-col lg:flex-row" style="font-size: 13px;"
      x-data="{ mobileLeftOpen: false, mobileRightOpen: false }">

    {{-- Toast Notifications --}}
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none" style="max-width: 360px;">
        @if (session('success'))
            <div class="toast-slide pointer-events-auto flex items-center gap-3 rounded-xl border border-emerald-500/25 bg-[#0a1f14]/90 backdrop-blur px-4 py-3 text-emerald-400 shadow-2xl shadow-black/40">
                <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-emerald-500/15 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="toast-slide pointer-events-auto flex flex-col gap-1.5 rounded-xl border border-red-500/25 bg-[#1a0a0a]/90 backdrop-blur px-4 py-3 text-red-400 shadow-2xl shadow-black/40">
                <div class="flex items-center gap-2 font-semibold text-sm">
                    <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-red-500/15 flex items-center justify-center">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    </div>
                    Terdapat kesalahan:
                </div>
                <ul class="pl-9 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li class="text-xs text-red-300/80 font-mono">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- MOBILE TOP BAR (Visible only on < lg) --}}
    <div class="lg:hidden flex-shrink-0 flex items-center justify-between px-4 py-3 bg-[#0d1424] border-b border-slate-800/60 z-30">
        <button @click="mobileLeftOpen = true" class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition focus:outline-none">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                <i data-lucide="layers" class="w-3 h-3 text-white"></i>
            </div>
            <span class="font-bold text-sm text-white tracking-tight">AnoPraktika</span>
        </div>

        <button @click="mobileRightOpen = true" class="p-2 -mr-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition focus:outline-none">
            <i data-lucide="info" class="w-5 h-5"></i>
        </button>
    </div>

    {{-- LEFT SIDEBAR BACKDROP (Mobile) --}}
    <div x-show="mobileLeftOpen"
         x-transition.opacity
         @click="mobileLeftOpen = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
         x-cloak></div>

    {{-- LEFT SIDEBAR --}}
    <div :class="mobileLeftOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
         class="fixed lg:static inset-y-0 left-0 z-50 w-64 lg:w-60 bg-[#0d1424] border-r border-slate-800/60 flex flex-col flex-shrink-0 transform transition-transform duration-300 ease-in-out">
        
        {{-- Mobile close button inside sidebar --}}
        <div class="lg:hidden absolute top-3 right-3">
            <button @click="mobileLeftOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white bg-slate-800/50 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{ $sidebarLeft ?? '' }}
    </div>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 bg-[#080e1c] flex flex-col overflow-hidden relative min-w-0 z-10">
        {{ $mainContent ?? '' }}
    </div>

    {{-- RIGHT SIDEBAR BACKDROP (Mobile) --}}
    <div x-show="mobileRightOpen"
         x-transition.opacity
         @click="mobileRightOpen = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
         x-cloak></div>

    {{-- RIGHT SIDEBAR --}}
    <div :class="mobileRightOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
         class="fixed lg:static inset-y-0 right-0 z-50 w-72 bg-[#0d1424] border-l border-slate-800/60 flex flex-col flex-shrink-0 transform transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none">
        
        {{-- Mobile close button inside sidebar --}}
        <div class="lg:hidden absolute top-3 right-3 z-10">
            <button @click="mobileRightOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white bg-slate-800/50 transition border border-slate-700">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>

        {{ $sidebarRight ?? '' }}
    </div>

    {{-- Modals --}}
    {{ $modals ?? '' }}

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('validationModal', () => ({
                show: false,
                data: null,

                open(logs) {
                    this.data = typeof logs === 'string' ? JSON.parse(logs) : logs;
                    this.show = true;
                    document.body.style.overflow = 'hidden';
                },

                close() {
                    this.show = false;
                    this.data = null;
                    document.body.style.overflow = '';
                },

                get totalErrors() {
                    if (!this.data) return 0;
                    return this.data.total_pelanggaran ||
                        (this.data.detail_pelanggaran?.length || 0);
                },

                get hasSummary() {
                    return this.data?.ringkasan && Object.keys(this.data.ringkasan).length > 0;
                },

                get hasGlobalIssues() {
                    return this.data?.masalah_global?.length > 0;
                },

                get hasDetails() {
                    return this.data?.detail_pelanggaran?.length > 0;
                },

                get hasRecommendation() {
                    return !!this.data?.rekomendasi;
                }
            }));
        });

        // Init Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            // Auto-dismiss toasts
            document.querySelectorAll('.toast-slide').forEach(el => {
                setTimeout(() => {
                    el.classList.add('toast-out');
                    setTimeout(() => el.remove(), 300);
                }, 4000);
            });
        });
    </script>

    @stack('scripts')

</body>
</html>
