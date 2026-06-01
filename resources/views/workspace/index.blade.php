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


            let currentPdfDoc = null;
            let currentPdfPage = 1;
            let currentPdfScale = null; // Will be calculated dynamically to fit width
            let currentSubmissionId = null;
            let isContinuousMode = false;
            let activeAnnotations = [];
            let annotationCounter = 0;

            const pdfCanvas = document.getElementById('pdf-canvas');
            const pdfContext = pdfCanvas?.getContext('2d');

            let pendingAnnotation = null;
            let currentRenderVersion = 0;


            function loadPdfViewer(pdfUrl, submissionId, savedAnnotations, savedNotesBase64, systemValidationLogs) {
                // console.log("System Validation Logs:", systemValidationLogs);
                window.currentValidationLogs = systemValidationLogs;

                currentSubmissionId = submissionId;
                // Read ?page= from URL for tracking, default to 1
                const urlParams = new URLSearchParams(window.location.search);
                currentPdfPage = parseInt(urlParams.get('page')) || 1;
                activeAnnotations = savedAnnotations || [];
                annotationCounter = activeAnnotations.length > 0 ? Math.max(...activeAnnotations.map(a => a.id)) : 0;

                document.getElementById('annotation_coordinates').value = JSON.stringify(activeAnnotations);
                document.getElementById('assistant_notes').value = savedNotesBase64 ? decodeURIComponent(escape(atob(
                    savedNotesBase64))) : '';

                showPdfUI();

                const loadingOverlay = document.getElementById('pdf-loading-overlay');
                const loadingPct = document.getElementById('pdf-loading-percentage');
                if(loadingOverlay) { loadingOverlay.classList.remove('hidden'); loadingOverlay.classList.add('flex'); }
                if(loadingPct) loadingPct.textContent = '0%';

                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                loadingTask.onProgress = function(progress) {
                    if (progress.total > 0 && loadingPct) {
                        const percent = Math.round((progress.loaded / progress.total) * 100);
                        loadingPct.textContent = percent + '%';
                    }
                };

                loadingTask.promise.then(pdfDoc => {
                    if(loadingOverlay) { loadingOverlay.classList.add('hidden'); loadingOverlay.classList.remove('flex'); }
                    currentPdfDoc = pdfDoc;
                    document.getElementById('pdf-total-pages').textContent = pdfDoc.numPages;
                    // Clamp page to valid range
                    currentPdfPage = Math.min(currentPdfPage, pdfDoc.numPages);
                    if (isContinuousMode) {
                        renderAllPages();
                    } else {
                        renderPage(currentPdfPage);
                    }
                    // Sync URL
                    const url = new URL(window.location.href);
                    url.searchParams.set('page', currentPdfPage);
                    history.replaceState(null, '', url.toString());
                }).catch(err => {
                    if(loadingOverlay) { loadingOverlay.classList.add('hidden'); loadingOverlay.classList.remove('flex'); }
                    console.error(err);
                    alert("Gagal membaca PDF.");
                });
            }

            function loadPdfViewerStudent(pdfUrl, savedAnnotations, savedNotesBase64, systemValidationLogs) {
                // console.log("System Validation Logs:", systemValidationLogs);

                window.currentValidationLogs = systemValidationLogs;

                // Read ?page= from URL for tracking, default to 1
                const urlParams = new URLSearchParams(window.location.search);
                currentPdfPage = parseInt(urlParams.get('page')) || 1;
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

                const loadingOverlay = document.getElementById('pdf-loading-overlay');
                const loadingPct = document.getElementById('pdf-loading-percentage');
                if(loadingOverlay) { loadingOverlay.classList.remove('hidden'); loadingOverlay.classList.add('flex'); }
                if(loadingPct) loadingPct.textContent = '0%';

                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                loadingTask.onProgress = function(progress) {
                    if (progress.total > 0 && loadingPct) {
                        const percent = Math.round((progress.loaded / progress.total) * 100);
                        loadingPct.textContent = percent + '%';
                    }
                };

                loadingTask.promise.then(pdfDoc => {
                    if(loadingOverlay) { loadingOverlay.classList.add('hidden'); loadingOverlay.classList.remove('flex'); }
                    currentPdfDoc = pdfDoc;
                    document.getElementById('pdf-total-pages').textContent = pdfDoc.numPages;
                    // Clamp page to valid range
                    currentPdfPage = Math.min(currentPdfPage, pdfDoc.numPages);
                    if (isContinuousMode) {
                        renderAllPages();
                    } else {
                        renderPage(currentPdfPage);
                    }
                    // Sync URL
                    const url = new URL(window.location.href);
                    url.searchParams.set('page', currentPdfPage);
                    history.replaceState(null, '', url.toString());
                }).catch(err => {
                    if(loadingOverlay) { loadingOverlay.classList.add('hidden'); loadingOverlay.classList.remove('flex'); }
                    console.error(err);
                    alert("Gagal membaca PDF.");
                });
            }

            function showPdfUI() {
                document.getElementById('pdf-placeholder').classList.add('hidden');
                document.getElementById('pdf-toolbar').classList.remove('hidden');
                
                if (!isContinuousMode) {
                    document.getElementById('pdf-render-wrapper').classList.remove('hidden');
                    document.getElementById('pdf-continuous-container').classList.add('hidden');
                }
                const panel = document.getElementById('annotation-panel');
                const submissionList = document.getElementById('submission-list');
                if (panel) panel.classList.remove('hidden');
                const form = document.getElementById('form-review-asprak');
                if (form) form.action = `/submissions/${currentSubmissionId}/review`;
            }


            function renderPage(num) {
                if (!currentPdfDoc) return;
                const version = ++currentRenderVersion;
                
                currentPdfDoc.getPage(num).then(page => {
                    if (currentRenderVersion !== version) return;
                    
                    // Dynamically calculate scale to fit the container width on first load
                    if (!currentPdfScale) {
                        const container = document.getElementById('pdf-scroll-container');
                        const unscaledViewport = page.getViewport({ scale: 1.0 });
                        // Use container width, subtract a small margin (e.g. 16px) for safety
                        const containerWidth = container.clientWidth || window.innerWidth;
                        const calculatedScale = ((containerWidth - 64) / unscaledViewport.width) * 0.85;
                        currentPdfScale = Math.min(1.3, Math.max(0.5, calculatedScale));
                    }

                    const viewport = page.getViewport({
                        scale: currentPdfScale
                    });

                    // HD Rendering for crisp text on high DPI displays
                    const outputScale = window.devicePixelRatio || 1;

                    pdfCanvas.width = Math.floor(viewport.width * outputScale);
                    pdfCanvas.height = Math.floor(viewport.height * outputScale);
                    
                    // Set CSS size to match the logical viewport size
                    pdfCanvas.style.width = Math.floor(viewport.width) + "px";
                    pdfCanvas.style.height = Math.floor(viewport.height) + "px";

                    const wrapper = document.getElementById('pdf-render-wrapper');
                    wrapper.style.width = Math.floor(viewport.width) + 'px';
                    wrapper.style.height = Math.floor(viewport.height) + 'px';

                    const transform = outputScale !== 1 
                        ? [outputScale, 0, 0, outputScale, 0, 0] 
                        : null;

                    const renderContext = {
                        canvasContext: pdfContext,
                        transform: transform,
                        viewport: viewport
                    };

                    page.render(renderContext).promise.then(() => {
                        if (currentRenderVersion !== version) return;
                        
                        document.getElementById('pdf-current-page').textContent = num;
                        const indicator = document.getElementById('target-page-indicator');
                        if (indicator) indicator.textContent = 'Halaman ' + num;

                        renderCoordinatesLog();


                        let textLayerDiv = document.getElementById('pdf-text-layer');
                        if (textLayerDiv) textLayerDiv.remove();

                        textLayerDiv = document.createElement('div');
                        textLayerDiv.id = 'pdf-text-layer';
                        textLayerDiv.className = 'textLayer';
                        textLayerDiv.style.width = viewport.width + 'px';
                        textLayerDiv.style.height = viewport.height + 'px';
                        textLayerDiv.style.position = 'absolute';
                        textLayerDiv.style.top = '0';
                        textLayerDiv.style.left = '0';
                        textLayerDiv.style.zIndex = '20';

                        wrapper.appendChild(textLayerDiv);


                        page.getTextContent().then(textContent => {
                            const textLayerRenderTask = pdfjsLib.renderTextLayer({
                                textContent: textContent,
                                container: textLayerDiv,
                                viewport: viewport,
                                textDivs: []
                            });


                            const textPromise = textLayerRenderTask.promise ? textLayerRenderTask
                                .promise : textLayerRenderTask;
                            if (textPromise && typeof textPromise.then === 'function') {
                                textPromise.then(() => {
                                    if (currentRenderVersion !== version) return;
                                    highlightErrorsInTextLayer(textLayerDiv, num, viewport);
                                });
                            } else {

                                setTimeout(() => {
                                    if (currentRenderVersion !== version) return;
                                    highlightErrorsInTextLayer(textLayerDiv, num, viewport);
                                }, 200);
                            }
                        });


                        let markerLayerDiv = document.getElementById('pdf-marker-layer');
                        if (markerLayerDiv) markerLayerDiv.remove();

                        markerLayerDiv = document.createElement('div');
                        markerLayerDiv.id = 'pdf-marker-layer';
                        markerLayerDiv.style.width = viewport.width + 'px';
                        markerLayerDiv.style.height = viewport.height + 'px';
                        markerLayerDiv.style.position = 'absolute';
                        markerLayerDiv.style.top = '0';
                        markerLayerDiv.style.left = '0';
                        markerLayerDiv.style.zIndex = '30';
                        markerLayerDiv.style.pointerEvents = 'none';

                        wrapper.appendChild(markerLayerDiv);

                        clearMarkers();
                        drawMarkersForPage(num);

                        textLayerDiv.addEventListener('click', function(event) {
                            catchCoordinates(event);
                        });

                        const interactiveLayer = document.getElementById('pdf-interactive-layer');
                        if (interactiveLayer) {
                            interactiveLayer.style.display = 'none';
                        }
                    });
                });
            }

            function highlightErrorsInTextLayer(container, currentPage, viewport) {

                let logs = [];
                if (window.currentValidationLogs) {
                    if (Array.isArray(window.currentValidationLogs.laporan_lengkap)) {
                        logs = window.currentValidationLogs.laporan_lengkap;
                    } else if (window.currentValidationLogs.logs && Array.isArray(window.currentValidationLogs.logs.laporan_lengkap)) {
                        logs = window.currentValidationLogs.logs.laporan_lengkap;
                    }
                }

                const invalidLogs = logs.filter(item => {
                    if (item.status !== "Tidak Valid") return false;
                    if (item.koordinat_pdf && item.koordinat_pdf.halaman) {
                        return item.koordinat_pdf.halaman === currentPage;
                    }
                    const match = item.lokasi && item.lokasi.match(/Hlm\.\s*(\d+)/);
                    return match ? parseInt(match[1]) === currentPage : false;
                });

                if (invalidLogs.length === 0) {
                    // No errors on this page — still clear any stale highlights from previous page
                    const wrapper = document.getElementById('pdf-render-wrapper');
                    if (wrapper) wrapper.querySelectorAll('.pdf-error-highlight').forEach(el => el.remove());
                    return;
                }

                // IMPORTANT: Overlays are appended to pdf-render-wrapper (position:relative),
                // NOT to the text layer. This guarantees coordinates are in the same space
                // as the PDF canvas: pdfPoint * viewport.scale = CSS pixels from page top-left.
                // This is pixel-perfect on ALL devices regardless of text layer CSS or scroll.
                const wrapper = document.getElementById('pdf-render-wrapper');
                const scale = viewport.scale;

                // Build page text for fallback (no-coords) matching
                const pageTextString = Array.from(container.querySelectorAll('span'))
                    .map(s => s.textContent).join('').toLowerCase();

                // Remove stale highlights from a previous render of this page
                wrapper.querySelectorAll('.pdf-error-highlight').forEach(el => el.remove());


                invalidLogs.forEach(item => {
                    let searchStr = item.teks;
                    if (searchStr.endsWith('...')) searchStr = searchStr.slice(0, -3);
                    searchStr = searchStr.trim().toLowerCase();

                    // Strategy A — pdfBox available: draw precise overlay div on the wrapper.
                    // pdfplumber gives x0,y0,x1,y1 in PDF points (Y from page top).
                    // Multiply by viewport.scale → CSS pixels relative to wrapper top-left.
                    // This is guaranteed correct on every device/screen size.
                    if (item.koordinat_pdf) {
                        const pdfBox = {
                            x0: item.koordinat_pdf.x0 * scale,
                            y0: item.koordinat_pdf.y0 * scale,
                            x1: item.koordinat_pdf.x1 * scale,
                            y1: item.koordinat_pdf.y1 * scale,
                        };

                        const box = document.createElement('div');
                        box.classList.add('pdf-error-highlight');
                        box.style.position = 'absolute';
                        box.style.left     = `${pdfBox.x0}px`;
                        box.style.top      = `${pdfBox.y0}px`;
                        box.style.width    = `${Math.max(pdfBox.x1 - pdfBox.x0, 4)}px`;
                        box.style.height   = `${Math.max(pdfBox.y1 - pdfBox.y0, 4)}px`;
                        box.style.backgroundColor = 'rgba(239, 68, 68, 0.25)';
                        box.style.borderBottom    = '2px dashed #ef4444';
                        box.style.borderRadius    = '2px';
                        box.style.cursor          = 'help';
                        box.style.pointerEvents   = 'auto';
                        box.style.zIndex          = '25';
                        box.dataset.errors   = JSON.stringify(item.detail_catatan || [item.teks]);
                        box.dataset.location = item.lokasi;
                        box.dataset.specs    = `Font: ${item.font_terdeteksi} | Size: ${item.ukuran_terdeteksi}pt | Spasi: ${item.spasi_terdeteksi}`;
                        wrapper.appendChild(box);
                        return; // done for this item
                    }

                    // Strategy B — no pdfBox: fall back to span-based text highlight.
                    if (searchStr.length < 4) return;
                    if (pageTextString.indexOf(searchStr) === -1) return;

                    Array.from(container.querySelectorAll('span')).forEach(span => {
                        if (!span.textContent.toLowerCase().includes(searchStr)) return;
                        span.classList.add('pdf-error-highlight');
                        span.style.pointerEvents    = 'auto';
                        span.style.backgroundColor  = 'rgba(239, 68, 68, 0.25)';
                        span.style.borderBottom     = '2px dashed #ef4444';
                        span.style.borderRadius     = '2px';
                        span.style.cursor           = 'help';
                        span.dataset.errors   = JSON.stringify(item.detail_catatan || [item.teks]);
                        span.dataset.location = item.lokasi;
                        span.dataset.specs    = `Font: ${item.font_terdeteksi} | Size: ${item.ukuran_terdeteksi}pt | Spasi: ${item.spasi_terdeteksi}`;
                    });
                });

                setupTooltipEngine(wrapper);
            }


            function setupTooltipEngine(container) {

                let tooltip = document.getElementById('pdf-validation-tooltip');
                if (!tooltip) {
                    tooltip = document.createElement('div');
                    tooltip.id = 'pdf-validation-tooltip';


                    Object.assign(tooltip.style, {
                        position: 'fixed',
                        zIndex: '9999',
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        color: '#f8fafc',
                        padding: '12px 16px',
                        borderRadius: '8px',
                        fontSize: '12px',
                        boxShadow: '0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.4)',
                        display: 'none',
                        pointerEvents: 'none',
                        maxWidth: '300px',
                        lineHeight: '1.5',
                        border: '1px solid rgba(255, 255, 255, 0.1)',
                        backdropFilter: 'blur(4px)',
                        fontFamily: 'sans-serif'
                    });
                    document.body.appendChild(tooltip);
                }


                container.addEventListener('mouseover', function(e) {
                    const target = e.target.closest('.pdf-error-highlight');
                    if (!target) return;

                    const errors = JSON.parse(target.dataset.errors || '[]');
                    const location = target.dataset.location;
                    const specs = target.dataset.specs;

                    let errorItems = errors.map(err =>
                        `<li style="margin-bottom: 4px; color: #f87171; list-style-type: disc; margin-left: 14px;">${err}</li>`
                    ).join('');

                    tooltip.innerHTML = `
            <div style="font-weight: 600; margin-bottom: 6px; color: #38bdf8; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                ⚠️ Malformat (${location})
            </div>
            <ul style="padding: 0; margin: 0 0 8px 0;">
                ${errorItems}
            </ul>
            <div style="font-size: 10px; color: #94a3b8; background: rgba(255,255,255,0.05); padding: 4px 6px; border-radius: 4px; font-style: italic;">
                ${specs}
            </div>
        `;
                    tooltip.style.display = 'block';
                });

                container.addEventListener('mousemove', function(e) {
                    const target = e.target.closest('.pdf-error-highlight');
                    if (!target) {
                        tooltip.style.display = 'none';
                        return;
                    }

                    tooltip.style.left = (e.clientX + 15) + 'px';
                    tooltip.style.top = (e.clientY + 15) + 'px';
                });

                container.addEventListener('mouseout', function(e) {
                    const target = e.target.closest('.pdf-error-highlight');
                    if (target) {
                        tooltip.style.display = 'none';
                    }
                });
            }

            function changePage(offset) {
                if (!currentPdfDoc || isContinuousMode) return;
                const next = currentPdfPage + offset;
                if (next >= 1 && next <= currentPdfDoc.numPages) {
                    currentPdfPage = next;
                    renderPage(currentPdfPage);
                    // URL tracking: update ?page= without reload
                    const url = new URL(window.location.href);
                    url.searchParams.set('page', currentPdfPage);
                    history.replaceState(null, '', url.toString());
                }
            }

            function jumpToPage(numStr) {
                if (!currentPdfDoc) return;
                
                if (numStr.toLowerCase() === 'all') {
                    if (!isContinuousMode) {
                        // Keep input as 'All', it'll be handled by toggleContinuousMode
                        toggleContinuousMode(); 
                    }
                    return;
                }

                const num = parseInt(numStr);
                if (!isNaN(num) && num >= 1 && num <= currentPdfDoc.numPages) {
                    if (isContinuousMode) {
                        const pageWrapper = document.querySelector('.pdf-continuous-page[data-page="' + num + '"]');
                        if (pageWrapper) {
                            pageWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                        // Update to the selected page immediately
                        document.getElementById('pdf-current-page').textContent = num;
                    } else {
                        currentPdfPage = num;
                        renderPage(currentPdfPage);
                        const url = new URL(window.location.href);
                        url.searchParams.set('page', currentPdfPage);
                        history.replaceState(null, '', url.toString());
                    }
                } else {
                    document.getElementById('pdf-current-page').textContent = isContinuousMode ? 'All' : currentPdfPage;
                }
            }

            // Keyboard Shortcuts
            document.addEventListener('keydown', function(e) {
                if (!currentPdfDoc || isContinuousMode) return;
                // Ignore if user is typing in an input or textarea
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                if (e.key === 'ArrowRight') {
                    changePage(1);
                } else if (e.key === 'ArrowLeft') {
                    changePage(-1);
                }
            });

            function toggleContinuousMode() {
                if (!currentPdfDoc) return;
                isContinuousMode = !isContinuousMode;
                
                const btn = document.getElementById('btn-continuous-mode');
                const scrollContainer = document.getElementById('pdf-scroll-container');
                const singleWrapper = document.getElementById('pdf-render-wrapper');
                const contContainer = document.getElementById('pdf-continuous-container');
                const btnNext = document.getElementById('btn-page-next');
                const btnPrev = document.getElementById('btn-page-prev');
                
                if (isContinuousMode) {
                    btn.classList.add('bg-indigo-500/20', 'border-indigo-500/50', 'text-indigo-400');
                    btn.classList.remove('text-slate-400', 'bg-slate-800/50', 'border-slate-700/40');
                    
                    singleWrapper.classList.add('hidden');
                    if(btnNext) btnNext.classList.add('opacity-50', 'pointer-events-none');
                    if(btnPrev) btnPrev.classList.add('opacity-50', 'pointer-events-none');
                    
                    renderAllPages();
                } else {
                    btn.classList.remove('bg-indigo-500/20', 'border-indigo-500/50', 'text-indigo-400');
                    btn.classList.add('text-slate-400', 'bg-slate-800/50', 'border-slate-700/40');
                    
                    contContainer.innerHTML = '';
                    contContainer.classList.add('hidden');
                    contContainer.classList.remove('flex');
                    singleWrapper.classList.remove('hidden');
                    
                    if(btnNext) btnNext.classList.remove('opacity-50', 'pointer-events-none');
                    if(btnPrev) btnPrev.classList.remove('opacity-50', 'pointer-events-none');
                    
                    renderPage(currentPdfPage);
                }
            }

            async function renderAllPages() {
                if (!currentPdfDoc) return;
                
                const version = ++currentRenderVersion;
                
                const container = document.getElementById('pdf-continuous-container');
                container.innerHTML = '';
                container.classList.remove('hidden');
                container.classList.add('flex');
                
                // Initialize to page 1 instead of 'All'
                document.getElementById('pdf-current-page').textContent = currentPdfPage || 1;

                // Optional: show loading overlay while rendering all pages
                const loadingOverlay = document.getElementById('pdf-loading-overlay');
                const loadingPct = document.getElementById('pdf-loading-percentage');
                if(loadingOverlay) { loadingOverlay.classList.remove('hidden'); loadingOverlay.classList.add('flex'); }
                if(loadingPct) loadingPct.textContent = 'Rendering...';

                for (let i = 1; i <= currentPdfDoc.numPages; i++) {
                    if (currentRenderVersion !== version) return; // Abort if newer render requested
                    await renderSingleContinuousPage(i, container, version);
                }

                if (currentRenderVersion === version) {
                    if(loadingOverlay) { loadingOverlay.classList.add('hidden'); loadingOverlay.classList.remove('flex'); }
                }
            }

            async function renderSingleContinuousPage(num, container, version) {
                const page = await currentPdfDoc.getPage(num);
                if (version && currentRenderVersion !== version) return;
                
                if (!currentPdfScale) {
                    const scrollContainer = document.getElementById('pdf-scroll-container');
                    const unscaledViewport = page.getViewport({ scale: 1.0 });
                    const containerWidth = scrollContainer.clientWidth || window.innerWidth;
                    const calculatedScale = ((containerWidth - 64) / unscaledViewport.width) * 0.85;
                    currentPdfScale = Math.min(1.3, Math.max(0.5, calculatedScale));
                }

                const viewport = page.getViewport({ scale: currentPdfScale });
                const outputScale = window.devicePixelRatio || 1;

                // Create wrapper
                const wrapper = document.createElement('div');
                wrapper.className = "relative shadow-2xl origin-top-left mx-auto text-left pdf-continuous-page";
                wrapper.style.width = Math.floor(viewport.width) + 'px';
                wrapper.style.height = Math.floor(viewport.height) + 'px';
                wrapper.style.borderRadius = "4px";
                wrapper.style.boxShadow = "0 25px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05)";
                wrapper.setAttribute('data-page', num);

                // Create canvas
                const canvas = document.createElement('canvas');
                canvas.className = "block";
                canvas.width = Math.floor(viewport.width * outputScale);
                canvas.height = Math.floor(viewport.height * outputScale);
                canvas.style.width = Math.floor(viewport.width) + "px";
                canvas.style.height = Math.floor(viewport.height) + "px";
                wrapper.appendChild(canvas);

                // Interactive layer
                const disableClick = {{ $disableClick ?? 'false' }};
                const interactiveLayer = document.createElement('div');
                interactiveLayer.className = `absolute inset-0 z-20 ${disableClick ? 'cursor-default' : 'cursor-crosshair'}`;
                interactiveLayer.setAttribute('data-page', num);
                if (!disableClick) {
                    interactiveLayer.onclick = function(e) {
                        catchCoordinates(e, num);
                    };
                }
                wrapper.appendChild(interactiveLayer);

                // Marker layer
                const markerLayer = document.createElement('div');
                markerLayer.id = `pdf-marker-layer-${num}`;
                markerLayer.className = "absolute inset-0 pointer-events-none z-30";
                wrapper.appendChild(markerLayer);
                
                if (version && currentRenderVersion !== version) return;
                container.appendChild(wrapper);

                const renderContext = {
                    canvasContext: canvas.getContext('2d'),
                    transform: outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null,
                    viewport: viewport
                };

                await page.render(renderContext).promise;
                if (version && currentRenderVersion !== version) return;

                // Render markers for this page
                drawMarkersForPage(num);
            }

            let zoomTimeout = null;
            function zoomPdf(amount) {
                if (!currentPdfScale) return;
                currentPdfScale = Math.max(0.3, Math.min(4.0, currentPdfScale + amount));
                // Remove any lingering CSS transforms
                document.getElementById('pdf-render-wrapper').style.transform = '';
                
                if (zoomTimeout) clearTimeout(zoomTimeout);
                zoomTimeout = setTimeout(() => {
                    if (isContinuousMode) {
                        renderAllPages();
                    } else {
                        renderPage(currentPdfPage);
                    }
                }, 200); // debounce renders
            }

            function catchCoordinates(event, pageNum = null) {

                if (window.getSelection().toString().trim() !== '') {
                    return;
                }

                // If pageNum is provided (Continuous Mode), use it. Otherwise use currentPdfPage.
                const targetPage = pageNum || currentPdfPage;
                const layer = event.currentTarget;
                if (!layer) return;

                const rect = layer.getBoundingClientRect();
                // Normalize coordinates to scale 1.0 so they remain accurate across zooms
                const x = Math.round((event.clientX - rect.left) / currentPdfScale);
                const y = Math.round((event.clientY - rect.top) / currentPdfScale);

                annotationCounter++;
                pendingAnnotation = {
                    id: annotationCounter,
                    page: targetPage,
                    x: x,
                    y: y,
                    comment: ''
                };

                document.getElementById('comment-coord-info').innerText =
                    `X: ${x}, Y: ${y} | Hal ${targetPage}`;
                document.getElementById('comment-text').value = '';
                document.getElementById('commentModal').classList.remove('hidden');
            }

            function closeCommentModal() {
                document.getElementById('commentModal').classList.add('hidden');
                pendingAnnotation = null;
            }


            document.getElementById('save-comment-btn')?.addEventListener('click', function() {
                if (!pendingAnnotation) return;
                pendingAnnotation.comment = document.getElementById('comment-text').value.trim() ||
                    'Tanpa komentar';
                activeAnnotations.push(pendingAnnotation);


                document.getElementById('annotation_coordinates').value = JSON.stringify(activeAnnotations);
                renderCoordinatesLog();
                createMarker(pendingAnnotation);
                closeCommentModal();
            });

            function createMarker(ann) {
                let layer;
                if (isContinuousMode) {
                    layer = document.getElementById(`pdf-marker-layer-${ann.page}`);
                } else {
                    if (ann.page !== currentPdfPage) return;
                    layer = document.getElementById('pdf-marker-layer');
                }
                
                if (!layer) return;

                const marker = document.createElement('div');
                marker.className =
                    "absolute w-5 h-5 bg-red-500 text-white font-mono font-bold text-[9px] rounded-full flex items-center justify-center shadow-lg border border-white transform -translate-x-1/2 -translate-y-1/2 cursor-pointer transition hover:scale-110";
                
                // Scale back up the normalized coordinates
                marker.style.left = (ann.x * currentPdfScale) + 'px';
                marker.style.top = (ann.y * currentPdfScale) + 'px';


                marker.style.pointerEvents = 'auto';
                marker.style.zIndex = '40';

                marker.innerText = ann.id;
                marker.setAttribute('data-marker-id', ann.id);
                marker.setAttribute('data-comment', ann.comment || 'Tidak ada komentar');


                marker.addEventListener('mouseenter', showTooltip);
                marker.addEventListener('mouseleave', hideTooltip);


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
                document.querySelectorAll('.pdf-continuous-page [data-marker-id]').forEach(el => el.remove());
                document.querySelectorAll('#pdf-interactive-layer [data-marker-id]').forEach(el => el.remove());
                // Fallback for marker-layer specific elements
                document.querySelectorAll('[id^="pdf-marker-layer"] [data-marker-id]').forEach(el => el.remove());
            }

            function removeAnnotation(id) {
                activeAnnotations = activeAnnotations.filter(a => a.id !== id);
                document.getElementById('annotation_coordinates').value = JSON.stringify(activeAnnotations);
                clearMarkers();
                if (isContinuousMode) {
                    activeAnnotations.forEach(a => createMarker(a));
                } else {
                    drawMarkersForPage(currentPdfPage);
                }
                renderCoordinatesLog();
            }

            function renderCoordinatesLog() {
                const container = document.getElementById('list-coordinates-log');
                if (!container) return;

                const pageAnns = activeAnnotations.filter(a => a.page === currentPdfPage);

                if (pageAnns.length === 0) {
                    container.innerHTML =
                        '<span class="text-slate-700 italic text-[10px]">Belum ada titik komentar di halaman ini.</span>';
                    return;
                }

                container.innerHTML = pageAnns.map(a => `
                    <div class="flex justify-between items-center rounded-lg px-2 py-1.5 border" style="background: rgba(13,20,36,0.8); border-color: rgba(51,65,85,0.5);">
                        <span class="text-[10px] font-mono text-amber-400 truncate flex-1 min-w-0">
                            <span class="text-slate-500">#${a.id}</span>
                            <span class="text-slate-600 mx-1">(${a.x},${a.y})</span>
                            ${String(a.comment).substring(0, 18)}${a.comment.length > 18 ? '…' : ''}
                        </span>
                        <button
                            type="button"
                            onclick="removeAnnotation(${a.id})"
                            class="flex-shrink-0 ml-1 text-red-500 hover:text-red-400 transition"
                            title="Hapus anotasi">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                `).join('');
            }


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


                const markerLeft = markerRect.left - containerRect.left + scrollContainer.scrollLeft;
                const markerTop = markerRect.top - containerRect.top + scrollContainer.scrollTop;
                const markerCenterX = markerLeft + markerRect.width / 2;
                const markerCenterY = markerTop + markerRect.height / 2;

                const tooltipWidth = tooltip.offsetWidth;
                const tooltipHeight = tooltip.offsetHeight;
                const margin = 12;

                let left, top;


                if (markerLeft + markerRect.width + tooltipWidth + margin <= scrollContainer.scrollWidth) {
                    left = markerLeft + markerRect.width + margin;
                    top = markerCenterY - tooltipHeight / 2;
                } else if (markerLeft - tooltipWidth - margin >= 0) {
                    left = markerLeft - tooltipWidth - margin;
                    top = markerCenterY - tooltipHeight / 2;
                } else {
                    left = markerCenterX - tooltipWidth / 2;
                    top = markerTop - tooltipHeight - margin;
                }


                const maxLeft = scrollContainer.scrollWidth - tooltipWidth;
                const maxTop = scrollContainer.scrollHeight - tooltipHeight;

                left = Math.max(0, Math.min(left, maxLeft));
                top = Math.max(0, Math.min(top, maxTop));

                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';
                tooltip.style.transform = 'none';
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


                    if (ARCHIVE_EXTENSIONS.includes(extension)) {
                        renderArchive(container, filePath, fileName);
                        return;
                    }


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
            
            // Scroll observer for continuous mode to update page number
            let continuousScrollTimeout;
            const pdfScrollContainerNode = document.getElementById('pdf-scroll-container');
            if (pdfScrollContainerNode) {
                pdfScrollContainerNode.addEventListener('scroll', function(e) {
                    if (!isContinuousMode) return;
                    if (continuousScrollTimeout) cancelAnimationFrame(continuousScrollTimeout);
                    
                    continuousScrollTimeout = requestAnimationFrame(() => {
                        const pages = document.querySelectorAll('.pdf-continuous-page');
                        if (pages.length === 0) return;
                        
                        const containerRect = pdfScrollContainerNode.getBoundingClientRect();
                        let currentPageNum = currentPdfPage;
                        
                        for (let i = 0; i < pages.length; i++) {
                            const rect = pages[i].getBoundingClientRect();
                            // A page is considered "current" if its bottom edge is visible below the top of the container
                            // We use a threshold of 30% of the page's height or 100px (whichever is smaller)
                            const threshold = Math.min(rect.height * 0.3, 100);
                            
                            if (rect.bottom > containerRect.top + threshold) {
                                currentPageNum = pages[i].getAttribute('data-page');
                                break;
                            }
                        }
                        
                        const inputEl = document.getElementById('pdf-current-page');
                        if (inputEl && document.activeElement !== inputEl) {
                            inputEl.textContent = currentPageNum;
                        }
                    });
                });
            }
        </script>
    @endpush
</x-workspace-layout>
