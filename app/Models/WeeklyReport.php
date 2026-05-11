<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReport extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'week_start',
        'week_end',
        'summary',
        'avg_mood',
        'avg_energy',
        'avg_sleep',
        'burnout_risk_score',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'avg_mood' => 'decimal:2',
            'avg_energy' => 'decimal:2',
            'avg_sleep' => 'decimal:2',
            'burnout_risk_score' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
