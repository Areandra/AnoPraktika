{{-- resources/views/components/pdf-canvas.blade.php --}}

<div class="flex-1 overflow-auto flex justify-start items-start relative"
     id="pdf-scroll-container"
     style="background: #060b18; scrollbar-width: thin; scrollbar-color: #1e293b transparent;">

    {{-- Annotation Tooltip --}}
    <div id="annotation-tooltip"
         class="absolute hidden text-xs p-2.5 rounded-xl shadow-2xl z-50 max-w-[200px] pointer-events-none whitespace-pre-wrap font-mono leading-relaxed"
         style="background: rgba(10,16,32,0.95); border: 1px solid rgba(99,102,241,0.25); color: #c7d2fe; backdrop-filter: blur(8px);">
    </div>

    {{-- PDF Loading Overlay --}}
    <div id="pdf-loading-overlay" class="absolute inset-0 z-50 hidden flex-col items-center justify-center" style="background: rgba(6,11,24,0.85); backdrop-filter: blur(4px);">
        <div class="relative w-16 h-16 mb-4">
            <svg class="animate-spin w-full h-full text-slate-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="#6366f1" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span id="pdf-loading-percentage" class="text-[10px] font-bold text-white font-mono">0%</span>
            </div>
        </div>
        <p class="text-xs font-medium text-slate-300 tracking-wide uppercase">Memuat Dokumen</p>
    </div>

    {{-- Continuous Mode Container --}}
    <div id="pdf-continuous-container" class="hidden w-full flex-col gap-6 items-center py-6">
        {{-- Wrappers will be injected here dynamically --}}
    </div>

    {{-- PDF Render Wrapper --}}
    <div id="pdf-render-wrapper"
         class="relative shadow-2xl hidden origin-top-left mx-auto my-6 w-max"
         style="border-radius: 4px; box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05);">
        <canvas id="pdf-canvas" class="block"></canvas>

        <div id="pdf-interactive-layer"
             class="absolute inset-0 z-20 {{ $disableClick ? 'cursor-default' : 'cursor-crosshair' }}"
             @if (!$disableClick) onclick="catchCoordinates(event)" @endif>
        </div>
    </div>

    {{-- Placeholder (before PDF is loaded) --}}
    <div id="pdf-placeholder" class="m-auto flex flex-col items-center justify-center text-center p-10 select-none">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 border border-slate-800/60"
             style="background: rgba(99,102,241,0.06);">
            <i data-lucide="file-search" class="w-8 h-8 text-indigo-500/40" style="animation: pulse 2.5s ease-in-out infinite;"></i>
        </div>
        <p class="text-sm text-slate-600">Klik tombol <span class="text-indigo-400 font-semibold">Periksa Laporan</span></p>
        <p class="text-xs text-slate-700 mt-1">untuk memuat berkas PDF.</p>
    </div>
</div>
