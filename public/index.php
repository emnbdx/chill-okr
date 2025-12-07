<?php

use App\Middleware\AuthMiddleware;

$app = require __DIR__ . "/../bootstrap/app.php";

function app()
{
  global $app;
  return $app;
}

function view($res, $path, $params = [])
{
  extract($params);
  $viewPath = __DIR__ . "/../app/Views/$path.php";
  ob_start();
  require __DIR__ . "/../app/Views/layout.php";
  $res->getBody()->write(ob_get_clean());
  return $res;
}

function json($res, $data, $status = 200)
{
  $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
  $res->getBody()->write($payload);
  return $res->withHeader("Content-Type", "application/json")->withStatus($status);
}

$app->get("/", \App\Controllers\LandingController::class . ":index");

$app->post("/auth/login", \App\Controllers\AuthController::class . ":login");
$app->post("/auth/register", \App\Controllers\AuthController::class . ":register");
$app->post("/auth/logout", \App\Controllers\AuthController::class . ":logout");
$app->post("/auth/forgot-password", \App\Controllers\AuthController::class . ":forgotPassword");
$app->post("/auth/reset-password", \App\Controllers\AuthController::class . ":resetPassword");

$app->get("/dashboard", \App\Controllers\NodeController::class . ":index")->add(new AuthMiddleware());

$app->group("/api", function ($g) {
  $g->get("/nodes", \App\Controllers\ApiNodeController::class . ":list");
  $g->post("/nodes", \App\Controllers\ApiNodeController::class . ":create");
  $g->patch("/nodes/{id}", \App\Controllers\ApiNodeController::class . ":update");
  $g->delete("/nodes/{id}", \App\Controllers\ApiNodeController::class . ":delete");

  $g->get("/nodes/{node_id}/comments", \App\Controllers\ApiCommentController::class . ":list");
  $g->post("/nodes/{node_id}/comments", \App\Controllers\ApiCommentController::class . ":create");

  $g->get("/teams", \App\Controllers\ApiTeamController::class . ":list");
  $g->post("/teams", \App\Controllers\ApiTeamController::class . ":create");

  $g->get("/users", \App\Controllers\ApiUserController::class . ":list");
  $g->post("/users", \App\Controllers\ApiUserController::class . ":create");

  $g->get("/profile", \App\Controllers\ApiUserController::class . ":getProfile");
  $g->patch("/profile", \App\Controllers\ApiUserController::class . ":updateProfile");

  $g->get("/nodes/{node_id}/key-results", \App\Controllers\ApiKeyResultController::class . ":list");
  $g->post("/nodes/{node_id}/key-results", \App\Controllers\ApiKeyResultController::class . ":create");
  $g->patch("/key-results/{id}", \App\Controllers\ApiKeyResultController::class . ":update");
  $g->delete("/key-results/{id}", \App\Controllers\ApiKeyResultController::class . ":delete");

  $g->get("/company", \App\Controllers\CompanyController::class . ":getCompanyInfo");
  $g->patch("/company", \App\Controllers\CompanyController::class . ":updateCompany");
  $g->get("/company/users", \App\Controllers\CompanyController::class . ":getCompanyUsers");
  $g->post("/company/invite", \App\Controllers\CompanyController::class . ":inviteUser");
  $g->delete("/company/users/{id}", \App\Controllers\CompanyController::class . ":removeUser");
})->add(new AuthMiddleware());

$app->run();
