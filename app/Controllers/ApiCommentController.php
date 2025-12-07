<?php

namespace App\Controllers;

use App\Repositories\CommentRepository;
use App\Repositories\NodeRepository;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApiCommentController
{
  private CommentRepository $repo;
  private NodeRepository $nodeRepo;
  private AuthService $authService;

  public function __construct($container = null)
  {
    $container = $container ?? app()->getContainer();
    if ($container && $container->has("pdo")) {
      $pdo = $container->get("pdo");
    } else {
      require __DIR__ . "/../../core/db.php";
    }
    $this->repo = new CommentRepository($pdo);
    $this->nodeRepo = new NodeRepository($pdo);
    $this->authService = $container->get('authService');
  }

  public function list(Request $req, Response $res, array $args): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $nodeId = (int)$args["node_id"];
    if (!$this->nodeRepo->canAccessNode($nodeId, $currentUser['id'], $currentUser['company_id'])) {
      return json($res, ['error' => 'Forbidden'], 403);
    }

    $comments = $this->repo->findByNodeId($nodeId);
    return json($res, ["comments" => $comments]);
  }

  public function create(Request $req, Response $res, array $args): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $nodeId = (int)$args["node_id"];
    if (!$this->nodeRepo->canAccessNode($nodeId, $currentUser['id'], $currentUser['company_id'])) {
      return json($res, ['error' => 'Forbidden'], 403);
    }

    $body = $req->getBody()->getContents();
    $b = json_decode($body, true) ?: [];

    $content = trim($b["content"] ?? "");
    $parentCommentId = isset($b["parent_comment_id"]) && $b["parent_comment_id"] !== ""
      ? (int)$b["parent_comment_id"]
      : null;
    $progressUpdate = isset($b["progress_update"]) && $b["progress_update"] !== ""
      ? (int)$b["progress_update"]
      : null;

    if (empty($content)) {
      return json($res, ["error" => "Content is required"], 400);
    }

    $commentId = $this->repo->create([
      "node_id" => $nodeId,
      "parent_comment_id" => $parentCommentId,
      "content" => $content,
      "progress_update" => $progressUpdate
    ]);

    if ($progressUpdate !== null) {
      $this->repo->updateNodeProgress($nodeId, $progressUpdate);
    }

    return json($res, ["ok" => true, "id" => $commentId], 201);
  }
}
