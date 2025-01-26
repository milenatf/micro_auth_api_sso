<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Support\Facades\Auth;

use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Exception\ClientException;

class ProviderController extends Controller
{
    public function redirectToProvider(string $provider)
    {
        $validated = $this->validateProvider($provider);

        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        $validated = $this->validateProvider($provider);

        if (!is_null($validated)) {
            return $validated;
        }

        try {
            $providerUser = Socialite::driver($provider)->stateless()->user();
        } catch (ClientException $exception) {
            return response()->json(['error' => 'Credenciais inválidas.'], 422);
        }

        $user = User::firstOrCreate([
            'email' => $providerUser->email,
        ], [
            'name' => $providerUser->name,
        ]);

        $user->providers()->updateOrCreate(
            [
                'provider_id' => $providerUser->id,
                'provider_name' => $provider,
            ],
            [
                'provider_nickname' => $providerUser->nickname,
                'provider_avatar' => $providerUser->avatar,
                'provider_token' => $providerUser->token,
                'provider_refresh_token' => $providerUser->refreshToken,
                'id_token' => $providerUser->accessTokenResponseBody['id_token'] ?? null
            ]
        );

        Auth::login($user);

        return redirect('/me');
    }

    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['github', 'google', 'keycloak'])) {
            return response()->json(['error' => 'Autenticar com keycloak, github ou google'], 422);
        }
    }
}
