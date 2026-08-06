<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EclipAssistanceCategory extends Model
{
    protected $fillable = ['code', 'name', 'description', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EclipAssistanceCategoryHistory::class, 'category_id');
    }
}
