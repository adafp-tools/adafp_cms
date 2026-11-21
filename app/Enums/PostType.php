<?php

namespace App\Enums;

enum PostType: string
{
    case News      = 'news';
    case Statement = 'statement';
    case Article   = 'article';

    public function label(): string
    {
        return match ($this) {
            self::News      => 'News',
            self::Statement => 'Statement',
            self::Article   => 'Article',
        };
    }
}
