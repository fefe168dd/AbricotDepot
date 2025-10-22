<?php 
namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use abricotdepot\api\provider\AuthProviderInterface;
use abricotdepot\core\domain\exceptions\AuthenticationException;

class SignInAction
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            
            $validationErrors = $this->validateInput($data);
            if (!empty($validationErrors)) {
                $payload = json_encode([
                    'error' => 'Données invalides',
                    'details' => $validationErrors
                ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            if (!is_string($data['email']) || !is_string($data['password'])) {
                $payload = json_encode([
                    'error' => 'Email et mot de passe doivent être des chaînes de caractères'
                ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $payload = json_encode([
                    'error' => 'Format d\'email invalide'
                ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            $authToken = $this->authProvider->signin(
                trim($data['email']), 
                $data['password']
            );

            $payload = json_encode([
                'success' => true,
                'message' => 'Authentification réussie',
                'data' => $authToken->toArray()
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

        } catch (AuthenticationException $e) {
            $payload = json_encode([
                'error' => $e->getMessage(),
                'code' => 'AUTHENTICATION_FAILED'
            ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
                
        } catch (\Exception $e) {
            $payload = json_encode([
                'error' => 'Erreur interne du serveur',
                'code' => 'INTERNAL_ERROR'
            ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }

    /**
     * Valide les données d'entrée
     */
    private function validateInput(?array $data): array
    {
        $errors = [];

        if ($data === null) {
            $errors[] = 'Aucune donnée fournie';
            return $errors;
        }

        if (!isset($data['email']) || empty($data['email'])) {
            $errors[] = 'Email requis';
        }

        if (!isset($data['password']) || empty($data['password'])) {
            $errors[] = 'Mot de passe requis';
        }

        if (isset($data['password']) && strlen($data['password']) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
        }

        return $errors;
    }
}