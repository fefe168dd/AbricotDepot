<?php
namespace abricotdepot\web\actions ;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeconnexionAction
{
    public function __invoke(Request $request, Response $response): Response
    {

        setcookie('access_token', '', time() - 3600, '/');
        setcookie('refresh_token', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');


        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }
}
