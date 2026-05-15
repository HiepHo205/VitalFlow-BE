<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutineItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'routine_id',
        'title',
        'description',
        'category',
        'start_time',
        'end_time',
        'duration_minutes',
        'priority',
        'recurrence_type',
    ];

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function completions()
    {
        return $this->hasMany(
            RoutineCompletion::class,
            'routine_item_id'
        );
    }
}
