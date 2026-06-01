<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Routine extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'goal_id',
        'name',
        'description',
        'is_ai_generated',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_ai_generated' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RoutineItem::class);
    }

    public function aiFeedbacks(): HasMany
    {
        return $this->hasMany(AiFeedback::class, 'related_routine_id');
    }
}
