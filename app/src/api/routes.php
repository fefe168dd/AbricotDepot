<?php
declare(strict_types=1);


return function(\Slim\App $app):\Slim\App {


    $app->post('/reservations', \abricotdepot\api\actions\AddReservationAction::class);
    $app->get('/reservations', \abricotdepot\api\actions\GetReservationAction::class);
    $app->get('/reservation/{id}' , \abricotdepot\api\actions\GetReservationByIDAction::class);
    $app->put('/reservations/{id}/cancel', \abricotdepot\api\actions\CancelReservationAction::class);
    $app->put('/reservations/{id}/complete', \abricotdepot\api\actions\CompleteReservationAction::class);
    $app->get('/reservations/{id}/{date_debut}/{date_fin}', \abricotdepot\api\actions\GetReservationByOutilAndDatesAction::class);
    $app->get('/outils', \abricotdepot\api\actions\GetOutilAction::class);
    $app->get('/outils/{id}', \abricotdepot\api\actions\GetOutilbyidAction::class);
    $app->get('/categories/{categorieName}/outils' , \abricotdepot\api\actions\GetOutilByCategorie::class);
    $app->get('/stocks', \abricotdepot\api\actions\GetStockAction::class);
    $app->get('/stocks/{id}', \abricotdepot\api\actions\GetStockByIdAction::class);
    $app->get('/outils/{id}/stocks' , \abricotdepot\api\actions\GetStockByIDOutilAction::class);
    $app->post('/auth/signup', \abricotdepot\api\actions\SignUpAction::class);
    $app->post('/auth/signin', \abricotdepot\api\actions\SignInAction::class);
    $app->post('/auth/authenticate',  \abricotdepot\api\actions\AuthentificationUserAction::class);
    $app->post('/auth/refresh' , \abricotdepot\api\actions\RefreshTokenAction::class);
    $app->post('/auth/logout', \abricotdepot\api\actions\LogoutAction::class);
    $app->post('/panier/ajoutProduit', \abricotdepot\api\actions\AddToPanierAction::class)
    ->add(\abricotdepot\api\middlewares\AuthnMiddleware::class);
        

    return $app;
};