<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Ib39FeaReadiness;
use App\Models\Ib39SurfacedFormerRebel;

class LockedIb39FeaReadiness implements Ib39FeaReadiness
{
    private const DENIAL_MESSAGE = 'FEA processing is unavailable until the required PSWDO enrollment forms are completed.';

    public function isReady(Ib39SurfacedFormerRebel $record): bool
    {
        return false;
    }

    public function assertReady(Ib39SurfacedFormerRebel $record): void
    {
        abort(403, $this->denialMessage());
    }

    public function denialMessage(): string
    {
        return self::DENIAL_MESSAGE;
    }
}
