<?php

namespace App\Enums;

enum DocumentCategory: string
{
    case Manifesto   = 'manifesto';
    case Constitution = 'constitution';
    case Report      = 'report';
    case Statement   = 'statement';
    case Other       = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Manifesto    => 'Manifesto',
            self::Constitution => 'Constitution',
            self::Report       => 'Report',
            self::Statement    => 'Statement',
            self::Other        => 'Other',
        };
    }
}
