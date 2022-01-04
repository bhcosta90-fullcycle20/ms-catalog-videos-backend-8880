<?php

use App\Http\Controllers\{
    CategoryController,
    GenreController
};

use Illuminate\Support\Facades\Route;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('genres', GenreController::class);
