<?php

namespace App\Enums;

enum AppointmentStatus: int
{
    case NEW = 1;
    case CONFIRMED = 2;
    case CANCELLED = 3;
    case COMPLETED = 4;

    public function label(): string
    {
        return match($this) {
            self::NEW => 'New',
            self::CONFIRMED => 'Confirmed',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::NEW => 'blue',
            self::CONFIRMED => 'green',
            self::CANCELLED => 'red',
            self::COMPLETED => 'gray',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
