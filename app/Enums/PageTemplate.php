<?php

namespace App\Enums;

enum PageTemplate: string
{
    case Default = 'default';
    case About   = 'about';
    case Contact = 'contact';
    case Join    = 'join';

    public function label(): string
    {
        return match ($this) {
            self::Default => 'Default',
            self::About   => 'About',
            self::Contact => 'Contact',
            self::Join    => 'Join',
        };
    }
}
