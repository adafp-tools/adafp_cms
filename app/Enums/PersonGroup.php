<?php

namespace App\Enums;

enum PersonGroup: string
{
    case Leadership  = 'leadership';
    case Executive   = 'executive';
    case Parliament  = 'parliament';
    case Other       = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Leadership => 'Leadership',
            self::Executive  => 'Executive',
            self::Parliament => 'Parliament',
            self::Other      => 'Other',
        };
    }
}
