<?php

namespace App\Repositories;

class CommentRepository
{
  public function __construct(private \PDO $pdo) {}

  public function findByNodeId(int $nodeId): array
  {
    $st = $this->pdo->prepare("
      SELECT * FROM comments 
      WHERE node_id = ? 
      ORDER BY created_at ASC
    ");
    $st->execute([$nodeId]);
    return $st->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function create(array $d): int
  {
    $st = $this->pdo->prepare("
      INSERT INTO comments(node_id, parent_comment_id, content, progress_update)
      VALUES (?, ?, ?, ?)
    ");
    $st->execute([
      $d["node_id"],
      $d["parent_comment_id"] ?? null,
      $d["content"],
      $d["progress_update"] ?? null
    ]);
    return (int)$this->pdo->lastInsertId();
  }

  public function updateNodeProgress(int $nodeId, ?int $progress): void
  {
    $st = $this->pdo->prepare("UPDATE nodes SET progress = ? WHERE id = ?");
    $st->execute([$progress, $nodeId]);
  }
}
