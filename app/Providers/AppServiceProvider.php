<?php

namespace App\Providers;

use App\Models\User;
use App\Models\FriendRequest;
use App\Policies\UserPolicy;
use App\Policies\FriendRequestPolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        FriendRequest::class => FriendRequestPolicy::class,
    ];

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
        //
    }
}
