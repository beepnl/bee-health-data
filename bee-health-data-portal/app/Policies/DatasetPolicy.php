<?php

namespace App\Policies;

use App\Models\Dataset;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class DatasetPolicy
{
    use HandlesAuthorization;

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
     * @param  \App\Models\Dataset  $dataset
     * @return mixed
     */
    public function view(User $user, Dataset $dataset)
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
        return Auth::check();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Dataset  $dataset
     * @return mixed
     */
    public function update(User $user, Dataset $dataset)
    {
        return $dataset->is_editable;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Dataset  $dataset
     * @return mixed
     */
    public function delete(User $user, Dataset $dataset)
    {
        return $dataset->is_owner;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Dataset  $dataset
     * @return mixed
     */
    public function restore(User $user, Dataset $dataset)
    {
        return $dataset->is_owner;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Dataset  $dataset
     * @return mixed
     */
    public function forceDelete(User $user, Dataset $dataset)
    {
        return $dataset->is_owner;
    }
}
