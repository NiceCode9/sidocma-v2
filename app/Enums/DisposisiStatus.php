<?php

namespace App\Enums;

enum DisposisiStatus: string
{
    case Draft = 'draft';
    case Diproses = 'diproses';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'badge-secondary',
            self::Diproses => 'badge-warning',
            self::Selesai => 'badge-success',
        };
    }
}
