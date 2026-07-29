<?php

namespace LeaveManagement\Repositories;

use LeaveManagement\Models\Employee;

use PDO;

class EmployeeRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM employees");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn($row) => Employee::fromArray($row),
            $rows
        );
    }

    public function findByEmail(string $email): ?Employee
    {
        $stmt = $this->pdo->prepare("SELECT * FROM employees WHERE email = ?");
        $stmt->execute([$email]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Employee::fromArray($data) : null;
    }

    public function findById(int $id): ?Employee
    {
        $stmt = $this->pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Employee::fromArray($data) : null;
    }
}