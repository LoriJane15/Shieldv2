<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EclipDocumentRequirement extends Model
{
    protected $fillable = ['code', 'name', 'description', 'is_required', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'is_active' => 'boolean'];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EclipDocument::class, 'requirement_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EclipDocumentRequirementHistory::class, 'requirement_id');
    }
}
