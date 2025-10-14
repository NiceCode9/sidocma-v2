@extends('layouts.app', ['title' => 'Kirim Surat'])

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Surat Masuk</h1>
        </div>
        <div class="section-body">
            <h2>Surat Masuk</h2>
            <p class="section-lead">
                Halaman untuk mengelola surat masuk.
            </p>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>Data Surat Masuk</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="suratMasukTable" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor Surat</th>
                                            <th>Judul</th>
                                            <th>Kategori Surat</th>
                                            <th>Tanggal Dikirim</th>
                                            <th>Pengirim</th>
                                            <th>Laporan Dibaca</th>
                                            <th>File Info</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                </table>
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
            $('#suratMasukTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('surat-masuk.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'document_number',
                        name: 'document_number'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'creator',
                        name: 'creator'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'file_info',
                        name: 'file_info',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [3, 'desc']
                ],
            });
        });
    </script>
@endpush
