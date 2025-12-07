<?php

use Slim\Factory\AppFactory;
use App\Repositories\UserRepository;
use App\Repositories\CompanyRepository;
use App\Services\MailService;
use App\Services\AuthService;
use DI\Container;

require __DIR__ . "/../vendor/autoload.php";

$config = require __DIR__ . "/../config/config.php";

require __DIR__ . "/../core/db.php";

$container = new Container();

$container->set('pdo', $pdo);
$container->set('config', $config);

$container->set('userRepository', function () use ($pdo) {
  return new UserRepository($pdo);
});

$container->set('companyRepository', function () use ($pdo) {
  return new CompanyRepository($pdo);
});

$container->set('mailService', function () use ($config) {
  return new MailService($config);
});

$container->set('authService', function () use ($container, $config) {
  return new AuthService(
    $container->get('userRepository'),
    $container->get('companyRepository'),
    $container->get('mailService'),
    $config
  );
});

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

return $app;
