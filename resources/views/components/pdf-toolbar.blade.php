{{-- components/pdf-toolbar.blade.php --}}
<div id="pdf-toolbar"
     class="hidden flex-shrink-0 flex items-center justify-between px-4 py-2 border-b border-slate-800/60 z-10"
     style="background: #0a1020;">

    {{-- Navigation --}}
    <div class="flex items-center gap-2">
        <button id="btn-page-prev" onclick="changePage(-1)"
            class="w-7 h-7 rounded-lg flex items-center justify-center transition text-slate-400 hover:text-white hover:bg-slate-700/60 border border-slate-700/40">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
        </button>

        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800/50 border border-slate-700/40">
            <i data-lucide="file-text" class="w-3 h-3 text-slate-500"></i>
            <span class="text-xs font-mono text-slate-300">
                Hal 
                <span id="pdf-current-page" contenteditable="true" class="text-white font-bold outline-none focus:ring-0 mx-1 min-w-[24px] inline-block text-center border-b border-transparent focus:border-slate-500 transition-colors" onblur="jumpToPage(this.textContent)" onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}">0</span>
                <span class="text-slate-600">/</span>
                <span id="pdf-total-pages" class="text-slate-400 ml-0.5">0</span>
            </span>
        </div>

        <button id="btn-page-next" onclick="changePage(1)"
            class="w-7 h-7 rounded-lg flex items-center justify-center transition text-slate-400 hover:text-white hover:bg-slate-700/60 border border-slate-700/40">
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </button>

        <div class="h-5 w-px bg-slate-700/60 mx-1"></div>

        <button onclick="toggleContinuousMode()" id="btn-continuous-mode"
            class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800/50 border border-slate-700/40 text-xs font-mono text-slate-400 hover:text-white transition"
            title="Tampilkan Semua Halaman (Scroll Ke Bawah)">
            <i data-lucide="gallery-vertical" class="w-3.5 h-3.5"></i>
            <span class="hidden lg:inline">Mode Gulir</span>
        </button>
    </div>

    {{-- Zoom Controls --}}
    <div class="flex items-center gap-1.5">
        <span class="text-xs text-slate-600 font-mono mr-1">Zoom</span>
        <button onclick="zoomPdf(-0.1)"
            class="w-7 h-7 rounded-lg flex items-center justify-center transition text-slate-400 hover:text-white hover:bg-slate-700/60 border border-slate-700/40">
            <i data-lucide="zoom-out" class="w-4 h-4"></i>
        </button>
        <button onclick="zoomPdf(0.1)"
            class="w-7 h-7 rounded-lg flex items-center justify-center transition text-slate-400 hover:text-white hover:bg-slate-700/60 border border-slate-700/40">
            <i data-lucide="zoom-in" class="w-4 h-4"></i>
        </button>
    </div>
</div>
