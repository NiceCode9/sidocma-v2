@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Management Data Unit</h1>
        </div>
        <div class="section-body">
            <h2 class="section-title">Data Unit</h2>
            <p class="section-lead">
                Halaman ini ditujukan untuk mengelola data unit.
            </p>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <div class="card-header-action">
                                <a href="#" class="btn btn-primary" data-target="#modal-form" data-toggle="modal">
                                    <i class="fas fa-plus"></i> Add New Unit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="unit-table" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" tabindex="-1" role="dialog" id="modal-form">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form Unit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST" id="formUnit">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nama Unit</label>
                            <input type="text" class="form-control" name="name" id="name"
                                placeholder="Masukkan Nama Unit" required>
                        </div>
                        <div class="form-group">
                            <label for="code">Kode Unit</label>
                            <input type="text" class="form-control" name="code" id="code"
                                placeholder="Masukkan Kode Unit" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Deskripsi Unit</label>
                            <textarea class="form-control" name="description" id="description" placeholder="Masukkan Deskripsi Unit" required></textarea>
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
        function editUnit(id) {
            $.ajax({
                type: "GET",
                url: "{{ route('units.edit', ':id') }}".replace(':id', id),
                dataType: "json",
                success: function(response) {
                    if (response.status == 'success') {
                        $('#id').val(response.data.id);
                        $('#name').val(response.data.name);
                        $('#code').val(response.data.code);
                        $('#formUnit').attr('action', "{{ route('units.update', ':id') }}".replace(
                            ':id', id));
                        $('#formUnit').attr('method', 'PUT');
                        $('#description').val(response.data.description);
                        $('#modal-form').modal('show');
                    }
                }
            });
        }

        function deleteUnit(id) {
            let url = "{{ route('units.destroy', ':id') }}".replace(':id', id);
            destroy(url);
        }
        $(document).ready(function() {
            let table = $('#unit-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('units.index') }}',
                    type: 'GET',
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-warning btn-sm" onclick="editUnit(${row.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteUnit(${row.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            `;
                        }
                    },
                ],
            });

            $('#formUnit').on('submit', function(e) {
                e.preventDefault();
                let url = $(this).attr('action');
                let method = $(this).attr('method');
                let formData = new FormData(this);

                if (method == 'PUT') {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    type: "POST",
                    url: url || '{{ route('units.store') }}',
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#modal-form').modal('hide');
                            $('#unit-table').DataTable().ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';

                        $.each(errors, function(key, value) {
                            errorMessage += value + '\n';
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.message,
                        });
                    }
                });
            });

            $('#modal-form').on('hidden.bs.modal', function() {
                $('#formUnit').trigger('reset');
                $('#formUnit').attr('action', '');
                $('#formUnit').attr('method', 'POST');
                $('#id').val('');
            });
        });
    </script>
@endpush
