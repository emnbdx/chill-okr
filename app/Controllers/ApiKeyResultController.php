<?php

namespace App\Controllers;

use App\Repositories\KeyResultRepository;
use App\Repositories\NodeRepository;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApiKeyResultController
{
    private KeyResultRepository $repo;
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
        $this->repo = new KeyResultRepository($pdo);
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

        $krs = $this->repo->findByNode($nodeId);
        return json($res, ["key_results" => $krs]);
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

        $name = trim($b["name"] ?? "");
        $description = trim($b["description"] ?? "");
        $progress = isset($b["progress"]) && $b["progress"] !== "" ? (int)$b["progress"] : null;
        $weight = isset($b["weight"]) && $b["weight"] !== "" ? (int)$b["weight"] : 1;

        if (empty($name)) {
            return json($res, ["error" => "Name is required"], 400);
        }

        if ($weight < 1 || $weight > 10) {
            return json($res, ["error" => "Weight must be between 1 and 10"], 400);
        }

        $this->repo->create([
            "node_id" => $nodeId,
            "name" => $name,
            "description" => $description ?: null,
            "progress" => $progress,
            "weight" => $weight
        ]);

        return json($res, ["ok" => true], 201);
    }

    public function update(Request $req, Response $res, array $args): Response
    {
        $currentUser = $this->authService->getCurrentUser();
        if (!$currentUser || !$currentUser['company_id']) {
            return json($res, ['error' => 'Unauthorized'], 401);
        }

        $id = (int)$args["id"];
        $kr = $this->repo->find($id);
        if (!$kr) {
            return json($res, ['error' => 'Key result not found'], 404);
        }

        if (!$this->nodeRepo->canAccessNode($kr['node_id'], $currentUser['id'], $currentUser['company_id'])) {
            return json($res, ['error' => 'Forbidden'], 403);
        }

        $body = $req->getBody()->getContents();
        $b = json_decode($body, true) ?: [];

        $name = trim($b["name"] ?? "");
        $description = trim($b["description"] ?? "");
        $progress = isset($b["progress"]) && $b["progress"] !== "" ? (int)$b["progress"] : null;
        $weight = isset($b["weight"]) && $b["weight"] !== "" ? (int)$b["weight"] : 1;

        if (empty($name)) {
            return json($res, ["error" => "Name is required"], 400);
        }

        if ($weight < 1 || $weight > 10) {
            return json($res, ["error" => "Weight must be between 1 and 10"], 400);
        }

        $this->repo->update($id, [
            "name" => $name,
            "description" => $description ?: null,
            "progress" => $progress,
            "weight" => $weight
        ]);

        return json($res, ["ok" => true]);
    }

    public function delete(Request $req, Response $res, array $args): Response
    {
        $currentUser = $this->authService->getCurrentUser();
        if (!$currentUser || !$currentUser['company_id']) {
            return json($res, ['error' => 'Unauthorized'], 401);
        }

        $id = (int)$args["id"];
        $kr = $this->repo->find($id);
        if (!$kr) {
            return json($res, ['error' => 'Key result not found'], 404);
        }

        if (!$this->nodeRepo->canAccessNode($kr['node_id'], $currentUser['id'], $currentUser['company_id'])) {
            return json($res, ['error' => 'Forbidden'], 403);
        }

        $this->repo->delete($id);
        return json($res, ["ok" => true]);
    }
}
