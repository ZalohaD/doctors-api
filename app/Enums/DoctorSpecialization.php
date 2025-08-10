<?php

namespace App\Enums;

enum DoctorSpecialization: string
{
    case GENERAL_PRACTITIONER = 'general_practitioner';
    case CARDIOLOGIST = 'cardiologist';
    case DERMATOLOGIST = 'dermatologist';
    case NEUROLOGIST = 'neurologist';
    case PEDIATRICIAN = 'pediatrician';
    case PSYCHIATRIST = 'psychiatrist';
    case ORTHOPEDIST = 'orthopedist';
    case GYNECOLOGIST = 'gynecologist';
    case UROLOGIST = 'urologist';
    case OPHTHALMOLOGIST = 'ophthalmologist';
    case ENT_SPECIALIST = 'ent_specialist';
    case RADIOLOGIST = 'radiologist';
    case ANESTHESIOLOGIST = 'anesthesiologist';
    case SURGEON = 'surgeon';
    case ENDOCRINOLOGIST = 'endocrinologist';

    public function label(): string
    {
        return match($this) {
            self::GENERAL_PRACTITIONER => 'General Practitioner',
            self::CARDIOLOGIST => 'Cardiologist',
            self::DERMATOLOGIST => 'Dermatologist',
            self::NEUROLOGIST => 'Neurologist',
            self::PEDIATRICIAN => 'Pediatrician',
            self::PSYCHIATRIST => 'Psychiatrist',
            self::ORTHOPEDIST => 'Orthopedist',
            self::GYNECOLOGIST => 'Gynecologist',
            self::UROLOGIST => 'Urologist',
            self::OPHTHALMOLOGIST => 'Ophthalmologist',
            self::ENT_SPECIALIST => 'ENT Specialist',
            self::RADIOLOGIST => 'Radiologist',
            self::ANESTHESIOLOGIST => 'Anesthesiologist',
            self::SURGEON => 'Surgeon',
            self::ENDOCRINOLOGIST => 'Endocrinologist',
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
