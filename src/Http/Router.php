<?php
declare(strict_types=1);
namespace Course\Http;

final class Router
{
    private array $routes = [];
    public function add(string $method, string $path, callable $handler): void
    {
        // P04-ROUTING: Регистрировать маршрут; {id} — число от 1 до 9 цифр без ведущего нуля.
        $parts = explode('{id}', $path);
        $pattern = '#^' . implode('(?P<id>[1-9][0-9]{0,8})', array_map(static fn(string $part): string => preg_quote($part, '#'), $parts)) . '$#D';
        $this->routes[] = ['method' => strtoupper($method), 'pattern' => $pattern, 'handler' => $handler];
    }
    public function dispatch(Request $request): Response
    {
        // P04-ROUTING: Выбрать маршрут по пути и методу, передать id, различить 404/405 и поддержать HEAD.
        $method = $request->method === 'HEAD' ? 'GET' : $request->method;
        $allowed = [];
        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $request->path, $matches) !== 1) {
                continue;
            }
            $allowed[] = $route['method'];
            if ($route['method'] === 'GET') {
                $allowed[] = 'HEAD';
            }
            if ($route['method'] === $method) {
                return isset($matches['id'])
                    ? ($route['handler'])($request, (int) $matches['id'])
                    : ($route['handler'])($request);
            }
        }
        if ($allowed !== []) {
            throw new HttpError(405, 'method_not_allowed', 'Метод не разрешён', [],
                ['Allow' => implode(', ', array_unique($allowed))]);
        }
        throw new HttpError(404, 'not_found', 'Ресурс не найден');
    }
}
