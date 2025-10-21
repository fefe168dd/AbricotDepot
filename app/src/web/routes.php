<?php
declare(strict_types=1);

use abricotdepot\web\actions\HomeAction;

return function(\Slim\App $app):\Slim\App {



    $app->get('/', HomeAction::class);

  

    return $app;
};