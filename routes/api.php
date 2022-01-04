<?php

use App\Http\Controllers\{
    CastMemberController,
    CategoryController,
    GenreController,
    VideoController
};

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => response()->json(['success' => true]));
Route::apiResource('categories', CategoryController::class);
Route::apiResource('genres', GenreController::class);
Route::apiResource('cast_members', CastMemberController::class);
Route::apiResource('videos', VideoController::class);
