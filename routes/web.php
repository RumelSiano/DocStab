<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('portal');
})->name('portal');

require __DIR__.'/backend.php';

Route::get('/reset-password/{token}', function (string $token) {
    return view('portal', ['resetToken' => $token, 'resetEmail' => request('email', '')]);
})->name('password.reset');
