<?php

namespace App\Policies;

use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view time logs
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TimeLog $timeLog): bool
    {
        return $user->id === optional($timeLog->project->client)->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Users can create time logs for their projects
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TimeLog $timeLog): bool
    {
        return $user->id === optional($timeLog->project->client)->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TimeLog $timeLog): bool
    {
        return $user->id === optional($timeLog->project->client)->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TimeLog $timeLog): bool
    {
        return $user->id === optional($timeLog->project->client)->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TimeLog $timeLog): bool
    {
        return $user->id === optional($timeLog->project->client)->user_id;
    }
}
