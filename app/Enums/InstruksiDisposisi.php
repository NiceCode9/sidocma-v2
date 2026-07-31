<?php

namespace App\Enums;

enum InstruksiDisposisi: string
{
    case Selesaikan = 'selesaikan';
    case Tindaklanjuti = 'tindaklanjuti';
    case SaranPendapat = 'saran_pendapat';
    case Koordinasikan = 'koordinasikan';
    case PelajariKaji = 'pelajari_kaji';
    case WakiliHadiri = 'wakili_hadiri';
    case Pantau = 'pantau';
    case Perhatian = 'perhatian';
    case File = 'file';

    public function label(): string
    {
        return match ($this) {
            self::Selesaikan => 'Selesaikan/Tindaklanjuti',
            self::Tindaklanjuti => 'Tindaklanjuti',
            self::SaranPendapat => 'Saran/Pendapat',
            self::Koordinasikan => 'Koordinasikan',
            self::PelajariKaji => 'Pelajari/Kaji',
            self::WakiliHadiri => 'Wakili/Hadiri',
            self::Pantau => 'Pantau',
            self::Perhatian => 'Untuk Menjadi Perhatian',
            self::File => 'File',
        };
    }
}
