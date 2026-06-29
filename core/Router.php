<?php

declare(strict_types=1);

namespace Core;

class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $path, string $controller, string $action): self
    {
        return $this->addRoute('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): self
    {
        return $this->addRoute('POST', $path, $controller, $action);
    }

    public function put(string $path, string $controller, string $action): self
    {
        return $this->addRoute('PUT', $path, $controller, $action);
    }

    public function delete(string $path, string $controller, string $action): self
    {
        return $this->addRoute('DELETE', $path, $controller, $action);
    }

    private function addRoute(string $method, string $path, string $controller, string $action): self
    {
        $normalizedPath = $this->normalizePath($path);
        $this->routes[$method][$normalizedPath] = [$controller, $action];

        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?: '/');

        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }

        foreach ($this->routes[$method] as $routePath => $handler) {
            $params = $this->matchRoute($routePath, $path);

            if ($params === null) {
                continue;
            }

            [$controllerClass, $action] = $handler;
            $this->invokeController($controllerClass, $action, $params);

            return;
        }

        $this->notFound();
    }

    /**
     * @return array<string, string>|null
     */
    private function matchRoute(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        $params = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * @param array<string, string> $params
     */
    private function invokeController(string $controllerClass, string $action, array $params): void
    {
        $fqcn = 'App\\Controllers\\' . $controllerClass;

        if (!class_exists($fqcn)) {
            $this->notFound("Controller not found: {$controllerClass}");
            return;
        }

        $controller = new $fqcn($this->config);

        if (!method_exists($controller, $action)) {
            $this->notFound("Action not found: {$action}");
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function notFound(?string $message = null): void
    {
        http_response_code(404);

        if ($this->config['app']['debug'] ?? false) {
            echo $message ?? '404 Not Found';
            return;
        }

        echo '404 Not Found';
    }
}
