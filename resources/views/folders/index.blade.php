@extends('layouts.app', ['title' => 'Folder Managers | IDocMA'])

@section('content')
    <scetion class="section">
        <div class="section-header">
            <h1>
                <i class="fas fa-folder-open mr-2"></i>
                Manajemen Dokumen
            </h1>
            <div class="section-header-button">
                <button class="btn btn-primary mr-2" onclick="showCreateFolderModal()">
                    <i class="fas fa-folder-plus mr-1"></i>
                    Buat Folder
                </button>
                <button class="btn btn-success" onclick="showUploadModal()">
                    <i class="fas fa-upload mr-1"></i>
                    Upload File
                </button>
            </div>
        </div>

        <div class="section-body">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb bg-white border" id="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="#" onclick="navigateToFolder(null)">
                            <i class="fas fa-home mr-1"></i>Root
                        </a>
                    </li>
                </ol>
            </nav>

            <!-- Toolbar -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="search-input-container">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-search" id="searchIcon"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Cari folder atau file..."
                                        id="searchInput" autocomplete="off">
                                    <button type="button" class="clear-search-btn-search" onclick="clearSearch()"
                                        title="Clear search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="search-suggestions" id="searchSuggestions" style="display: none;">
                                    <!-- Search suggestions will be populated here -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted" id="itemsCounter" style="display: none;">
                                <!-- Items counter will be updated by JavaScript -->
                            </small>
                        </div>
                        <div class="col-md-3 text-right">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary active" onclick="setViewMode('grid')"
                                    title="Grid View">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="setViewMode('list')"
                                    title="List View">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                            <button class="btn btn-outline-secondary ml-2" onclick="loadFolderContent(currentFolderId)"
                                title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div class="text-center py-1" id="loadingSpinner" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data...</p>
            </div>

            <!-- Content Area -->
            <div class="row" id="contentArea">
                <!-- Folders will be loaded here -->
            </div>

            <!-- Ganti Load More section dengan ini: -->
            <div id="loadMoreContainer" class="text-center mt-4" style="display: none;">
                <!-- Loading Spinner -->
                <div id="loadMoreSpinner" class="mb-3" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <span class="ml-2 text-muted">Loading more items...</span>
                </div>

                <!-- Load More Button -->
                <button id="loadMoreButton" class="btn btn-outline-primary btn-lg px-4" onclick="loadMoreManual()">
                    <i class="fas fa-plus mr-2"></i>Load More
                </button>

                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Scroll down to automatically load more items
                    </small>
                </div>
            </div>

            <!-- Empty State -->
            <div class="text-center py-5" id="emptyState" style="display: none;">
                <i class="fas fa-folder-open" style="font-size: 4rem; color: #6c757d; margin-bottom: 20px;"></i>
                <h4>Folder Kosong</h4>
                <p class="text-muted">Belum ada folder atau file di dalam direktori ini.</p>
                <button class="btn btn-primary" onclick="showCreateFolderModal()">
                    <i class="fas fa-folder-plus mr-1"></i>
                    Buat Folder Pertama
                </button>
            </div>
        </div>

        <!-- Scroll Progress Indicator (opsional - tambahkan di bagian bawah) -->
        <div id="scrollProgress"
            style="
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #007bff, #28a745);
            z-index: 9999;
            transition: width 0.1s ease;
        ">
        </div>

        <!-- Floating Back to Top Button -->
        <button id="backToTop" class="btn btn-primary rounded-circle shadow"
            style="
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                display: none;
                z-index: 1000;
            "
            onclick="scrollToTop()">
            <i class="fas fa-arrow-up"></i>
        </button>

    </scetion>

    <!-- Modal Create Folder -->
    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Folder Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="createFolderForm">
                        <div class="form-group">
                            <label>Nama Folder <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="folderName" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" id="folderDescription" rows="3"></textarea>
                        </div>

                        <h6 class="mb-3">Permissions Folder</h6>

                        <div id="permissionContainer"></div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="createFolder()">
                        <i class="fas fa-save mr-1"></i>
                        Buat Folder
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload File -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload File</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Pilih File <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="fileInput" multiple>
                                <label class="custom-file-label" for="fileInput">Pilih file...</label>
                            </div>
                            <small class="form-text text-muted">Maksimal ukuran file 10MB per file</small>
                        </div>

                        <div class="form-group">
                            <label>Kategori</label>
                            <select class="form-control" id="category">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Pilih kategori dokumen</small>
                        </div>

                        <div class="form-group">
                            <label>Is Latter</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_latter" value="1">
                                <label class="custom-control-label" for="is_latter">Mark as Latter Document</label>
                            </div>
                            <small class="form-text text-muted">Centang jika dokumen ini adalah surat/dokumen resmi</small>
                        </div>

                        <div class="form-group">
                            <label for="document_number">Nomor Dokumen</label>
                            <input type="text" class="form-control" id="document_number"
                                placeholder="Masukkan nomor dokumen jika ada">
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" id="fileDescription" rows="3"
                                placeholder="Deskripsi opsional untuk file yang akan diupload"></textarea>
                        </div>
                    </form>

                    <div id="uploadProgress" style="display: none;">
                        <div class="progress mb-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 0%"></div>
                        </div>
                        <div class="text-center">
                            <small id="uploadStatus">Uploading...</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="uploadBtn" onclick="uploadFiles()">
                        <i class="fas fa-upload mr-1"></i>
                        Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Folder Permission -->
    <div class="modal fade" id="permissionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Permission</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="permissionForm" class="mb-5">
                        <input type="hidden" id="folderId">
                        <div id="showModalPermissionContainer"></div>

                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered datatable" id="permissionTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>User</th>
                                    <th>Unit</th>
                                    <th>Role</th>
                                    <th>Permission</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>

                    <button type="button" class="btn btn-primary btn-set-permission" onclick="setFolderPermission()">
                        <i class="fas fa-save mr-1"></i>
                        Set Permission
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Document Permission -->
    <div class="modal fade" id="documentPermissionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Permission</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="documentPermissionForm" class="mb-5">
                        <input type="hidden" id="documentId">
                        <div id="showModalDocumentPermissionContainer"></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered datatable" id="documentPermissionTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>User</th>
                                    <th>Unit</th>
                                    <th>Role</th>
                                    <th>Permission</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>

                    <button type="button" class="btn btn-primary btn-set-permission" onclick="setDocumentPermission()">
                        <i class="fas fa-save mr-1"></i>
                        Set Document Permission
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Scroll progress indicator
        $(window).scroll(function() {
            const scrollTop = $(window).scrollTop();
            const docHeight = $(document).height();
            const winHeight = $(window).height();
            const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
            $('#scrollProgress').css('width', scrollPercent + '%');

            // Show/hide back to top button
            if (scrollTop > 500) {
                $('#backToTop').fadeIn();
            } else {
                $('#backToTop').fadeOut();
            }
        });

        function scrollToTop() {
            $('html, body').animate({
                scrollTop: 0
            }, 800);
        }
    </script>
    <script>
        let currentFolderId = null;
        let viewMode = 'grid';
        let isLoading = false;
        let hasMoreItems = true;
        let currentOffset = 0;
        let itemsPerLoad = 10;
        let totalItems = 0;
        let loadedItems = 0;
        let isSearchMode = false;

        $(document).ready(function() {
            $('.datatable').DataTable();
            // Load initial content
            loadFolderContent(null);

            // Setup infinite scroll
            $(window).on('scroll', throttle(handleInfiniteScroll, 150));

            // Setup search
            $('#searchInput').on('keyup', debounce(() => {
                performSearch($('#searchInput').val());
            }, 500));

            // File input change handler
            $('#fileInput').on('change', function() {
                const files = Array.from(this.files);
                if (files.length > 0) {
                    $('.custom-file-label').text(`${files.length} file(s) selected`);
                } else {
                    $('.custom-file-label').text('Pilih file...');
                }
            });

            const searchInput = $('#searchInput');
            const searchContainer = $('.search-input-container');
            const clearBtn = $('.clear-search-btn-search');
            const searchIcon = $('#searchIcon');

            // Show/hide clear button
            searchInput.on('input', function() {
                const hasContent = $(this).val().length > 0;
                searchContainer.toggleClass('has-content', hasContent);

                // Change icon during search
                if (hasContent) {
                    searchIcon.removeClass('fa-search').addClass('fa-search text-primary');
                } else {
                    searchIcon.removeClass('text-primary').addClass('fa-search');
                }
            });

            // Handle Enter key
            searchInput.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch($(this).val());
                }

                // Handle Escape key to clear search
                if (e.key === 'Escape') {
                    clearSearch();
                }
            });

            // Focus handling
            searchInput.on('focus', function() {
                $(this).parent().parent().addClass('shadow-sm');
            }).on('blur', function() {
                $(this).parent().parent().removeClass('shadow-sm');
            });

            $('#uploadModal').on('hidden.bs.modal', function() {
                // Reset form dan checkbox saat modal ditutup
                $('#uploadForm')[0].reset();
                $('#is_latter').prop('checked', false);
                $('.custom-file-label').text('Pilih file...');
                $('#uploadProgress').hide();
                $('.modal-footer button').prop('disabled', false);
                $('.progress-bar').css('width', '0%');
                $('#uploadStatus').text('Uploading...');
            });

            handleModalEvents('#createFolderModal', '#permissionContainer');
            handleModalEvents('#permissionModal', '#showModalPermissionContainer');
            handleModalEvents('#documentPermissionModal', '#showModalDocumentPermissionContainer');
        });
    </script>

    <script>
        function loadFolderContent(folderId) {
            resetLoadingState();
            showLoading();
            currentFolderId = folderId;

            const url = folderId ?
                `{{ url('folders/browse') }}/${folderId}` :
                `{{ url('folders/browse') }}`;

            // console.log('Loading folder:', folderId, 'URL:', url);

            $.get(url, {
                    offset: 0,
                    limit: itemsPerLoad
                })
                .done(function(response) {
                    console.log('Response received:', response);

                    // Check if request was successful
                    if (!response.success) {
                        console.error('Access denied:', response.message);
                        showAlert('error', response.message || 'Anda tidak memiliki akses ke folder ini');
                        hideLoading();
                        return;
                    }

                    updateBreadcrumb(response.breadcrumb, response.current_folder);

                    // Clear content area
                    $('#contentArea').html('');

                    // Check if we have any data
                    if (response.folders.length === 0 && response.documents.length === 0) {
                        // console.log('No data found, showing empty state');
                        $('#contentArea').hide();
                        $('#emptyState').show();
                    } else {
                        // console.log('Data found:', response.folders.length, 'folders,', response.documents.length,
                        //     'documents');
                        $('#emptyState').hide();
                        $('#contentArea').show();

                        // Use renderContent instead of appendItemsToContent for initial load
                        renderContent(response.folders, response.documents);
                    }

                    // Update state
                    currentOffset = response.loaded_items || (response.folders.length + response.documents.length);
                    hasMoreItems = response.has_more || false;
                    totalItems = response.total_items || (response.folders.length + response.documents.length);
                    loadedItems = response.loaded_items || (response.folders.length + response.documents.length);

                    updateItemsCounter();
                    toggleLoadMoreButton();
                    hideLoading();
                })
                .fail(function(xhr) {
                    console.error('Failed to load folder content:', xhr);

                    // Rollback ke folder sebelumnya
                    currentFolderId = previousFolderId;

                    // Try to parse JSON response if available
                    let errorMessage = 'Terjadi kesalahan saat memuat folder';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // Use default message if JSON parsing fails
                    }

                    showAlert('error', errorMessage);
                    hideLoading();
                    handleLoadError(xhr);
                });
        }

        function renderContent(folders, documents) {
            const contentArea = $('#contentArea');
            const emptyState = $('#emptyState');

            // console.log('Rendering content:', folders.length, 'folders,', documents.length, 'documents');

            if (folders.length === 0 && documents.length === 0) {
                contentArea.hide();
                emptyState.show();
                return;
            }

            contentArea.show();
            emptyState.hide();
            contentArea.html(''); // Clear existing content

            // Render folders first
            if (folders && folders.length > 0) {
                folders.forEach(folder => {
                    // console.log('Rendering folder:', folder.name);
                    const folderHtml = viewMode === 'grid' ? renderFolderGrid(folder) : renderFolderList(folder);
                    contentArea.append(folderHtml);
                });
            }

            // Render documents
            if (documents && documents.length > 0) {
                documents.forEach(document => {
                    console.log('Rendering document:', document.name);
                    const documentHtml = viewMode === 'grid' ? renderDocumentGrid(document) : renderDocumentList(
                        document);
                    contentArea.append(documentHtml);
                });
            }

            // console.log('Content rendered, total items in DOM:', contentArea.children().length);
        }

        function showFolderInfo(folderId) {
            $.get(`{{ url('folders/info') }}/${folderId}`)
                .done(function(response) {
                    if (response.success) {
                        const folder = response.folder;
                        let infoHtml = `
                            <div class="modal fade" id="folderInfoModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title">Info Folder</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Nama Folder:</strong> ${folder.name}</p>
                                            <p><strong>Deskripsi:</strong> ${folder.description || 'Tidak ada deskripsi'}</p>
                                            <p><strong>Dibuat Pada:</strong> ${folder.created_at}</p>
                                            <p><strong>Total Sub Folder:</strong> ${folder.subfolders_count} Folder</p>
                                            <p><strong>Total Dokumen:</strong> ${folder.document_count} Dokumen</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Remove existing modal if any
                        $('#folderInfoModal').remove();

                        // Add modal to body and show
                        $('body').append(infoHtml);
                        $('#folderInfoModal').modal('show');

                        // Remove modal from DOM after it's hidden
                        $('#folderInfoModal').on('hidden.bs.modal', function() {
                            $(this).remove();
                        });
                    } else {
                        showAlert('error', response.message || 'Gagal memuat info folder');
                    }
                })
                .fail(function() {
                    showAlert('error', 'Gagal memuat info folder');
                });
        }

        function showDocumentInfo(documentId) {
            $.get(`{{ url('documents/info') }}/${documentId}`)
                .done(function(response) {
                    if (response.success) {
                        const document = response.document;
                        let infoHtml = `
                            <div class="modal fade" id="documentInfoModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title">Info Document</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Nama Document:</strong> ${document.name}</p>
                                            <p><strong>Deskripsi:</strong> ${document.description || 'Tidak ada deskripsi'}</p>
                                            <p><strong>Tanggal Upload:</strong> ${moment(document.created_at).format('DD MMMM YYYY HH:mm')}</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Remove existing modal if any
                        $('#documentInfoModal').remove();

                        // Add modal to body and show
                        $('body').append(infoHtml);
                        $('#documentInfoModal').modal('show');

                        // Remove modal from DOM after it's hidden
                        $('#documentInfoModal').on('hidden.bs.modal', function() {
                            $(this).remove();
                        });
                    } else {
                        showAlert('error', response.message || 'Gagal memuat info document');
                    }
                })
                .fail(function() {
                    showAlert('error', 'Gagal memuat info document');
                });
        }

        function renderFolderGrid(folder) {
            return `
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="folder-item text-center" onclick="navigateToFolder(${folder.id})">
                    <div class="folder-actions position-absolute" style="top: 10px; right: 10px;">
                        <button class="btn btn-sm btn-info btn-circle"
                                onclick="event.stopPropagation(); showFolderInfo(${folder.id})"
                                title="Setting Folder Permission">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="btn btn-sm btn-circle btn-warning"
                                onclick="event.stopPropagation(); showPermission(${folder.id})"
                                title="Setting Folder Permission">
                            <i class="fas fa-cogs"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-circle"
                                onclick="event.stopPropagation(); deleteFolder(${folder.id})"
                                title="Hapus Folder">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <i class="fas fa-folder folder-icon"></i>
                    <div class="folder-name">${folder.name}</div>
                    <div class="folder-info">
                        <small>
                            <i class="fas fa-folder mr-1"></i>${folder.subfolders_count} folder
                            <br>
                            <i class="fas fa-file mr-1"></i>${folder.documents_count} file
                        </small>
                    </div>
                    <div class="folder-info mt-1">
                        <small class="text-muted">${folder.created_at}</small>
                    </div>
                </div>
            </div>
        `;
        }

        function renderFolderList(folder) {
            return `
                <div class="col-12">
                    <div class="folder-item d-flex align-items-center" onclick="navigateToFolder(${folder.id})">
                        <i class="fas fa-folder text-warning mr-3" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <div class="folder-name mb-1">${folder.name}</div>
                            <div class="folder-info">
                                <small class="text-muted">${folder.description || 'Tidak ada deskripsi'}</small>
                            </div>
                        </div>
                        <div class="text-right mr-3">
                            <small class="text-muted d-block">${folder.subfolders_count} folder, ${folder.documents_count} file</small>
                            <small class="text-muted">${folder.created_at}</small>
                        </div>
                        <div class="folder-actions">
                            <button class="btn btn-sm btn-info"
                                    onclick="event.stopPropagation(); showFolderInfo(${folder.id})"
                                    title="Info Folder">
                                <i class="fas fa-info"></i>
                            </button>
                            <button class="btn btn-sm btn-warning"
                                    onclick="event.stopPropagation(); showPermission(${folder.id})"
                                    title="Setting Folder Permission">
                                <i class="fas fa-cogs"></i>
                            </button>
                            <button class="btn btn-sm btn-danger"
                                    onclick="event.stopPropagation(); deleteFolder(${folder.id})"
                                    title="Hapus Folder">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderDocumentGrid(document) {
            const icon = getFileIcon(document.extension);
            return `
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <div class="file-item text-center position-relative" onclick="downloadDocument(${document.id})">
                        <!-- Action buttons -->
                        <div class="document-actions position-absolute" style="top: 10px; right: 10px;">
                            <button class="btn btn-sm btn-info btn-circle"
                                onclick="event.stopPropagation(); showDocumentInfo(${document.id})"
                                title="Setting Document Permission">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button class="btn btn-sm btn-warning btn-circle"
                                    onclick="event.stopPropagation(); showDocumentPermissions(${document.id})"
                                    title="Share Dokumen">
                                <i class="fas fa-share"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-circle"
                                    onclick="event.stopPropagation(); deleteDocument(${document.id})"
                                    title="Hapus Dokumen">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <i class="${icon} file-icon" style="font-size: 2rem;"></i>
                        <div class="folder-name" style="font-size: 0.9rem;">${document.name}</div>
                        <div class="folder-info">
                            <small class="text-muted">
                                ${formatFileSize(document.file_size)}
                                <br>
                                ${document.created_at}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderDocumentList(document) {
            const icon = getFileIcon(document.extension);
            return `
                <div class="col-12">
                    <div class="file-item d-flex align-items-center" onclick="downloadDocument(${document.id})">
                        <i class="${icon} mr-3" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <div class="folder-name mb-1" style="font-weight: 500;">${document.name}</div>
                            <div class="folder-info">
                                <small class="text-muted">${document.file_name}</small>
                            </div>
                        </div>
                        <div class="text-right mr-3">
                            <small class="text-muted d-block">${formatFileSize(document.file_size)}</small>
                            <small class="text-muted">${document.created_at}</small>
                        </div>
                        <div class="document-actions">
                            <button class="btn btn-sm btn-warning"
                                    onclick="event.stopPropagation(); showDocumentPermissions(${document.id})"
                                    title="Share Dokumen">
                                <i class="fas fa-share"></i>
                            </button>
                            <button class="btn btn-sm btn-danger"
                                    onclick="event.stopPropagation(); deleteDocument(${document.id})"
                                    title="Hapus Dokumen">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function getFileIcon(filename) {
            const extension = filename.split('.').pop().toLowerCase();

            const iconMap = {
                'pdf': 'fas fa-file-pdf text-danger',
                'doc': 'fas fa-file-word text-primary',
                'docx': 'fas fa-file-word text-primary',
                'xls': 'fas fa-file-excel text-success',
                'xlsx': 'fas fa-file-excel text-success',
                'ppt': 'fas fa-file-powerpoint text-warning',
                'pptx': 'fas fa-file-powerpoint text-warning',
                'jpg': 'fas fa-file-image text-info',
                'jpeg': 'fas fa-file-image text-info',
                'png': 'fas fa-file-image text-info',
                'gif': 'fas fa-file-image text-info',
                'zip': 'fas fa-file-archive text-secondary',
                'rar': 'fas fa-file-archive text-secondary',
                'txt': 'fas fa-file-alt text-secondary',
                'default': 'fas fa-file text-secondary'
            };

            return iconMap[extension] || iconMap['default'];
        }

        function showPermission(folderId) {
            $('#permissionModal').modal('show');
            $.get(`{{ url('folders/get-permission') }}`, {
                    folder_id: folderId
                })
                .done(function(response) {
                    if (response.success) {
                        console.log(response)
                        const permissionTable = $('#permissionTable');
                        const tbody = permissionTable.find('tbody');
                        const fodlerIdField = $('#folderId');
                        fodlerIdField.val(folderId);

                        tbody.empty(); // Clear existing data

                        response.data.forEach((permission, index) => {
                            const row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${permission.user ? permission.user.name : 'N/A'}</td>
                                    <td>${permission.unit ? permission.unit.name : 'N/A'}</td>
                                    <td>${permission.role ? permission.role.name : 'N/A'}</td>
                                    <td>${permission.permission_type}</td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    }
                })
                .fail(function() {
                    console.error('Error loading permission data:', response);
                    showAlert('error', 'Gagal memuat data izin folder');
                });
            // loadPermissionFolder(folderId);
        }

        function setFolderPermission() {
            const units = $('#folderUnit').val(); // Array of unit IDs
            const roles = $('#folderRole').val(); // Array of role IDs
            const users = $('#folderUser').val(); // Array of user IDs
            const permissionTypes = $('#folderPermissionTypes').val();
            const folderId = $('#folderId').val();

            const data = {
                folder_id: folderId,
                user_id: users,
                role_id: roles,
                unit_id: units,
                permission_types: permissionTypes,
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            console.log(data);


            $.post("{{ route('folders.set-permission') }}", data)
                .done(function(response) {
                    console.log(response);
                    if (response.success) {
                        showAlert('success', 'Izin folder berhasil disetel');
                        $('#permissionModal').modal('hide');
                    } else {
                        // Handle warning/error dari response
                        if (response.status === 'warning' && response.existing_permissions) {
                            showExistingPermissionsModal(response);
                        } else {
                            showAlert('error', response.message || 'Gagal menyetel izin folder');
                        }
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);

                    let errorMessage = 'Gagal menyetel izin folder';

                    try {
                        const response = JSON.parse(xhr.responseText);

                        if (response.status === 'warning' && response.existing_permissions) {
                            // Handle warning case dari fail handler (status 409)
                            showExistingPermissionsModal(response);
                            return;
                        } else if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // Jika response bukan JSON, gunakan status text atau error message
                        errorMessage = xhr.statusText || error || 'Terjadi kesalahan sistem';
                    }

                    showAlert('error', errorMessage);
                });
        }

        // Function untuk menampilkan modal existing permissions
        function showExistingPermissionsModal(response) {
            let existingList = '';

            response.existing_permissions.forEach(function(perm) {
                existingList +=
                    `<li><strong>${perm.name}</strong> (${perm.type}) - Permission: ${perm.permission_type}</li>`;
            });

            const modalHtml = `
                <div class="modal fade" id="existingPermissionsModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Permission Sudah Ada
                                </h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Permission berikut sudah terdaftar untuk folder ini:</strong></p>
                                <ul class="list-unstyled">
                                    ${existingList}
                                </ul>
                                <p class="text-muted mt-3">
                                    <small>Apakah Anda ingin melanjutkan dengan menimpa permission yang sudah ada, atau batalkan operasi ini?</small>
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Batal
                                </button>
                                <button type="button" class="btn btn-warning" onclick="forceSetPermission()">
                                    Timpa Permission
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            $('#existingPermissionsModal').remove();

            // Add modal to body and show
            $('body').append(modalHtml);
            $('#existingPermissionsModal').modal('show');
        }

        // Function untuk force set permission (menimpa yang sudah ada)
        function forceSetPermission() {
            // Tambahkan parameter force ke data
            const forceData = {
                ...data,
                force: true
            };

            $.post("{{ route('folders.set-permission') }}", forceData)
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', 'Izin folder berhasil disetel');
                        $('#existingPermissionsModal').modal('hide');
                        $('#permissionModal').modal('hide');
                    } else {
                        showAlert('error', response.message || 'Gagal menyetel izin folder');
                    }
                })
                .fail(function(xhr) {
                    let errorMessage = 'Gagal menyetel izin folder';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.statusText || 'Terjadi kesalahan sistem';
                    }
                    showAlert('error', errorMessage);
                });
        }

        function loadMoreItems() {
            // Don't load more during search
            if (isSearchMode || isLoading || !hasMoreItems) {
                return;
            }

            // Rest of the function remains the same...
            isLoading = true;
            showLoadMoreSpinner();

            const url = currentFolderId ?
                `{{ url('folders/browse') }}/${currentFolderId}` :
                `{{ url('folders/browse') }}`;

            $.get(url, {
                    offset: currentOffset,
                    limit: itemsPerLoad
                })
                .done(function(response) {
                    appendItemsToContent(response.folders, response.documents);

                    // Update state
                    currentOffset = response.loaded_items;
                    hasMoreItems = response.has_more;
                    loadedItems = response.loaded_items;

                    updateItemsCounter();
                    toggleLoadMoreButton();
                })
                .fail(function() {
                    showAlert('error', 'Gagal memuat item tambahan');
                })
                .always(function() {
                    isLoading = false;
                    hideLoadMoreSpinner();
                });
        }

        function appendItemsToContent(folders, documents) {
            const contentArea = $('#contentArea');

            console.log('Appending content:', folders.length, 'folders,', documents.length, 'documents');

            // Show content area if hidden
            if (contentArea.is(':hidden')) {
                contentArea.show();
                $('#emptyState').hide();
            }

            // Add folders
            if (folders && folders.length > 0) {
                folders.forEach(folder => {
                    const folderHtml = viewMode === 'grid' ? renderFolderGrid(folder) : renderFolderList(folder);
                    const $element = $(folderHtml).hide();
                    contentArea.append($element);
                    $element.fadeIn(400);
                });
            }

            // Add documents
            if (documents && documents.length > 0) {
                documents.forEach(document => {
                    const documentHtml = viewMode === 'grid' ? renderDocumentGrid(document) : renderDocumentList(
                        document);
                    const $element = $(documentHtml).hide();
                    contentArea.append($element);
                    $element.fadeIn(400);
                });
            }

            console.log('Content appended, total items in DOM:', contentArea.children().length);

            // Show empty state if still no items
            if (contentArea.children().length === 0) {
                contentArea.hide();
                $('#emptyState').show();
            }
        }

        function handleInfiniteScroll() {
            // Disable infinite scroll during search
            if (isSearchMode || isLoading || !hasMoreItems) {
                return;
            }

            const scrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();

            // Load more when 300px from bottom
            if (scrollTop + windowHeight >= documentHeight - 300) {
                loadMoreItems();
            }
        }

        function resetLoadingState() {
            isLoading = false;
            hasMoreItems = true;
            currentOffset = 0;
            totalItems = 0;
            loadedItems = 0;
        }

        function updateItemsCounter() {
            const counter = $('#itemsCounter');
            if (totalItems > 0) {
                counter.text(`${loadedItems} of ${totalItems} items`);
                counter.show();
            } else {
                counter.hide();
            }
        }

        function toggleLoadMoreButton() {
            const container = $('#loadMoreContainer');
            const button = $('#loadMoreButton');

            if (hasMoreItems) {
                container.show();
                button.html('<i class="fas fa-plus mr-2"></i>Load More');
            } else if (loadedItems > 0) {
                container.show();
                button.html('<i class="fas fa-check mr-2"></i>All items loaded').prop('disabled', true);
            } else {
                container.hide();
            }
        }

        function showLoadMoreSpinner() {
            $('#loadMoreSpinner').show();
            $('#loadMoreButton').prop('disabled', true);
        }

        function hideLoadMoreSpinner() {
            $('#loadMoreSpinner').hide();
            $('#loadMoreButton').prop('disabled', false);
        }

        function loadMoreManual() {
            loadMoreItems();
        }

        function updateBreadcrumb(breadcrumb, currentFolder) {
            const breadcrumbEl = $('#breadcrumb');

            breadcrumbEl.html(`
                <li class="breadcrumb-item">
                    <a href="#" onclick="navigateToFolder(null)">
                        <i class="fas fa-home mr-1"></i>Root
                    </a>
                </li>
            `);

            if (breadcrumb && breadcrumb.length > 0) {
                breadcrumb.forEach(function(item, index) {
                    const isLast = index === breadcrumb.length - 1;
                    if (isLast) {
                        breadcrumbEl.append(`
                        <li class="breadcrumb-item active">
                            <i class="fas fa-folder mr-1"></i>${item.name}
                        </li>
                    `);
                    } else {
                        breadcrumbEl.append(`
                        <li class="breadcrumb-item">
                            <a href="#" onclick="navigateToFolder(${item.id})">
                                <i class="fas fa-folder mr-1"></i>${item.name}
                            </a>
                        </li>
                    `);
                    }
                });
            }
        }

        function navigateToFolder(folderId) {
            // Clear search when navigating
            if (isSearchMode) {
                $('#searchInput').val('');
                isSearchMode = false;
            }
            loadFolderContent(folderId);
        }

        function setViewMode(mode) {
            viewMode = mode;
            $('.btn-group button').removeClass('active');
            $(`.btn-group button[onclick="setViewMode('${mode}')"]`).addClass('active');
            // Re-render existing content with new view mode
            const contentArea = $('#contentArea');
            const existingItems = [];

            // Extract existing data (you might want to store this in a variable)
            contentArea.children().each(function() {
                // This is a simplified approach - in real implementation,
                // you'd want to store the original data
                existingItems.push($(this).html());
            });

            // For now, just reload the current folder
            loadFolderContent(currentFolderId);
        }

        function showCreateFolderModal() {
            $('#createFolderModal').modal('show');
        }

        function showUploadModal() {
            if (currentFolderId === null) {
                showAlert('warning', 'Pilih folder terlebih dahulu untuk mengupload file');
                return;
            }
            $('#uploadModal').modal('show');
        }

        function loadMasterData() {
            // slect unit
            $.get('{{ route('folders.get-units') }}')
                .done(function(units) {
                    const selectUnit = $('#folderUnit');
                    // Simpan nilai yang dipilih sebelumnya
                    const selectedUnitValue = selectUnit.val();
                    selectUnit.html('<option value="">-- Pilih Unit --</option>');
                    units.forEach(function(unit) {
                        selectUnit.append(`<option value="${unit.id}">${unit.name}</option>`);
                    });

                    // Set nilai yang dipilih sebelumnya jika masih ada
                    if (selectedUnitValue) {
                        selectUnit.val(selectedUnitValue).trigger('change');
                    }

                    // Refresh Select2 untuk menampilkan data baru
                    selectUnit.trigger('change.select2');
                });

            // select roles
            $.get('{{ route('folders.get-roles') }}')
                .done(function(roles) {
                    const selectRole = $('#folderRole');

                    // Simpan nilai yang dipilih sebelumnya
                    const selectedRoleValue = selectRole.val();

                    selectRole.html('<option value="">-- Pilih Role --</option>');
                    roles.forEach(function(role) {
                        selectRole.append(`<option value="${role.id}">${role.name}</option>`);
                    });

                    // Set nilai yang dipilih sebelumnya jika masih ada
                    if (selectedRoleValue) {
                        selectRole.val(selectedRoleValue).trigger('change');
                    }

                    // Refresh Select2 untuk menampilkan data baru
                    selectRole.trigger('change.select2');
                });

            // Select usres
            $.get('{{ route('folders.get-users') }}')
                .done(function(users) {
                    const selectUser = $('#folderUser');

                    // Simpan nilai yang dipilih sebelumnya
                    const selectedUserValue = selectUser.val();

                    selectUser.html('<option value="">-- Pilih User --</option>');
                    users.forEach(function(user) {
                        selectUser.append(`<option value="${user.id}">${user.display_name}</option>`);
                    });

                    // Set nilai yang dipilih sebelumnya jika masih ada
                    if (selectedUserValue) {
                        selectUser.val(selectedUserValue).trigger('change');
                    }

                    // Refresh Select2 untuk menampilkan data baru
                    selectUser.trigger('change.select2');
                });
        }

        function createFolder() {
            const name = $('#folderName').val().trim();
            const description = $('#folderDescription').val().trim();
            const units = $('#folderUnits').val(); // Array of unit IDs
            const roles = $('#folderRoles').val(); // Array of role IDs
            const users = $('#folderUsers').val(); // Array of user IDs
            const permissionType = $('#folderPermissionType').val();

            if (!name) {
                showAlert('warning', 'Nama folder harus diisi!');
                return;
            }

            // Validate permission type if any permissions are set
            if (currentFolderId) {
                if ((units && units.length > 0) || (roles && roles.length > 0) || (users && users.length > 0)) {
                    if (!permissionType) {
                        showAlert('warning', 'Jenis permission harus dipilih!');
                        return;
                    }
                }
            }

            const data = {
                name: name,
                description: description,
                parent_id: currentFolderId,
                units: units,
                roles: roles,
                users: users,
                permission_type: permissionType,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            $.post('/folders', data)
                .done(function(response) {
                    if (response.success) {
                        $('#createFolderModal').modal('hide');
                        $('#createFolderForm')[0].reset();
                        $('#folderUnits').val(null).trigger('change');
                        $('#folderRoles').val(null).trigger('change');
                        $('#folderUsers').val(null).trigger('change');
                        showAlert('success', response.message);
                        loadFolderContent(currentFolderId);
                    } else {
                        showAlert('error', response.message);
                    }
                })
                .fail(function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal membuat folder';
                    showAlert('error', message);
                });
        }

        function uploadFiles() {
            const files = $('#fileInput')[0].files;
            const description = $('#fileDescription').val().trim();
            const isLatterChecked = $('#is_latter').is(':checked');
            const category = $('#category').val();
            const documentNumber = $('#document_number').val().trim();

            // console.log('Upload started with:');
            // console.log('- Files count:', files.length);
            // console.log('- Description:', description);
            // console.log('- Is Latter:', isLatterChecked);
            // console.log('- Current Folder ID:', currentFolderId);
            // console.log('- Category:', category);
            // console.log('- Document Number:', documentNumber);

            if (files.length === 0) {
                showAlert('warning', 'Pilih minimal satu file!');
                return;
            }

            if (!currentFolderId) {
                showAlert('warning', 'Pilih folder tujuan terlebih dahulu!');
                return;
            }

            const formData = new FormData();
            formData.append('folder_id', currentFolderId);
            formData.append('description', description);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                '{{ csrf_token() }}');

            // Penting: Kirim sebagai string, bukan boolean
            formData.append('is_latter', isLatterChecked ? '1' : '0');
            formData.append('category', category);
            formData.append('document_number', documentNumber);

            // Tambahkan files
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            // Debug FormData
            console.log('FormData entries:');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(`${key}: File - ${value.name}`);
                } else {
                    console.log(`${key}: ${value}`);
                }
            }

            // Show progress
            $('#uploadProgress').show();
            $('#uploadBtn').prop('disabled', true);
            $('.modal-footer .btn-secondary').prop('disabled', true);

            $.ajax({
                    url: '{{ route('documents.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                $('.progress-bar').css('width', percentComplete + '%');
                                $('.progress-bar').text(percentComplete + '%');
                                $('#uploadStatus').text(`Uploading... ${percentComplete}%`);
                            }
                        }, false);
                        return xhr;
                    }
                })
                .done(function(response) {
                    console.log('Upload response:', response);

                    if (response.success) {
                        let errorMessage = [];
                        $('#uploadModal').modal('hide');

                        if (response.errors && response.errors.length > 0) {
                            response.errors.forEach(error => {
                                errorMessage.push(error);
                            });
                        }

                        if (errorMessage.length > 0) {
                            showAlert('warning', errorMessage.join('<br>'));
                        } else {
                            showAlert('success', response.message || 'File berhasil diupload');
                        }
                        loadFolderContent(currentFolderId);

                        // // Log untuk debugging
                        // if (response.data && response.data.length > 0) {
                        //     console.log('Uploaded documents is_latter values:');
                        //     response.data.forEach(doc => {
                        //         console.log(`${doc.name}: is_latter = ${doc.is_latter}`);
                        //     });
                        // }
                    } else {
                        showAlert('error', response.message || 'Gagal mengupload file');
                        if (response.errors && response.errors.length > 0) {
                            response.errors.forEach(error => {
                                showAlert('warning', error);
                            });
                        }
                    }
                })
                .fail(function(xhr) {
                    console.error('Upload failed:', xhr);
                    let message = 'Gagal mengupload file';

                    if (xhr.responseJSON) {
                        message = xhr.responseJSON.message || message;
                        if (xhr.status == 422) {
                            // Clear previous error messages
                            $('.error-message').remove();

                            // Display error messages below each field
                            Object.keys(xhr.responseJSON.errors).forEach(field => {
                                const errorMessage = xhr.responseJSON.errors[field][0];
                                const inputField = $(`#${field}`);
                                inputField.addClass('is-invalid');
                                inputField.after(
                                    `<div class="invalid-feedback">${errorMessage}</div>`);
                            });

                            console.error('Validation errors:', xhr.responseJSON.errors);
                        }
                    }

                    showAlert('error', message);
                })
                .always(function() {
                    // Reset progress
                    $('#uploadProgress').hide();
                    $('#uploadBtn').prop('disabled', false);
                    $('.modal-footer .btn-secondary').prop('disabled', false);
                    $('.progress-bar').css('width', '0%');
                    $('.progress-bar').text('');
                    $('#uploadStatus').text('Uploading...');
                    // Clear validation errors
                });
        }

        async function downloadDocument(documentId) {
            try {
                const url = `{{ route('documents.download', ':id') }}`.replace(':id', documentId);

                // Lakukan fetch request terlebih dahulu
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Cek jika response adalah JSON (berarti ada error)
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();

                    if (response.status === 403) {
                        // Handle permission error
                        showAlert('error', data.error || 'Anda tidak memiliki izin untuk mengunduh dokumen ini');
                        return;
                    }

                    if (response.status === 500) {
                        // Handle server error
                        showAlert('error', data.error || 'Terjadi kesalahan saat mengunduh file');
                        return;
                    }
                }

                // Jika response adalah file download, redirect ke URL
                if (response.ok) {
                    window.location.href = url;
                } else {
                    alert('Terjadi kesalahan yang tidak diketahui');
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan atau server tidak merespon');
            }
        }

        function performSearch(searchTerm) {
            const trimmedTerm = searchTerm.trim();

            if (!trimmedTerm) {
                // Clear search mode and reload normal content
                isSearchMode = false;
                loadFolderContent(currentFolderId);
                return;
            }

            // Enter search mode
            isSearchMode = true;
            resetLoadingState();

            // Hide load more button during search
            $('#loadMoreContainer').hide();

            // Show loading
            showLoading();

            $.get('{{ route('folders.search') }}', {
                    q: trimmedTerm
                })
                .done(function(response) {
                    console.log('Search results:', response);
                    renderSearchResults(response.folders, response.documents, trimmedTerm);
                    hideLoading();
                })
                .fail(function() {
                    showAlert('error', 'Gagal melakukan pencarian');
                    hideLoading();
                });
        }

        function renderSearchResults(folders, documents, searchTerm) {
            const contentArea = $('#contentArea');
            const emptyState = $('#emptyState');

            // Clear content
            contentArea.html('');

            const totalResults = folders.length + documents.length;

            if (totalResults === 0) {
                // Show search empty state
                contentArea.html(`
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search" style="font-size: 3rem; color: #6c757d;"></i>
                    <h5 class="mt-3">Tidak ada hasil ditemukan</h5>
                    <p class="text-muted">Tidak ada folder atau file yang cocok dengan "<strong>${searchTerm}</strong>"</p>
                    <button class="btn btn-outline-primary" onclick="clearSearch()">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke folder
                    </button>
                </div>
            `);
                contentArea.show();
                emptyState.hide();
                return;
            }

            // Show search results header
            contentArea.append(`
                <div class="col-12 mb-3">
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-search mr-2"></i>
                            Hasil pencarian untuk "<strong>${searchTerm}</strong>": ${totalResults} item ditemukan
                        </div>
                        <button class="btn btn-sm btn-outline-info" onclick="clearSearch()">
                            <i class="fas fa-times mr-1"></i>Tutup Pencarian
                        </button>
                    </div>
                </div>
            `);

            contentArea.show();
            emptyState.hide();

            // Render folders with search highlight
            folders.forEach(folder => {
                const folderHtml = renderSearchFolder(folder, searchTerm);
                contentArea.append(folderHtml);
            });

            // Render documents with search highlight
            documents.forEach(document => {
                const documentHtml = renderSearchDocument(document, searchTerm);
                contentArea.append(documentHtml);
            });

            // Update counter for search results
            $('#itemsCounter').text(`${totalResults} search results`).show();
        }

        function renderSearchFolder(folder, searchTerm) {
            const highlightedName = highlightSearchTerm(folder.name, searchTerm);
            const highlightedDesc = folder.description ? highlightSearchTerm(folder.description, searchTerm) :
                'Tidak ada deskripsi';

            return `
                <div class="col-12 mb-2">
                    <div class="folder-item d-flex align-items-center" onclick="navigateToFolder(${folder.id})">
                        <i class="fas fa-folder text-warning mr-3" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <div class="folder-name mb-1">${highlightedName}</div>
                            <div class="folder-info">
                                <small class="text-muted">${highlightedDesc}</small>
                                <br>
                                <small class="text-info">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    Path: ${folder.path || 'Root'}
                                </small>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-primary">Folder</span>
                            <br>
                            <small class="text-muted">${folder.created_at || ''}</small>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderSearchDocument(document, searchTerm) {
            const icon = getFileIcon(document.original_name || document.name);
            const highlightedName = highlightSearchTerm(document.name, searchTerm);
            const highlightedOriginal = document.original_name ? highlightSearchTerm(document.original_name, searchTerm) :
                '';

            return `
                <div class="col-12 mb-2">
                    <div class="file-item d-flex align-items-center" onclick="downloadDocument(${document.id})">
                        <i class="${icon} mr-3" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <div class="folder-name mb-1" style="font-weight: 500;">${highlightedName}</div>
                            <div class="folder-info">
                                ${highlightedOriginal ? `<small class="text-muted">${highlightedOriginal}</small><br>` : ''}
                                <small class="text-info">
                                    <i class="fas fa-folder mr-1"></i>
                                    Folder: ${document.folder_name || 'Unknown'}
                                </small>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-success">Dokumen</span>
                            <br>
                            <small class="text-muted">${document.created_at || ''}</small>
                        </div>
                    </div>
                </div>
            `;
        }

        function highlightSearchTerm(text, searchTerm) {
            if (!text || !searchTerm) return text;

            const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
            return text.replace(regex, '<mark class="bg-warning">$1</mark>');
        }

        function escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function clearSearch() {
            const searchInput = $('#searchInput');
            const searchContainer = $('.search-input-container');
            const searchIcon = $('#searchIcon');

            searchInput.val('');
            searchContainer.removeClass('has-content');
            searchIcon.removeClass('text-primary');

            // Clear search mode and reload
            isSearchMode = false;
            loadFolderContent(currentFolderId);

            // Focus back to input
            searchInput.focus();
        }

        function showLoading() {
            // $('#loadingSpinner').show();
            // $('#contentArea').hide();
            // $('#emptyState').hide();
            $('#loadingSpinner').show();
            // Jangan sembunyikan contentArea di sini - biarkan tetap terlihat
            // sampai kita tahu request berhasil
            $('#emptyState').hide();
        }

        function hideLoading() {
            $('#loadingSpinner').hide();
            // Kembalikan contentArea jika sebelumnya ada content
            // Ini akan diatur ulang di loadFolderContent jika request berhasil
            if ($('#contentArea').children().length > 0) {
                $('#contentArea').show();
            }
        }

        function showAlert(type, message) {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            };

            const alert = $(`
                <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `);

            $('.section-body').prepend(alert);

            setTimeout(() => {
                alert.fadeOut();
            }, 5000);
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        }

        // Function untuk menghapus folder
        function deleteFolder(folderId) {
            if (!confirm(
                    'Apakah Anda yakin ingin menghapus folder ini? Semua file dan subfolder di dalamnya akan ikut terhapus.'
                )) {
                return;
            }

            $.ajax({
                url: `{{ url('folders') }}/${folderId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadFolderContent(currentFolderId); // Reload current folder
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal menghapus folder';
                    showAlert('error', message);
                }
            });
        }

        // Function untuk menghapus document
        function deleteDocument(documentId) {
            if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
                return;
            }

            $.ajax({
                url: `{{ url('documents') }}/${documentId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadFolderContent(currentFolderId); // Reload current folder
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal menghapus dokumen';
                    showAlert('error', message);
                }
            });
        }

        // Function untuk share document
        function shareDocument(documentId) {
            // Implementasi share document - bisa berupa modal atau langsung copy link
            const shareUrl = `{{ url('documents/share') }}/${documentId}`;

            // Copy to clipboard
            navigator.clipboard.writeText(shareUrl).then(function() {
                showAlert('success', 'Link dokumen berhasil disalin ke clipboard');
            }).catch(function() {
                // Fallback untuk browser yang tidak support clipboard API
                showShareModal(documentId, shareUrl);
            });
        }

        // Modal untuk share jika clipboard API tidak tersedia
        function showShareModal(documentId, shareUrl) {
            const modalHtml = `
                <div class="modal fade" id="shareModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Share Dokumen</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Link Dokumen:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="${shareUrl}" id="shareUrlInput" readonly>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" onclick="copyShareUrl()" type="button">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            $('#shareModal').remove();

            // Add modal to body
            $('body').append(modalHtml);

            // Show modal
            $('#shareModal').modal('show');

            // Remove modal after hide
            $('#shareModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }

        function permissionFormElement() {
            let html = `
                <div class="form-group">
                    <label>User</label>
                    <select class="form-control select2" id="folderUser" multiple>
                    </select>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <select class="form-control select2" id="folderUnit" multiple>
                    </select>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select class="form-control select2" id="folderRole" multiple>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jenis Permission</label>
                    <select class="form-control select2" id="folderPermissionTypes" multiple>
                        <option value="read">Read - Hanya bisa melihat</option>
                        <option value="write">Write - Bisa melihat dan menambah</option>
                        <option value="download">Download - Bisa mendownload file</option>
                        <option value="delete">Delete - Bisa menghapus</option>
                    </select>
                    <small class="form-text text-muted">Pilih level akses untuk unit/role/user yang dipilih (bisa
                        pilih lebih dari satu)</small>
                </div>
            `;

            return html;
        }

        function initializeSelect2() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'Select options...',
                allowClear: true,
            });
        }

        function handleModalEvents(modalId, containerId) {
            $(modalId).on('shown.bs.modal', function() {
                $(containerId).html(permissionFormElement());
                // Pastikan select2 diinisialisasi setelah HTML dimasukkan
                setTimeout(() => {
                    initializeSelect2();
                    loadMasterData();
                }, 100);
            }).on('hidden.bs.modal', function() {
                $(containerId).html(''); // Clear container
            });
        }
    </script>
