<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\CompanyRepository;

class AuthService
{
  private UserRepository $userRepo;
  private CompanyRepository $companyRepo;
  private MailService $mailService;
  private array $config;

  public function __construct(UserRepository $userRepo, CompanyRepository $companyRepo, MailService $mailService, array $config)
  {
    $this->userRepo = $userRepo;
    $this->companyRepo = $companyRepo;
    $this->mailService = $mailService;
    $this->config = $config;
  }

  public function register(string $firstName, string $lastName, string $email, string $password): ?array
  {
    $existing = $this->userRepo->findByEmail($email);
    if ($existing) {
      return null;
    }

    try {
      $companyName = trim($firstName . ' ' . $lastName) . "'s Company";
      $companyId = $this->companyRepo->create($companyName);

      $userId = $this->userRepo->create($firstName, $lastName, $email, $password, $companyId);

      $this->companyRepo->setOwner($companyId, $userId);

      $user = $this->userRepo->find($userId);

      if ($user) {
        $this->mailService->sendWelcomeEmail($email, "{$firstName} {$lastName}");
      }

      return $user;
    } catch (\PDOException $e) {
      return null;
    }
  }

  public function login(string $email, string $password): ?array
  {
    $user = $this->userRepo->findByEmail($email);

    if (!$user || !isset($user['password'])) {
      return null;
    }

    if (!password_verify($password, $user['password'])) {
      return null;
    }

    unset($user['password']);
    unset($user['reset_token']);
    unset($user['reset_token_expires']);

    return $user;
  }

  public function requestPasswordReset(string $email): bool
  {
    $user = $this->userRepo->findByEmail($email);

    if (!$user) {
      return false;
    }

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $this->userRepo->setResetToken($user['id'], $token, $expires);

    $resetUrl = $this->config['app']['url'] . '/?reset-token=' . $token;
    $fullName = trim($user['first_name'] . ' ' . $user['last_name']);

    return $this->mailService->sendResetPasswordEmail($user['email'], $fullName, $resetUrl);
  }

  public function resetPassword(string $token, string $newPassword): bool
  {
    $user = $this->userRepo->findByResetToken($token);

    if (!$user) {
      return false;
    }

    $this->userRepo->updatePassword($user['id'], $newPassword);
    $this->userRepo->clearResetToken($user['id']);

    return true;
  }

  public function startSession(array $user): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
  }

  public function endSession(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }

    session_destroy();
  }

  public function getCurrentUser(): ?array
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    if (!isset($_SESSION['user_id'])) {
      return null;
    }

    return $this->userRepo->find($_SESSION['user_id']);
  }

  public function isAuthenticated(): bool
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    return isset($_SESSION['user_id']);
  }
}
