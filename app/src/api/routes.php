<?php
declare(strict_types=1);


return function(\Slim\App $app):\Slim\App {


    $app->post('/reservations', \abricotdepot\api\actions\AddReservationAction::class);
    $app->get('/reservations', \abricotdepot\api\actions\GetReservationAction::class);
    $app->get('/reservation/{id}' , \abricotdepot\api\actions\GetReservationByIDAction::class) ;
    $app->get('/outils', \abricotdepot\api\actions\GetOutilbyidAction::class);
    $app->get('/outils/{id}', \abricotdepot\api\actions\GetOutilbyidAction::class);
    $app->get('/categories/{categorieName}/outils' , \abricotdepot\api\actions\GetOutilByCategorie::class);
    $app->get('/stocks', \abricotdepot\api\actions\GetStockAction::class);
    $app->get('/stocks/{id}', \abricotdepot\api\actions\GetStockByIdAction::class);
    $app->get('/outils/{id}/stocks' , \abricotdepot\api\actions\GetStockByIdOutilAction::class);
  

    return $app;
};