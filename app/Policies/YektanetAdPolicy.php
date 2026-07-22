<?php

namespace App\Policies;

use App\Models\User;
use App\Models\YektanetAd;
use Illuminate\Auth\Access\HandlesAuthorization;

class YektanetAdPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_yektanet::ad');
    }

    public function view(User $user, YektanetAd $yektanetAd): bool
    {
        return $user->can('view_yektanet::ad');
    }

    public function create(User $user): bool
    {
        return $user->can('create_yektanet::ad');
    }

    public function update(User $user, YektanetAd $yektanetAd): bool
    {
        return $user->can('update_yektanet::ad');
    }

    public function delete(User $user, YektanetAd $yektanetAd): bool
    {
        return $user->can('delete_yektanet::ad');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_yektanet::ad');
    }

    public function forceDelete(User $user, YektanetAd $yektanetAd): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, YektanetAd $yektanetAd): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, YektanetAd $yektanetAd): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return false;
    }
}
