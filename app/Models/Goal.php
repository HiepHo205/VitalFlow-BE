<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'goal_type',
        'target_value',
        'current_value',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class);
    }

    public function recordProgress(float $amount = 1.0): void
    {
        $currentValue = $this->current_value ?? 0;
        $targetValue = $this->target_value ?? 0;

        if ($targetValue <= 0) {
            return;
        }

        $updated = min($targetValue, $currentValue + $amount);
        $this->current_value = $updated;

        if ($updated >= $targetValue) {
            $this->status = 'completed';
        } elseif ($this->status !== 'completed') {
            $this->status = 'in_progress';
        }

        $this->save();
    }
}
