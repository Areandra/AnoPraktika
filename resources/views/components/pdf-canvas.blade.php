{{-- resources/views/components/pdf-canvas.blade.php --}}

<div class="flex-1 overflow-auto scrollbar-thin scrollbar-thumb-slate-500 flex justify-start items-start shadow-inner relative"
    id="pdf-scroll-container">
    {{-- Tooltip anotasi --}}
    <div id="annotation-tooltip"
        class="absolute hidden bg-gray-900 text-white text-[11px] p-2 rounded shadow-lg z-50 max-w-[200px] pointer-events-none whitespace-pre-wrap">
    </div>

    <div id="pdf-render-wrapper"
        class="relative bg-white shadow-2xl rounded border border-gray-700 hidden origin-top-left">
        <canvas id="pdf-canvas" class="block"></canvas>

        <div id="pdf-interactive-layer"
            class="absolute inset-0 z-20 {{ $disableClick ? 'cursor-default' : 'cursor-crosshair' }}"
            @if (!$disableClick) onclick="catchCoordinates(event)" @endif>
        </div>
    </div>

    <div id="pdf-placeholder" class="m-auto text-center p-8 text-gray-600">
        <i class="ri-file-pdf-line text-4xl block mb-2 opacity-40 animate-pulse"></i>
        <p>Klik tombol <b class="text-gray-400">"Periksa Laporan"</b> untuk memuat berkas.</p>
    </div>
</div>
