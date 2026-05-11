<?php

namespace App\Policies;

use App\Models\DailyLog;
use App\Models\User;

class DailyLogPolicy
{
    public function view(User $user, DailyLog $dailyLog): bool
    {
        return $user->id === $dailyLog->user_id;
    }

    public function update(User $user, DailyLog $dailyLog): bool
    {
        return $user->id === $dailyLog->user_id;
    }

    public function delete(User $user, DailyLog $dailyLog): bool
    {
        return $user->id === $dailyLog->user_id;
    }
}
