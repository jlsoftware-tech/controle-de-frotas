<?php

namespace App\Providers;

use App\Models\Profile;
use App\Models\Secretariat;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\DevCommands;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DevCommands::except('vite');
        Relation::morphMap([
            'users' => User::class,
            'profiles' => Profile::class,
            'secretariats' => Secretariat::class,
            'vehicles' => Vehicle::class,
        ]);
    }
}
