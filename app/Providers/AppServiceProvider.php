<?php

namespace App\Providers;

use App\Models\Profile;
use App\Models\User;
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
        User::created(function ($user) {
            Profile::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'role' => 'pemohon'
            ]);
        });
    }
}
