<?php

namespace App\Enums;

enum PolicyStatus: string
{
    case Quoted = 'cotizado';

    case Contracted = 'contratado';

    /**
     * All possible status values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Quoted => 'Cotizado',
            self::Contracted => 'Contratado',
        };
    }
}