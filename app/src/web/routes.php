<?php

declare(strict_types=1);

use abricotdepot\web\actions\DeconnexionAction;
use abricotdepot\web\actions\HomeAction;
use abricotdepot\web\actions\ConnexionAction;
use abricotdepot\web\actions\DetailProduitAction;
use abricotdepot\web\actions\PanierAction;
use abricotdepot\web\actions\PostConnexionAction;
use abricotdepot\web\actions\GetProfileAction;
use abricotdepot\api\middlewares\AuthnMiddleware;
use abricotdepot\web\actions\AddToPanierAction;
use abricotdepot\web\actions\PanierAddAction;
use abricotdepot\web\actions\PanierRemoveAction;
use abricotdepot\web\actions\ConfirmationReservationAction;
use abricotdepot\web\actions\PostInscriptionAction;

return function (\Slim\App $app): \Slim\App {

    $app->get('/reservation/confirmation', ConfirmationReservationAction::class);
    $app->get('/panier', PanierAction::class);
    $app->get('/', HomeAction::class);
    $app->get('/connexion', ConnexionAction::class);
    $app->post('/connexion', PostConnexionAction::class);
    $app->get('/inscription', \abricotdepot\web\actions\InscriptionAction::class);
    $app->post('/inscription', PostInscriptionAction::class);
    $app->get('/deconnexion', DeconnexionAction::class);
    $app->get('/profile', GetProfileAction::class);
    $app->post('/{id}/ajouterPanier', AddToPanierAction::class);
    $app->post('/panier/add/{outil_id}/{datedebut}/{datefin}', \abricotdepot\web\actions\AddPanierAction::class);
    $app->post('/panier/remove/{outil_id}/{datedebut}/{datefin}', \abricotdepot\web\actions\RemovePanierAction::class);
    $app->post('/panier/reserver', \abricotdepot\web\actions\ReserverPanierAction::class);
    $app->get('/{id}', DetailProduitAction::class);




    return $app;
};
