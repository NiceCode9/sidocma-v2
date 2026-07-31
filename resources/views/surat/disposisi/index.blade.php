@extends('layouts.app', ['title' => 'Disposisi Surat'])

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Disposisi Surat</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            <div class="breadcrumb-item">Disposisi Surat</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">Daftar Disposisi</h2>
        <p class="section-lead">Kelola disposisi surat untuk diteruskan ke unit terkait.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Disposisi</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="disposisiTable" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>No Agenda</th>
                                        <th>Asal Naskah / Perihal</th>
                                        <th>Target Unit</th>
                                        <th>Sifat</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
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

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#disposisiTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('disposisi.data') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'no_agenda', name: 'no_agenda' },
                { data: 'asal', name: 'asal' },
                { data: 'target_units', name: 'target_units' },
                { data: 'sifat_badge', name: 'sifat' },
                { data: 'status_badge', name: 'status' },
                { data: 'tanggal', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[6, 'desc']],
        });

        window.selesaikanDisposisi = function(id) {
            if (!confirm('Apakah Anda yakin ingin menyelesaikan disposisi ini?')) return;

            $.ajax({
                url: '{{ url("disposisi") }}/' + id + '/selesaikan',
                type: 'PATCH',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    toastr.success(res.message);
                    table.ajax.reload();
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal menyelesaikan disposisi');
                }
            });
        };

        window.hapusDisposisi = function(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus disposisi ini?')) return;

            $.ajax({
                url: '{{ url("disposisi") }}/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    toastr.success(res.message);
                    table.ajax.reload();
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal menghapus disposisi');
                }
            });
        };
    });
</script>
@endpush
@endsection
