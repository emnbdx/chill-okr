<?php

namespace App\Controllers;

use App\Repositories\TeamRepository;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApiTeamController
{
  private TeamRepository $repo;
  private AuthService $authService;

  public function __construct($container = null)
  {
    $container = $container ?? app()->getContainer();
    if ($container && $container->has("pdo")) {
      $pdo = $container->get("pdo");
    } else {
      require __DIR__ . "/../../core/db.php";
    }
    $this->repo = new TeamRepository($pdo);
    $this->authService = $container->get('authService');
  }

  public function list(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $teams = $this->repo->allByCompany($currentUser['company_id']);
    return json($res, ["teams" => $teams]);
  }

  public function create(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $body = $req->getBody()->getContents();
    $b = json_decode($body, true) ?: [];
    $name = trim($b["name"] ?? "");
    if (empty($name)) {
      return json($res, ["error" => "Name is required"], 400);
    }
    $id = $this->repo->createIfNotExists($name, $currentUser['company_id']);
    return json($res, ["id" => $id, "name" => $name], 201);
  }
}
