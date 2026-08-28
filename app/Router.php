<?php
declare(strict_types=1);

class Router
{
    /** @var array<int,array{method:string,path:string,handler:callable,name?:?string}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function any(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = ['method' => strtoupper($method), 'path' => $path, 'handler' => $handler];
    }

    /**
     * Dispatch against a method + path (e.g. '/staff/edit/123').
     * Supports {param} placeholders.
     */
    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = '/' . ltrim(trim($path), '/');
        if ($path === '/') $path = '/dashboard';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^/?' . $pattern . '/?$#';

            if (preg_match($pattern, $path, $m)) {
                $params = [];
                foreach ($m as $k => $v) {
                    if (is_string($k)) $params[$k] = $v;
                }
                if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && ($_SERVER['CONTENT_TYPE'] ?? '') === 'application/json') {
                    $body = json_decode(file_get_contents('php://input'), true) ?: [];
                    $_POST = array_replace($_POST, $body);
                }
                $handler = $route['handler'];
                $handler($params);
                return;
            }
        }

        http_response_code(404);
        $this->render404($path);
    }

    private function render404(string $path): void
    {
        http_response_code(404);
        if (Auth::check()) {
            view('errors/404', ['path' => $path]);
        } else {
            echo '<h1>404 - Page not found</h1>';
        }
    }
}
