<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/login/{provider}/redirect', function (string $provider) {
//     $urlDeRedirecionamento = Socialite::driver($provider)->redirect();
//     return $urlDeRedirecionamento;
// });

// Route::get('/auth/{provider}/callback', function (string $provider) {
//     $user = Socialite::driver($provider)->user();

//     dd($user);

//     // $user->token
// });
