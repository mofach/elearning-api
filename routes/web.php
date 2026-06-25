<?php

use Illuminate\Support\Facades\Route;

// Timpa rute default agar mengembalikan JSON, bukan tampilan HTML
Route::get('/', function () {
    return response()->json([
        'message' => 'E-Learning API Service is running.',
        'version' => '1.0'
    ]);
});
