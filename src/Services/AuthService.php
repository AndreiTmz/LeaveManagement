<?php

namespace LeaveManagement\Services;

use LeaveManagement\Repositories\EmployeeRepository;

class AuthService
{
     public function __construct(
        private EmployeeRepository $employeeRepository
    ) {}

    public function attempt(string $username, string $password): bool
    {
        $user = $this->employeeRepository->findByEmail($username);

        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user->password_hash)) {
            return false;
        }

        $_SESSION['user_id'] = $user->id;
        $_SESSION['position_id'] = $user->position_id;
        $_SESSION['full_name'] = $user->first_name . ' ' . $user->last_name;

        return true;
    }

    public function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->employeeRepository->findById($_SESSION['user_id']);
    }

    public function logout(): void
    {
        session_destroy();
    }
}