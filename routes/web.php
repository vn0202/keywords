<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/upload', [\App\Http\Controllers\HomeController::class, "uploadFile"])
->name("upload-file");
