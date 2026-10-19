<?php

namespace LeaveManagement\Services;

use LeaveManagement\Models\Employee;
use LeaveManagement\Repositories\EmployeeRepository;

class EmployeeService
{
    public function __construct(private EmployeeRepository $employeeRepository)    
    {
    }

    public function getAllEmployees(): array
    {
        return $this->employeeRepository->getAll();
    }

    public function getAllPositions(): array
    {
        return $this->employeeRepository->getAllPositions();
    }

    public function getEmployeeById(int $employeeId): ?Employee
    {
        return $this->employeeRepository->findById($employeeId);
    }

    public function addEmployee(
        string $firstName,
        string $lastName,
        string $email,
        string $username,
        int $positionId,
        int $departmentId,
        int $managerId
    ): void {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $email = trim($email);
        $username = trim($username);

        if ($firstName === '' || strlen($firstName) > 50) {
            throw new \InvalidArgumentException('Enter a first name of up to 50 characters.');
        }
        if ($lastName === '' || strlen($lastName) > 50) {
            throw new \InvalidArgumentException('Enter a last name of up to 50 characters.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            throw new \InvalidArgumentException('Enter a valid e-mail address of up to 100 characters.');
        }
        if ($username === '' || strlen($username) > 25) {
            throw new \InvalidArgumentException('Enter a username of up to 25 characters.');
        }
        if ($positionId < 1) {
            throw new \InvalidArgumentException('Select a position.');
        }
        if (!$this->employeeRepository->positionExists($positionId)) {
            throw new \InvalidArgumentException('Select an available position.');
        }
        if ($this->employeeRepository->usernameExists($username)) {
            throw new \DomainException('That username is already in use.');
        }

        $this->employeeRepository->create(
            $firstName,
            $lastName,
            $email,
            $username,
            password_hash('Password123!', PASSWORD_BCRYPT),
            $positionId,
            $departmentId,
            $managerId
        );
    }
}