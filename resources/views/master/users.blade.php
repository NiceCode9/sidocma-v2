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
                                <a href="javascript:void(0)" class="btn btn-primary" data-target="#modal-form"
                                    data-toggle="modal">
                                    <i class="fas fa-plus"></i> Add New User
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="unit-table" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Unit</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->username }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->unit?->name }}</td>
                                                <td>{{ $user->getRoleNames()->implode(', ') }}</td>
                                                <td>{!! $user->is_active
                                                    ? '<span class="badge badge-success">Aktif</span>'
                                                    : '<span class="badge badge-danger">Non-Aktif</span>' !!}</td>
                                                <td>
                                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm">Edit</a>
                                                    <a href="javascript:void(0)" class="btn btn-danger btn-sm">Delete</a>
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
        $(document).ready(function() {
            $('#unit-table').DataTable();
        });
    </script>
@endpush
