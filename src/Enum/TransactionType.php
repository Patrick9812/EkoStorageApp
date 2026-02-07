<?php

namespace App\Enum;

enum TransactionType: string
{
    case IN = 'in';
    case OUT = 'out';

    public function getLabel(): string
    {
        return match ($this) {
            self::IN => 'Przyjęcie',
            self::OUT => 'Wydanie',
        };
    }
}
