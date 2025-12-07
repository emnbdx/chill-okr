<?php

namespace App\Controllers;

use App\Repositories\NodeRepository;
use App\Repositories\TeamRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApiNodeController
{
  private NodeRepository $repo;
  private TeamRepository $teamRepo;
  private UserRepository $userRepo;
  private AuthService $authService;
  private \PDO $pdo;

  public function __construct($container = null)
  {
    $container = $container ?? app()->getContainer();
    if ($container && $container->has("pdo")) {
      $pdo = $container->get("pdo");
    } else {
      require __DIR__ . "/../../core/db.php";
    }
    $this->pdo = $pdo;
    $this->repo = new NodeRepository($pdo);
    $this->teamRepo = new TeamRepository($pdo);
    $this->userRepo = new UserRepository($pdo);
    $this->authService = $container->get('authService');
  }

  public function list(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $nodes = $this->repo->allByCompany($currentUser['company_id'], $currentUser['id']);
    $byParent = [];
    foreach ($nodes as $r) {
      $p = $r["parent_id"] ?? 0;
      $byParent[$p][] = $r;
    }
    return json($res, ["byParent" => $byParent]);
  }

  public function create(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $body = $req->getBody()->getContents();
    $b = json_decode($body, true) ?: [];
    $d = $this->payload($b, true);

    if (isset($d['parent_id']) && $d['parent_id'] !== null) {
      $parent = $this->repo->find($d['parent_id']);
      if (!$parent || $parent['company_id'] != $currentUser['company_id']) {
        return json($res, ['error' => 'Invalid parent node'], 403);
      }
    }

    $d['company_id'] = $currentUser['company_id'];
    $this->repo->create($d);
    return json($res, ["ok" => true], 201);
  }

  public function update(Request $req, Response $res, array $args): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $nodeId = (int)$args["id"];
    if (!$this->repo->canAccessNode($nodeId, $currentUser['id'], $currentUser['company_id'])) {
      return json($res, ['error' => 'Forbidden'], 403);
    }

    $body = $req->getBody()->getContents();
    $b = json_decode($body, true) ?: [];
    $d = $this->payload($b, false);
    $this->repo->update($nodeId, $d);
    return json($res, ["ok" => true]);
  }

  public function delete(Request $req, Response $res, array $args): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $nodeId = (int)$args["id"];
    if (!$this->repo->canAccessNode($nodeId, $currentUser['id'], $currentUser['company_id'])) {
      return json($res, ['error' => 'Forbidden'], 403);
    }

    $this->repo->delete($nodeId);
    return json($res, ["ok" => true]);
  }

  private function payload(array $b, bool $withType): array
  {
    $progress = ($b["progress"] ?? "") === "" ? null : (int)$b["progress"];
    $parent_id = null;
    if (isset($b["parent_id"])) {
      $parent_id = $b["parent_id"] === null || $b["parent_id"] === "" ? null : (int)$b["parent_id"];
    }

    $team_id = null;
    if (isset($b["team_id"])) {
      if ($b["team_id"] === "" || $b["team_id"] === null) {
        $team_id = null;
      } else {
        $team_id = (int)$b["team_id"];
      }
    } elseif (isset($b["team_name"]) && !empty(trim($b["team_name"]))) {
      $currentUser = $this->authService->getCurrentUser();
      $companyId = $currentUser ? $currentUser['company_id'] : null;
      $team_id = $this->teamRepo->createIfNotExists(trim($b["team_name"]), $companyId);
    }

    $user_id = null;
    if (isset($b["user_id"])) {
      if ($b["user_id"] === "" || $b["user_id"] === null) {
        $user_id = null;
      } else {
        $user_id = (int)$b["user_id"];
      }
    } elseif (
      isset($b["user_first_name"]) && isset($b["user_last_name"]) &&
      !empty(trim($b["user_first_name"])) && !empty(trim($b["user_last_name"]))
    ) {
      $currentUser = $this->authService->getCurrentUser();
      $companyId = $currentUser ? $currentUser['company_id'] : null;
      $user_id = $this->userRepo->createIfNotExists(
        trim($b["user_first_name"]),
        trim($b["user_last_name"]),
        $companyId
      );
    }

    $d = [
      "title" => trim($b["title"] ?? ""),
      "description" => trim($b["description"] ?? ""),
      "owner" => trim($b["owner"] ?? ""),
      "team_id" => $team_id,
      "user_id" => $user_id,
      "progress" => $progress,
      "parent_id" => $parent_id
    ];
    if ($withType) $d["type"] = $b["type"] ?? "company";
    return $d;
  }
}
