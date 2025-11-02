<?php

namespace abricotdepot\web\helpers;

use Psr\Http\Message\ServerRequestInterface as Request;

class TokenHelper
{
    private const API_URL = 'http://apicot:80';
    
    /**
     * Vérifie si le token est valide et le rafraîchit automatiquement si nécessaire
     * 
     * @param Request $request La requête HTTP contenant les cookies
     * @return array|null Retourne un tableau avec les tokens mis à jour ou null si échec
     */
    public static function validateAndRefreshToken(Request $request): ?array
    {
        $cookies = $request->getCookieParams();
        $accessToken = $cookies['access_token'] ?? null;
        $refreshToken = $cookies['refresh_token'] ?? null;
        $userId = $cookies['user_id'] ?? null;

        // Si pas de token, l'utilisateur n'est pas connecté
        if (!$accessToken || !$refreshToken) {
            return null;
        }

        // Vérifier si le token est valide en faisant une requête test
        if (self::isTokenValid($accessToken)) {
            return [
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken,
                'userId' => $userId,
                'refreshed' => false
            ];
        }

        // Si le token n'est pas valide, tenter de le rafraîchir
        return self::refreshToken($refreshToken, $userId);
    }

    /**
     * Vérifie si le token d'accès est toujours valide
     * 
     * @param string $accessToken Le token à vérifier
     * @return bool True si le token est valide, false sinon
     */
    private static function isTokenValid(string $accessToken): bool
    {
        // On peut vérifier en décodant le JWT localement ou en faisant un appel API
        // Pour simplifier, on décode le JWT et on vérifie l'expiration
        try {
            $parts = explode('.', $accessToken);
            if (count($parts) !== 3) {
                return false;
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            
            if (!$payload || !isset($payload['exp'])) {
                return false;
            }

            // Vérifier si le token n'est pas expiré (avec une marge de 60 secondes)
            return $payload['exp'] > (time() + 60);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Rafraîchit le token d'accès en utilisant le refresh token
     * 
     * @param string $refreshToken Le refresh token
     * @param string|null $userId L'ID de l'utilisateur
     * @return array|null Les nouveaux tokens ou null si échec
     */
    private static function refreshToken(string $refreshToken, ?string $userId): ?array
    {
        $apiUrl = self::API_URL . '/auth/refresh';

        $data = json_encode([
            'refreshToken' => $refreshToken
        ]);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        $result = json_decode($response, true);

        if (!$result || !isset($result['success']) || !$result['success']) {
            return null;
        }

        $newTokens = $result['data'] ?? null;

        if (!$newTokens) {
            return null;
        }

        return [
            'accessToken' => $newTokens['accessToken'],
            'refreshToken' => $newTokens['refreshToken'],
            'userId' => $userId ?? $newTokens['user']['id'] ?? null,
            'refreshed' => true
        ];
    }

    /**
     * Met à jour les cookies avec les nouveaux tokens
     * 
     * @param array $tokens Les tokens à définir dans les cookies
     * @param int $expires Durée de vie des cookies (par défaut 7 jours)
     */
    public static function updateCookies(array $tokens, int $expires = 604800): void
    {
        $cookieOptions = [
            'expires' => time() + $expires,
            'path' => '/',
            'secure' => false, // Mettre à true si HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        if (isset($tokens['accessToken'])) {
            setcookie('access_token', $tokens['accessToken'], $cookieOptions);
        }

        if (isset($tokens['refreshToken'])) {
            setcookie('refresh_token', $tokens['refreshToken'], $cookieOptions);
        }

        if (isset($tokens['userId'])) {
            setcookie('user_id', $tokens['userId'], $cookieOptions);
        }
    }

    /**
     * Vérifie si l'utilisateur est authentifié et rafraîchit le token si nécessaire
     * 
     * @param Request $request La requête HTTP
     * @return bool True si l'utilisateur est authentifié, false sinon
     */
    public static function ensureAuthenticated(Request $request): bool
    {
        $tokens = self::validateAndRefreshToken($request);

        if ($tokens === null) {
            return false;
        }

        // Si le token a été rafraîchi, mettre à jour les cookies
        if ($tokens['refreshed']) {
            self::updateCookies($tokens);
        }

        return true;
    }

    /**
     * Récupère l'ID utilisateur depuis les cookies après validation du token
     * 
     * @param Request $request La requête HTTP
     * @return string|null L'ID de l'utilisateur ou null si non authentifié
     */
    public static function getUserId(Request $request): ?string
    {
        $tokens = self::validateAndRefreshToken($request);

        if ($tokens === null) {
            return null;
        }

        // Si le token a été rafraîchi, mettre à jour les cookies
        if ($tokens['refreshed']) {
            self::updateCookies($tokens);
        }

        return $tokens['userId'] ?? null;
    }
}
