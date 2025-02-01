<?php

use Illuminate\Support\Facades\Route;

Route::get('*', function () {
    abort(400); // Block all non-API routes and return a 404 error
});
/*
Route::get('/', function () {
    return view('welcome');
});
*/
