@extends('layouts.app', ['title' => 'Document Categories'])

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Document Categories</h1>
        </div>
        <div class="section-body">
            <h2 class="section-title">Data Document Categories</h2>
            <p class="section-lead">
                Halaman ini ditujukan untuk mengelola data document categories.
            </p>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <div class="card-header-action">
                                <a href="#" class="btn btn-primary" data-target="#modal-form" data-toggle="modal">
                                    <i class="fas fa-plus"></i> Add New Document Category
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="document-category-table" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Is Active</th>
                                            <th>Action</th>
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

    <div class="modal fade" tabindex="-1" role="dialog" id="modal-form">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form Document Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST" id="formDocumentCategory">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" name="name" id="name"
                                placeholder="Masukkan Nama Document Category" required>
                        </div>
                        {{-- <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" class="form-control" name="color" id="color"
                                placeholder="Masukkan Color" required>
                        </div>
                        <div class="form-group">
                            <label for="icon">Icon</label>
                            <input type="text" class="form-control" name="icon" id="icon"
                                placeholder="Masukkan Icon" required>
                        </div> --}}
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" name="description" id="description" placeholder="Masukkan Deskripsi Document Category"
                                required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="is_active">Is Active</label>
                            <select class="form-control" name="is_active" id="is_active" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#document-category-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('document-categories.index') }}',
                    type: 'GET',
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data, type, row) {
                            return data == 1 ? '<span class="badge badge-success">Yes</span>' :
                                '<span class="badge badge-danger">No</span>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-warning btn-sm editBtn" data-id="${row.id}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm destroyBtn" data-id="${row.id}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            `;
                        }
                    },
                ],
            });

            $('#formDocumentCategory').on('submit', function(e) {
                e.preventDefault();

                let url = $(this).attr('action');
                let method = $(this).attr('method');
                let formData = new FormData(this);

                if (method == 'PUT') {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    type: "POST",
                    url: url || '{{ route('document-categories.store') }}',
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            $('#modal-form').modal('hide');
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        let errors = xhr.responseJSON.message;
                        let errorMessages = '';

                        $.each(errors, function(key, value) {
                            errorMessages += value + '\n';
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessages,
                        });
                    }
                });
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                let url = '{{ route('document-categories.edit', ':id') }}'.replace(':id', id);
                let method = 'GET';

                $.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            console.log(response);
                            $('#modal-form').modal('show');
                            $('#id').val(response.data.id);
                            $('#formDocumentCategory').attr('action',
                                '{{ route('document-categories.update', ':id') }}'.replace(
                                    ':id', id));
                            $('#formDocumentCategory').attr('method', 'PUT');
                            $('#name').val(response.data.name);
                            $('#description').val(response.data.description);
                            $('#is_active').val(response.data.is_active ? '1' : '0');
                        }
                    },
                    error: function(xhr, status, error) {
                        let errors = xhr.responseJSON.message;
                        let errorMessages = '';

                        $.each(errors, function(key, value) {
                            errorMessages += value + '\n';
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessages,
                        });
                    }
                });
            });

            $(document).on('click', '.destroyBtn', function() {
                let id = $(this).data('id');
                let url = '{{ route('document-categories.destroy', ':id') }}'.replace(':id', id);
                destroy(url, table);
            });
            $('#modal-form').on('hidden.bs.modal', function() {
                $('#formDocumentCategory').trigger('reset');
                $('#formDocumentCategory').attr('action', '');
                $('#formDocumentCategory').attr('method', 'POST');
                $('#id').val('');
            });
        });
    </script>
@endpush
