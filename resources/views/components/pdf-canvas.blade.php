{{-- resources/views/components/pdf-canvas.blade.php --}}

<div class="flex-1 overflow-auto flex justify-start items-start relative"
     id="pdf-scroll-container"
     style="background: #060b18; scrollbar-width: thin; scrollbar-color: #1e293b transparent;">

    {{-- Annotation Tooltip --}}
    <div id="annotation-tooltip"
         class="absolute hidden text-xs p-2.5 rounded-xl shadow-2xl z-50 max-w-[200px] pointer-events-none whitespace-pre-wrap font-mono leading-relaxed"
         style="background: rgba(10,16,32,0.95); border: 1px solid rgba(99,102,241,0.25); color: #c7d2fe; backdrop-filter: blur(8px);">
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
