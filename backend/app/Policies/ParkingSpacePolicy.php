<?php

namespace App\Policies;

use App\Models\ParkingSpace;
use App\Models\User;

class ParkingSpacePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ParkingSpace $parkingSpace): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ParkingSpace $parkingSpace): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, ParkingSpace $parkingSpace): bool
    {
        return $user->isAdmin();
    }
}
