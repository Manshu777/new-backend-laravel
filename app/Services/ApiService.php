<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ApiToken;

class ApiService
{
    private $clientId;
    private $username;
    private $password;

    public function __construct()
    {
        $this->clientId = 'ApiIntegrationNew';
        $this->username = 'Next';
        $this->password = 'Next@1234';
    }

    public function authenticate()
    {
        $response = Http::post('http://Sharedapi.tektravels.com/SharedData.svc/rest/Authenticate', [
            'ClientId' => $this->clientId,
            'UserName' => $this->username,
            'Password' => $this->password,
            'EndUserIp' => '148.135.137.54',
        ]);
    
        if ($response->successful()) {
            $data = $response->json();
    
            if (!isset($data['TokenId'])) {
                throw new \Exception('TokenId not found in response: ' . json_encode($data));
            }
    
            $token = $data['TokenId'];
            $expiresAt = now()->addHours(14);
    
            ApiToken::updateOrCreate(
                ['id' => 1],
                ['token' => $token, 'expires_at' => $expiresAt]
            );
    
            return $token;
        }
    
        throw new \Exception('Authentication failed: ' . $response->body());
    }
    

    public function getToken()
    {
        $tokenData = ApiToken::first();

        if ($tokenData && now()->lessThan($tokenData->expires_at)) {
            return $tokenData->token;
        }

        return $this->authenticate();
    }

}
