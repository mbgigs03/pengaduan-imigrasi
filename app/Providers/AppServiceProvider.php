<?php

namespace App\Providers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use App\Repositories\PengaduanRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PengaduanRepository::class, fn() => new PengaduanRepository());
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
