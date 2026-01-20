<?php

namespace App\Policies;

use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PipelineStagePolicy
{
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return true; // Allow viewing list, but controller filters it
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, PipelineStage $pipelineStage): bool
  {
    // Ideally verify if stage belongs to user's team template
    return $user->team && $user->team->template_id === $pipelineStage->template_id;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    return $user->isManager();
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, PipelineStage $pipelineStage): bool
  {
    return $user->isManager() && ($user->team->template_id === $pipelineStage->template_id);
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, PipelineStage $pipelineStage): bool
  {
    return $user->isManager() && ($user->team->template_id === $pipelineStage->template_id);
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(User $user, PipelineStage $pipelineStage): bool
  {
    return false;
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(User $user, PipelineStage $pipelineStage): bool
  {
    return false;
  }
}
