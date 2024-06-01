<?php

namespace Syntech\Syntechfcm;

use Illuminate\Support\Facades\Http;
use Exception;

class FcmService
{
    protected $client;
    protected $projectId;
    protected $accessToken;

    public function __construct()
    {
        $this->projectId = config('syntechfcm.project_id');
        $this->accessToken = $this->getAccessToken();
    }

    protected function getAccessToken()
    {
        $credentialsPath = config('syntechfcm.credentials');
        $credentials = json_decode(file_get_contents($credentialsPath), true);

        $jwt = $this->createJwt($credentials);

        return $this->exchangeJwtForAccessToken($jwt);
    }

    protected function createJwt($credentials)
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $now = time();
        $expiry = $now + 3600;

        $payload = [
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $expiry,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = '';
        openssl_sign(
            $base64UrlHeader . "." . $base64UrlPayload,
            $signature,
            $credentials['private_key'],
            'sha256'
        );
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    protected function exchangeJwtForAccessToken($jwt)
    {
        $response = $this->makeHttpRequest('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            return $data['access_token'];
        }

        throw new Exception('Failed to obtain access token: ' . $response);
    }

    protected function makeHttpRequest($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return $response;
    }

    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    public function send($data)
    {

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $notification = array_merge([
            'title' => '',
            'body' => '',
            'image' => ''
        ], $data['notification']);

        $message = [
            "message" => [
                "token"        => $data['to'],
                "notification" => $notification,
            ],
        ];

        $response = Http::withToken($this->accessToken)->post($url, $message);

        return $response->json();
    }

    public function sendNotification($title, $body, $token, $image = null, $clickAction = null, $icon = null, $sound = null)
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $message = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title"        => $title,
                    "body"         => $body,
                    "image"        => $image,
                ],
            ],
        ];

        $response = Http::withToken($this->accessToken)->post($url, $message);

        return $response->json();
    }
}
