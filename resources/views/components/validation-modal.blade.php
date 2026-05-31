{{-- resources/views/components/validation-modal.blade.php --}}
<div x-data="validationModal" x-show="show" x-transition.opacity.duration.200 @keydown.escape.window="close()"
    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

    {{-- Overlay --}}
    <div class="fixed inset-0" style="background: rgba(6,11,24,0.85); backdrop-filter: blur(8px);" @click="close()"></div>

    {{-- Modal --}}
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div x-show="show" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-1 scale-100"
             @click.stop
             class="relative w-full max-w-3xl rounded-2xl border border-slate-700/40 shadow-2xl max-h-[90vh] flex flex-col"
             style="background: #0d1424;">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/60 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                         style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.2);">
                        <i data-lucide="shield-alert" class="w-5 h-5 text-red-400"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Detail Validasi Format</h3>
                        <p class="text-xs text-slate-500" x-text="`${totalErrors} pelanggaran terdeteksi`"></p>
                    </div>
                </div>
                <button @click="close()"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-700/50 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4">

                {{-- Ringkasan Dokumen --}}
                <template x-if="hasSummary">
                    <div class="rounded-xl border border-slate-800/60 overflow-hidden" style="background: rgba(6,11,24,0.5);">
                        <div class="flex items-center gap-2 px-4 py-2.5 border-b border-slate-800/60">
                            <i data-lucide="bar-chart-2" class="w-3.5 h-3.5 text-indigo-400"></i>
                            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Ringkasan Dokumen</h4>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4">
                            <template x-for="(value, key) in data.ringkasan" :key="key">
                                <div class="rounded-xl p-3 text-center border border-slate-800/40"
                                     style="background: rgba(13,20,36,0.8);">
                                    <p class="text-[10px] text-slate-500 uppercase tracking-wide mb-1" x-text="key.replace(/_/g, ' ')"></p>
                                    <p class="text-sm text-white font-semibold font-mono" x-text="value"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Masalah Global --}}
                <template x-if="hasGlobalIssues">
                    <div class="rounded-xl border border-red-500/15 overflow-hidden" style="background: rgba(127,29,29,0.06);">
                        <div class="flex items-center gap-2 px-4 py-2.5 border-b border-red-500/10">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-red-400"></i>
                            <h4 class="text-xs font-bold text-red-400 uppercase tracking-wider">Masalah Global</h4>
                        </div>
                        <div class="p-3 space-y-2">
                            <template x-for="issue in data.masalah_global" :key="issue">
                                <div class="flex gap-2.5 items-start rounded-xl p-3 border border-red-500/10"
                                     style="background: rgba(239,68,68,0.05);">
                                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-400 flex-shrink-0 mt-0.5"></i>
                                    <span class="text-sm text-red-300" x-text="issue"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Detail Pelanggaran --}}
                <template x-if="hasDetails">
                    <div class="rounded-xl border border-slate-800/60 overflow-hidden" style="background: rgba(6,11,24,0.5);">
                        <div class="flex items-center justify-between px-4 py-2.5 border-b border-slate-800/60">
                            <div class="flex items-center gap-2">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400"></i>
                                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Detail Pelanggaran</h4>
                            </div>
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-red-500/12 text-red-400 border border-red-500/20"
                                  x-text="data.detail_pelanggaran.length + ' item'"></span>
                        </div>
                        <div class="p-3 space-y-2 max-h-80 overflow-y-auto">
                            <template x-for="(error, index) in data.detail_pelanggaran" :key="index">
                                <div class="rounded-xl p-3.5 border border-slate-700/30" style="background: rgba(13,20,36,0.8);">
                                    <div class="flex items-start gap-3">
                                        <span class="flex-shrink-0 font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-red-500/15 text-red-400 border border-red-500/20"
                                              x-text="'#' + (index + 1)"></span>
                                        <div class="flex-1 space-y-2">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-sm font-semibold text-white"
                                                      x-text="error.location || 'Tidak diketahui'"></span>
                                                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700/40"
                                                      x-text="error.type"></span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="rounded-lg p-2.5 border border-red-500/15"
                                                     style="background: rgba(239,68,68,0.05);">
                                                    <p class="text-[10px] text-red-400/60 mb-0.5 flex items-center gap-1">
                                                        <i data-lucide="x-circle" class="w-3 h-3"></i> Ditemukan
                                                    </p>
                                                    <p class="text-xs text-red-300 font-semibold font-mono" x-text="error.actual"></p>
                                                </div>
                                                <div class="rounded-lg p-2.5 border border-emerald-500/15"
                                                     style="background: rgba(16,185,129,0.05);">
                                                    <p class="text-[10px] text-emerald-400/60 mb-0.5 flex items-center gap-1">
                                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Seharusnya
                                                    </p>
                                                    <p class="text-xs text-emerald-300 font-semibold font-mono" x-text="error.expected"></p>
                                                </div>
                                            </div>
                                            <template x-if="error.text_preview">
                                                <div class="rounded-lg p-2.5 border-l-2 border-slate-600"
                                                     style="background: rgba(30,41,59,0.5);">
                                                    <p class="text-[10px] text-slate-500 mb-0.5 flex items-center gap-1">
                                                        <i data-lucide="text" class="w-3 h-3"></i> Teks
                                                    </p>
                                                    <p class="text-xs text-slate-300 italic font-mono"
                                                       x-text="'&quot;' + error.text_preview + '&quot;'"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Rekomendasi --}}
                <template x-if="hasRecommendation">
                    <div class="rounded-xl border border-amber-500/20 overflow-hidden"
                         style="background: rgba(120,53,15,0.08);">
                        <div class="flex items-center gap-2 px-4 py-2.5 border-b border-amber-500/10">
                            <i data-lucide="lightbulb" class="w-3.5 h-3.5 text-amber-400"></i>
                            <h4 class="text-xs font-bold text-amber-400 uppercase tracking-wider">Rekomendasi</h4>
                        </div>
                        <p class="text-sm text-amber-200/80 leading-relaxed p-4" x-text="data.rekomendasi"></p>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 border-t border-slate-800/60 px-6 py-4 flex justify-end">
                <button @click="close()"
                    class="flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-300 rounded-xl border border-slate-700/60 hover:bg-slate-700/40 hover:text-white transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
