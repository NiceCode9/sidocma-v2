<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cetak Disposisi - {{ $disposisi->no_agenda }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 18px; text-transform: uppercase; }
        .header h3 { margin: 5px 0; font-size: 14px; }
        .header hr { border: 1px solid black; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 4px 8px; vertical-align: top; }
        .info-table .label { width: 25%; font-weight: bold; }
        .info-table .value { width: 75%; }
        .divider { border: none; border-top: 2px solid black; margin: 10px 0; }
        .section-title { font-weight: bold; font-size: 14px; margin: 10px 0 5px; text-transform: uppercase; }
        .target-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .target-table th, .target-table td { border: 1px solid black; padding: 6px 8px; text-align: left; }
        .target-table th { background: #f0f0f0; }
        .instruksi-item { display: block; }
        .footer { margin-top: 40px; }
        .signature { display: flex; justify-content: flex-end; }
        .signature-box { text-align: center; width: 250px; }
        .signature-box .name { font-weight: bold; margin-top: 60px; }
        .print-btn { text-align: center; margin: 20px 0; }
        @media print {
            .print-btn { display: none; }
            body { margin: 10px; }
        }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">Cetak / Print</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <div class="header">
        <h2>LEMBAR DISPOSISI</h2>
        <hr>
    </div>

    <table class="info-table">
        @php
        $sifatLabels = ['sangat_segera' => 'Sangat Segera', 'segera' => 'Segera', 'rahasia' => 'Rahasia', 'biasa' => 'Biasa'];
        @endphp
        <tr>
            <td class="label">Nomor Agenda</td>
            <td class="value">: {{ $disposisi->no_agenda }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Naskah</td>
            <td class="value">: {{ $disposisi->tanggal_naskah->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td class="label">Masuk ke TU</td>
            <td class="value">: {{ $disposisi->masuk_tu->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Tgl / No Naskah</td>
            <td class="value">: {{ $disposisi->tgl_no_naskah }}</td>
        </tr>
        <tr>
            <td class="label">Asal Naskah</td>
            <td class="value">: {{ $disposisi->asal_naskah }}</td>
        </tr>
        <tr>
            <td class="label">Isi Informasi</td>
            <td class="value">: {{ $disposisi->isi_informasi }}</td>
        </tr>
        <tr>
            <td class="label">Sifat</td>
            <td class="value">
                : {{ $sifatLabels[$disposisi->sifat] ?? $disposisi->sifat }}
            </td>
        </tr>
        @if ($disposisi->batas_waktu)
        <tr>
            <td class="label">Batas Waktu</td>
            <td class="value">: {{ $disposisi->batas_waktu->format('d-m-Y') }}</td>
        </tr>
        @endif
    </table>

    <hr class="divider">

    <div class="section-title">B. Diteruskan Kepada</div>
    <table class="target-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Unit Kerja</th>
                <th width="45%">Instruksi</th>
                <th width="12%">Paraf</th>
                <th width="13%">Tanggal</th>
            </tr>
        </thead>
        <tbody>
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
            @forelse ($disposisi->targets as $index => $target)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $target->unit->name }}</td>
                <td>
                    @foreach ($target->instruksi as $inst)
                        <span class="instruksi-item">&check; {{ $instruksiLabels[$inst] ?? $inst }}</span>
                    @endforeach
                </td>
                <td></td>
                <td></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">Tidak ada target</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if ($disposisi->catatan_lain)
    <div class="section-title">C. Catatan Lain</div>
    <p>{{ $disposisi->catatan_lain }}</p>
    @endif

    <hr class="divider">

    <div class="signature">
        <div class="signature-box">
            <p>Mengetahui / Meneruskan,</p>
            <br><br><br>
            <div class="name">{{ $disposisi->creator->name ?? '________________' }}</div>
            <hr style="width: 80%;">
            <small>NIP. ________________</small>
        </div>
    </div>

    <p style="text-align: center; margin-top: 20px; font-size: 10px;">
        Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}
    </p>
</body>
</html>
