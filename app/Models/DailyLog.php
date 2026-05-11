<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'log_date',
        'mood_score',
        'energy_level',
        'stress_level',
        'sleep_hours',
        'water_intake_ml',
        'body_condition',
        'productivity_score',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'sleep_hours' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiFeedbacks(): HasMany
    {
        return $this->hasMany(AiFeedback::class, 'related_log_id');
    }
}
