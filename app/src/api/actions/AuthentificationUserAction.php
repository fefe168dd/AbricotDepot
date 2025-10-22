<?php



namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use abricotdepot\core\domain\entities\auth\AuthServiceInterface;
use abricotdepot\core\domain\exceptions\AuthenticationException;
/**
 * Action pour authentifier un utilisateur
 */
class AuthenticateUserAction
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            
            // Validation des données d'entrée
            if (!isset($data['email']) || !isset($data['password'])) {
                $payload = json_encode(['error' => 'Email et mot de passe requis']);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            // Authentification
            $userProfile = $this->authService->authenticateEmail($data['email'], $data['password']);

            // Retour du profil utilisateur (sans le mot de passe)
            $payload = json_encode([
                'success' => true,
                'user' => $userProfile->toArray(),
                'message' => 'Authentification réussie'
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

        } catch (AuthenticationException $e) {
            $payload = json_encode(['error' => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
                
        } catch (\Exception $e) {
            $payload = json_encode(['error' => 'Erreur interne du serveur']);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}