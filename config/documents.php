<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Pengaturan batas ukuran upload dokumen/surat (dalam MB).
    | Nilai ini menjadi sumber kebenaran (single source of truth) yang dipakai
    | oleh controller validation, service, maupun validasi client-side.
    |
    */

    'max_upload_size_mb' => 100,

    'max_upload_size_bytes' => 100 * 1024 * 1024,

];