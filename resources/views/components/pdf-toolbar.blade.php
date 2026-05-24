<div id="pdf-toolbar"
    class="hidden bg-[#181f32] p-2 border-b border-gray-800 flex items-center justify-between px-4 text-gray-400 font-mono text-[10px] z-10 shadow">
    <div class="flex items-center gap-2">
        <button onclick="changePage(-1)" class="p-1 hover:text-white bg-gray-800 rounded transition">
            <i class="ri-arrow-left-s-line"></i>
        </button>
        <span>Halaman <span id="pdf-current-page" class="text-white font-bold">0</span> / <span
                id="pdf-total-pages">0</span></span>
        <button onclick="changePage(1)" class="p-1 hover:text-white bg-gray-800 rounded transition">
            <i class="ri-arrow-right-s-line"></i>
        </button>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="zoomPdf(0.1)" class="p-1 hover:text-white bg-gray-800 rounded transition">
            <i class="ri-zoom-in-line"></i>
        </button>
        <button onclick="zoomPdf(-0.1)" class="p-1 hover:text-white bg-gray-800 rounded transition">
            <i class="ri-zoom-out-line"></i>
        </button>
    </div>
</div>
