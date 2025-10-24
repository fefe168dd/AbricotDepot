<?php

namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use abricotdepot\core\application\usecases\CreateUserUseCase;
use abricotdepot\core\domain\exceptions\AuthenticationException;

class SignUpAction
{
    private CreateUserUseCase $createUserUseCase;

    public function __construct(CreateUserUseCase $createUserUseCase)
    {
        $this->createUserUseCase = $createUserUseCase;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            // Validation basique des données
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

            // Vérifier les types
            if (!is_string($data['username']) || !is_string($data['email']) || !is_string($data['password'])) {
                $payload = json_encode([
                    'error' => 'Tous les champs doivent être des chaînes de caractères'
                ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            // Vérifier la confirmation du mot de passe si fournie
            if (isset($data['password_confirm']) && $data['password'] !== $data['password_confirm']) {
                $payload = json_encode([
                    'error' => 'Les mots de passe ne correspondent pas'
                ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            // Créer l'utilisateur via le use case
            $userProfile = $this->createUserUseCase->execute(
                trim($data['username']),
                trim($data['email']),
                $data['password']
            );

            $payload = json_encode([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => [
                    'id' => $userProfile->getId(),
                    'username' => $userProfile->getName(),
                    'email' => $userProfile->getEmail(),
                    'role' => $userProfile->getRole()
                ]
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (AuthenticationException $e) {
            $payload = json_encode([
                'error' => $e->getMessage(),
                'code' => 'REGISTRATION_FAILED'
            ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (\Exception $e) {
            $payload = json_encode([
                'error' => 'Erreur interne du serveur',
                'message' => $e->getMessage(),
                'code' => 'INTERNAL_ERROR'
            ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }

    /**
     * Valide les données d'entrée basiques
     */
    private function validateInput(?array $data): array
    {
        $errors = [];

        if ($data === null) {
            return ['Les données du corps de la requête sont manquantes'];
        }

        if (!isset($data['username'])) {
            $errors[] = 'Le champ username est requis';
        }

        if (!isset($data['email'])) {
            $errors[] = 'Le champ email est requis';
        }

        if (!isset($data['password'])) {
            $errors[] = 'Le champ password est requis';
        }

        return $errors;
    }
}
