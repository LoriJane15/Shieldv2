<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAccessLog extends Model
{
    protected $fillable = [
        'user_id', 'document_type', 'document_id', 'document_version_id',
        'action', 'ip_address', 'user_agent',
    ];
}
