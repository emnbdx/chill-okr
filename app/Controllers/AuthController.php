<?php

namespace App\Controllers;

use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
  private AuthService $authService;

  public function __construct($container = null)
  {
    $container = $container ?? app()->getContainer();
    $this->authService = $container->get('authService');
  }

  public function showLogin(Request $req, Response $res): Response
  {
    return $res->withHeader('Location', '/')->withStatus(302);
  }

  public function showRegister(Request $req, Response $res): Response
  {
    return $res->withHeader('Location', '/')->withStatus(302);
  }

  public function showForgotPassword(Request $req, Response $res): Response
  {
    return $res->withHeader('Location', '/')->withStatus(302);
  }

  public function login(Request $req, Response $res): Response
  {
    $body = $req->getParsedBody();
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';

    if (empty($email) || empty($password)) {
      return json($res, ['error' => 'Email and password are required'], 400);
    }

    $user = $this->authService->login($email, $password);

    if (!$user) {
      return json($res, ['error' => 'Invalid email or password'], 401);
    }

    $this->authService->startSession($user);

    return json($res, ['success' => true, 'user' => $user]);
  }

  public function register(Request $req, Response $res): Response
  {
    $body = $req->getParsedBody();
    $firstName = trim($body['first_name'] ?? '');
    $lastName = trim($body['last_name'] ?? '');
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
      return json($res, ['error' => 'All fields are required'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return json($res, ['error' => 'Invalid email address'], 400);
    }

    if (strlen($password) < 8) {
      return json($res, ['error' => 'Password must be at least 8 characters'], 400);
    }

    $user = $this->authService->register($firstName, $lastName, $email, $password);

    if (!$user) {
      return json($res, ['error' => 'This email is already in use'], 400);
    }

    $this->authService->startSession($user);

    return json($res, ['success' => true, 'user' => $user], 201);
  }

  public function logout(Request $req, Response $res): Response
  {
    $this->authService->endSession();
    return $res->withHeader('Location', '/')->withStatus(302);
  }

  public function forgotPassword(Request $req, Response $res): Response
  {
    $body = $req->getParsedBody();
    $email = trim($body['email'] ?? '');

    if (empty($email)) {
      return json($res, ['error' => 'Email is required'], 400);
    }

    $sent = $this->authService->requestPasswordReset($email);

    return json($res, [
      'success' => true,
      'message' => 'If this email exists, a reset link has been sent'
    ]);
  }

  public function resetPassword(Request $req, Response $res): Response
  {
    $body = $req->getParsedBody();
    $token = trim($body['token'] ?? '');
    $password = $body['password'] ?? '';

    if (empty($token) || empty($password)) {
      return json($res, ['error' => 'Token and password are required'], 400);
    }

    if (strlen($password) < 8) {
      return json($res, ['error' => 'Password must be at least 8 characters'], 400);
    }

    $success = $this->authService->resetPassword($token, $password);

    if (!$success) {
      return json($res, ['error' => 'Invalid or expired token'], 400);
    }

    return json($res, ['success' => true, 'message' => 'Password reset successfully']);
  }
}
