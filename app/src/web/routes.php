<?php
declare(strict_types=1);

use abricotdepot\web\actions\HomeAction;
use abricotdepot\web\actions\ConnexionAction;
use abricotdepot\web\actions\DetailProduitAction;
use abricotdepot\web\actions\PanierAction;
use abricotdepot\web\actions\PostConnexionAction;
use abricotdepot\web\actions\ProfileAction;

return function(\Slim\App $app):\Slim\App {


    $app->get('/panier', PanierAction::class);
    $app->get('/', HomeAction::class);
    $app->get('/connexion', ConnexionAction::class);
    $app->post('/connexion', PostConnexionAction::class);
    $app->get('/inscription', \abricotdepot\web\actions\InscriptionAction::class);
    $app->post('/inscription', \abricotdepot\web\actions\PostInscriptionAction::class);
    $app->get('/{id}', DetailProduitAction::class); 

    return $app;
};