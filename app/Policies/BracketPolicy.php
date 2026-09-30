<?php

namespace App\Policies;

use App\Models\Bracket;
use App\Models\User;

class BracketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Bracket $bracket): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Bracket $bracket): bool
    {
        return $user->is_admin || $bracket->user_id === $user->id;
    }

    public function addCandidate(User $user, Bracket $bracket): bool
    {
        return $this->update($user, $bracket) || $bracket->allow_candidate_submissions;
    }

    public function resolveTie(User $user, Bracket $bracket): bool
    {
        return $this->update($user, $bracket);
    }

    public function viewStats(User $user, Bracket $bracket): bool
    {
        return $this->update($user, $bracket);
    }

    public function advance(User $user, Bracket $bracket): bool
    {
        return $this->update($user, $bracket);
    }

    public function pause(User $user, Bracket $bracket): bool
    {
        return $this->update($user, $bracket);
    }

    public function reset(User $user, Bracket $bracket): bool
    {
        return $this->update($user, $bracket);
    }

    public function delete(User $user, Bracket $bracket): bool
    {
        return false;
    }
}
