<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MapBarangay extends Model
{
    protected $fillable = [
        'fid', 'province', 'municipality', 'barangay',
        'frs', 'status', 'infestation_color', 'rebels',
    ];

    public function colorHistories(): HasMany
    {
        return $this->hasMany(ColorHistory::class);
    }

    /**
     * RCSP infestation classification by former-rebel count.
     * Thresholds and colors preserved from the legacy 39th-IB module.
     */
    public static function classify(int $frs): array
    {
        return match (true) {
            $frs >= 20 => ['status' => 'Konsolidado', 'color' => 'rgba(255,0,0,0.5)'],
            $frs >= 15 => ['status' => 'Rekonsilida', 'color' => 'rgba(255,165,0,0.5)'],
            $frs >= 10 => ['status' => 'Expansion',   'color' => 'rgba(255,255,0,0.5)'],
            default => ['status' => 'Recovery',    'color' => 'rgba(0,255,0,0.5)'],
        };
    }
}
