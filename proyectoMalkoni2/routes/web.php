<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/supervisor/dashboard', function () {
    return view('supervisor/dashboard');
});
