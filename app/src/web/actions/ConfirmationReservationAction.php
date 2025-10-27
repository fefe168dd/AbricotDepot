<?php

namespace abricotdepot\web\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConfirmationReservationAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write('Votre réservation a bien été enregistrée ✅');
        return $response->withStatus(200);
    }
}