<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class KeycloakController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $client = new Client();
        $keycloakAdminUrl = config('services.keycloak.base_url') . '/admin/realms/' . config('services.keycloak.realms') . '/users';
        $adminToken = $this->getKeycloakToken();

        if(!$adminToken) {
            return response()->json([
                'message' => 'Nao foi possivel gerar o token do admin'
            ], 500);
        }

        // Dados do novo usuário
        $userData = [
            'username' => $request->input('username'),
            'enabled' => true,
            'email' => $request->input('email'),
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'credentials' => [
                [
                    'type' => 'password',
                    'value' => $request->input('password'),
                    'temporary' => false,
                ],
            ],
        ];

        try {
            // Requisição para criar o usuário no Keycloak
            $response =  $client->post($keycloakAdminUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => $userData
            ]);

            if($response->getStatusCode() !== 201) {
                return response()->json([
                    'erro' => 'Falha ao registrar o usuário no keycloak'
                ], 500);
            }
        } catch(Exception $e) {
            return response()->json(['erro' => 'Erro o registrar o usuário no keycloak: ' . $e->getMessage()], 500);
        }

        $user = User::firstOrCreate([
            'email' => $userData['email'],
        ], [
            'name' => $request->input('firstName'). ' '. $request->input('lastName'),
            'password' => Hash::make($request->input('password'))
        ]);

        $user->providers()->updateOrCreate(
            [
                'user_id' => $user['id'],
            ],
            [
                'provider_name' => 'keycloak',
            ]
        );

        return response()->json(['message' => 'Usuário registrado com sucesso', 'user' => $user], 201);
    }

    // Método para obter o token do administrador do keycloak
    private function getKeycloakToken()
    {
        $client = new Client();
        $keycloakTokenUrl = config('services.keycloak.base_url') . '/realms/'.config('services.keycloak.realms').'/protocol/openid-connect/token';

        try {
            $response = $client->post($keycloakTokenUrl, [
                'form_params' => [
                    'client_id' => config('services.keycloak.client_id'),
                    'client_secret' => config('services.keycloak.client_secret'),
                    'grant_type' => 'client_credentials'
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['access_token'];
        } catch (\Exception $e) {
            return response()->json('Erro ao obter o token do Keycloak: ' . $e->getMessage(), 500);
        }
    }

    // public function logout() {

    //     $keycloakBaseUrl = config('services.keycloak.base_url');
    //     $realm = config('services.keycloak.realms');

    //     // URL for logging out of Keycloak
    //     $keycloakLogoutUrl = "{$keycloakBaseUrl}/realms/{$realm}/protocol/openid-connect/logout";

    //     // dd(Auth::user());
    //     // Recuperar o token de ID do usuário autenticado
    //     $idToken = Auth::user()->id_token; // Ajuste conforme o local de armazenamento do token

    //     $client = new Client();

    //     // Fazer a requisição de logout
    //     // $response = Http::asForm()->post($keycloakLogoutUrl, [
    //     //     'id_token_hint' => $idToken, // Token de ID do usuário autenticado
    //     // ]);

    //     $response = $client->post($keycloakLogoutUrl, [
    //         'form_params' => [
    //             'id_token_hint' => $idToken
    //         ],
    //     ]);

    //     // Verificar se o logout foi bem-sucedido
    //     if ($response->successful()) {
    //         // dd("hasoudhahsdso");
    //         // Realizar logout local no Laravel
    //         Auth::logout();

    //         // Redirecionar para a página inicial
    //         return redirect('/');
    //     }

    //     // Caso ocorra erro, retornar com mensagem de erro
    //     return response()->json([
    //         'message' => 'Logout no Keycloak falhou.',
    //         'error' => $response->body(),
    //     ], 500);
    // }
}
