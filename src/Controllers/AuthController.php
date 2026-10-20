<?php

namespace LeaveManagement\Controllers;

use LeaveManagement\Core\BaseController;
use LeaveManagement\Services\AuthService;

class AuthController extends BaseController
{
    public function __construct(private AuthService $authService) {}

    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /calendar');
            exit;
        }

        $this->showLoginForm();
    }

    public function login(): void
    {
        $identifier = is_string($_POST['identifier'] ?? null) ? $_POST['identifier'] : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        //attempt authentication
        if ($this->authService->attempt($identifier, $password)) {
            session_regenerate_id(true);
            header('Location: /calendar');
            exit;
        }

        //auth failed. return to login page with error message
        $this->showLoginForm($identifier, 'Invalid credentials. Please try again.');
    }

    public function logout(): void
    {
        $this->authService->logout();

        header('Location: /login');
        exit;
    }

    public function showChangePassword(): void
    {
        $this->requireAuth();

        $this->renderChangePasswordForm();
    }

    public function changePassword(): void
    {
        $this->requireAuth();

        $currentPassword = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
        $newPassword = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
        $confirmPassword = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

        try {
            if ($newPassword !== $confirmPassword) {
                throw new \InvalidArgumentException('The new password and confirmation do not match.');
            }

            $this->authService->changePassword(
                (int) $_SESSION['user_id'],
                $currentPassword,
                $newPassword
            );
        } catch (\InvalidArgumentException $exception) {
            $this->renderChangePasswordForm($exception->getMessage());
            return;
        }

        header('Location: /change-password?success=1');
        exit;
    }

    private function renderChangePasswordForm(string $errorMessage = ''): void
    {
        $this->view('auth/change-password', [
            'title' => 'Change password',
            'styles' => ['/css/forms.css'],
            'scripts' => ['/js/password-visibility.js'],
            'errorMessage' => $errorMessage,
            'success' => ($_GET['success'] ?? '') === '1',
        ]);
    }

    private function showLoginForm(string $identifier = '', string $errorMessage = ''): void
    {
        $this->view('auth/login', [
            'title' => 'Login',
            'styles' => ['/css/forms.css'],
            'scripts' => ['/js/password-visibility.js'],
            'identifier' => $identifier,
            'errorMessage' => $errorMessage,
        ], 'guest');
    }
}