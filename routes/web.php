<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', fn() => redirect()->to(route('filament.store.pages.dashboard')));
