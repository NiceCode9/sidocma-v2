@extends('layouts.app')

@section('title', 'View Surat - ' . $surat->no_surat)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt mr-2"></i>
                            {{ $surat->no_surat }} - {{ $surat->perihal }}
                        </h5>
                        <div>
                            <a href="{{ route('kirim-surat.download', $surat->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                            <a href="{{ auth()->user()->hasRole('super admin') ? route('management-surat.index') : route('kirim-surat.index') }}"
                                class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if ($fileExtension === 'pdf')
                            {{-- Custom PDF Viewer dengan PDF.js --}}
                            <div id="pdf-viewer-container" style="height: 80vh; position: relative;">
                                <!-- Custom Toolbar -->
                                <div id="pdf-toolbar"
                                    class="d-flex justify-content-between align-items-center p-2 border-bottom bg-light">
                                    <div>
                                        <button id="prev-page" class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="fas fa-chevron-left"></i> Prev
                                        </button>
                                        <span id="page-info" class="mx-2">
                                            Page <span id="page-num">1</span> of <span id="page-count">-</span>
                                        </span>
                                        <button id="next-page" class="btn btn-sm btn-outline-secondary" disabled>
                                            Next <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                    <div>
                                        <button id="zoom-out" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        <span id="zoom-level" class="mx-2">100%</span>
                                        <button id="zoom-in" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- PDF Canvas Container -->
                                <div id="pdf-canvas-container"
                                    style="overflow: auto; height: calc(100% - 50px); background: #f0f0f0;">
                                    <div class="d-flex justify-content-center p-3">
                                        <canvas id="pdf-canvas"
                                            style="border: 1px solid #ccc; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"></canvas>
                                    </div>
                                </div>
                            </div>
                        @elseif(in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
                            {{-- Image Viewer --}}
                            <div class="text-center p-3">
                                <img src="{{ route('surat.stream', $surat->id) }}" class="img-fluid no-select"
                                    style="max-height: 80vh; border: 1px solid #dee2e6;" alt="{{ $surat->perihal }}"
                                    oncontextmenu="return false;" ondragstart="return false;">
                            </div>
                        @elseif(in_array($fileExtension, ['doc', 'docx']))
                            {{-- Document Viewer dengan iframe Office Online --}}
                            <div class="p-4">
                                @if ($docxHtml)
                                    {{-- Local HTML Viewer (Converted) --}}
                                    <div class="alert alert-success">
                                        <h5><i class="fas fa-file-word mr-2"></i>Dokumen Microsoft Word</h5>
                                        <p class="mb-2">Dokumen berhasil dikonversi dan siap untuk dilihat.</p>
                                    </div>

                                    <div class="docx-viewer-container">
                                        <div
                                            class="docx-toolbar d-flex justify-content-between align-items-center p-2 border-bottom bg-light mb-3">
                                            <div>
                                                <h6 class="mb-0">
                                                    <i class="fas fa-file-word text-primary mr-2"></i>
                                                    {{ $surat->perihal }}
                                                </h6>
                                            </div>
                                            <div>
                                                <button id="zoom-out-docx" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-search-minus"></i>
                                                </button>
                                                <span id="zoom-level-docx" class="mx-2">100%</span>
                                                <button id="zoom-in-docx" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-search-plus"></i>
                                                </button>

                                                {{-- @if (auth()->user()->canDownloadFile()) --}}
                                                <a href="{{ route('kirim-surat.download', $surat->id) }}"
                                                    class="btn btn-sm btn-primary ml-2">
                                                    <i class="fas fa-download mr-1"></i> Download
                                                </a>
                                                {{-- @endif --}}
                                            </div>
                                        </div>

                                        <div id="docx-content-container"
                                            style="
                                                height: 70vh;
                                                overflow-y: auto;
                                                border: 1px solid #ddd;
                                                background: #f8f9fa;
                                                padding: 20px;
                                            ">
                                            <div id="docx-content" class="no-select">
                                                {!! $docxHtml !!}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Fallback to Online Viewers --}}
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-file-word mr-2"></i>Dokumen Microsoft Word</h5>
                                        <p class="mb-3">Pilih salah satu opsi untuk melihat dokumen:</p>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <i class="fab fa-microsoft fa-2x text-primary mb-2"></i>
                                                        <h6>Office Online</h6>
                                                        <button class="btn btn-primary btn-sm"
                                                            onclick="viewWithOfficeOnline()">
                                                            <i class="fas fa-eye mr-1"></i> Lihat
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <i class="fab fa-google fa-2x text-danger mb-2"></i>
                                                        <h6>Google Docs</h6>
                                                        <button class="btn btn-danger btn-sm"
                                                            onclick="viewWithGoogleDocs()">
                                                            <i class="fas fa-eye mr-1"></i> Lihat
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (auth()->user()->canDownloadFile())
                                                <div class="col-md-4">
                                                    <div class="card border-success">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-download fa-2x text-success mb-2"></i>
                                                            <h6>Download</h6>
                                                            <a href="{{ route('kirim-surat.download', $surat->id) }}"
                                                                class="btn btn-success btn-sm">
                                                                <i class="fas fa-download mr-1"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Online Viewer Containers --}}
                                    <div id="office-viewer-container" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6><i class="fab fa-microsoft mr-2"></i>Microsoft Office Online</h6>
                                            <button class="btn btn-sm btn-secondary"
                                                onclick="closeViewer('office-viewer-container')">
                                                <i class="fas fa-times"></i> Tutup
                                            </button>
                                        </div>
                                        <div style="height: 70vh; border: 1px solid #ddd;">
                                            <iframe id="office-viewer-frame" width="100%" height="100%"
                                                frameborder="0"></iframe>
                                        </div>
                                    </div>

                                    <div id="google-docs-container" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6><i class="fab fa-google mr-2"></i>Google Docs Viewer</h6>
                                            <button class="btn btn-sm btn-secondary"
                                                onclick="closeViewer('google-docs-container')">
                                                <i class="fas fa-times"></i> Tutup
                                            </button>
                                        </div>
                                        <div style="height: 70vh; border: 1px solid #ddd;">
                                            <iframe id="google-docs-frame" width="100%" height="100%"
                                                frameborder="0"></iframe>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Unknown file type --}}
                            <div class="p-4 text-center">
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle mr-2"></i>File Tidak Dapat Ditampilkan</h5>
                                    <p>Tipe file ini tidak dapat ditampilkan di browser.</p>
                                    <a href="{{ route('kirim-surat.download', $surat->id) }}" class="btn btn-primary">
                                        <i class="fas fa-download mr-1"></i> Download File
                                    </a>
                                    {{-- <p class="text-muted">Hubungi admin untuk mengakses file ini.</p> --}}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- File Info --}}
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Pengirim:</strong> {{ $surat->user->name ?? 'N/A' }}<br>
                                    <strong>Tanggal:</strong> {{ $surat->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div class="col-md-6 text-right">
                                <small class="text-muted">
                                    @if ($surat->read_at)
                                        <i class="fas fa-eye text-success"></i>
                                        Dibaca oleh {{ $surat->opened_by }} pada
                                        {{ $surat->read_at->format('d/m/Y H:i') }}
                                    @else
                                        <i class="fas fa-eye-slash text-muted"></i> Belum dibaca
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- @push('styles')
        <style>
            .no-select {
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }

            #pdf-canvas {
                display: block;
                margin: 0 auto;
            }

            #pdf-toolbar {
                background-color: #f8f9fa !important;
                border-bottom: 1px solid #dee2e6;
            }
        </style>
    @endpush --}}

    @push('scripts')
        <!-- PDF.js Library -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

        <script>
            // Set worker untuk PDF.js
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            // Set permission untuk user
            window.canDownload = {{ auth()->user()->hasRole('super admin') ? 'true' : 'false' }};

            @if ($fileExtension === 'pdf')
                // PDF Viewer Variables
                let pdfDoc = null;
                let pageNum = 1;
                let pageRendering = false;
                let pageNumPending = null;
                let scale = 1.2;
                const canvas = document.getElementById('pdf-canvas');
                const ctx = canvas.getContext('2d');

                // Load PDF
                const pdfUrl = "{{ route('surat.stream', $surat->id) }}";

                pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
                    pdfDoc = pdfDoc_;
                    document.getElementById('page-count').textContent = pdfDoc.numPages;

                    // Enable/disable navigation buttons
                    document.getElementById('prev-page').disabled = pageNum <= 1;
                    document.getElementById('next-page').disabled = pageNum >= pdfDoc.numPages;

                    // Initial page render
                    renderPage(pageNum);
                }).catch(function(error) {
                    console.error('Error loading PDF:', error);
                    document.getElementById('pdf-canvas-container').innerHTML =
                        '<div class="alert alert-danger m-3">Error loading PDF file</div>';
                });

                // Render page
                function renderPage(num) {
                    pageRendering = true;

                    pdfDoc.getPage(num).then(function(page) {
                        const viewport = page.getViewport({
                            scale: scale
                        });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };

                        const renderTask = page.render(renderContext);

                        renderTask.promise.then(function() {
                            pageRendering = false;
                            if (pageNumPending !== null) {
                                renderPage(pageNumPending);
                                pageNumPending = null;
                            }
                        });
                    });

                    document.getElementById('page-num').textContent = num;
                }

                // Queue rendering
                function queueRenderPage(num) {
                    if (pageRendering) {
                        pageNumPending = num;
                    } else {
                        renderPage(num);
                    }
                }

                // Previous page
                document.getElementById('prev-page').addEventListener('click', function() {
                    if (pageNum <= 1) return;
                    pageNum--;
                    queueRenderPage(pageNum);

                    document.getElementById('prev-page').disabled = pageNum <= 1;
                    document.getElementById('next-page').disabled = false;
                });

                // Next page
                document.getElementById('next-page').addEventListener('click', function() {
                    if (pageNum >= pdfDoc.numPages) return;
                    pageNum++;
                    queueRenderPage(pageNum);

                    document.getElementById('next-page').disabled = pageNum >= pdfDoc.numPages;
                    document.getElementById('prev-page').disabled = false;
                });

                // Zoom in
                document.getElementById('zoom-in').addEventListener('click', function() {
                    if (scale < 3) {
                        scale += 0.2;
                        document.getElementById('zoom-level').textContent = Math.round(scale * 100) + '%';
                        queueRenderPage(pageNum);
                    }
                });

                // Zoom out
                document.getElementById('zoom-out').addEventListener('click', function() {
                    if (scale > 0.5) {
                        scale -= 0.2;
                        document.getElementById('zoom-level').textContent = Math.round(scale * 100) + '%';
                        queueRenderPage(pageNum);
                    }
                });
            @endif

            // Office Online Viewer for Word documents
            function viewWithOfficeOnline() {
                const streamUrl = "{{ route('surat.stream', $surat->id) }}";
                const officeUrl =
                    `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(window.location.origin + streamUrl)}`;

                document.getElementById('office-viewer-frame').src = officeUrl;
                document.getElementById('office-viewer-container').style.display = 'block';
            }

            // Security measures
            document.addEventListener('keydown', function(e) {
                if (!window.canDownload) {
                    if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'a' || e.key === 'p' || e.key ===
                            'u')) {
                        e.preventDefault();
                        alert('Fungsi ini tidak diizinkan untuk role Anda');
                    }
                    if (e.key === 'F12') {
                        e.preventDefault();
                        alert('Developer tools tidak diizinkan');
                    }
                }
            });

            document.addEventListener('contextmenu', function(e) {
                if (!window.canDownload) {
                    e.preventDefault();
                }
            });

            // Disable printing
            window.addEventListener('beforeprint', function(e) {
                if (!window.canDownload) {
                    e.preventDefault();
                    alert('Printing tidak diizinkan untuk role Anda');
                }
            });
        </script>

        <script>
            @if ($docxHtml)
                // DOCX Zoom functionality
                let docxZoomLevel = 1.0;

                document.getElementById('zoom-in-docx').addEventListener('click', function() {
                    if (docxZoomLevel < 2.0) {
                        docxZoomLevel += 0.1;
                        applyDocxZoom();
                    }
                });

                document.getElementById('zoom-out-docx').addEventListener('click', function() {
                    if (docxZoomLevel > 0.5) {
                        docxZoomLevel -= 0.1;
                        applyDocxZoom();
                    }
                });

                function applyDocxZoom() {
                    const content = document.getElementById('docx-content');
                    content.style.transform = `scale(${docxZoomLevel})`;
                    content.style.transformOrigin = 'top left';
                    content.style.width = `${100/docxZoomLevel}%`;

                    document.getElementById('zoom-level-docx').textContent = Math.round(docxZoomLevel * 100) + '%';
                }
            @else
                // Online viewers functions (same as before)
                function viewWithOfficeOnline() {
                    const streamUrl = "{{ route('surat.stream', $surat) }}";
                    const fullUrl = window.location.origin + streamUrl;
                    const officeUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fullUrl)}`;

                    document.getElementById('office-viewer-frame').src = officeUrl;
                    document.getElementById('office-viewer-container').style.display = 'block';
                }

                function viewWithGoogleDocs() {
                    const streamUrl = "{{ route('surat.stream', $surat) }}";
                    const fullUrl = window.location.origin + streamUrl;
                    const googleUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(fullUrl)}&embedded=true`;

                    document.getElementById('google-docs-frame').src = googleUrl;
                    document.getElementById('google-docs-container').style.display = 'block';
                }

                function closeViewer(containerId) {
                    document.getElementById(containerId).style.display = 'none';
                    if (containerId === 'office-viewer-container') {
                        document.getElementById('office-viewer-frame').src = '';
                    } else if (containerId === 'google-docs-container') {
                        document.getElementById('google-docs-frame').src = '';
                    }
                }
            @endif
        </script>
    @endpush
@endsection
