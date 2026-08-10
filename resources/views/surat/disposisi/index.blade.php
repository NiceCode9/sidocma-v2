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

<!-- Modal Teruskan Disposisi -->
<div class="modal fade" id="forwardModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Teruskan Disposisi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="forwardForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Disposisi Asal:</strong> <span id="forwardAsalLabel">-</span>
                    </div>

                    <div class="form-group">
                        <label>Nomor Agenda <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="forwardNoAgenda" name="no_agenda"
                            placeholder="Contoh: AGND/2026/0001">
                        <small class="text-muted">Diisi manual oleh Super Admin</small>
                    </div>

                    <div class="form-group">
                        <label>Unit Tujuan <span class="text-danger">*</span></label>
                        <div id="forwardUnitList" class="border rounded p-3" style="max-height: 220px; overflow-y: auto;">
                            <div class="text-center text-muted py-3">Memuat daftar unit...</div>
                        </div>
                        <small class="text-muted">Instruksi disalin dari disposisi asal</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="forwardSubmitBtn">
                        <i class="fas fa-share"></i> Teruskan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

        // Teruskan Disposisi
        window.teruskanDisposisi = function(id) {
            $('#forwardModal').data('disposisiId', id);

            $.ajax({
                url: '{{ url("disposisi") }}/' + id + '/teruskan',
                type: 'GET',
                success: function(res) {
                    if (!res.success) {
                        toastr.error(res.message || 'Gagal memuat data disposisi');
                        return;
                    }

                    const data = res.data;
                    $('#forwardAsalLabel').text(data.no_agenda + ' - ' + data.asal);
                    $('#forwardNoAgenda').val(data.no_agenda);

                    // Render unit list
                    let html = '';
                    if (res.units.length === 0) {
                        html = '<div class="text-center text-muted py-3">Tidak ada unit tersedia</div>';
                    }
                    res.units.forEach(function(unit) {
                        html += `
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input forward-unit"
                                    id="forward_unit_${unit.id}" value="${unit.id}" name="unit_ids[]">
                                <label class="custom-control-label" for="forward_unit_${unit.id}">
                                    <i class="fas fa-building mr-1 text-primary"></i>${unit.name}
                                </label>
                            </div>`;
                    });
                    $('#forwardUnitList').html(html);

                    $('#forwardModal').modal('show');
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal memuat data disposisi');
                }
            });
        };

        // Submit forward
        $('#forwardForm').on('submit', function(e) {
            e.preventDefault();

            const selectedUnits = $('.forward-unit:checked').length;
            if (selectedUnits === 0) {
                toastr.error('Pilih minimal satu unit tujuan.');
                return;
            }

            const id = $('#forwardModal').data('disposisiId');
            const noAgenda = $('#forwardNoAgenda').val().trim();
            if (!noAgenda) {
                toastr.error('Nomor agenda wajib diisi.');
                return;
            }

            const formData = $(this).serialize();
            $('#forwardSubmitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: '{{ url("disposisi") }}/' + id + '/teruskan',
                type: 'POST',
                data: formData,
                success: function(res) {
                    toastr.success(res.message);
                    $('#forwardModal').modal('hide');
                    table.ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        for (const key in errors) {
                            toastr.error(errors[key][0]);
                        }
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'Gagal meneruskan disposisi');
                    }
                },
                complete: function() {
                    $('#forwardSubmitBtn').prop('disabled', false).html('<i class="fas fa-share"></i> Teruskan');
                }
            });
        });

        // Simpan id disposisi saat modal dibuka
        $(document).on('shown.bs.modal', '#forwardModal', function() {
            // id ditentukan saat teruskanDisposisi dipanggil
        });
    });
</script>
@endpush
@endsection

