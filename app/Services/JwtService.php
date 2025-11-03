<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class JwtService
{
    private string $privateKey;
    private string $publicKey;
    private string $algorithm = 'RS256';

    public function __construct()
    {
        $this->privateKey = file_get_contents(storage_path('oauth-private.key'));
        $this->publicKey = file_get_contents(storage_path('oauth-public.key'));
    }

    /**
     * Génère un token JWT
     */
    public function generateToken(array $payload, int $expiresIn = 3600): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $expiresIn;

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'jti' => Str::uuid()->toString(),
        ]);

        return JWT::encode($tokenPayload, $this->privateKey, $this->algorithm);
    }

    /**
     * Décode et vérifie un token JWT
     */
    public function decodeToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->publicKey, $this->algorithm));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Vérifie si un token est valide
     */
    public function isValidToken(string $token): bool
    {
        $decoded = $this->decodeToken($token);
        return $decoded !== null && $decoded->exp > time();
    }

    /**
     * Génère un refresh token
     */
    public function generateRefreshToken(): string
    {
        return Str::random(64);
    }

    /**
     * Stocke un refresh token
     */
    public function storeRefreshToken(string $refreshToken, array $data, int $ttl = 2592000): void
    {
        Cache::put('refresh_token_' . $refreshToken, $data, $ttl);
    }

    /**
     * Récupère les données d'un refresh token
     */
    public function getRefreshTokenData(string $refreshToken): ?array
    {
        return Cache::get('refresh_token_' . $refreshToken);
    }

    /**
     * Supprime un refresh token
     */
    public function revokeRefreshToken(string $refreshToken): void
    {
        Cache::forget('refresh_token_' . $refreshToken);
    }

    /**
     * Génère un token d'accès et un refresh token
     */
    public function generateTokens(array $userData): array
    {
        $accessToken = $this->generateToken([
            'user_id' => $userData['id'],
            'email' => $userData['email'],
            'name' => $userData['name'],
            'role' => $userData['role'] ?? 'client',
            'type' => 'access',
        ], config('passport.tokens_expire_in', 3600));

        $refreshToken = $this->generateRefreshToken();

        $this->storeRefreshToken($refreshToken, [
            'user_id' => $userData['id'],
            'email' => $userData['email'],
            'expires_at' => now()->addSeconds(config('passport.refresh_tokens_expire_in', 2592000)),
        ], config('passport.refresh_tokens_expire_in', 2592000));

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => config('passport.tokens_expire_in', 3600),
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Rafraîchit les tokens
     */
    public function refreshTokens(string $refreshToken): ?array
    {
        $tokenData = $this->getRefreshTokenData($refreshToken);

        if (!$tokenData || now()->isAfter($tokenData['expires_at'])) {
            return null;
        }

        // Révoquer l'ancien refresh token
        $this->revokeRefreshToken($refreshToken);

        // Générer de nouveaux tokens
        return $this->generateTokens([
            'id' => $tokenData['user_id'],
            'email' => $tokenData['email'],
            'name' => $tokenData['name'] ?? '',
            'role' => $tokenData['role'] ?? 'client',
        ]);
    }
}