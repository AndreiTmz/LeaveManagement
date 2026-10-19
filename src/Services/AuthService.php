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
        $_SESSION['department_id'] = $user->department_id;
        $_SESSION['full_name'] = $user->first_name . ' ' . $user->last_name;
        $_SESSION['is_manager'] = $user->manager_id === null;

        return true;
    }

    public function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->employeeRepository->findById($_SESSION['user_id']);
    }

    public function changePassword(int $employeeId, string $currentPassword, string $newPassword): void
    {
        if ($currentPassword === '') {
            throw new \InvalidArgumentException('Enter your current password.');
        }
        if ($newPassword === '') {
            throw new \InvalidArgumentException('Enter a new password.');
        }
        $passwordLength = preg_match_all('/[\s\S]/u', $newPassword);
        if ($passwordLength === false) {
            throw new \InvalidArgumentException('Enter a valid new password.');
        }
        if ($passwordLength < 8) {
            throw new \InvalidArgumentException('Your new password must be at least 8 characters long.');
        }
        if (!preg_match('/[A-Z]/', $newPassword)) {
            throw new \InvalidArgumentException('Your new password must contain at least one uppercase letter.');
        }
        if (!preg_match('/[0-9]/', $newPassword)) {
            throw new \InvalidArgumentException('Your new password must contain at least one number.');
        }

        $employee = $this->employeeRepository->findById($employeeId);
        if ($employee === null) {
            throw new \RuntimeException('The authenticated user could not be found.');
        }
        if (!password_verify($currentPassword, $employee->password_hash)) {
            throw new \InvalidArgumentException('The current password is incorrect.');
        }
        if (password_verify($newPassword, $employee->password_hash)) {
            throw new \InvalidArgumentException('Your new password must be different from your current password.');
        }

        $this->employeeRepository->updatePassword(
            $employeeId,
            password_hash($newPassword, PASSWORD_BCRYPT)
        );
    }

    public function logout(): void
    {
        session_destroy();
    }
}