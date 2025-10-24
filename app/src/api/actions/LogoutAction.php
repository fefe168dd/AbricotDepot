<?php

namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action pour déconnecter un utilisateur
 */
class LogoutAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // Récupération du profil utilisateur depuis les attributs (ajouté par AuthnMiddleware)
            $userProfile = $request->getAttribute('userProfile');

            if (!$userProfile) {
                $payload = json_encode([
                    'error' => 'Utilisateur non authentifié'
                ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(401);
            }

            $payload = json_encode([
                'success' => true,
                'message' => 'Déconnexion réussie',
                'data' => [
                    'userId' => $userProfile->getId(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $payload = json_encode([
                'error' => 'Erreur lors de la déconnexion',
                'message' => $e->getMessage()
            ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
