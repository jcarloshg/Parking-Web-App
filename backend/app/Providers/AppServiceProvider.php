<?php

namespace App\Providers;

use App\Models\ParkingSpace;
use App\Policies\ParkingSpacePolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        ParkingSpace::class => ParkingSpacePolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
