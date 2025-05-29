<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CommentPolicy;
use App\Policies\RolePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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
    //

        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        RateLimiter::for('api', function (Request $request) {
            if ($request->user()) {
                return Limit::perMinute(50)->by($request->user()->id);
            }
            return Limit::perMinute(25)->by($request->ip());
        });
    }
}
