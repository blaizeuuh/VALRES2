<?php
/**
 * Contrôleur de base pour l'application VALRES2
 */

abstract class BaseController {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->checkSession();
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     */
    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth', 'login');
        }
    }
    
    /**
     * Vérifie si l'utilisateur a le rôle requis
     */
    protected function requireRole($requiredRole) {
        $this->requireAuth();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $requiredRole) {
            $this->redirect('home', 'error', ['message' => 'Accès non autorisé']);
        }
    }
    
    /**
     * Redirige vers une action
     */
    protected function redirect($controller, $action = 'index', $params = []) {
        $url = 'index.php?controller=' . $controller . '&action=' . $action;
        
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Charge une vue
     */
    protected function render($view, $data = []) {
        extract($data);
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new Exception("Vue non trouvée : $view");
        }
    }
    
    /**
     * Vérifie la validité de la session
     */
    private function checkSession() {
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
                session_destroy();
                session_start();
            }
        }
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Sécurise les données d'entrée
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Génère un token CSRF
     */
    protected function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifie le token CSRF
     */
    protected function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>