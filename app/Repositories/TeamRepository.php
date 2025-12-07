<?php

namespace App\Repositories;

class TeamRepository
{
  public function __construct(private \PDO $pdo) {}

  public function all(): array
  {
    return $this->pdo->query("SELECT * FROM teams ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function allByCompany(int $companyId): array
  {
    $st = $this->pdo->prepare("SELECT * FROM teams WHERE company_id=? ORDER BY name");
    $st->execute([$companyId]);
    return $st->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function find(int $id): ?array
  {
    $st = $this->pdo->prepare("SELECT * FROM teams WHERE id=?");
    $st->execute([$id]);
    return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
  }

  public function findByName(string $name): ?array
  {
    $st = $this->pdo->prepare("SELECT * FROM teams WHERE name=?");
    $st->execute([$name]);
    return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
  }

  public function create(string $name, ?int $companyId = null): int
  {
    $st = $this->pdo->prepare("INSERT INTO teams (name, company_id) VALUES (?, ?)");
    $st->execute([trim($name), $companyId]);
    return (int)$this->pdo->lastInsertId();
  }

  public function createIfNotExists(string $name, ?int $companyId = null): int
  {
    $existing = $this->findByName($name);
    if ($existing) {
      return (int)$existing["id"];
    }
    return $this->create($name, $companyId);
  }
}
