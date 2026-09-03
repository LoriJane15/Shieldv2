<?php

namespace App\Enums;

enum Ib39FeaDocumentHistoryEvent: string
{
    case ProcessingStarted = 'processing_started';
    case ComplianceChanged = 'compliance_changed';
    case RemarksChanged = 'remarks_changed';
    case DelayChanged = 'delay_changed';

    public function label(): string
    {
        return match ($this) {
            self::ProcessingStarted => 'Preliminary processing started',
            self::ComplianceChanged => 'Compliance status changed',
            self::RemarksChanged => 'Remarks changed',
            self::DelayChanged => 'Delay information changed',
        };
    }
}
