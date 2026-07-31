@extends('layouts.app', ['title' => 'Disposisi Masuk'])

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Disposisi Masuk</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            <div class="breadcrumb-item">Disposisi Masuk</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">Disposisi Masuk</h2>
        <p class="section-lead">Daftar disposisi yang ditujukan ke unit Anda.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Disposisi Masuk</h4>
                    </div>
                    <div class="card-body">
                        @if ($disposisiTargets->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Tidak ada disposisi masuk untuk unit Anda.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No Agenda</th>
                                            <th>Asal Naskah</th>
                                            <th>Isi Informasi</th>
                                            <th>Instruksi</th>
                                            <th>Sifat</th>
                                            <th>Status Disposisi</th>
                                            <th>Paraf</th>
                                            <th>Surat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($disposisiTargets as $index => $target)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $target->disposisi->no_agenda }}</td>
                                            <td>{{ $target->disposisi->asal_naskah }}</td>
                                            <td>{{ Str::limit($target->disposisi->isi_informasi, 50) }}</td>
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
                                                @php
                                                    $sifatLabels = ['sangat_segera' => 'Sangat Segera', 'segera' => 'Segera', 'rahasia' => 'Rahasia', 'biasa' => 'Biasa'];
                                                    $sifatClasses = ['sangat_segera' => 'badge-danger', 'segera' => 'badge-warning', 'rahasia' => 'badge-dark', 'biasa' => 'badge-info'];
                                                @endphp
                                                <span class="badge {{ $sifatClasses[$target->disposisi->sifat] ?? 'badge-secondary' }}">
                                                    {{ $sifatLabels[$target->disposisi->sifat] ?? $target->disposisi->sifat }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $target->disposisi->status->badgeClass() }}">
                                                    {{ $target->disposisi->status->label() }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($target->paraf)
                                                    <span class="badge badge-success">Sudah</span>
                                                @else
                                                    <button class="btn btn-sm btn-outline-success" onclick="tandaiParaf({{ $target->id }})">
                                                        <i class="fas fa-check"></i> Paraf
                                                    </button>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($target->disposisi->surat)
                                                    <a href="{{ route('surat.view', $target->disposisi->surat->id) }}" class="btn btn-sm btn-info" target="_blank" title="Lihat Surat">
                                                        <i class="fas fa-file-alt"></i>
                                                    </a>
                                                @elseif ($target->disposisi->document)
                                                    <a href="{{ route('documents.view-file', $target->disposisi->document->id) }}" class="btn btn-sm btn-info" target="_blank" title="Lihat Dokumen">
                                                        <i class="fas fa-file"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('disposisi.show', $target->disposisi->id) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    window.tandaiParaf = function(id) {
        if (!confirm('Tandai paraf disposisi ini?')) return;

        $.ajax({
            url: '{{ url("disposisi-target") }}/' + id + '/paraf',
            type: 'PATCH',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                toastr.success(res.message);
                location.reload();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Gagal menandai paraf');
            }
        });
    };
</script>
@endpush
@endsection
