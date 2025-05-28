<?php


use App\Http\Controllers\Api\ArticleController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->name('api.v1.')->middleware(['guest'])->group(function (){
    //auth
    Route::prefix('auth')->name('auth.')->group(function (){
        Route::post('/register',[\App\Http\Controllers\Api\AuthController::class,'register'])->name('register');
        Route::post('/login',[\App\Http\Controllers\Api\AuthController::class,'login'])->name('login');
        Route::post('/forgot',[\App\Http\Controllers\Api\AuthController::class,'forgot'])->name('forgot');
        Route::post('/forgot/change-password',[\App\Http\Controllers\Api\AuthController::class,'change_password'])->name('forgot.change_password');
    });
});

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum'])->group(function (){
    //auth
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Api\AuthController::class, 'profile'])->name('profile.show');
        Route::put('/profile', [\App\Http\Controllers\Api\AuthController::class, 'profile_update'])->name('profile.update');
    });
    //article
    Route::apiResource('articles', ArticleController::class);
    //category
    Route::apiResource('categories', \App\Http\Controllers\Api\CategoryController::class);
});



