{{-- resources/views/components/validation-modal.blade.php --}}
<div x-data="validationModal" x-show="show" x-transition.opacity.duration.200 @keydown.escape.window="close()"
    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm" @click="close()"></div>

    {{-- Modal --}}
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div x-show="show" x-transition.scale.95.opacity.duration.300 @click.stop
            class="bg-gray-900 rounded-2xl shadow-2xl max-w-3xl w-full border border-gray-700/50 max-h-[90vh] flex flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-700/50">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Detail Validasi Format</h3>
                        <p class="text-sm text-gray-400" x-text="`${totalErrors} pelanggaran terdeteksi`"></p>
                    </div>
                </div>
                <button @click="close()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4">

                {{-- Ringkasan --}}
                <template x-if="hasSummary">
                    <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                        <h4 class="text-white font-medium mb-3">📊 Ringkasan Dokumen</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <template x-for="(value, key) in data.ringkasan" :key="key">
                                <div class="bg-gray-900/50 rounded-lg p-3 text-center">
                                    <p class="text-xs text-gray-400" x-text="key.replace(/_/g, ' ')"></p>
                                    <p class="text-sm text-white font-medium" x-text="value"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Masalah Global --}}
                <template x-if="hasGlobalIssues">
                    <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                        <h4 class="text-white font-medium mb-3">📐 Masalah Global</h4>
                        <div class="space-y-2">
                            <template x-for="issue in data.masalah_global" :key="issue">
                                <div
                                    class="flex gap-2 items-start bg-red-500/5 rounded-lg p-3 border border-red-500/10">
                                    <span class="text-red-400">⚠️</span>
                                    <span class="text-sm text-red-300" x-text="issue"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Detail Pelanggaran --}}
                <template x-if="hasDetails">
                    <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                        <h4 class="text-white font-medium mb-3">
                            🔍 Detail Pelanggaran
                            <span class="text-xs bg-red-500/20 text-red-300 px-2 py-0.5 rounded-full"
                                x-text="data.detail_pelanggaran.length"></span>
                        </h4>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <template x-for="(error, index) in data.detail_pelanggaran" :key="index">
                                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-700/30">
                                    <div class="flex items-start gap-3">
                                        <span class="bg-red-500/20 text-red-300 px-2 py-1 rounded text-xs font-bold"
                                            x-text="'#' + (index + 1)"></span>
                                        <div class="flex-1 space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span>📍</span>
                                                <span class="text-white font-medium text-sm"
                                                    x-text="error.location || 'Tidak diketahui'"></span>
                                            </div>
                                            <span class="text-xs bg-red-500/10 text-red-300 px-2 py-0.5 rounded"
                                                x-text="error.type"></span>

                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="bg-red-500/5 rounded p-2">
                                                    <p class="text-xs text-red-300/70">❌ Ditemukan</p>
                                                    <p class="text-sm text-red-300 font-medium" x-text="error.actual">
                                                    </p>
                                                </div>
                                                <div class="bg-green-500/5 rounded p-2">
                                                    <p class="text-xs text-green-300/70">✅ Seharusnya</p>
                                                    <p class="text-sm text-green-300 font-medium"
                                                        x-text="error.expected"></p>
                                                </div>
                                            </div>

                                            <template x-if="error.text_preview">
                                                <div class="bg-gray-800 rounded p-2 border-l-2 border-red-500/50">
                                                    <p class="text-xs text-gray-400">📝 Teks:</p>
                                                    <p class="text-sm text-gray-300 italic"
                                                        x-text="'\"' + error.text_preview + '\"'"></p>
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
                    <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl p-4">
                        <div class="flex gap-2 items-start">
                            <span>💡</span>
                            <div>
                                <p class="text-amber-300 font-medium text-sm mb-1">Rekomendasi</p>
                                <p class="text-amber-200/80 text-sm" x-text="data.rekomendasi"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="border-t border-gray-700/50 p-4 flex justify-end">
                <button @click="close()"
                    class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
