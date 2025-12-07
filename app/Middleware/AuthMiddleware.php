<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware implements MiddlewareInterface
{
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    if (!isset($_SESSION['user_id'])) {
      $response = new Response();

      $path = $request->getUri()->getPath();
      if (strpos($path, '/api/') === 0) {
        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(401);
      }

      return $response
        ->withHeader('Location', '/')
        ->withStatus(302);
    }

    return $handler->handle($request);
  }
}
