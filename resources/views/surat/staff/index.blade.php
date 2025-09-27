@extends('layouts.app', ['title' => 'Kirim Surat'])

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Kirim Surat</h1>
        </div>
        <div class="section-body">
            <h2>Kirim Surat</h2>
            <p class="section-lead">
                Halaman untuk mengelola surat yang untuk kesekretariatan.
            </p>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>Data Surat Terkirim</h4>
                            <div class="card-header-action">
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#kirimSuratModal">
                                    <i class="fas fa-send"></i>
                                    Kirim Surat
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="suratTable" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor Surat</th>
                                            <th>Perihal</th>
                                            <th>Tanggal Dikirim</th>
                                            <th>Laporan Dibaca</th>
                                            <th>Waktu Dibaca</th>
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

    <!-- Modal Kirim/Edit Surat -->
    <div class="modal fade" id="kirimSuratModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Kirim Surat</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="suratForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="surat_id" name="surat_id">
                        <input type="hidden" id="method" name="_method" value="">

                        <div class="form-group">
                            <label>Nomor Surat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="no_surat" id="no_surat">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label>Perihal <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="perihal" id="perihal">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="keterangan" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label>File Surat</label>
                            <input type="file" class="form-control-file" name="file" id="file"
                                accept=".pdf,.doc,.docx">
                            <small class="text-muted">Format: PDF, DOC, DOCX. Maksimal 5MB</small>
                            <div class="invalid-feedback"></div>
                            <div id="currentFile" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-send"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal View Surat -->
    <div class="modal fade" id="viewSuratModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Surat</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="200"><strong>Nomor Surat</strong></td>
                            <td>: <span id="view_no_surat"></span></td>
                        </tr>
                        <tr>
                            <td><strong>Perihal</strong></td>
                            <td>: <span id="view_perihal"></span></td>
                        </tr>
                        <tr>
                            <td><strong>Keterangan</strong></td>
                            <td>: <span id="view_keterangan"></span></td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Dikirim</strong></td>
                            <td>: <span id="view_created_at"></span></td>
                        </tr>
                        <tr>
                            <td><strong>Status Baca</strong></td>
                            <td>: <span id="view_status_baca"></span></td>
                        </tr>
                        <tr>
                            <td><strong>Waktu Dibaca</strong></td>
                            <td>: <span id="view_read_at"></span></td>
                        </tr>
                        <tr>
                            <td><strong>Dibaca Oleh</strong></td>
                            <td>: <span id="view_opened_by"></span></td>
                        </tr>
                        <tr>
                            <td><strong>File</strong></td>
                            <td>: <span id="view_file"></span></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            window.tableSuratUnit = $('#suratTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('kirim-surat.data') }}",
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
                        data: 'tanggal_dikirim',
                        name: 'created_at'
                    },
                    {
                        data: 'laporan_dibaca',
                        name: 'is_read'
                    },
                    {
                        data: 'waktu_dibaca',
                        name: 'read_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            // Reset form when modal is closed
            $('#kirimSuratModal').on('hidden.bs.modal', function() {
                resetForm();
            });

            // Handle form submission
            $('#suratForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const isEdit = $('#surat_id').val() !== '';
                const url = isEdit ?
                    "{{ route('kirim-surat.update', ':id') }}".replace(':id', $('#surat_id').val()) :
                    "{{ route('kirim-surat.store') }}";

                if (isEdit) {
                    formData.append('_method', 'PUT');
                }

                // Clear previous errors
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                // Disable submit button
                $('#submitBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#kirimSuratModal').modal('hide');
                            window.tableSuratUnit.ajax.reload();

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function(key) {
                                $(`[name="${key}"]`).addClass('is-invalid');
                                $(`[name="${key}"]`).siblings('.invalid-feedback').text(
                                    errors[key][0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Terjadi kesalahan saat menyimpan data'
                            });
                        }
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="fas fa-send"></i> Kirim');
                    }
                });
            });
        });

        function viewSurat(id) {
            $.ajax({
                url: "{{ route('kirim-surat.show', ':id') }}".replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#view_no_surat').text(data.no_surat);
                        $('#view_perihal').text(data.perihal);
                        $('#view_keterangan').text(data.keterangan || '-');
                        $('#view_created_at').text(moment(data.created_at).format('DD-MM-YYYY HH:mm:ss'));
                        $('#view_status_baca').html(data.is_read ?
                            '<span class="badge badge-success">Dibaca</span>' :
                            '<span class="badge badge-warning">Belum Dibaca</span>'
                        );
                        $('#view_read_at').text(data.read_at ? moment(data.read_at).format(
                            'DD-MM-YYYY HH:mm:ss') : '-');
                        $('#view_opened_by').text(data.opened_by || '-');
                        $('#view_file').html(data.file ?
                            `<a href="{{ asset('storage/') }}/${data.file}" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-download"></i> Download</a>` :
                            '-'
                        );

                        $('#viewSuratModal').modal('show');
                    }
                }
            });
        }

        function editSurat(id) {
            $.ajax({
                url: "{{ route('kirim-surat.show', ':id') }}".replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;

                        $('#modalTitle').text('Edit Surat');
                        $('#submitBtn').html('<i class="fas fa-save"></i> Update');
                        $('#surat_id').val(data.id);
                        $('#method').val('PUT');
                        $('#no_surat').val(data.no_surat);
                        $('#perihal').val(data.perihal);
                        $('#keterangan').val(data.keterangan);

                        if (data.file) {
                            $('#currentFile').html(`
                        <div class="alert alert-info">
                            <i class="fas fa-file"></i> File saat ini:
                            <a href="{{ asset('storage/') }}/${data.file}" target="_blank">Download</a>
                        </div>
                    `);
                        }

                        $('#kirimSuratModal').modal('show');
                    }
                }
            });
        }

        function deleteSurat(id) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('kirim-surat.destroy', ':id') }}".replace(':id', id),
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#suratTable').DataTable().ajax.reload();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Terjadi kesalahan saat menghapus data'
                            });
                        }
                    });
                }
            });
        }

        function resetForm() {
            $('#suratForm')[0].reset();
            $('#modalTitle').text('Kirim Surat');
            $('#submitBtn').html('<i class="fas fa-send"></i> Kirim');
            $('#surat_id').val('');
            $('#method').val('');
            $('#currentFile').html('');
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }
    </script>
@endpush
