<?php
/**
 * Contrôleur principal de l'application
 */

require_once 'BaseController.php';

class HomeController extends BaseController {
    
    public function index() {
        $data = [
            'title' => 'Accueil - ' . APP_NAME,
            'user' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? null
        ];
        
        $this->render('home/index', $data);
    }
    
    public function dashboard() {
        $this->requireAuth();
        
        $role = $_SESSION['user_role'];
        
        switch ($role) {
            case ROLE_ADMIN:
                $this->redirect('admin', 'dashboard');
                break;
            case ROLE_SECRETARIAT:
                $this->redirect('reservation', 'dashboard');
                break;
            case ROLE_RESPONSABLE:
                $this->redirect('reservation', 'mes_reservations');
                break;
            case ROLE_USER:
                $this->redirect('reservation', 'consulter');
                break;
            default:
                $this->redirect('home', 'index');
        }
    }
    
    public function error() {
        $message = $_GET['message'] ?? 'Une erreur est survenue';
        
        $data = [
            'title' => 'Erreur - ' . APP_NAME,
            'message' => $this->sanitize($message)
        ];
        
        $this->render('errors/error', $data);
    }
}
?>