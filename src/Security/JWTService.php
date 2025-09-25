<?php

namespace App\Security;

use App\Entity\Participant;

class JWTService
{
    private string $secret;
    private int $defaultTtl;

    public function __construct(string $secret = 'your-secret-key', int $defaultTtl = 86400)
    {
        $this->secret = $secret;
        $this->defaultTtl = $defaultTtl; // 24h par défaut
    }

    /**
     * Créer un JWT pour un participant
     */
    public function createToken(Participant $participant, int $ttl = null): string
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $now = time();

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload = [
            'pseudo' => $participant->getPseudo(),
            'roles' => $participant->getRoles(),
            'iat' => $now,
            'exp' => $now + $ttl
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Valider et décoder un JWT
     */
    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Vérifier la signature
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secret, true);
        $expectedSignature = $this->base64UrlEncode($signature);

        if (!hash_equals($expectedSignature, $signatureEncoded)) {
            return null;
        }

        // Décoder le payload
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        if (!$payload) {
            return null;
        }

        // Vérifier l'expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Extraire le pseudo du token
     */
    public function getPseudoFromToken(string $token): ?string
    {
        $payload = $this->validateToken($token);
        return $payload['pseudo'] ?? null;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}