<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFeedback extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'related_log_id',
        'related_routine_id',
        'feedback_type',
        'summary',
        'recommendation',
        'generated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailyLog(): BelongsTo
    {
        return $this->belongsTo(DailyLog::class, 'related_log_id');
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class, 'related_routine_id');
    }
}
