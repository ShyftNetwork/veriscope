<?php

namespace App\Policies;

use App\{User, Constant};
use Illuminate\Auth\Access\HandlesAuthorization;

class ConstantPolicy
{
    use HandlesAuthorization;

    public function edit(User $user)
    {
        return $user->isRole('super|god');
    }
}
