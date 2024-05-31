<?php

namespace  Syntech\SyntechFcm;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;

class FCMService
{
    protected $client;
    protected $projectId;
    protected $accessToken;

    public function __construct()
    {
        $this->projectId = config('fcm.project_id');
        $this->client = new GoogleClient();
        $this->client->setAuthConfig(config('fcm.credentials'));
        $this->client->addScope('https://www.googleapis.com/auth/cloud-platform');
        $this->accessToken = $this->getAccessToken();
    }

    protected function getAccessToken()
    {
        $token = $this->client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }

    public function sendNotification($title, $body, $token)
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $message = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
            ],
        ];

        $response = Http::withToken($this->accessToken)
            ->post($url, $message);

        return $response->json();
    }
}