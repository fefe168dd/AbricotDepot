<?php

namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $file = __DIR__ . '/../../../public/html/acceuil.html';


        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }

        $html = file_get_contents($file);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
