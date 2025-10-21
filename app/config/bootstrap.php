<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use abricotdepot\api\middlewares\Cors;





$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/settings.php');
$containerBuilder->addDefinitions([
    'displayErrorDetails' => true,
]);

$c = $containerBuilder->build();
$app = AppFactory::createFromContainer($c);

$app->addBodyParsingMiddleware();
$app->add(Cors::class);
$app->addRoutingMiddleware();
$app->addErrorMiddleware($c->get('displayErrorDetails'), false, false)
    ->getDefaultErrorHandler()
    ->forceContentType('application/json')
;

$app = (require_once __DIR__ . '/../src/api/routes.php')($app);
$app = (require_once __DIR__ . '/../src/web/routes.php')($app);
$routeParser = $app->getRouteCollector()->getRouteParser();



return $app;