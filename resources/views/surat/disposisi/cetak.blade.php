<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Disposisi - {{ $disposisi->no_agenda }}</title>
    <style>
        :root {
            --border: 1px solid #111;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #111;
            margin: 0;
            padding: 24px;
            background: #fff;
        }

        .page {
            max-width: 210mm;
            margin: 0 auto;
        }

        .print-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .print-actions button {
            font-family: inherit;
            font-size: 12px;
            padding: 6px 16px;
            border: var(--border);
            background: #fff;
            cursor: pointer;
        }

        .print-actions button:hover { background: #eee; }

        .kop {
            text-align: center;
            margin-bottom: 6px;
        }

        .kop .instansi {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .kop .aplikasi {
            font-size: 10px;
            text-transform: uppercase;
            color: #444;
            margin-top: 2px;
        }

        .kop .judul {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 12px;
            letter-spacing: 1px;
        }

        .rule-double {
            border: none;
            border-top: 2px solid #111;
            border-bottom: 1px solid #111;
            height: 3px;
            margin: 8px 0 14px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #444;
            margin-bottom: 12px;
        }

        table.form {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        table.form th,
        table.form td {
            border: var(--border);
            padding: 5px 8px;
            vertical-align: top;
            text-align: left;
        }

        table.form th {
            width: 28%;
            background: #f2f2f2;
            font-weight: bold;
        }

        .sifat {
            font-weight: bold;
        }

        .section-label {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            margin: 16px 0 6px;
        }

        table.targets {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        table.targets th,
        table.targets td {
            border: var(--border);
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        table.targets th {
            background: #f2f2f2;
            font-weight: bold;
        }

        table.targets tbody tr:nth-child(even) { background: #fafafa; }
        table.targets thead { display: table-header-group; }
        table.targets tr { page-break-inside: avoid; }

        .instruksi-item { display: block; line-height: 1.6; }

        .catatan-lain {
            border: var(--border);
            padding: 8px;
            min-height: 60px;
            margin-bottom: 14px;
            white-space: pre-wrap;
        }

        .ttd-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 48px;
        }

        .ttd-box {
            width: 260px;
            text-align: center;
        }

        .ttd-box .jabatan { font-weight: bold; }

        .ttd-box .nama {
            font-weight: bold;
            margin-top: 70px;
        }

        .ttd-box .nip { margin-top: 4px; }

        .ttd-line {
            width: 70%;
            margin: 2px auto 6px;
            border: none;
            border-top: 1px solid #111;
        }

        .footer-cetak {
            text-align: center;
            font-size: 10px;
            color: #444;
            margin-top: 24px;
        }

        @media print {
            body { padding: 0; }
            .print-actions { display: none; }
        }

        @page {
            margin: 1.5cm 2cm;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="print-actions">
            <button onclick="window.print()">Cetak / Print</button>
            <button onclick="window.close()">Tutup</button>
        </div>

        <div class="kop">
            {{-- <div class="instansi">{{ config('institution.name', 'RSI JOMBANG') }}</div>
            <div class="aplikasi">Sistem Informasi Disposisi Surat</div> --}}
            <div class="judul">Lembar Disposisi</div>
        </div>
        <hr class="rule-double">

        <div class="meta-row">
            <span>Nomor Agenda: <strong>{{ $disposisi->no_agenda }}</strong></span>
            <span>Status: <strong>{{ $disposisi->status->label() }}</strong></span>
        </div>

        @php
            $sifatLabels = [
                'sangat_segera' => 'Sangat Segera',
                'segera' => 'Segera',
                'rahasia' => 'Rahasia',
                'biasa' => 'Biasa',
            ];

            $perihal = $disposisi->surat->perihal ?? ($disposisi->document->title ?? null);
            $noSurat = $disposisi->surat->no_surat ?? null;
        @endphp

        <table class="form">
            <tr>
                <th>Nomor Agenda</th>
                <td>: {{ $disposisi->no_agenda }}</td>
            </tr>
            <tr>
                <th>Tanggal Naskah</th>
                <td>: {{ $disposisi->tanggal_naskah->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Masuk ke Sekretariat</th>
                <td>: {{ $disposisi->masuk_tu->format('d-m-Y H:i') }}</td>
            </tr>
            <tr>
                <th>Tanggal / Nomor Naskah</th>
                <td>: {{ $disposisi->tgl_no_naskah }}</td>
            </tr>
            <tr>
                <th>Asal Naskah</th>
                <td>: {{ $disposisi->asal_naskah }}</td>
            </tr>
            @if ($perihal)
            <tr>
                <th>Perihal</th>
                <td>: {{ $perihal }}{{ $noSurat ? ' (' . $noSurat . ')' : '' }}</td>
            </tr>
            @endif
            <tr>
                <th>Isi Informasi</th>
                <td>: {{ $disposisi->isi_informasi }}</td>
            </tr>
            <tr>
                <th>Sifat</th>
                <td>: <span class="sifat">{{ $sifatLabels[$disposisi->sifat] ?? $disposisi->sifat }}</span></td>
            </tr>
            @if ($disposisi->batas_waktu)
            <tr>
                <th>Batas Waktu</th>
                <td>: {{ $disposisi->batas_waktu->format('d-m-Y') }}</td>
            </tr>
            @endif
            <tr>
                <th>Dsiposisi oleh</th>
                <td>: {{ $disposisi->creator->name ?? '-' }}, {{ $disposisi->created_at->format('d-m-Y H:i') }}</td>
            </tr>
        </table>

        <div class="section-label">A. Diteruskan Kepada</div>
        <table class="targets">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Unit Kerja</th>
                    <th width="45%">Instruksi</th>
                    <th width="25%">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $instruksiLabels = [
                        'selesaikan' => 'Selesaikan / Tindaklanjuti',
                        'tindaklanjuti' => 'Tindaklanjuti',
                        'saran_pendapat' => 'Saran / Pendapat',
                        'koordinasikan' => 'Koordinasikan',
                        'pelajari_kaji' => 'Pelajari / Kaji',
                        'wakili_hadiri' => 'Wakili / Hadiri',
                        'pantau' => 'Pantau',
                        'perhatian' => 'Untuk Menjadi Perhatian',
                        'file' => 'File',
                    ];
                @endphp
                @forelse ($disposisi->targets as $index => $target)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $target->unit->name }}</td>
                    <td>
                        @foreach ($target->instruksi as $inst)
                            <span class="instruksi-item">&check; {{ $instruksiLabels[$inst] ?? $inst }}</span>
                        @endforeach
                    </td>
                    <td>{{ $target->keterangan ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada target</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if ($disposisi->catatan_lain)
        <div class="section-label">B. Catatan Lain</div>
        <div class="catatan-lain">{{ $disposisi->catatan_lain }}</div>
        @endif

        {{-- <div class="ttd-row">
            <div class="ttd-box">
                <div class="jabatan">Mengetahui / Meneruskan,</div>
                <div class="nama">{{ $disposisi->creator->name ?? '________________' }}</div>
                <hr class="ttd-line">
                <div class="nip">NIP. ________________</div>
            </div>
        </div> --}}

        <div class="footer-cetak">
            Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}
        </div>
    </div>
</body>
</html>
