<?php

namespace App\Policies;

use App\Models\Archive;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArchivePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_archive');
    }

    public function view(User $user, Archive $archive): bool
    {
        return $user->can('view_archive');
    }

    public function create(User $user): bool
    {
        return $user->can('create_archive');
    }

    public function update(User $user, Archive $archive): bool
    {
        return $user->can('update_archive');
    }

    public function delete(User $user, Archive $archive): bool
    {
        return $user->can('delete_archive');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_archive');
    }

    public function forceDelete(User $user, Archive $archive): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Archive $archive): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, Archive $archive): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return false;
    }
}
