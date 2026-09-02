<?php
/**
 * Router Dispatcher
 * Parses URI and maps to Controller/Action/Params
 */

class Router {
    public static function dispatch(): void {
        require_once __DIR__ . '/../config/routes.php';

        $uri = $_GET['url'] ?? '';
        $uri = trim($uri, '/');

        // Check matching route aliases in routes.php
        foreach ($routes as $routePattern => $target) {
            $pattern = str_replace('(:num)', '([0-9]+)', $routePattern);
            $pattern = str_replace('(:any)', '([a-zA-Z0-9\-_]+)', $pattern);
            $pattern = "^" . $pattern . "$";

            if (preg_match("#$pattern#", $uri, $matches)) {
                array_shift($matches); // remove full match
                $targetParts = explode('/', $target);
                $controllerName = ucfirst($targetParts[0]);
                $methodName = $targetParts[1] ?? 'index';

                // Replace placeholders in method or params
                foreach ($matches as $index => $value) {
                    $methodName = str_replace('$' . ($index + 1), $value, $methodName);
                }

                self::execute($controllerName, $methodName, $matches);
                return;
            }
        }

        // Standard controller/method/params fallback
        $segments = explode('/', $uri);
        $controllerName = !empty($segments[0]) ? ucfirst($segments[0]) : 'Dashboard';
        $methodName = !empty($segments[1]) ? $segments[1] : 'index';
        $params = array_slice($segments, 2);

        self::execute($controllerName, $methodName, $params);
    }

    private static function execute(string $controllerName, string $methodName, array $params = []): void {
        $controllerFile = __DIR__ . "/../controllers/{$controllerName}.php";
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            if (class_exists($controllerName)) {
                $controllerObj = new $controllerName();
                if (method_exists($controllerObj, $methodName)) {
                    call_user_func_array([$controllerObj, $methodName], $params);
                    return;
                }
            }
        }

        // 404 Error
        http_response_code(404);
        echo "<h2 style='color:red; font-family:sans-serif; text-align:center; margin-top:50px;'>404 - Page Not Found</h2>";
        echo "<p style='text-align:center;'><a href='" . BASE_URL . "/dashboard'>Return to Dashboard</a></p>";
        exit;
    }
}
