<?php

declare(strict_types=1);

namespace Core;

class Controller
{
    /** @var array<string, mixed> */
    protected array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function view(string $view, array $data = [], ?string $layout = 'app'): void
    {
        $viewPath = $this->config['paths']['views'] . '/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($viewPath)) {
            http_response_code(500);
            echo "View not found: {$view}";
            return;
        }

        $data['config'] = $this->config;
        extract($data, EXTR_SKIP);

        if ($layout !== null) {
            $content = $this->renderPartial($viewPath, $data);
            $layoutPath = $this->config['paths']['views'] . '/layouts/' . $layout . '.php';

            if (!is_file($layoutPath)) {
                http_response_code(500);
                echo "Layout not found: {$layout}";
                return;
            }

            require $layoutPath;
            return;
        }

        require $viewPath;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderPartial(string $path, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;

        return (string) ob_get_clean();
    }

    protected function redirect(string $path): void
    {
        $baseUrl = rtrim($this->config['app']['url'], '/');
        header('Location: ' . $baseUrl . $path);
        exit;
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * @return array{id: int, name: string, email: string, role: string}|null
     */
    protected function user(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id'    => (int) $_SESSION['user_id'],
            'name'  => (string) $_SESSION['name'],
            'email' => (string) $_SESSION['email'],
            'role'  => (string) $_SESSION['role'],
        ];
    }

    /**
     * @param array<string, mixed> $user
     */
    protected function loginUser(array $user, bool $remember = false): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['name']    = (string) $user['name'];
        $_SESSION['email']   = (string) $user['email'];
        $_SESSION['role']    = (string) $user['role'];

        if ($remember) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                time() + 60 * 60 * 24 * 30,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }

    protected function logoutUser(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->setFlash('error', 'Please sign in to continue.');
            $this->redirect('/login');
        }
    }

    protected function requireRole(string $role): void
    {
        $this->requireLogin();

        if (($_SESSION['role'] ?? '') !== $role) {
            http_response_code(403);
            $this->setFlash('error', 'You do not have permission to access that page.');
            $this->redirectToRoleHome();
        }
    }

    protected function redirectIfAuthenticated(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirectToRoleHome();
        }
    }

    protected function redirectToRoleHome(): void
    {
        $role = $_SESSION['role'] ?? '';

        if ($role === 'shopper') {
            $this->redirect('/shopper/dashboard');
        }

        $this->redirect('/customer/orders');
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * @return array{type: string, message: string}|null
     */
    protected function getFlash(): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return is_array($flash) ? $flash : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function jsonInput(): array
    {
        $raw = file_get_contents('php://input');

        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
