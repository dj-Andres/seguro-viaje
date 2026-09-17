<?php

use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [CountryController::class, 'index']);

Route::get('/quotes', [QuoteController::class, 'index']);
Route::post('/quotes', [QuoteController::class, 'store']);
Route::get('/quotes/{policy}', [QuoteController::class, 'show']);
Route::post('/quotes/{policy}/contract', [QuoteController::class, 'contract']);
Route::get('/quotes/{policy}/pdf', [QuoteController::class, 'pdf']);
