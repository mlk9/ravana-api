<?php


use App\Http\Controllers\Panel\ArticleController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->name('api.v1.')->middleware(['throttle:api','throttle:14,1'])->group(function (){

    Route::get('articles', [\App\Http\Controllers\ArticleController::class, 'index'])
        ->name('articles.index');

    Route::get('articles/{article:slug}', [\App\Http\Controllers\ArticleController::class, 'show'])
        ->name('articles.show');

    Route::get('categories', [\App\Http\Controllers\CategoryController::class, 'index'])
        ->name('categories.index');

    Route::get('categories/{category:slug}', [\App\Http\Controllers\CategoryController::class, 'show'])
        ->name('categories.show');

    Route::get('articles/{article:uuid}/comments', [\App\Http\Controllers\CommentController::class,'article'])
        ->name('comments.article');
});

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum','throttle:api', \App\Http\Middleware\CheckUserSuspended::class])->group(function (){
    Route::apiResource('comments', \App\Http\Controllers\CommentController::class)->middleware(['throttle:10,1'])->only(['index', 'show', 'store']);
    Route::post('bookmarks/sync', [\App\Http\Controllers\BookmarkController::class, 'sync'])->middleware(['throttle:5,1'])->name('bookmarks.sync');
    Route::get('bookmarks', [\App\Http\Controllers\BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::get('me', [\App\Http\Controllers\UserController::class, 'me'])->middleware(['throttle:25,1'])
        ->name('me');
});

Route::prefix('v1')->name('api.v1.')->middleware(['guest','throttle:api'])->group(function (){
    //auth
    Route::prefix('auth')->name('auth.')->group(function (){
        Route::post('/register',[\App\Http\Controllers\Panel\AuthController::class,'register'])->middleware('throttle:10,1')->name('register');
        Route::post('/login',[\App\Http\Controllers\Panel\AuthController::class,'login'])->middleware('throttle:5,1')->name('login');
        Route::post('/forgot',[\App\Http\Controllers\Panel\AuthController::class,'forgot'])->middleware('throttle:10,1')->name('forgot');
        Route::post('/forgot/change-password',[\App\Http\Controllers\Panel\AuthController::class,'change_password'])->middleware('throttle:10,1')->name('forgot.change_password');
    });
});

Route::prefix('v1/panel')->name('api.v1.panel.')->middleware(['auth:sanctum','throttle:api', \App\Http\Middleware\CheckUserSuspended::class])->group(function (){
    //auth
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Panel\AuthController::class, 'profile'])->middleware(['throttle:25,1'])->name('profile.show');
        Route::put('/profile', [\App\Http\Controllers\Panel\AuthController::class, 'profile_update'])->middleware(['throttle:10,1'])->name('profile.update');
    });
    //article
    Route::apiResource('articles', ArticleController::class);
    //category
    Route::apiResource('categories', \App\Http\Controllers\Panel\CategoryController::class);
    //roles
    Route::apiResource('roles', \App\Http\Controllers\Panel\RoleController::class);

    Route::post('comments/{comment}/answer', [\App\Http\Controllers\Panel\CommentController::class, 'answer'])->name('comments.answer');
    Route::apiResource('comments', \App\Http\Controllers\Panel\CommentController::class);

    Route::apiResource('users', \App\Http\Controllers\Panel\UserController::class);
});



