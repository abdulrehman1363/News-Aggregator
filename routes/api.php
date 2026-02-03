<?php

use App\Http\Controllers\Api\GetArticleController;
use App\Http\Controllers\Api\GetArticlesController;
use App\Http\Controllers\Api\GetAuthenticatedUserController;
use App\Http\Controllers\Api\GetCategoriesController;
use App\Http\Controllers\Api\GetCategoryController;
use App\Http\Controllers\Api\GetPersonalizedFeedController;
use App\Http\Controllers\Api\GetSourceController;
use App\Http\Controllers\Api\GetSourcesController;
use App\Http\Controllers\Api\GetUserPreferencesController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\StoreUserPreferencesController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);

// Public routes - Rate limited to 60 requests per minute
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/articles', GetArticlesController::class);
    Route::get('/articles/{id}', GetArticleController::class);
    Route::get('/sources', GetSourcesController::class);
    Route::get('/sources/{id}', GetSourceController::class);
    Route::get('/categories', GetCategoriesController::class);
    Route::get('/categories/{id}', GetCategoryController::class);
});

// Protected routes - Higher rate limit: 200 requests per minute
Route::middleware(['auth:sanctum', 'throttle:200,1'])->group(function () {
    Route::post('/logout', LogoutController::class);
    Route::get('/user', GetAuthenticatedUserController::class);
    Route::get('/preferences', GetUserPreferencesController::class);
    Route::post('/preferences', StoreUserPreferencesController::class);
    Route::get('/preferences/feed', GetPersonalizedFeedController::class);
});
