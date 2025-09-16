@extends('layouts.app', ['title' => 'Management Data Users'])

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Management Data Users</h1>
        </div>
        <div class="section-body">
            <h2 class="section-title">Data Users</h2>
            <p class="section-lead">
                Halaman ini ditujukan untuk mengelola data users.
            </p>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <div class="card-header-action">
                                <button type="button" class="btn btn-primary" data-target="#modal-form" data-toggle="modal"
                                    onclick="addUser()">
                                    <i class="fas fa-plus"></i> Add New User
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="user-table" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Username</th>
                                            <th>Kode User</th>
                                            <th>Email</th>
                                            <th>Unit</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr id="user-{{ $user->id }}">
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->username }}</td>
                                                <td>
                                                    <code>{{ $user->kode_user }}</code>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->unit?->name ?? '-' }}</td>
                                                <td>{{ $user->getRoleNames()->implode(', ') }}</td>
                                                <td>
                                                    {!! $user->is_active
                                                        ? '<span class="badge badge-success">Aktif</span>'
                                                        : '<span class="badge badge-danger">Non-Aktif</span>' !!}
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                        onclick="editUser({{ $user->id }})">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Form -->
    <div class="modal fade" tabindex="-1" role="dialog" id="modal-form">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Form User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formUser" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="_method" id="method" value="POST">
                    <div class="modal-body">
                        <!-- Alert Info for Auto Generated Code -->
                        <div class="alert alert-info" id="kode-info" style="display: none;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Info:</strong> Kode user akan digenerate otomatis berdasarkan unit yang dipilih.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name"
                                        placeholder="Masukkan Nama Lengkap" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="username" id="username"
                                        placeholder="Masukkan Username" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Kode User Display (Read Only for Edit) -->
                        <div class="row" id="kode-display" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_user_display">Kode User Saat Ini</label>
                                    <input type="text" class="form-control" id="kode_user_display" readonly>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        Kode akan berubah otomatis jika unit diubah
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" id="email"
                                        placeholder="Masukkan Email" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_id">Unit <span class="text-danger">*</span></label>
                                    <select class="form-control" name="unit_id" id="unit_id" required>
                                        <option value="">Pilih Unit</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password <span class="text-danger"
                                            id="password-required">*</span></label>
                                    <input type="password" class="form-control" name="password" id="password"
                                        placeholder="Masukkan Password">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted" id="password-help">
                                        Kosongkan jika tidak ingin mengubah password (untuk edit)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="roles">Role <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="roles[]" id="roles" multiple required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                            value="1" checked>
                                        <label class="form-check-label" for="is_active">
                                            Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btn-save">
                            <i class="fas fa-save"></i> Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let table;

        $(document).ready(function() {
            // Initialize DataTable
            table = $('#user-table').DataTable({
                responsive: true,
                autoWidth: false,
            });

            // Initialize Select2
            $('.select2').select2({
                dropdownParent: $('#modal-form'),
                width: '100%'
            });
        });

        // Add User
        function addUser() {
            $('#formUser')[0].reset();
            $('#formUser').find('.is-invalid').removeClass('is-invalid');
            $('#id').val('');
            $('#method').val('POST');
            $('#modal-title').text('Tambah User');
            $('#password').attr('required', true);
            $('#password-required').show();
            $('#password-help').hide();
            $('#kode-info').show();
            $('#kode-display').hide();
            $('.select2').val(null).trigger('change');
            $('#modal-form').modal('show');
        }

        // Edit User
        function editUser(id) {
            $.ajax({
                url: `{{ route('users.index') }}/${id}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const user = response.data;

                        $('#id').val(user.id);
                        $('#method').val('PUT');
                        $('#name').val(user.name);
                        $('#username').val(user.username);
                        $('#kode_user_display').val(user.kode_user);
                        $('#email').val(user.email);
                        $('#unit_id').val(user.unit_id);
                        $('#is_active').prop('checked', user.is_active);

                        // Set roles
                        const roleNames = user.roles.map(role => role.name);
                        $('#roles').val(roleNames).trigger('change');

                        $('#modal-title').text('Edit User');
                        $('#password').attr('required', false);
                        $('#password-required').hide();
                        $('#password-help').show();
                        $('#kode-info').hide();
                        $('#kode-display').show();
                        $('#formUser').find('.is-invalid').removeClass('is-invalid');

                        $('#modal-form').modal('show');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Gagal memuat data user', 'error');
                }
            });
        }

        // Delete User
        function deleteUser(id, name) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus user "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('users.index') }}/${id}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                table.row(`#user-${id}`).remove().draw();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            Swal.fire('Error!', response?.message || 'Terjadi kesalahan', 'error');
                        }
                    });
                }
            });
        }

        // Form Submit
        $('#formUser').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const id = $('#id').val();
            const method = $('#method').val();

            let url = '{{ route('users.store') }}';
            if (method === 'PUT') {
                url = `{{ route('users.index') }}/${id}`;
                formData.append('_method', 'PUT');
            }

            // Clear previous validation errors
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').text('');

            $('#btn-save').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#modal-form').modal('hide');
                        location.reload(); // Reload to update the table
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;

                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = response.errors;
                        for (const field in errors) {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(errors[field][0]);
                        }
                    } else {
                        Swal.fire('Error!', response?.message || 'Terjadi kesalahan', 'error');
                    }
                },
                complete: function() {
                    $('#btn-save').prop('disabled', false).html(
                        '<i class="fas fa-save"></i> Save changes');
                }
            });
        });

        // Modal hidden event
        $('#modal-form').on('hidden.bs.modal', function() {
            $('#formUser')[0].reset();
            $('.select2').val(null).trigger('change');
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').text('');
        });
    </script>
@endpush
