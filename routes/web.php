<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MeditationController;

Route::get('/', [MeditationController::class, 'index']);
Route::get('/jft', [MeditationController::class, 'jft']);
Route::get('/jft/{language}', [MeditationController::class, 'jftLanguage']);
Route::get('/spad', [MeditationController::class, 'spad']);
Route::get('/spad/{language}', [MeditationController::class, 'spadLanguage']);
