<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }

    public function update(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }

    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }
}
