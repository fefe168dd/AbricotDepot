<?php
declare(strict_types=1);

use abricotdepot\api\actions\HomeAction;

return function( \Slim\App $app):\Slim\App {



    $app->get('/', HomeAction::class);

  

    return $app;
};