<?php

namespace App\Enums;

enum JapicCertificationStatus: string
{
    case Pending = 'Pending';
    case Drafting = 'Drafting';
    case ForSignature = 'For Signature';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';
}
