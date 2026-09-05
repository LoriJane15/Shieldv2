<?php

namespace App\Enums;

enum JapicCertificationHistoryEvent: string
{
    case EnteredQueue = 'entered_queue';
    case Reconciled = 'reconciled';
    case ProcessingStarted = 'processing_started';
    case DraftSaved = 'draft_saved';
    case DelayRecorded = 'delay_recorded';
    case MarkedForSignature = 'marked_for_signature';
    case FinalUploaded = 'final_uploaded';
    case FinalReplaced = 'final_replaced';
    case ParentFrCancelled = 'parent_fr_cancelled';
}
