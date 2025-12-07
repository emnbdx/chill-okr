<?php

namespace App\Controllers;

use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LandingController
{
  private AuthService $authService;

  public function __construct($container = null)
  {
    $container = $container ?? app()->getContainer();
    $this->authService = $container->get('authService');
  }

  public function index(Request $req, Response $res): Response
  {
    if ($this->authService->isAuthenticated()) {
      return $res->withHeader('Location', '/dashboard')->withStatus(302);
    }

    ob_start();
    require __DIR__ . '/../Views/landing.php';
    $html = ob_get_clean();

    $res->getBody()->write($html);
    return $res;
  }
}

