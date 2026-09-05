<?php

namespace App\Enums;

enum JapicCertificationDocumentSource: string
{
    case SystemAuthored = 'system_authored';
    case ExternallyPrepared = 'externally_prepared';
}
