<?php

namespace App\Enums;

enum GradeBand: string
{
    case Poor = 'F';
    case Average = 'D';
    case Good = 'C';
    case VeryGood = 'B';
    case Excellent = 'A';

    public static function fromScore(float $score): self
    {
        return match (true) {
            $score >= 6.0 => self::Excellent,
            $score >= 5.0 => self::VeryGood,
            $score >= 4.0 => self::Good,
            $score >= 3.0 => self::Average,
            default => self::Poor,
        };
    }

    public static function fromLetter(?string $letter): ?self
    {
        return $letter === null ? null : self::tryFrom($letter);
    }

    public function letter(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return __('grades.bands.'.$this->name);
    }

    public function colorClasses(): string
    {
        return match ($this) {
            self::Excellent => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            self::VeryGood => 'bg-sky-100 text-sky-800 ring-sky-600/20',
            self::Good => 'bg-indigo-100 text-indigo-800 ring-indigo-600/20',
            self::Average => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            self::Poor => 'bg-red-100 text-red-800 ring-red-600/20',
        };
    }
}
