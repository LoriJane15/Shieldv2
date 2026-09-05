<?php

namespace App\Enums;

enum JapicCertificationTriggerSource: string
{
    case CdrCompletion = 'cdr_completion';
    case Reconciliation = 'reconciliation';
}
