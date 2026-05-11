<?php

namespace App\Contracts;

use App\Models\DailyLog;
use App\Models\Routine;
use App\Models\User;

interface AiAnalysisContract
{
    public function generateRoutineDraft(User $user, array $preferences): array;

    public function analyzeRoutine(User $user, Routine $routine): void;

    public function analyzeDailyLog(User $user, DailyLog $dailyLog): void;
}
