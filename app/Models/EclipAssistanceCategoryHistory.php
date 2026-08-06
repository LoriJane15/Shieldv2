<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EclipAssistanceCategoryHistory extends Model
{
    protected $fillable = ['category_id', 'user_id', 'action', 'new_values'];

    protected function casts(): array
    {
        return ['new_values' => 'array'];
    }
}
