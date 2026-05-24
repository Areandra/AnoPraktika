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
            let currentPdfScale = 1.3;
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
                renderPage(currentPdfPage);
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
                document.getElementById('comment-coord-info').innerText = `X:${x}, Y:${y} | Hal ${currentPdfPage}`;
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
                pendingAnnotation.comment = document.getElementById('comment-text').value.trim() || 'Tanpa komentar';
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
                <span>#${a.id} (${a.x},${a.y}) ${String(a.comment).substring(0,20)}</span>
                <button type="button" onclick="removeAnnotation(${a.id})" class="text-red-400 hover:text-red-300 font-bold px-1">Hapus</button>
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
        </script>
    @endpush
</x-workspace-layout>
