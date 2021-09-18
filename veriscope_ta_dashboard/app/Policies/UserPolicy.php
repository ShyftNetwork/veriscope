<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->inGroup('admin');
    }

    public function show(User $user, User $editUser)
    {
        return $user->id === $editUser->id || $user->inGroup('admin');
    }

    public function edit(User $user, User $editUser)
    {
        return $user->id === $editUser->id || $user->isRole('super|compliance|god');
    }

    public function update(User $user, User $editUser)
    {
        return $user->id === $editUser->id || $user->isRole('super|compliance|god');
    }
}
