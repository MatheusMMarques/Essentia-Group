<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('customers.index'))->name('home');

Route::resource('customers', CustomerController::class)->except('show');
