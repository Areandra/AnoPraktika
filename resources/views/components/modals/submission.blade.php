<div id="submissionModal"
    class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="w-full max-w-md bg-[#151c2c] rounded-2xl border border-gray-800 p-6 shadow-2xl space-y-4">
        <div class="flex justify-between items-center border-b border-gray-800 pb-3">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Kumpul Berkas Submission</h2>
            <button onclick="toggleModal('submissionModal')" class="text-gray-500 hover:text-white">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <form action="{{ route('submissions.store', $activeAssignment->id) }}" method="POST"
            enctype="multipart/form-data" class="space-y-4">
            @csrf
            @if ($activeAssignment->type === 'module')
                <div>
                    <label class="block text-gray-400 mb-1">Berkas Word Laporan (.docx)</label>
                    <input type="file" name="word_file" accept=".docx"
                        class="w-full text-gray-400 bg-[#0f1524] border border-gray-700 rounded-xl p-2" required>
                </div>
                <div>
                    <label class="block text-gray-400 mb-1">Berkas PDF Laporan (.pdf)</label>
                    <input type="file" name="pdf_file" accept=".pdf"
                        class="w-full text-gray-400 bg-[#0f1524] border border-gray-700 rounded-xl p-2" required>
                </div>
            @else
                <div>
                    <label class="block text-gray-400 mb-1">Attachment File (Zip/Markdown/Code)</label>
                    <input type="file" name="attachment_file"
                        class="w-full text-gray-400 bg-[#0f1524] border border-gray-700 rounded-xl p-2" required>
                </div>
            @endif
            <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold p-2.5 rounded-xl transition">
                Kirim Pengumpulan
            </button>
        </form>
    </div>
</div>
