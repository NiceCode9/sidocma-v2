@extends('layouts.app', ['title' => 'Detail Disposisi'])

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Detail Disposisi</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            @if (auth()->user()->hasRole(['super admin', 'direktur']))
            <div class="breadcrumb-item"><a href="{{ route('disposisi.index') }}">Disposisi</a></div>
            @else
            <div class="breadcrumb-item"><a href="{{ route('disposisi.masuk') }}">Disposisi Masuk</a></div>
            @endif
            <div class="breadcrumb-item">Detail</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Disposisi: {{ $disposisi->no_agenda }}</h4>
                        <div class="card-header-action">
                            @php $canManage = $disposisi->isEditable() && $disposisi->canManage(auth()->user()); @endphp
                            @if ($canManage)
                                <button type="button" class="btn btn-success" onclick="selesaikanDisposisi({{ $disposisi->id }})">
                                    <i class="fas fa-check"></i> Selesaikan
                                </button>
                                <a href="{{ route('disposisi.edit', $disposisi->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            @endif
                            <a href="{{ route('disposisi.cetak', $disposisi->id) }}" class="btn btn-secondary" target="_blank">
                                <i class="fas fa-print"></i> Cetak
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($disposisi->forwardedFrom)
                        <div class="alert alert-primary">
                            <i class="fas fa-share mr-1"></i>
                            <strong>Diteruskan dari:</strong>
                            <a href="{{ route('disposisi.show', $disposisi->forwardedFrom->id) }}">
                                {{ $disposisi->forwardedFrom->no_agenda }}
                            </a>
                            <span class="text-muted">- {{ $disposisi->forwardedFrom->asal_naskah }}</span>
                        </div>
                        @endif
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>Status:</strong>
                                    <span class="badge {{ $disposisi->status->badgeClass() }}">{{ $disposisi->status->label() }}</span>
                                    &nbsp;|&nbsp;
                                    <strong>Sifat:</strong>
                                    @php
                                        $sifatLabels = ['sangat_segera' => 'Sangat Segera', 'segera' => 'Segera', 'rahasia' => 'Rahasia', 'biasa' => 'Biasa'];
                                        $sifatClasses = ['sangat_segera' => 'badge-danger', 'segera' => 'badge-warning', 'rahasia' => 'badge-dark', 'biasa' => 'badge-info'];
                                    @endphp
                                    <span class="badge {{ $sifatClasses[$disposisi->sifat] ?? 'badge-secondary' }}">
                                        {{ $sifatLabels[$disposisi->sifat] ?? $disposisi->sifat }}
                                    </span>
                                    @if ($disposisi->batas_waktu)
                                        &nbsp;|&nbsp; <strong>Batas Waktu:</strong> {{ $disposisi->batas_waktu->format('d-m-Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered table-sm">
                                    <tr>
                                        <th width="40%">Nomor Agenda</th>
                                        <td>{{ $disposisi->no_agenda }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Naskah</th>
                                        <td>{{ $disposisi->tanggal_naskah->format('d-m-Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Masuk ke TU</th>
                                        <td>{{ $disposisi->masuk_tu->format('d-m-Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tgl / No Naskah</th>
                                        <td>{{ $disposisi->tgl_no_naskah }}</td>
                                    </tr>
                                    <tr>
                                        <th>Asal Naskah</th>
                                        <td>{{ $disposisi->asal_naskah }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered table-sm">
                                    <tr>
                                        <th width="40%">Isi Informasi</th>
                                        <td>{{ $disposisi->isi_informasi }}</td>
                                    </tr>
                                    <tr>
                                        <th>Catatan Lain</th>
                                        <td>{{ $disposisi->catatan_lain ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat Oleh</th>
                                        <td>{{ $disposisi->creator->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Diselesaikan Oleh</th>
                                        <td>{{ $disposisi->approver->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Dibuat</th>
                                        <td>{{ $disposisi->created_at->format('d-m-Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if ($disposisi->surat)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6>Surat Terkait</h6>
                                        <div class="card-header-action">
                                            <a href="{{ route('surat.view', $disposisi->surat->id) }}" class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-eye"></i> Lihat Surat
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>No Surat:</strong> {{ $disposisi->surat->no_surat }}</p>
                                        <p><strong>Perihal:</strong> {{ $disposisi->surat->perihal }}</p>
                                        <p><strong>Pengirim:</strong> {{ $disposisi->surat->user->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif ($disposisi->document)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6>Dokumen Terkait</h6>
                                        <div class="card-header-action">
                                            <a href="{{ route('documents.view-file', $disposisi->document->id) }}" class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-eye"></i> Lihat Dokumen
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Judul:</strong> {{ $disposisi->document->title }}</p>
                                        <p><strong>Pembuat:</strong> {{ $disposisi->document->creator->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Target Unit</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Unit</th>
                                                <th>Instruksi</th>
                                                <th>Paraf</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($disposisi->targets as $index => $target)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $target->unit->name }}</td>
                                                <td>
                                                    @php
                                                        $instruksiLabels = [
                                                            'selesaikan' => 'Selesaikan/Tindaklanjuti',
                                                            'tindaklanjuti' => 'Tindaklanjuti',
                                                            'saran_pendapat' => 'Saran/Pendapat',
                                                            'koordinasikan' => 'Koordinasikan',
                                                            'pelajari_kaji' => 'Pelajari/Kaji',
                                                            'wakili_hadiri' => 'Wakili/Hadiri',
                                                            'pantau' => 'Pantau',
                                                            'perhatian' => 'Untuk Menjadi Perhatian',
                                                            'file' => 'File',
                                                        ];
                                                    @endphp
                                                    @foreach ($target->instruksi as $inst)
                                                        <span class="badge badge-primary">{{ $instruksiLabels[$inst] ?? $inst }}</span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @if ($target->paraf)
                                                        <span class="badge badge-success">Sudah</span>
                                                        <small class="text-muted d-block">{{ $target->paraf_at ? $target->paraf_at->format('d-m-Y H:i') : '' }}</small>
                                                    @else
                                                        <span class="badge badge-secondary">Belum</span>
                                                    @endif
                                                </td>
                                                <td>{{ $target->keterangan ?? '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada target unit</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
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

@push('scripts')
<script>
    window.selesaikanDisposisi = function(id) {
        if (!confirm('Tandai disposisi ini sebagai selesai?')) return;

        $.ajax({
            url: '{{ url("disposisi") }}/' + id + '/selesaikan',
            type: 'PATCH',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                toastr.success(res.message);
                location.reload();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Gagal menyelesaikan disposisi');
            }
        });
    };
</script>
@endpush
@endsection
