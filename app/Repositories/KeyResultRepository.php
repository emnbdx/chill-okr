<?php

namespace App\Repositories;

class KeyResultRepository
{
    public function __construct(private \PDO $pdo) {}

    public function findByNode(int $nodeId): array
    {
        $st = $this->pdo->prepare("SELECT * FROM key_results WHERE node_id = ? ORDER BY id");
        $st->execute([$nodeId]);
        return $st->fetchAll() ?: [];
    }

    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM key_results WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function create(array $d): void
    {
        $st = $this->pdo->prepare("
      INSERT INTO key_results(node_id, name, description, progress, weight)
      VALUES (?, ?, ?, ?, ?)
    ");
        $st->execute([
            $d["node_id"],
            $d["name"],
            $d["description"] ?? null,
            $d["progress"] ?? null,
            $d["weight"] ?? 1
        ]);
    }

    public function update(int $id, array $d): void
    {
        $st = $this->pdo->prepare("
      UPDATE key_results 
      SET name = ?, description = ?, progress = ?, weight = ?
      WHERE id = ?
    ");
        $st->execute([
            $d["name"],
            $d["description"] ?? null,
            $d["progress"] ?? null,
            $d["weight"] ?? 1,
            $id
        ]);
    }

    public function delete(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM key_results WHERE id = ?");
        $st->execute([$id]);
    }

    public function calculateNodeProgress(int $nodeId): ?float
    {
        $krs = $this->findByNode($nodeId);
        if (empty($krs)) {
            return null;
        }

        $totalWeight = 0;
        $weightedProgress = 0;

        foreach ($krs as $kr) {
            if ($kr["progress"] !== null) {
                $weight = (int)$kr["weight"];
                $totalWeight += $weight;
                $weightedProgress += $kr["progress"] * $weight;
            }
        }

        if ($totalWeight === 0) {
            return null;
        }

        return $weightedProgress / $totalWeight;
    }
}