@endpush

{{-- Document permissions --}}
@include('folders.document-script')
@push('styles')
    <style>
        /* Action buttons styling */
        .folder-actions,
        .document-actions {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .folder-item:hover .folder-actions,
        .file-item:hover .document-actions {
            opacity: 1;
        }

        .btn-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-size: 0.8rem;
        }

        /* Untuk grid view - position absolute buttons */
        .folder-item .folder-actions,
        .file-item .document-actions {
            z-index: 10;
        }

        /* Untuk list view - flex buttons */
        .folder-item .folder-actions .btn,
        .file-item .document-actions .btn {
            margin-left: 5px;
        }

        .folder-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 15px;
            margin: 5px;
            border: 1px solid #e3e6f0;
            background: white;
        }

        .folder-item:hover {
            background-color: #f8f9fc;
            border-color: #5a5c69;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .file-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 10px;
            margin: 3px;
            border: 1px solid #e3e6f0;
            background: white;
        }

        .file-item:hover {
            background-color: #f8f9fc;
            border-color: #5a5c69;
        }

        .folder-icon {
            font-size: 3rem;
            color: #ffc107;
        }

        .file-icon {
            font-size: 1.5rem;
            color: #6c757d;
        }

        .folder-name {
            font-weight: 600;
            color: #5a5c69;
            margin-top: 10px;
        }

        .folder-info {
            font-size: 0.8rem;
            color: #858796;
        }

        .breadcrumb {
            background-color: #fff;
        }

        /* Search highlight styling */
        mark.bg-warning {
            background-color: #fff3cd !important;
            padding: 1px 2px;
            border-radius: 2px;
            font-weight: 600;
        }

        /* Search mode indicators */
        .search-active {
            border-left: 3px solid #007bff !important;
            background-color: #f8f9fc !important;
        }

        /* Search input focus state */
        #searchInput:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Search results styling */
        .search-result-item {
            transition: all 0.2s ease;
            border-radius: 8px;
        }

        .search-result-item:hover {
            background-color: #e3f2fd !important;
            transform: translateX(5px);
        }

        /* Clear search button */
        .clear-search-btn-search {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            display: none;
        }

        .clear-search-btn-search:hover {
            color: #dc3545;
        }

        /* Search input container */
        .search-input-container {
            position: relative;
        }

        .search-input-container.has-content .clear-search-btn-search {
            display: block;
        }
    </style>
@endpush
