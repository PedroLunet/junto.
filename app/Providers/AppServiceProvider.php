<?php

namespace App\Providers;

use App\Models\User\User;
use App\Models\Post\FriendRequest;
use App\Models\Post\Post;
use App\Models\Post\Report;
use App\Models\Post\Comment;
use App\Models\Message;
use App\Models\User\Notification;
use App\Policies\UserPolicy;
use App\Policies\FriendRequestPolicy;
use App\Policies\PostPolicy;
use App\Policies\ReportPolicy;
use App\Policies\CommentPolicy;
use App\Policies\MessagePolicy;
use App\Policies\NotificationPolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        FriendRequest::class => FriendRequestPolicy::class,
        Post::class => PostPolicy::class,
        Report::class => ReportPolicy::class,
        Comment::class => CommentPolicy::class,
        Message::class => MessagePolicy::class,
        Notification::class => NotificationPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // register services as singletons for better performance
        $this->app->singleton(\App\Services\FavoriteService::class);
        $this->app->singleton(\App\Services\Media\MovieService::class);
        $this->app->singleton(\App\Services\Media\BookService::class);
        $this->app->singleton(\App\Services\Media\MusicService::class);
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
