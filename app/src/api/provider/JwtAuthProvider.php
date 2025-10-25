<?php

namespace abricotdepot\api\provider;

use abricotdepot\core\domain\entities\auth\AuthServiceInterface;
use abricotdepot\core\domain\entities\auth\AuthTokenDTO;
use abricotdepot\core\application\ports\spi\repositoryInterface\UserRepositoryInterface;
use abricotdepot\core\domain\entities\auth\UserProfile;
use abricotdepot\core\domain\exceptions\AuthenticationException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuthProvider implements AuthProviderInterface
{
    private AuthServiceInterface $authService;
    private string $jwtSecret;
    private string $jwtAlgorithm;
    private int $accessTokenExpiry;
    private int $refreshTokenExpiry;

    public function __construct(
        AuthServiceInterface $authService,
        string $jwtSecret = 'your-secret-key',
        string $jwtAlgorithm = 'HS256',
        int $accessTokenExpiry = 900,
        int $refreshTokenExpiry = 604800
    ) {
        $this->authService = $authService;
        $this->jwtSecret = $jwtSecret;
        $this->jwtAlgorithm = $jwtAlgorithm;
        $this->accessTokenExpiry = $accessTokenExpiry;
        $this->refreshTokenExpiry = $refreshTokenExpiry;
    }

        public function signin(string $email, string $password): AuthTokenDTO
    {
        $userProfile = $this->authService->authenticateEmail($email, $password);

        $accessToken = $this->generateAccessToken($userProfile);
        $refreshToken = $this->generateRefreshToken($userProfile);

        return new AuthTokenDTO(
            $userProfile,
            $accessToken,
            $refreshToken,
            $this->accessTokenExpiry
        );
    }


    public function refresh(string $refreshToken): AuthTokenDTO
    {
        try {
            $decoded = JWT::decode($refreshToken, new Key($this->jwtSecret, $this->jwtAlgorithm));
            
            if ($decoded->type !== 'refresh') {
                throw new AuthenticationException('Token de rafraîchissement invalide');
            }

            $userProfile = new UserProfile(
                $decoded->sub,
                $decoded->email,
                $decoded->role
            );

            $newAccessToken = $this->generateAccessToken($userProfile);
            $newRefreshToken = $this->generateRefreshToken($userProfile);

            return new AuthTokenDTO(
                $userProfile,
                $newAccessToken,
                $newRefreshToken,
                $this->accessTokenExpiry
            );

        } catch (\Exception $e) {
            throw new AuthenticationException('Token de rafraîchissement invalide ou expiré');
        }
    }


    public function validateToken(string $accessToken): AuthTokenDTO
    {
        try {
            $decoded = JWT::decode($accessToken, new Key($this->jwtSecret, $this->jwtAlgorithm));
            
            if ($decoded->type !== 'access') {
                throw new AuthenticationException('Token d\'accès invalide');
            }

            $userProfile = new UserProfile(
                $decoded->sub,
                $decoded->email,
                $decoded->role
            );

            return new AuthTokenDTO(
                $userProfile,
                $accessToken,
                '',
                $this->accessTokenExpiry
            );

        } catch (\Exception $e) {
            throw new AuthenticationException('Token d\'accès invalide ou expiré');
        }
    }
    private function generateAccessToken($userProfile): string
    {
        $payload = [
            'sub' => $userProfile->getId(),
            'email' => $userProfile->getEmail(),
            'role' => $userProfile->getRole(),
            'type' => 'access',
            'exp' => time() + $this->accessTokenExpiry,
        ];
        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);
    }
    private function generateRefreshToken($userProfile): string
    {
        $payload = [
            'sub' => $userProfile->getId(),
            'email' => $userProfile->getEmail(),
            'role' => $userProfile->getRole(),
            'type' => 'refresh',
            'exp' => time() + $this->refreshTokenExpiry,
        ];
        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);
    }
}

