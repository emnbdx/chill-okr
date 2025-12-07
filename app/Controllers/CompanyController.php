<?php

namespace App\Controllers;

use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\MailService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CompanyController
{
  private CompanyRepository $companyRepo;
  private UserRepository $userRepo;
  private AuthService $authService;
  private MailService $mailService;
  private array $config;

  public function __construct($container = null)
  {
    $container = $container ?? app()->getContainer();
    $this->companyRepo = $container->get('companyRepository');
    $this->userRepo = $container->get('userRepository');
    $this->authService = $container->get('authService');
    $this->mailService = $container->get('mailService');
    $this->config = $container->get('config');
  }

  public function inviteUser(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $bodyContent = $req->getBody()->getContents();
    $body = json_decode($bodyContent, true) ?: [];
    $firstName = trim($body['first_name'] ?? '');
    $lastName = trim($body['last_name'] ?? '');
    $email = trim($body['email'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email)) {
      return json($res, ['error' => 'First name, last name and email are required'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return json($res, ['error' => 'Invalid email'], 400);
    }

    $existingUser = $this->userRepo->findByEmail($email);
    if ($existingUser) {
      if ($existingUser['company_id'] == $currentUser['company_id']) {
        return json($res, ['error' => 'User already in your company'], 400);
      } elseif ($existingUser['company_id'] !== null) {
        return json($res, ['error' => 'User already belongs to another company'], 400);
      } else {
        try {
          $this->userRepo->updateInfo($existingUser['id'], $firstName, $lastName, $email);
          $this->userRepo->setCompany($existingUser['id'], $currentUser['company_id']);

          if (!$existingUser['password']) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
            $this->userRepo->setResetToken($existingUser['id'], $token, $expires);

            $resetUrl = $this->config['app']['url'] . '/?reset-token=' . $token;
            $fullName = trim($firstName . ' ' . $lastName);

            $company = $this->companyRepo->find($currentUser['company_id']);
            $this->mailService->sendInvitationEmail($email, $fullName, $company['name'], $resetUrl);
          }

          $user = $this->userRepo->find($existingUser['id']);
          return json($res, ['success' => true, 'user' => $user]);
        } catch (\PDOException $e) {
          return json($res, ['error' => 'Failed to link user to company'], 500);
        }
      }
    }

    try {
      $userId = $this->userRepo->create($firstName, $lastName, $email, null, $currentUser['company_id']);

      $token = bin2hex(random_bytes(32));
      $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
      $this->userRepo->setResetToken($userId, $token, $expires);

      $resetUrl = $this->config['app']['url'] . '/?reset-token=' . $token;
      $fullName = trim($firstName . ' ' . $lastName);

      $company = $this->companyRepo->find($currentUser['company_id']);
      $this->mailService->sendInvitationEmail($email, $fullName, $company['name'], $resetUrl);

      $user = $this->userRepo->find($userId);
      return json($res, ['success' => true, 'user' => $user]);
    } catch (\PDOException $e) {
      return json($res, ['error' => 'Failed to invite user'], 500);
    }
  }

  public function getCompanyUsers(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $users = $this->userRepo->allByCompany($currentUser['company_id']);

    foreach ($users as &$user) {
      unset($user['password']);
      unset($user['reset_token']);
      unset($user['reset_token_expires']);
    }

    return json($res, $users);
  }

  public function getCompanyInfo(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $company = $this->companyRepo->find($currentUser['company_id']);
    if (!$company) {
      return json($res, ['error' => 'Company not found'], 404);
    }

    return json($res, $company);
  }

  public function updateCompany(Request $req, Response $res): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $bodyContent = $req->getBody()->getContents();
    $body = json_decode($bodyContent, true) ?: [];
    $name = trim($body['name'] ?? '');
    $ownerId = isset($body['owner_id']) ? (int)$body['owner_id'] : null;

    if (empty($name)) {
      return json($res, ['error' => 'Name is required'], 400);
    }

    if ($ownerId !== null) {
      $owner = $this->userRepo->find($ownerId);
      if (!$owner || $owner['company_id'] != $currentUser['company_id']) {
        return json($res, ['error' => 'Invalid owner'], 400);
      }
    }

    $this->companyRepo->update($currentUser['company_id'], $name, $ownerId);
    $company = $this->companyRepo->find($currentUser['company_id']);

    return json($res, ['success' => true, 'company' => $company]);
  }

  public function removeUser(Request $req, Response $res, array $args): Response
  {
    $currentUser = $this->authService->getCurrentUser();
    if (!$currentUser || !$currentUser['company_id']) {
      return json($res, ['error' => 'Unauthorized'], 401);
    }

    $userId = (int)($args['id'] ?? 0);
    if (!$userId) {
      return json($res, ['error' => 'Invalid user ID'], 400);
    }

    if ($userId === $currentUser['id']) {
      return json($res, ['error' => 'Cannot remove yourself'], 400);
    }

    $user = $this->userRepo->find($userId);
    if (!$user || $user['company_id'] != $currentUser['company_id']) {
      return json($res, ['error' => 'User not found in your company'], 404);
    }

    $this->userRepo->removeFromCompany($userId);

    return json($res, ['success' => true]);
  }
}
