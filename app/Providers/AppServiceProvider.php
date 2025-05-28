<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Article::class => ArticlePolicy::class,
        Category::class => CategoryPolicy::class,
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
        RateLimiter::for('api', function (Request $request) {
            if ($request->user()) {
                return Limit::perMinute(50)->by($request->user()->id);
            }
            return Limit::perMinute(25)->by($request->ip());
        });
    }
}
