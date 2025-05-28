<?php


use App\Http\Controllers\Panel\ArticleController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->name('api.v1.')->middleware([])->group(function (){
    Route::apiResource('articles', \App\Http\Controllers\ArticleController::class)->only(['index', 'show']);
});

Route::prefix('v1')->name('api.v1.')->middleware(['guest'])->group(function (){
    //auth
    Route::prefix('auth')->name('auth.')->group(function (){
        Route::post('/register',[\App\Http\Controllers\Panel\AuthController::class,'register'])->name('register');
        Route::post('/login',[\App\Http\Controllers\Panel\AuthController::class,'login'])->name('login');
        Route::post('/forgot',[\App\Http\Controllers\Panel\AuthController::class,'forgot'])->name('forgot');
        Route::post('/forgot/change-password',[\App\Http\Controllers\Panel\AuthController::class,'change_password'])->name('forgot.change_password');
    });
});

Route::prefix('v1/panel')->name('api.v1.panel.')->middleware(['auth:sanctum'])->group(function (){
    //auth
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Panel\AuthController::class, 'profile'])->name('profile.show');
        Route::put('/profile', [\App\Http\Controllers\Panel\AuthController::class, 'profile_update'])->name('profile.update');
    });
    //article
    Route::apiResource('articles', ArticleController::class);
    //category
    Route::apiResource('categories', \App\Http\Controllers\Panel\CategoryController::class);
});



