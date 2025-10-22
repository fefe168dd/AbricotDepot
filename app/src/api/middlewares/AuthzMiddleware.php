<?php

namespace abricotdepot\api\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;
use abricotdepot\core\domain\exceptions\AuthorizationException;
use abricotdepot\core\domain\entities\auth\AuthzServiceInterface;
use abricotdepot\core\domain\entities\auth\UserProfile;

class AuthzMiddleware implements MiddlewareInterface
{
    private AuthzServiceInterface $authzService;
    public function __construct(AuthzServiceInterface $authzService)
    {
        $this->authzService = $authzService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $resource = $route->getPattern();
        $method = $request->getMethod();

        /** @var UserProfile $user */
        $user = $request->getAttribute('user');

        if (!$this->authzService->isAuthorized($user, $resource, $method)) {
            throw new AuthorizationException("User is not authorized to access this resource.");
        }

        return $handler->handle($request);
    }
private function checkAuthorization(
        ServerRequestInterface $request,
        UserProfile $userProfile, 
        ?string $routeName, 
        array $routeArguments
    ): void {
        // Implémentation de la logique d'autorisation basée sur le profil utilisateur,
        // le nom de la route et les arguments de la route.
        // Lancer une AuthorizationException si l'utilisateur n'est pas autorisé.        
    }
    private function createErrorResponse(string $message, int $statusCode): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message
        ]));
        return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
    }
}
