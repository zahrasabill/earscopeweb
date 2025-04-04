<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
    return view('welcome');
});

// Rute untuk mengakses file video dari storage
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $path = $folder . '/' . $filename;
    
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    
    $mimeType = Storage::disk('public')->mimeType($path);
    $size = Storage::disk('public')->size($path);
    
    $headers = [
        'Content-Type' => $mimeType,
        'Content-Length' => $size,
        'Accept-Ranges' => 'bytes',
        'Cache-Control' => 'public, max-age=3600',
    ];
    
    return Storage::disk('public')->response($path, null, $headers);
})->where('filename', '.*');
