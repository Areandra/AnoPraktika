<x-workspace-layout>

    <x-slot:sidebarLeft>
        <x-sidebar-left :user="Auth::user()" :my-practicums="$myPracticums" :available-practicums="$availablePracticums" />
    </x-slot:sidebarLeft>

    <x-slot:mainContent>
        <x-main-content :view-mode="$viewMode" :active-practicum="$activePracticum" :active-assignment="$activeAssignment" :center-data="$centerData" :role-in-active-practicum="$roleInActivePracticum" />
    </x-slot:mainContent>

    <x-slot:sidebarRight>
        <x-sidebar-right :active-practicum="$activePracticum" :role-in-active-practicum="$roleInActivePracticum" />
    </x-slot:sidebarRight>

    <x-slot:modals>
        <x-modals.practicum :available-practicums="$availablePracticums" />

        @if ($activePracticum && isset($roleInActivePracticum) && $roleInActivePracticum === 'assistant')
            <x-modals.assignment :active-practicum="$activePracticum" />
        @endif

        @if ($activeAssignment && isset($roleInActivePracticum) && $roleInActivePracticum === 'student')
            <x-modals.submission :active-assignment="$activeAssignment" />
        @endif
    </x-slot:modals>

    @push('scripts')
        <script>
            function toggleModal(modalId) {
                document.getElementById(modalId)?.classList.toggle('hidden');
            }

            // ==================== GLOBAL STATE ====================
            let currentPdfDoc = null;
            let currentPdfPage = 1;
            let currentPdfScale = 1;
            let currentSubmissionId = null;
            let activeAnnotations = []; // { id, page, x, y, comment }
            let annotationCounter = 0;

            const pdfCanvas = document.getElementById('pdf-canvas');
            const pdfContext = pdfCanvas?.getContext('2d');

            // Untuk modal komentar
            let pendingAnnotation = null; // simpan koordinat sementara sebelum komentar diisi

            // ==================== LOAD PDF ====================
            function loadPdfViewer(pdfUrl, submissionId, savedAnnotations, savedNotesBase64) {
                currentSubmissionId = submissionId;
                currentPdfPage = 1;
                activeAnnotations = savedAnnotations || [];
                annotationCounter = activeAnnotations.length > 0 ? Math.max(...activeAnnotations.map(a => a.id)) : 0;

                document.getElementById('annotation_coordinates').value = JSON.stringify(activeAnnotations);
                document.getElementById('assistant_notes').value = savedNotesBase64 ? decodeURIComponent(escape(atob(
                    savedNotesBase64))) : '';

                showPdfUI();
                pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc => {
                    currentPdfDoc = pdfDoc;
                    document.getElementById('pdf-total-pages').textContent = pdfDoc.numPages;
                    renderPage(currentPdfPage);
                }).catch(err => {
                    console.error(err);
                    alert("Gagal membaca PDF.");
                });
            }

            function loadPdfViewerStudent(pdfUrl, savedAnnotations, savedNotesBase64) {
                currentPdfPage = 1;
                activeAnnotations = savedAnnotations || [];
                showPdfUI();

                const notesPanel = document.getElementById('student-notes-panel');
                const notesContent = document.getElementById('student-notes-content');
                if (notesPanel && notesContent) {
                    if (savedNotesBase64) {
                        notesContent.innerText = decodeURIComponent(escape(atob(savedNotesBase64))) || "Tidak ada catatan.";
                    } else {
                        notesContent.innerText = "Belum ada catatan koreksi.";
                    }
                    notesPanel.classList.remove('hidden');
                }

                pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc => {
                    currentPdfDoc = pdfDoc;
                    document.getElementById('pdf-total-pages').textContent = pdfDoc.numPages;
                    renderPage(currentPdfPage);
                }).catch(err => {
                    console.error(err);
                    alert("Gagal membaca PDF.");
                });
            }

            function showPdfUI() {
                document.getElementById('pdf-placeholder').classList.add('hidden');
                document.getElementById('pdf-toolbar').classList.remove('hidden');
                document.getElementById('pdf-render-wrapper').classList.remove('hidden');
                const panel = document.getElementById('annotation-panel');
                const submissionList = document.getElementById('submission-list');
                if (panel) panel.classList.remove('hidden');
                const form = document.getElementById('form-review-asprak');
                if (form) form.action = `/submissions/${currentSubmissionId}/review`;
            }

            // ==================== RENDER HALAMAN ====================
            function renderPage(num) {
                if (!currentPdfDoc) return;
                currentPdfDoc.getPage(num).then(page => {
                    const viewport = page.getViewport({
                        scale: currentPdfScale
                    });
                    pdfCanvas.height = viewport.height;
                    pdfCanvas.width = viewport.width;
                    document.getElementById('pdf-render-wrapper').style.width = viewport.width + 'px';
                    document.getElementById('pdf-render-wrapper').style.height = viewport.height + 'px';

                    page.render({
                        canvasContext: pdfContext,
                        viewport
                    }).promise.then(() => {
                        document.getElementById('pdf-current-page').textContent = num;
                        const indicator = document.getElementById('target-page-indicator');
                        if (indicator) indicator.textContent = 'Halaman ' + num;
                        clearMarkers();
                        drawMarkersForPage(num);
                        renderCoordinatesLog();
                    });
                });
            }

            function changePage(offset) {
                if (!currentPdfDoc) return;
                const next = currentPdfPage + offset;
                if (next >= 1 && next <= currentPdfDoc.numPages) {
                    currentPdfPage = next;
                    renderPage(currentPdfPage);
                }
            }

            function zoomPdf(amount) {
                currentPdfScale = Math.max(0.8, Math.min(2.5, currentPdfScale + amount));
                document.getElementById('pdf-render-wrapper').style.transform = `scale(${currentPdfScale})`;

                // renderPage(currentPdfPage);
            }

            // ==================== ANOTASI (HANYA ASISTEN) ====================
            function catchCoordinates(event) {
                const layer = document.getElementById('pdf-interactive-layer');
                if (!layer) return;
                const rect = layer.getBoundingClientRect();
                const x = Math.round(event.clientX - rect.left);
                const y = Math.round(event.clientY - rect.top);

                annotationCounter++;
                // Simpan sementara
                pendingAnnotation = {
                    id: annotationCounter,
                    page: currentPdfPage,
                    x: x,
                    y: y,
                    comment: ''
                };

                // Tampilkan modal
                document.getElementById('comment-coord-info').innerText =
                    `X: ${x}, Y: ${y} | Hal ${currentPdfPage}`;
                document.getElementById('comment-text').value = '';
                document.getElementById('commentModal').classList.remove('hidden');
            }

            function closeCommentModal() {
                document.getElementById('commentModal').classList.add('hidden');
                pendingAnnotation = null;
            }

            // Pasang event listener untuk tombol simpan komentar
            document.getElementById('save-comment-btn')?.addEventListener('click', function() {
                if (!pendingAnnotation) return;
                pendingAnnotation.comment = document.getElementById('comment-text').value.trim() ||
                    'Tanpa komentar';
                activeAnnotations.push(pendingAnnotation);

                // Perbarui input hidden dan visual
                document.getElementById('annotation_coordinates').value = JSON.stringify(activeAnnotations);
                renderCoordinatesLog();
                createMarker(pendingAnnotation);
                closeCommentModal();
            });

            function createMarker(ann) {
                const layer = document.getElementById('pdf-interactive-layer');
                if (!layer || ann.page !== currentPdfPage) return;

                const marker = document.createElement('div');
                marker.className =
                    "absolute w-5 h-5 bg-red-500 text-white font-mono font-bold text-[9px] rounded-full flex items-center justify-center shadow-lg border border-white transform -translate-x-1/2 -translate-y-1/2 z-30 cursor-pointer transition hover:scale-110";
                marker.style.left = ann.x + 'px';
                marker.style.top = ann.y + 'px';
                marker.innerText = ann.id;
                marker.setAttribute('data-marker-id', ann.id);
                marker.setAttribute('data-comment', ann.comment || 'Tidak ada komentar');

                // Tooltip
                marker.addEventListener('mouseenter', showTooltip);
                marker.addEventListener('mouseleave', hideTooltip);

                // Klik untuk hapus
                marker.addEventListener('click', function(e) {
                    e.stopPropagation();
                    removeAnnotation(ann.id);
                });

                layer.appendChild(marker);
            }

            function drawMarkersForPage(pageNumber) {
                activeAnnotations.filter(a => a.page === pageNumber).forEach(a => createMarker(a));
            }

            function clearMarkers() {
                document.querySelectorAll('#pdf-interactive-layer [data-marker-id]').forEach(el => el.remove());
            }

            function removeAnnotation(id) {
                activeAnnotations = activeAnnotations.filter(a => a.id !== id);
                document.getElementById('annotation_coordinates').value = JSON.stringify(activeAnnotations);
                clearMarkers();
                drawMarkersForPage(currentPdfPage);
                renderCoordinatesLog();
            }

            function renderCoordinatesLog() {
                const container = document.getElementById('list-coordinates-log');
                if (!container) return;

                const pageAnns = activeAnnotations.filter(a => a.page === currentPdfPage);

                if (pageAnns.length === 0) {
                    container.innerHTML =
                        '<span class="text-gray-600 italic text-[9px]">Belum ada poin komentar di halaman ini.</span>';
                    return;
                }

                container.innerHTML = pageAnns.map(a => `
                    <div class="flex justify-between items-center bg-[#1e293b] p-1 rounded px-2 border border-gray-800">
                        <span>
                            #${a.id} (${a.x}, ${a.y})
                            ${String(a.comment).substring(0, 20)}
                        </span>

                        <button
                            type="button"
                            onclick="removeAnnotation(${a.id})"
                            class="text-red-400 hover:text-red-300 font-bold px-1">
                            Hapus
                        </button>
                    </div>
                `).join('');
            }

            // ==================== TOOLTIP (muncul tepat di samping marker) ====================
            const tooltip = document.getElementById('annotation-tooltip');
            const scrollContainer = document.getElementById('pdf-scroll-container');

            function showTooltip(e) {
                const comment = e.currentTarget.getAttribute('data-comment');
                if (!comment || comment === 'Tidak ada komentar') return;
                tooltip.innerText = comment;
                tooltip.classList.remove('hidden');
                positionTooltip(e.currentTarget);
            }

            function hideTooltip() {
                tooltip.classList.add('hidden');
            }

            function positionTooltip(marker) {
                const tooltip = document.getElementById('annotation-tooltip');
                const scrollContainer = document.getElementById('pdf-scroll-container');
                if (!tooltip || !scrollContainer || !marker) return;

                const markerRect = marker.getBoundingClientRect();
                const containerRect = scrollContainer.getBoundingClientRect();

                // Posisi marker relatif terhadap area konten container (termasuk scroll)
                const markerLeft = markerRect.left - containerRect.left + scrollContainer.scrollLeft;
                const markerTop = markerRect.top - containerRect.top + scrollContainer.scrollTop;
                const markerCenterX = markerLeft + markerRect.width / 2;
                const markerCenterY = markerTop + markerRect.height / 2;

                const tooltipWidth = tooltip.offsetWidth;
                const tooltipHeight = tooltip.offsetHeight;
                const margin = 12; // jarak antara marker dan tooltip

                let left, top;

                // Coba letakkan di kanan marker
                if (markerLeft + markerRect.width + tooltipWidth + margin <= scrollContainer.scrollWidth) {
                    left = markerLeft + markerRect.width + margin;
                    top = markerCenterY - tooltipHeight / 2;
                }
                // Kalau tidak cukup, coba di kiri
                else if (markerLeft - tooltipWidth - margin >= 0) {
                    left = markerLeft - tooltipWidth - margin;
                    top = markerCenterY - tooltipHeight / 2;
                }
                // Fallback: letakkan di atas marker
                else {
                    left = markerCenterX - tooltipWidth / 2;
                    top = markerTop - tooltipHeight - margin;
                }

                // Batasi agar tidak keluar dari container
                const maxLeft = scrollContainer.scrollWidth - tooltipWidth;
                const maxTop = scrollContainer.scrollHeight - tooltipHeight;

                left = Math.max(0, Math.min(left, maxLeft));
                top = Math.max(0, Math.min(top, maxTop));

                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';
                tooltip.style.transform = 'none'; // reset transform
            }

            const CODE_EXTENSIONS = [
                'js', 'ts', 'jsx', 'tsx',
                'php', 'py', 'java', 'cpp',
                'c', 'cs', 'go', 'rs',
                'rb', 'kt', 'swift',
                'html', 'css', 'scss',
                'json', 'xml', 'md',
                'txt', 'sql', 'sh'
            ];

            const ARCHIVE_EXTENSIONS = ['zip', 'rar', '7z'];

            async function loadTaskViewer(filePath, fileName) {

                const container = document.getElementById('task-viewer-container');

                const extension = getExtension(fileName);

                renderLoading(container);

                try {

                    // ARCHIVE
                    if (ARCHIVE_EXTENSIONS.includes(extension)) {
                        renderArchive(container, filePath, fileName);
                        return;
                    }

                    // CODE / TEXT
                    if (CODE_EXTENSIONS.includes(extension)) {

                        const response = await fetch(`/storage/${filePath}`);

                        if (!response.ok) {
                            throw new Error('Failed to load file');
                        }

                        const content = await response.text();

                        renderCode(
                            container,
                            filePath,
                            fileName,
                            extension,
                            content
                        );

                        return;
                    }

                    // UNKNOWN
                    renderUnsupported(container, filePath);

                } catch (error) {

                    console.error(error);

                    renderError(container);
                }
            }

            function getExtension(fileName) {
                return fileName.split('.').pop().toLowerCase();
            }

            function renderLoading(container) {

                container.innerHTML = `
            <div class="h-full overflow-auto flex items-center justify-center">
                <div class="text-center space-y-3">

                    <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto"></div>

                    <p class="text-gray-400 text-sm">
                        Loading file...
                    </p>

                </div>
            </div>
        `;
            }

            function renderCode(
                container,
                filePath,
                fileName,
                extension,
                content
            ) {

                container.innerHTML = `
            <div class="h-full flex flex-col overflow-auto">

                <!-- HEADER -->
                <div class="shrink-0 px-5 py-4 border-b border-gray-800 bg-[#111827]">

                    <div class="flex items-center justify-between">

                        <div class="min-w-0">

                            <h2 class="text-white font-semibold truncate">
                                ${escapeHtml(fileName)}
                            </h2>

                            <p class="text-xs text-gray-500 uppercase mt-1">
                                ${extension} file
                            </p>

                        </div>

                        <a
                            href="/storage/${filePath}"
                            download
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 transition rounded-xl text-sm font-medium text-white shrink-0"
                        >
                            Download
                        </a>

                    </div>

                </div>

                <!-- CONTENT -->
                <div class="flex-1 overflow-auto">

                    <pre class="min-h-full p-5 text-sm leading-7 text-gray-200 font-mono whitespace-pre-wrap break-words">
<code>${escapeHtml(content)}</code>
                    </pre>

                </div>

            </div>
        `;
            }

            function renderArchive(container, filePath, fileName) {

                container.innerHTML = `
            <div class="h-full flex items-center justify-center p-6">

                <div class="w-full max-w-md bg-[#111827] border border-gray-800 rounded-3xl p-8 text-center">

                    <div class="mb-5">

                        <div class="w-16 h-16 rounded-2xl bg-yellow-500/10 text-yellow-400 flex items-center justify-center mx-auto text-3xl">
                            📦
                        </div>

                    </div>

                    <h2 class="text-xl font-bold text-white mb-2">
                        File Arsip
                    </h2>

                    <p class="text-gray-400 text-sm mb-6">
                        File arsip tidak dapat dipreview langsung.
                    </p>

                    <div class="bg-[#0b1120] border border-gray-700 rounded-xl p-3 text-sm text-gray-300 font-mono truncate mb-6">
                        ${escapeHtml(fileName)}
                    </div>

                    <a
                        href="/storage/${filePath}"
                        download
                        class="inline-flex items-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 transition rounded-xl text-white font-medium"
                    >
                        Download File
                    </a>

                </div>

            </div>
        `;
            }

            function renderUnsupported(container, filePath) {

                container.innerHTML = `
            <div class="h-full flex items-center justify-center p-6">

                <div class="max-w-md w-full bg-[#111827] border border-gray-800 rounded-3xl p-8 text-center">

                    <div class="text-5xl mb-5">
                        📄
                    </div>

                    <h2 class="text-xl font-bold text-white mb-2">
                        Preview Tidak Didukung
                    </h2>

                    <p class="text-gray-400 text-sm mb-6">
                        File ini tidak dapat dipreview langsung.
                    </p>

                    <a
                        href="/storage/${filePath}"
                        download
                        class="inline-flex items-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-white font-medium"
                    >
                        Download File
                    </a>

                </div>

            </div>
        `;
            }

            function renderError(container) {

                container.innerHTML = `
            <div class="h-full flex items-center justify-center">

                <div class="text-center">

                    <div class="text-5xl mb-4">
                        ⚠️
                    </div>

                    <h2 class="text-white text-lg font-semibold mb-2">
                        Gagal Memuat File
                    </h2>

                    <p class="text-gray-400 text-sm">
                        Terjadi kesalahan saat membaca file.
                    </p>

                </div>

            </div>
        `;
            }

            function escapeHtml(text) {

                const div = document.createElement('div');

                div.innerText = text;

                return div.innerHTML;
            }
        </script>
    @endpush
</x-workspace-layout>
