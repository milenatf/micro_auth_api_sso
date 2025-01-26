<?php

use App\Http\Controllers\Api\Auth\{
    KeycloakController,
    ProviderController
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/register', [KeycloakController::class, 'register']);

// Route::post('/login', [KeycloakController::class, 'login']);
Route::get('/login/{provider}', [ProviderController::class,'redirectToProvider']);
Route::get('/login/{provider}/callback', [ProviderController::class,'handleProviderCallback']);

Route::get('/me', function() {

    $user = Auth::user();

    return response()->json(['data' => $user], 200);

});

Route::get('/logout-keycloak', [KeycloakController::class, 'logout']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
