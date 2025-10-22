<?php

use abricotdepot\api\actions\GetOutilAction;
use abricotdepot\api\actions\GetOutilbyidAction;
use abricotdepot\api\actions\GetReservationAction;
use abricotdepot\api\actions\GetReservationByIDAction;
use abricotdepot\api\actions\AddReservationAction;
use abricotdepot\api\actions\CancelReservationAction;
use abricotdepot\api\actions\CompleteReservationAction;
use abricotdepot\api\actions\GetStockAction;
use abricotdepot\api\actions\GetStockByIdAction;
use abricotdepot\api\actions\GetStockByIDOutilAction;
use abricotdepot\api\actions\GetOutilByCategorie;
use abricotdepot\core\application\usecases\ServiceOutil;
use abricotdepot\core\application\usecases\ServiceReservation;
use abricotdepot\core\application\usecases\ServiceStock;
use abricotdepot\infra\repository\PDOStockRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\StockRepository;
use abricotdepot\infra\repository\PDOReservationRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;
use abricotdepot\infra\repository\PDOOutilRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\OutilRepository;
use abricotdepot\web\actions\PanierAction;
use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use abricotdepot\infra\repository\PDOPanierRepository;

use Psr\Container\ContainerInterface;

return [

    'displayErrorDetails' => true,
    'logs.dir' => __DIR__ . '/../../var/logs',
     'env.config' => __DIR__ . '/.env.dist',

     GetOutilAction::class => function (ContainerInterface $container) {
         return new GetOutilAction($container->get(ServiceOutil::class));
     },
     GetOutilbyidAction::class => function (ContainerInterface $container) {
         return new GetOutilbyidAction($container->get(ServiceOutil::class));
     },
     GetOutilByCategorie::class => function (ContainerInterface $container) {
         return new GetOutilByCategorie($container->get(ServiceOutil::class));
     },
     GetReservationAction::class => function (ContainerInterface $container) {
         return new GetReservationAction($container->get(ServiceReservation::class));
     },
     GetReservationByIDAction::class => function (ContainerInterface $container) {
         return new GetReservationByIDAction($container->get(ServiceReservation::class));
     },
     AddReservationAction::class => function (ContainerInterface $container) {
         return new AddReservationAction($container->get(ServiceReservation::class));
     },
     CancelReservationAction::class => function (ContainerInterface $container) {
         return new CancelReservationAction($container->get(ServiceReservation::class));
     },
     CompleteReservationAction::class => function (ContainerInterface $container) {
         return new CompleteReservationAction($container->get(ServiceReservation::class));
     },
     GetStockAction::class => function (ContainerInterface $container) {
         return new GetStockAction($container->get(ServiceStock::class));
     },
     GetStockByIdAction::class => function (ContainerInterface $container) {
         return new GetStockByIdAction($container->get(ServiceStock::class));
     },
     GetStockByIDOutilAction::class => function (ContainerInterface $container) {
         return new GetStockByIDOutilAction($container->get(ServiceStock::class));
     },

     //infra 
     'outil.pdo' => function (ContainerInterface $container) {
        $config = parse_ini_file($container->get('env.config'));
        $dsn = "{$config['outil.driver']}:host={$config['outil.host']};dbname={$config['outil.database']}";
        $user = $config['outil.username'];
        $password = $config['outil.password'];
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
     },
     'reservation.pdo' => function (ContainerInterface $container) {
        $config = parse_ini_file($container->get('env.config'));
        $dsn = "{$config['reservation.driver']}:host={$config['reservation.host']};dbname={$config['reservation.database']}";
        $user = $config['reservation.username'];
        $password = $config['reservation.password'];
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
     },
     'stock.pdo' => function (ContainerInterface $container) {
        $config = parse_ini_file($container->get('env.config'));
        $dsn = "{$config['stock.driver']}:host={$config['stock.host']};dbname={$config['stock.database']}";
        $user = $config['stock.username'];
        $password = $config['stock.password'];
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
     },
'panier.pdo' => function (ContainerInterface $container) {
    $config = parse_ini_file($container->get('env.config'));
    $dsn = "{$config['panier.driver']}:host={$config['panier.host']};dbname={$config['panier.database']}";
    $user = $config['panier.username'];
    $password = $config['panier.password'];
    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
},

PanierRepository::class => function (ContainerInterface $c) {
    // récupérer les deux PDO du container
    $pdoPanier = $c->get('panier.pdo');
    $pdoOutil  = $c->get('outil.pdo');

    // injecter les deux PDO dans le repository
    return new PDOPanierRepository($pdoPanier, $pdoOutil);
},

// Bind PanierAction avec injection du repository
PanierAction::class => function (ContainerInterface $container) {
    return new PanierAction($container->get(PanierRepository::class));
},

    // Bind StockRepository interface to PDOStockRepository
    StockRepository::class => function (ContainerInterface $container) {
        return new PDOStockRepository($container->get('stock.pdo'));
    },
    ReservationRepository::class => function (ContainerInterface $container) {
        return new PDOReservationRepository($container->get('reservation.pdo'));
    },
    OutilRepository::class => function (ContainerInterface $container) {
        return new PDOOutilRepository($container->get('outil.pdo'));
    },

    // Services
    ServiceReservation::class => function (ContainerInterface $container) {
        return new ServiceReservation(
            $container->get(ReservationRepository::class),
            $container->get(StockRepository::class)
        );
    },

];