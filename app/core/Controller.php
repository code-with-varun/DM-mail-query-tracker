<?php
/**
 * Base Controller Class
 * Manages Auth Check, Model Instantiation, View Rendering
 */

class Controller {
    protected array $models = [];

    public function __construct() {
        Session::init();
    }

    /**
     * Load Model dynamically CodeIgniter style
     */
    protected function model(string $modelName) {
        $className = ucfirst($modelName);
        if (!isset($this->models[$className])) {
            $filePath = __DIR__ . "/../models/{$className}.php";
            if (file_exists($filePath)) {
                require_once $filePath;
                $this->models[$className] = new $className();
            } else {
                die("Model not found: {$className}");
            }
        }
        return $this->models[$className];
    }

    /**
     * Render View with Layout
     */
    protected function render(string $viewPath, array $data = [], bool $includeLayout = true): void {
        extract($data);

        $viewFile = __DIR__ . "/../views/{$viewPath}.php";
        if (!file_exists($viewFile)) {
            die("View file not found: {$viewPath}");
        }

        if ($includeLayout) {
            require __DIR__ . "/../views/layout/header.php";
            require __DIR__ . "/../views/layout/sidebar.php";
            require __DIR__ . "/../views/layout/navbar.php";
            require $viewFile;
            require __DIR__ . "/../views/layout/footer.php";
        } else {
            require $viewFile;
        }
    }

    /**
     * Return JSON Response
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Require Authentication Guard
     */
    protected function requireAuth(): void {
        if (!Session::has('user_id')) {
            Session::setFlash('warning', 'Please login to access the system.');
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }

    /**
     * Require Specific Roles
     */
    protected function requireRole(array $allowedRoles): void {
        $this->requireAuth();
        $userRole = Session::get('role_id');
        if (!in_array($userRole, $allowedRoles)) {
            Session::setFlash('danger', 'Unauthorized access to this module.');
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }
}
