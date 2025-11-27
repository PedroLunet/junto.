<?php

namespace App\Providers;

use App\Models\User;
use App\Models\FriendRequest;
use App\Models\Post;
use App\Models\Report;
use App\Policies\UserPolicy;
use App\Policies\FriendRequestPolicy;
use App\Policies\PostPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        FriendRequest::class => FriendRequestPolicy::class,
        Post::class => PostPolicy::class,
        Report::class => ReportPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // register services as singletons for better performance
        $this->app->singleton(\App\Services\FavoriteService::class);
        $this->app->singleton(\App\Services\MovieService::class);
        $this->app->singleton(\App\Services\BookService::class);
        $this->app->singleton(\App\Services\MusicService::class);
        $this->app->singleton(\App\Services\FriendService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }

    // register the application's policies
    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            \Illuminate\Support\Facades\Gate::policy($model, $policy);
        }
    }
}
