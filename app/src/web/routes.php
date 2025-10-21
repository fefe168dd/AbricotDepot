<?php
declare(strict_types=1);

use abricotdepot\web\actions\HomeAction;
use abricotdepot\web\actions\ConnexionAction;

return function(\Slim\App $app):\Slim\App {



    $app->get('/', HomeAction::class);

    $app->get('/connexion', ConnexionAction::class);


    return $app;
};