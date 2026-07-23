<?php

use Illuminate\Support\Facades\Route;

// Health check.
Route::get('/ping', fn () => response()->json(['data' => ['ok' => true, 'app' => config('app.name')]]));

// Public news API (اپ خبری) — /api/v1/*
require __DIR__.'/api_v1.php';

// Member area API (پنل کاربری، Sanctum tokens) — /api/member/*
require __DIR__.'/api_member.php';
