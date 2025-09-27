@extends('layouts.app', ['title' => 'Management Surat'])

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Management Surat</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                <div class="breadcrumb-item">Management Surat</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Management Surat</h2>
            <p class="section-lead">Manajemen Surat Masuk dan Keluar</p>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Data Surat</h4>
                            {{-- <div class="card-header-action">
                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#addSuratModal">
                                    <i class="fas fa-plus"></i> Tambah Surat
                                </button>
                            </div> --}}
                        </div>
                        <div class="card-body">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" id="suratTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="surat-masuk-tab" data-toggle="tab" href="#surat-masuk"
                                        role="tab" aria-controls="surat-masuk" aria-selected="true">
                                        <i class="fas fa-inbox"></i> Surat Masuk
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="surat-keluar-tab" data-toggle="tab" href="#surat-keluar"
                                        role="tab" aria-controls="surat-keluar" aria-selected="false">
                                        <i class="fas fa-paper-plane"></i> Surat Keluar
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content mt-3" id="suratTabContent">
                                <!-- Surat Masuk Tab -->
                                <div class="tab-pane fade show active" id="surat-masuk" role="tabpanel"
                                    aria-labelledby="surat-masuk-tab">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="card card-primary">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="text-muted">Total Surat Masuk</h6>
                                                            <h4 class="mb-0" id="totalSuratMasuk">-</h4>
                                                        </div>
                                                        <div class="align-self-center">
                                                            <i class="fas fa-inbox fa-2x text-primary"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card card-warning">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="text-muted">Belum Dibaca</h6>
                                                            <h4 class="mb-0" id="belumDibaca">-</h4>
                                                        </div>
                                                        <div class="align-self-center">
                                                            <i class="fas fa-envelope fa-2x text-warning"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card card-success">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="text-muted">Sudah Dibaca</h6>
                                                            <h4 class="mb-0" id="sudahDibaca">-</h4>
                                                        </div>
                                                        <div class="align-self-center">
                                                            <i class="fas fa-envelope-open fa-2x text-success"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card card-info">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="text-muted">Hari Ini</h6>
                                                            <h4 class="mb-0" id="hariIni">-</h4>
                                                        </div>
                                                        <div class="align-self-center">
                                                            <i class="fas fa-calendar-day fa-2x text-info"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped" id="suratMasukTable" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>No. Surat</th>
                                                    <th>Perihal</th>
                                                    <th>Pengirim</th>
                                                    <th>Unit</th>
                                                    <th>Status</th>
                                                    <th>File</th>
                                                    <th>Tanggal</th>
                                                    <th width="15%">Aksi</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>

                                <!-- Surat Keluar Tab -->
                                <div class="tab-pane fade" id="surat-keluar" role="tabpanel"
                                    aria-labelledby="surat-keluar-tab">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="card card-primary">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="text-muted">Total Surat Keluar</h6>
                                                            <h4 class="mb-0" id="totalSuratKeluar">-</h4>
                                                        </div>
                                                        <div class="align-self-center">
                                                            <i class="fas fa-paper-plane fa-2x text-primary"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card card-secondary">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="text-muted">Belum Dibaca</h6>
                                                            <h4 class="mb-0" id="belumDibaca">-</h4>
                                                        </div>
                                                        <div class="align-self-center">
                                                            <i class="fas fa-edit fa-2x text-secondary"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card card-success">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="text-muted">Sudah Dibaca</h6>
                                                            <h4 class="mb-0" id="sudahDibaca">-</h4>
                                                        </div>
                                                        <div class="align-self-center">
                                                            <i class="fas fa-check-circle fa-2x text-success"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card card-danger">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="text-muted">Hari Ini</h6>
                                                            <h4 class="mb-0" id="hariIni">-</h4>
                                                        </div>
                                                        <div class="align-self-center">
                                                            <i class="fas fa-lock fa-2x text-danger"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="suratKeluarTable"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>Judul</th>
                                                    <th>Kategori</th>
                                                    <th>Tanggal Dibuat</th>
                                                    <th>Status Dibaca</th>
                                                    <th>Waktu Dibaca</th>
                                                    <th>Dibaca Oleh</th>
                                                    <th>Jumlah Download</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Surat Masuk DataTable
            window.tableSm = $('#suratMasukTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('management-surat.surat-masuk.data') }}",
                    type: "GET"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'no_surat',
                        name: 'no_surat'
                    },
                    {
                        data: 'perihal',
                        name: 'perihal'
                    },
                    {
                        data: 'user_name',
                        name: 'user.name'
                    },
                    {
                        data: 'user.unit.name',
                        name: 'user.unit.name'
                    },
                    {
                        data: 'status',
                        name: 'read_at',
                        orderable: false
                    },
                    {
                        data: 'file',
                        name: 'file',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [6, 'desc']
                ],
            });

            // Initialize Surat Keluar DataTable
            window.tableSk = $('#suratKeluarTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('management-surat.surat-keluar.data') }}",
                    type: "GET"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    // {
                    //     data: 'creator_name',
                    //     name: 'creator_name'
                    // },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'tanggal_dibuat',
                        name: 'tanggal_dibuat'
                    },
                    {
                        data: 'is_read',
                        name: 'is_read',
                    },
                    {
                        data: 'read_at',
                        name: 'read_at'
                    },
                    {
                        data: 'opened_by',
                        name: 'opened_by'
                    },
                    {
                        data: 'jumlah_download',
                        name: 'jumlah_download',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [5, 'desc']
                ],
            });

            // Load statistics when switching tabs
            $('#surat-masuk-tab').on('click', function() {
                loadSuratMasukStats();
            });

            $('#surat-keluar-tab').on('click', function() {
                loadSuratKeluarStats();
            });

            // Load initial stats
            loadSuratMasukStats();
        });

        function loadSuratMasukStats() {
            // You can implement AJAX calls to get statistics
            // Example:
            $.ajax({
                url: "{{ route('management-surat.surat-masuk.stats') }}",
                type: "GET",
                success: function(data) {
                    $('#totalSuratMasuk').text(data.totalSuratMasuk || 0);
                    $('#belumDibaca').text(data.suratMasukBelumDibaca || 0);
                    $('#sudahDibaca').text(data.suratMasukDibaca || 0);
                    $('#hariIni').text(data.suratMasukHariIni || 0);
                },
                error: function() {
                    // Handle error or set default values
                    $('#totalSuratMasuk').text('0');
                    $('#belumDibaca').text('0');
                    $('#sudahDibaca').text('0');
                    $('#hariIni').text('0');
                }
            });
        }

        function loadSuratKeluarStats() {
            $.ajax({
                url: "{{ route('management-surat.surat-keluar.stats') }}",
                type: "GET",
                success: function(data) {
                    $('#totalSuratKeluar').text(data.total || 0);
                    $('#belumDibaca').text(data.belum_dibaca || 0);
                    $('#sudahDibaca').text(data.dibaca || 0);
                    $('#hariIni').text(data.hari_ini || 0);
                },
                error: function() {
                    // Handle error or set default values
                    $('#totalSuratKeluar').text('0');
                    $('#belumDibaca').text('0');
                    $('#sudahDibaca').text('0');
                    $('#hariIni').text('0');
                }
            });
        }

        window.reloadSuratMasukTable = () => {
            if (window.tableSm && typeof window.tableSm.ajax === 'object') {
                window.tableSm.ajax.reload(null, false);
                loadSuratMasukStats();
            }
        }

        // Action functions
        function viewSurat(id) {
            // Implement view surat logic
            console.log('View surat:', id);
        }

        function editSurat(id) {
            // Implement edit surat logic
            console.log('Edit surat:', id);
        }

        function deleteSurat(id) {
            if (confirm('Apakah Anda yakin ingin menghapus surat ini?')) {
                // Implement delete surat logic
                console.log('Delete surat:', id);
            }
        }

        function viewDocument(id) {
            // Implement view document logic
            console.log('View document:', id);
        }

        function editDocument(id) {
            // Implement edit document logic
            console.log('Edit document:', id);
        }

        function deleteDocument(id) {
            if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
                // Implement delete document logic
                console.log('Delete document:', id);
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 0;
            color: #6c757d;
        }

        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-bottom: 3px solid #007bff;
            color: #007bff;
            font-weight: bold;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .btn-group .btn {
            margin-right: 2px;
        }

        .badge {
            font-size: 0.75em;
        }
    </style>
@endpush
