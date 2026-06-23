<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TransactionController;

Route::get('/', [HomeController::class, 'index']);

Route::get('/exchange', [HomeController::class, 'exchange']);

Route::get('/faq', [HomeController::class, 'faq']);

Route::get('/agreement', [HomeController::class, 'agreement']);

Route::get('/contacts', [HomeController::class, 'contacts']);

Route::get('/transaction', [TransactionController::class, 'index']);
Route::get('/transaction/lookup', [TransactionController::class, 'lookup']);

Route::get('/tracker', fn() => view('tracker'));
