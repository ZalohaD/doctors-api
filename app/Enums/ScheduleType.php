<?php

namespace App\Enums;

enum ScheduleType: int
{
    case WORKING_HOURS = 1;
    case VACATION = 2;
    case DAY_OFF = 3;

    public function label(): string
    {
        return match($this) {
            self::WORKING_HOURS => 'Working Hours',
            self::VACATION => 'Vacation',
            self::DAY_OFF => 'Day Off',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::WORKING_HOURS => 'green',
            self::VACATION => 'blue',
            self::DAY_OFF => 'red',
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
