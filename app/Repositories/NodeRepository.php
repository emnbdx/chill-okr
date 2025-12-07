<?php

namespace App\Repositories;

class NodeRepository
{
  public function __construct(private \PDO $pdo) {}

  public function all(): array
  {
    return $this->pdo->query("SELECT * FROM nodes ORDER BY type,id")->fetchAll();
  }

  public function allByCompany(int $companyId, ?int $currentUserId = null): array
  {
    $sql = "
      SELECT n.* FROM nodes n
      LEFT JOIN companies c ON c.id = n.company_id
      WHERE 
        n.company_id = ?
        AND (
          n.type != 'okr_perso'
          OR ? IS NULL
          OR c.owner_id = ?
          OR n.user_id = ?
          OR EXISTS (
            SELECT 1 FROM nodes parent 
            WHERE parent.id = n.parent_id 
            AND parent.type = 'okr_team' 
            AND parent.user_id = ?
            AND parent.company_id = ?
          )
        )
      ORDER BY n.type, n.id
    ";
    $st = $this->pdo->prepare($sql);
    $st->execute([
      $companyId,
      $currentUserId,
      $currentUserId,
      $currentUserId,
      $currentUserId,
      $companyId
    ]);
    return $st->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function find(int $id): ?array
  {
    $st = $this->pdo->prepare("SELECT * FROM nodes WHERE id=?");
    $st->execute([$id]);
    return $st->fetch() ?: null;
  }

  public function create(array $d): void
  {
    $st = $this->pdo->prepare("
      INSERT INTO nodes(parent_id,type,title,description,owner,team_id,user_id,progress,company_id)
      VALUES (?,?,?,?,?,?,?,?,?)
    ");
    $st->execute([
      $d["parent_id"],
      $d["type"],
      $d["title"],
      $d["description"],
      $d["owner"],
      $d["team_id"] ?? null,
      $d["user_id"] ?? null,
      $d["progress"],
      $d["company_id"] ?? null
    ]);
  }

  public function update(int $id, array $d): void
  {
    $st = $this->pdo->prepare("
      UPDATE nodes SET title=?, description=?, owner=?, team_id=?, user_id=?, progress=? WHERE id=?
    ");
    $st->execute([
      $d["title"],
      $d["description"],
      $d["owner"],
      $d["team_id"] ?? null,
      $d["user_id"] ?? null,
      $d["progress"],
      $id
    ]);
  }

  public function delete(int $id): void
  {
    $st = $this->pdo->prepare("DELETE FROM nodes WHERE id=?");
    $st->execute([$id]);
  }

  public function byParent(): array
  {
    $rows = $this->all();
    $byParent = [];
    foreach ($rows as $r) {
      $p = $r["parent_id"] ?? 0;
      $byParent[$p][] = $r;
    }
    return $byParent;
  }

  public function allowedChildTypes(string $type): array
  {
    return [
      "axis" => ["okr_team"],
      "okr_team" => ["okr_perso"],
      "okr_perso" => []
    ][$type];
  }

  public function canAccessNode(int $nodeId, int $userId, int $companyId): bool
  {
    $node = $this->find($nodeId);
    if (!$node) {
      return false;
    }

    if ($node['company_id'] != $companyId) {
      return false;
    }

    if ($node['type'] !== 'okr_perso') {
      return true;
    }

    $sql = "
      SELECT 1
      FROM nodes n
      LEFT JOIN companies c ON c.id = n.company_id
      WHERE n.id = ?
      AND n.company_id = ?
      AND (
        c.owner_id = ?
        OR n.user_id = ?
        OR EXISTS (
          SELECT 1 FROM nodes parent 
          WHERE parent.id = n.parent_id 
          AND parent.type = 'okr_team' 
          AND parent.user_id = ?
          AND parent.company_id = ?
        )
      )
    ";
    $st = $this->pdo->prepare($sql);
    $st->execute([$nodeId, $companyId, $userId, $userId, $userId, $companyId]);
    return (bool)$st->fetch();
  }
}
