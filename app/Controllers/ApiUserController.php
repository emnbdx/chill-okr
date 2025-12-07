<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApiUserController
{
  private UserRepository $repo;
  private AuthService $authService;

  public function __construct($container = null)
  {
    $container = $container ?? app()->getContainer();
    if ($container && $container->has("pdo")) {
      $pdo = $container->get("pdo");
    } else {
      require __DIR__ . "/../../core/db.php";
    }
    $this->repo = new UserRepository($pdo);
    $this->authService = $container->get('authService');
  }

  public function list(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $users = $this->repo->allByCompany($currentUser['company_id']);

    foreach ($users as &$user) {
      unset($user['password']);
      unset($user['reset_token']);
      unset($user['reset_token_expires']);
    }

    return json($res, ["users" => $users]);
  }

  public function create(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $body = $req->getBody()->getContents();
    $b = json_decode($body, true) ?: [];
    $firstName = trim($b["first_name"] ?? "");
    $lastName = trim($b["last_name"] ?? "");
    if (empty($firstName) || empty($lastName)) {
      return json($res, ["error" => "First name and last name are required"], 400);
    }
    $id = $this->repo->createIfNotExists($firstName, $lastName, $currentUser['company_id']);
    return json($res, ["id" => $id, "first_name" => $firstName, "last_name" => $lastName], 201);
  }
}
