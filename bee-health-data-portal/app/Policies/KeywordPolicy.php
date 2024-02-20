<?php

namespace App\Policies;

use App\Models\Keyword;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeywordPolicy
{
    use HandlesAuthorization;

    public function viewAdmin(User $user)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Keyword  $keyword
     * @return mixed
     */
    public function view(User $user, Keyword $keyword)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Keyword  $keyword
     * @return mixed
     */
    public function update(User $user, Keyword $keyword)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Keyword  $keyword
     * @return mixed
     */
    public function delete(User $user, Keyword $keyword)
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Keyword  $keyword
     * @return mixed
     */
    public function restore(User $user, Keyword $keyword)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Keyword  $keyword
     * @return mixed
     */
    public function forceDelete(User $user, Keyword $keyword)
    {
        return $user->isSuperAdmin();
    }


}
