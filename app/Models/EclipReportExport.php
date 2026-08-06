<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EclipReportExport extends Model
{
    protected $fillable = ['user_id', 'municipality_id', 'format', 'financial_included', 'ip_address', 'exported_at'];

    protected function casts(): array
    {
        return ['financial_included' => 'boolean', 'exported_at' => 'datetime'];
    }
}
