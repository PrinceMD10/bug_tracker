<?php

class Ticket
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll()
    {
        $sql = "
            SELECT t.*, c.title AS category, u.name AS creator
            FROM tickets t
            JOIN categories c ON t.category_id = c.id
            JOIN users u ON t.created_by = u.id
            ORDER BY t.created_at DESC
        ";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function create($title, $categoryId, $userId)
    {
        $sql = "
            INSERT INTO tickets (title, category_id, created_by)
            VALUES (:title, :category_id, :created_by)
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'title' => $title,
            'category_id' => $categoryId,
            'created_by' => $userId
        ]);
    }
}
