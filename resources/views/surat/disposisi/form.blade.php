@extends('layouts.app', ['title' => $disposisi ? 'Edit Disposisi' : 'Buat Disposisi'])

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $disposisi ? 'Edit Disposisi' : 'Buat Disposisi' }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('disposisi.index') }}">Disposisi</a></div>
            <div class="breadcrumb-item">{{ $disposisi ? 'Edit' : 'Buat' }}</div>
        </div>
    </div>

    <div class="section-body">
        <form id="disposisiForm" enctype="multipart/form-data">
            @csrf
            @if ($disposisi)
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="id" value="{{ $disposisi?->id }}">
            @endif
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="surat_id" value="{{ $surat->id ?? '' }}">
            <input type="hidden" name="document_id" value="{{ $document->id ?? '' }}">

            <!-- Bagian A: Header Info Surat -->
            <div class="card">
                <div class="card-header">
                    <h4>A. Informasi Surat</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nomor Agenda <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="no_agenda" id="no_agenda"
                                    value="{{ $disposisi?->no_agenda ?? ($prefill['no_agenda'] ?? '') }}"
                                    placeholder="Contoh: AGND/2026/0001">
                                <small class="text-muted">Diisi sesuai nomor agenda surat</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Naskah <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_naskah" id="tanggal_naskah"
                                    value="{{ $disposisi?->tanggal_naskah?->format('Y-m-d') ?? (isset($prefill['tanggal_naskah']) && $prefill['tanggal_naskah'] ? \Carbon\Carbon::parse($prefill['tanggal_naskah'])->format('Y-m-d') : date('Y-m-d')) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Masuk ke Sekretariat <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="masuk_tu" id="masuk_tu"
                                    value="{{ $disposisi?->masuk_tu?->format('Y-m-d\TH:i') ?? (isset($prefill['masuk_tu']) && $prefill['masuk_tu'] ? \Carbon\Carbon::parse($prefill['masuk_tu'])->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    @if ($surat)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Surat Asal</label>
                                <input type="text" class="form-control" value="{{ $surat->no_surat }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pengirim</label>
                                <input type="text" class="form-control" value="{{ $surat->user->name ?? '-' }}" readonly>
                            </div>
                        </div>
                    </div>
                    @elseif ($document)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Judul Dokumen</label>
                                <input type="text" class="form-control" value="{{ $document->title }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pembuat</label>
                                <input type="text" class="form-control" value="{{ $document->creator->name ?? '-' }}" readonly>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tgl / No Naskah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tgl_no_naskah" id="tgl_no_naskah"
                                    value="{{ $disposisi?->tgl_no_naskah ?? ($prefill['tgl_no_naskah'] ?? '') }}"
                                    placeholder="Contoh: 15-01-2026 / 001/SK/2026">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Asal Naskah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="asal_naskah" id="asal_naskah"
                                    value="{{ $disposisi?->asal_naskah ?? ($prefill['asal_naskah'] ?? ($surat->user->name ?? '')) }}"
                                    placeholder="Asal surat/naskah">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Isi Informasi Naskah <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="isi_informasi" id="isi_informasi" rows="3"
                            placeholder="Ringkasan isi surat/naskah">{{ $disposisi?->isi_informasi ?? ($prefill['isi_informasi'] ?? ($surat->perihal ?? '')) }}</textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <!-- Bagian B: Unit Target -->
            <div class="card">
                <div class="card-header">
                    <h4>B. Diteruskan Kepada</h4>
                    <div class="card-header-action">
                        <button type="button" class="btn btn-primary btn-sm" onclick="tambahTarget()">
                            <i class="fas fa-plus"></i> Tambah Unit
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="targetsContainer">
                        @if ($disposisi && $disposisi?->targets->count() > 0)
                            @foreach ($disposisi?->targets as $index => $target)
                            <div class="target-item card bg-light mb-3" data-index="{{ $index }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="mb-0">Unit Tujuan #{{ $index + 1 }}</h6>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusTarget(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Unit Kerja <span class="text-danger">*</span></label>
                                                <select class="form-control" name="targets[{{ $index }}][unit_id]" required>
                                                    <option value="">-- Pilih Unit --</option>
                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit->id }}" {{ $target->unit_id == $unit->id ? 'selected' : '' }}>
                                                            {{ $unit->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label>Instruksi Disposisi <span class="text-danger">*</span></label>
                                                <div class="row">
                                                    @php
                                                        $instruksiList = [
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
                                                        $selectedInstruksi = $target->instruksi ?? [];
                                                    @endphp
                                                    @foreach ($instruksiList as $key => $label)
                                                    <div class="col-md-4">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                name="targets[{{ $index }}][instruksi][]"
                                                                value="{{ $key }}"
                                                                id="instruksi_{{ $index }}_{{ $key }}"
                                                                {{ in_array($key, $selectedInstruksi) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="instruksi_{{ $index }}_{{ $key }}">
                                                                {{ $label }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Keterangan (opsional)</label>
                                        <input type="text" class="form-control" name="targets[{{ $index }}][keterangan]"
                                            value="{{ $target->keterangan ?? '' }}" placeholder="Catatan khusus untuk unit ini">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>

                    <div id="noTargetMessage" class="text-center text-muted py-3 {{ ($disposisi && $disposisi?->targets->count() > 0) ? 'd-none' : '' }}">
                        <i class="fas fa-arrow-up"></i> Klik "Tambah Unit" untuk menambahkan unit tujuan
                    </div>
                </div>
            </div>

            <!-- Bagian C: Sifat -->
            <div class="card">
                <div class="card-header">
                    <h4>C. Sifat</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $sifatList = [
                                'sangat_segera' => 'Sangat Segera',
                                'segera' => 'Segera',
                                'rahasia' => 'Rahasia',
                                'biasa' => 'Biasa',
                            ];
                            $selectedSifat = $disposisi?->sifat ?? 'biasa';
                        @endphp
                        @foreach ($sifatList as $key => $label)
                        <div class="col-md-3">
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" name="sifat"
                                    value="{{ $key }}" id="sifat_{{ $key }}"
                                    {{ $selectedSifat == $key ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sifat_{{ $key }}">{{ $label }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Bagian D: Catatan Lain -->
            <div class="card">
                <div class="card-header">
                    <h4>D. Catatan Lain</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea class="form-control" name="catatan_lain" id="catatan_lain" rows="3"
                            placeholder="Catatan tambahan (opsional)">{{ $disposisi?->catatan_lain ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Bagian E: Batas Waktu -->
            <div class="card">
                <div class="card-header">
                    <h4>E. Batas Waktu Penyelesaian</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="date" class="form-control" name="batas_waktu" id="batas_waktu"
                                    value="{{ $disposisi?->batas_waktu?->format('Y-m-d') ?? '' }}">
                                <small class="text-muted">Kosongkan jika tidak ada batas waktu</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> {{ $disposisi ? 'Simpan Perubahan' : 'Buat Disposisi' }}
                    </button>
                    <a href="{{ route('disposisi.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</section>

@push('scripts')
<script>
    let targetIndex = {{ ($disposisi && $disposisi?->targets->count() > 0) ? $disposisi?->targets->count() : 0 }};

    const instruksiList = {
        'selesaikan': 'Selesaikan/Tindaklanjuti',
        'tindaklanjuti': 'Tindaklanjuti',
        'saran_pendapat': 'Saran/Pendapat',
        'koordinasikan': 'Koordinasikan',
        'pelajari_kaji': 'Pelajari/Kaji',
        'wakili_hadiri': 'Wakili/Hadiri',
        'pantau': 'Pantau',
        'perhatian': 'Untuk Menjadi Perhatian',
        'file': 'File',
    };

    function tambahTarget() {
        const idx = targetIndex++;
        const units = @json($units->pluck('name', 'id'));
        let unitOptions = '<option value="">-- Pilih Unit --</option>';
        for (const [id, name] of Object.entries(units)) {
            unitOptions += `<option value="${id}">${name}</option>`;
        }

        let instruksiHtml = '';
        for (const [key, label] of Object.entries(instruksiList)) {
            instruksiHtml += `
                <div class="col-md-4">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input"
                            name="targets[${idx}][instruksi][]"
                            value="${key}" id="instruksi_${idx}_${key}">
                        <label class="custom-control-label" for="instruksi_${idx}_${key}">${label}</label>
                    </div>
                </div>
            `;
        }

        const html = `
            <div class="target-item card bg-light mb-3" data-index="${idx}">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">Unit Tujuan #${idx + 1}</h6>
                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusTarget(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Unit Kerja <span class="text-danger">*</span></label>
                                <select class="form-control" name="targets[${idx}][unit_id]" required>
                                    ${unitOptions}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Instruksi Disposisi <span class="text-danger">*</span></label>
                                <div class="row">${instruksiHtml}</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Keterangan (opsional)</label>
                        <input type="text" class="form-control" name="targets[${idx}][keterangan]"
                            placeholder="Catatan khusus untuk unit ini">
                    </div>
                </div>
            </div>
        `;

        $('#targetsContainer').append(html);
        $('#noTargetMessage').addClass('d-none');
    }

    window.hapusTarget = function(btn) {
        $(btn).closest('.target-item').remove();
        if ($('.target-item').length === 0) {
            $('#noTargetMessage').removeClass('d-none');
        }
    };

    $('#disposisiForm').on('submit', function(e) {
        e.preventDefault();

        // Validasi setiap target punya instruksi
        let valid = true;
        let errorMsg = '';

        if ($('.target-item').length === 0) {
            valid = false;
            errorMsg = 'Minimal satu unit tujuan harus ditambahkan.';
        } else {
            $('.target-item').each(function() {
                const checked = $(this).find('input[type="checkbox"]:checked').length;
                if (checked === 0) {
                    valid = false;
                    $(this).addClass('border border-danger');
                    errorMsg = 'Setiap unit harus memiliki minimal satu instruksi disposisi.';
                } else {
                    $(this).removeClass('border border-danger');
                }
            });
        }

        if (!valid) {
            toastr.error(errorMsg);
            console.error('Validation failed:', errorMsg);
            return;
        }

        const form = $(this);
        const isEdit = '{{ $disposisi ? "true" : "false" }}' === 'true';
        const id = '{{ $disposisi?->id ?? "" }}';
        const url = isEdit ? `{{ url("disposisi") }}/${id}` : '{{ route("disposisi.store") }}';
        const method = isEdit ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize() + '&_method=' + method,
            success: function(res) {
                toastr.success(res.message);
                if (isEdit) {
                    window.location.href = '{{ route("disposisi.index") }}';
                } else {
                    window.location.href = '{{ url("disposisi") }}/' + res.data.id;
                }
            },
            error: function(xhr) {
                console.error('Disposisi submit error:', xhr.responseJSON);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let msg = 'Validasi gagal:<br><ul style="margin:5px 0 0 15px;text-align:left">';
                    for (const key in errors) {
                        msg += '<li>' + errors[key][0] + '</li>';
                        const input = $(`[name="${key}"]`);
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(errors[key][0]);
                        }
                    }
                    msg += '</ul>';
                    toastr.error(msg, '', { escapeHtml: false });
                    console.error('Validation errors:', errors);
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan server');
                    console.error('Server error:', xhr.responseJSON);
                }
            }
        });
    });

    // Reset validation on input
    $(document).on('change', '.target-item select, .target-item input', function() {
        $(this).closest('.target-item').removeClass('border border-danger');
    });
</script>
@endpush
@endsection
