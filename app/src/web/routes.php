<?php
declare(strict_types=1);

use abricotdepot\web\actions\HomeAction;
use abricotdepot\web\actions\ConnexionAction;
use abricotdepot\web\actions\DetailProduitAction;

return function(\Slim\App $app):\Slim\App {



    $app->get('/', HomeAction::class);
    $app->get('/connexion', ConnexionAction::class);
    $app->get('/inscription', \abricotdepot\web\actions\InscriptionAction::class);
    $app->get('/{id}', DetailProduitAction::class);

    return $app;
};