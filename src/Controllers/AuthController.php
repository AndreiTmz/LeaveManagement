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
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        //attempt authentication
        if ($this->authService->attempt($email, $password)) {
            session_regenerate_id(true);
            header('Location: /calendar');
            exit;
        }

        //auth failed. return to login page with error message
        $this->showLoginForm($email, 'Invalid credentials. Please try again.');
    }

    public function logout(): void
    {
        $this->authService->logout();

        header('Location: /login');
        exit;
    }

    private function showLoginForm(string $email = '', string $errorMessage = ''): void
    {
        $this->view('auth/login', [
            'title' => 'Login',
            'styles' => ['/css/forms.css'],
            'email' => $email,
            'errorMessage' => $errorMessage,
        ], 'guest');
    }
}