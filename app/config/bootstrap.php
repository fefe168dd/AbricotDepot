<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use toubilib\api\middlewares\Cors;



$app = AppFactory::create();


$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    'displayErrorDetails' => true,
]);

$c = $containerBuilder->build();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware($c->get('displayErrorDetails'), false, false)
    ->getDefaultErrorHandler()
    ->forceContentType('application/json')
;

$app = (require_once __DIR__ . '/../src/api/routes.php')($app);
$app = (require_once __DIR__ . '/../src/web/routes.php')($app);


return $app;