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
use abricotdepot\api\actions\GetStockByIdOutilAction;
use abricotdepot\api\actions\GetOutilByCategorie;
use abricotdepot\api\actions\RefreshTokenAction;
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
use abricotdepot\core\domain\entities\auth\AuthServiceInterface; 
use abricotdepot\api\provider\AuthProviderInterface;
use abricotdepot\core\application\usecases\AuthService;
use abricotdepot\core\application\ports\spi\repositoryInterface\UserRepositoryInterface;
use abricotdepot\api\provider\JwtAuthProvider;
use abricotdepot\infra\repository\RdvRepository;
use abricotdepot\infra\repository\PraticienRepository;
use abricotdepot\api\middlewares\AuthnMiddleware;
use abricotdepot\api\middlewares\AuthzMiddleware;
use abricotdepot\core\domain\entities\auth\AuthzServiceInterface;
use abricotdepot\core\application\usecases\AuthzService;
use abricotdepot\api\actions\AuthentificationUserAction;
use abricotdepot\api\actions\SignInAction;
use abricotdepot\api\actions\SignUpAction;
use abricotdepot\core\application\usecases\CreateUserUseCase;
use Psr\Container\ContainerInterface;
use abricotdepot\infra\repository\PDOUserRepository;

return [

    'displayErrorDetails' => true,
    'logs.dir' => __DIR__ . '/../../var/logs',
     'env.config' => __DIR__ . '/.env.dist',
     'env.set'=> __DIR__ . '/env.config',

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
     GetStockByIdOutilAction::class => function (ContainerInterface $container) {
         return new GetStockByIdOutilAction($container->get(ServiceStock::class));
     },
     AuthentificationUserAction::class => function (ContainerInterface $container) {
         return new AuthentificationUserAction(
             $container->get(AuthServiceInterface::class),
         );
     },
     SignInAction::class => function (ContainerInterface $container) {
         return new SignInAction(
             $container->get(AuthProviderInterface::class),
         );
     },
     SignUpAction::class => function (ContainerInterface $container) {
         return new SignUpAction(
             $container->get(CreateUserUseCase::class)
         );
     },
        RefreshTokenAction::class => function (ContainerInterface $container) {
        return new RefreshTokenAction(
            $container->get(AuthProviderInterface::class),
        );
    },
    CreateUserUseCase::class => function (ContainerInterface $container) {
        return new CreateUserUseCase(
            $container->get(UserRepositoryInterface::class)
        );
    },
    AuthServiceInterface::class => function (ContainerInterface $container) {
        return new AuthService(
            $container->get(UserRepositoryInterface::class)
        );
    },
     AuthProviderInterface::class => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get('env.set'));
        $secret = $config['auth.jwt.key'] ?? getenv('AUTH_JWT_KEY') ?? null;
        if (!$secret) {
            throw new \RuntimeException('JWT secret not configured. Add auth.jwt.key to your env file or set AUTH_JWT_KEY.');
        }

        return new JwtAuthProvider(
            $c->get(AuthServiceInterface::class),
            $secret,
            'HS256',
            3600,
            86400
        );
    },
    AuthnMiddleware::class => function (ContainerInterface $container) {
        return new AuthnMiddleware(
            $container->get(AuthProviderInterface::class)
        );
    },
    AuthzServiceInterface::class => function (ContainerInterface $container) {
        return new AuthzService();
    },
    AuthzMiddleware::class => function (ContainerInterface $container) {
        return new AuthzMiddleware(
            $container->get(AuthzServiceInterface::class)
        );
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
 'auth.pdo' => function (ContainerInterface $container) {
     $config = parse_ini_file($container->get('env.config'));
     $dsn = "{$config['auth.driver']}:host={$config['auth.host']};dbname={$config['auth.database']}";
     $user = $config['auth.username'];
     $password = $config['auth.password'];
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
    UserRepositoryInterface::class => function (ContainerInterface $container) {
        return new PDOUserRepository($container->get('auth.pdo'));
    },

    // Services
    ServiceOutil::class => function (ContainerInterface $container) {
        return new ServiceOutil($container->get(OutilRepository::class));
    },
    ServiceStock::class => function (ContainerInterface $container) {
        return new ServiceStock($container->get(StockRepository::class));
    },
    ServiceReservation::class => function (ContainerInterface $container) {
        return new ServiceReservation(
            $container->get(ReservationRepository::class),
            $container->get(StockRepository::class)
        );
    },

];