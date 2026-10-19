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

    public function getAllPositions(): array
    {
        $stmt = $this->pdo->query('SELECT id, name FROM positions ORDER BY name');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM employees WHERE username = :username');
        $stmt->execute(['username' => $username]);

        return $stmt->fetchColumn() !== false;
    }

    public function positionExists(int $positionId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM positions WHERE id = :position_id');
        $stmt->execute(['position_id' => $positionId]);

        return $stmt->fetchColumn() !== false;
    }

    public function create(
        string $firstName,
        string $lastName,
        string $email,
        string $username,
        string $passwordHash,
        int $positionId,
        int $departmentId,
        int $managerId
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO employees
                (first_name, last_name, email, username, password_hash, position_id, department_id, manager_id, created_at)
             VALUES
                (:first_name, :last_name, :email, :username, :password_hash, :position_id, :department_id, :manager_id, :created_at)'
        );
        try {
            $stmt->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'username' => $username,
                'password_hash' => $passwordHash,
                'position_id' => $positionId,
                'department_id' => $departmentId,
                'manager_id' => $managerId,
                'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
        } catch (\PDOException $exception) {
            if ($exception->getCode() === '23000' && (int) ($exception->errorInfo[1] ?? 0) === 1062) {
                throw new \DomainException('That username is already in use.', 0, $exception);
            }

            throw $exception;
        }
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