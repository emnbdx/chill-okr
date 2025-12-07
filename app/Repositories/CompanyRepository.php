<?php

namespace App\Repositories;

class CompanyRepository
{
  public function __construct(private \PDO $pdo) {}

  public function all(): array
  {
    return $this->pdo->query("SELECT * FROM companies ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function find(int $id): ?array
  {
    $st = $this->pdo->prepare("SELECT * FROM companies WHERE id=?");
    $st->execute([$id]);
    return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
  }

  public function create(string $name, ?int $ownerId = null): int
  {
    $st = $this->pdo->prepare("INSERT INTO companies (name, owner_id) VALUES (?, ?)");
    $st->execute([trim($name), $ownerId]);
    return (int)$this->pdo->lastInsertId();
  }

  public function update(int $id, string $name, ?int $ownerId = null): bool
  {
    if ($ownerId !== null) {
      $st = $this->pdo->prepare("UPDATE companies SET name=?, owner_id=? WHERE id=?");
      return $st->execute([trim($name), $ownerId, $id]);
    } else {
      $st = $this->pdo->prepare("UPDATE companies SET name=? WHERE id=?");
      return $st->execute([trim($name), $id]);
    }
  }

  public function setOwner(int $companyId, int $ownerId): bool
  {
    $st = $this->pdo->prepare("UPDATE companies SET owner_id=? WHERE id=?");
    return $st->execute([$ownerId, $companyId]);
  }

  public function delete(int $id): bool
  {
    $st = $this->pdo->prepare("DELETE FROM companies WHERE id=?");
    return $st->execute([$id]);
  }

  public function getUsersByCompany(int $companyId): array
  {
    $st = $this->pdo->prepare("SELECT * FROM users WHERE company_id=? ORDER BY last_name, first_name");
    $st->execute([$companyId]);
    return $st->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getTeamsByCompany(int $companyId): array
  {
    $st = $this->pdo->prepare("SELECT * FROM teams WHERE company_id=? ORDER BY name");
    $st->execute([$companyId]);
    return $st->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function addUserToCompany(int $userId, int $companyId): bool
  {
    $st = $this->pdo->prepare("UPDATE users SET company_id=? WHERE id=?");
    return $st->execute([$companyId, $userId]);
  }
}
