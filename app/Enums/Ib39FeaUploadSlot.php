<?php

namespace App\Enums;

enum Ib39FeaUploadSlot: string
{
    case Primary = 'primary';
    case JustificationComparison = 'justification_comparison';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Draft file',
            self::JustificationComparison => 'Justification Section 4 comparison photograph',
        };
    }
}
