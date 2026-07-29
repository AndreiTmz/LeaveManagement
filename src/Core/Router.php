<?php

namespace LeaveManagement\Core;

class Router
{
    private array $routes = [];

    public function __construct(private ?Container $container = null)
    {
    }

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
           $this->renderError(404,
                'Page not found',
                'The page you are looking for does not exist.'
            );
            return;
        }

        if ($method === 'POST') {
            $token = $_POST['csrf_token'] ?? '';

            if (!Csrf::validate($token)) {
                $this->renderError(403,
                    'Forbidden',
                    'The request could not be validated. Please try again.'
                );
                return;
            }
        }

        [$controller, $action] = $handler;

        if (is_string($controller)) {
            $controller = $this->container?->get($controller) ?? new $controller();
        }

        $controller->$action();
    }

    private function renderError(int $statusCode, string $title, string $message): void
    {
        http_response_code($statusCode);

        View::render('errors/error', [
            'title' => $title,
            'statusCode' => $statusCode,
            'message' => $message
        ]);

        exit;
    }
}