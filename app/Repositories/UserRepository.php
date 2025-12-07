<?php

namespace App\Repositories;

class UserRepository
{
  public function __construct(private \PDO $pdo) {}

  public function all(): array
  {
    return $this->pdo->query("SELECT * FROM users ORDER BY last_name, first_name")->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function allByCompany(int $companyId): array
  {
    $st = $this->pdo->prepare("SELECT * FROM users WHERE company_id=? ORDER BY last_name, first_name");
    $st->execute([$companyId]);
    return $st->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function find(int $id): ?array
  {
    $st = $this->pdo->prepare("SELECT * FROM users WHERE id=?");
    $st->execute([$id]);
    return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
  }

  public function findByName(string $firstName, string $lastName): ?array
  {
    $st = $this->pdo->prepare("SELECT * FROM users WHERE first_name=? AND last_name=?");
    $st->execute([trim($firstName), trim($lastName)]);
    return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
  }

  public function findByEmail(string $email): ?array
  {
    $st = $this->pdo->prepare("SELECT * FROM users WHERE email=?");
    $st->execute([trim($email)]);
    return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
  }

  public function findByResetToken(string $token): ?array
  {
    $st = $this->pdo->prepare("SELECT * FROM users WHERE reset_token=? AND reset_token_expires > NOW()");
    $st->execute([$token]);
    return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
  }

  public function create(string $firstName, string $lastName, ?string $email = null, ?string $password = null, ?int $companyId = null): int
  {
    if ($email && $password) {
      $st = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, password, company_id) VALUES (?, ?, ?, ?, ?)");
      $st->execute([trim($firstName), trim($lastName), trim($email), password_hash($password, PASSWORD_DEFAULT), $companyId]);
    } elseif ($email) {
      $st = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, company_id) VALUES (?, ?, ?, ?)");
      $st->execute([trim($firstName), trim($lastName), trim($email), $companyId]);
    } else {
      $st = $this->pdo->prepare("INSERT INTO users (first_name, last_name, company_id) VALUES (?, ?, ?)");
      $st->execute([trim($firstName), trim($lastName), $companyId]);
    }
    return (int)$this->pdo->lastInsertId();
  }

  public function createIfNotExists(string $firstName, string $lastName, ?int $companyId = null): int
  {
    $existing = $this->findByName($firstName, $lastName);
    if ($existing) {
      return (int)$existing["id"];
    }
    return $this->create($firstName, $lastName, null, null, $companyId);
  }

  public function updatePassword(int $userId, string $password): bool
  {
    $st = $this->pdo->prepare("UPDATE users SET password=? WHERE id=?");
    return $st->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
  }

  public function setResetToken(int $userId, string $token, string $expires): bool
  {
    $st = $this->pdo->prepare("UPDATE users SET reset_token=?, reset_token_expires=? WHERE id=?");
    return $st->execute([$token, $expires, $userId]);
  }

  public function clearResetToken(int $userId): bool
  {
    $st = $this->pdo->prepare("UPDATE users SET reset_token=NULL, reset_token_expires=NULL WHERE id=?");
    return $st->execute([$userId]);
  }

  public function removeFromCompany(int $userId): bool
  {
    $st = $this->pdo->prepare("UPDATE users SET company_id=NULL WHERE id=?");
    return $st->execute([$userId]);
  }

  public function setCompany(int $userId, int $companyId): bool
  {
    $st = $this->pdo->prepare("UPDATE users SET company_id=? WHERE id=?");
    return $st->execute([$companyId, $userId]);
  }

  public function updateInfo(int $userId, string $firstName, string $lastName, ?string $email = null): bool
  {
    if ($email) {
      $st = $this->pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?");
      return $st->execute([trim($firstName), trim($lastName), trim($email), $userId]);
    } else {
      $st = $this->pdo->prepare("UPDATE users SET first_name=?, last_name=? WHERE id=?");
      return $st->execute([trim($firstName), trim($lastName), $userId]);
    }
  }
}
