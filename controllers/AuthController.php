<?php
/**
 * Contrôleur d'authentification
 */

require_once 'BaseController.php';

class AuthController extends BaseController {
    
    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('home', 'dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
        } else {
            $data = [
                'title' => 'Connexion - ' . APP_NAME,
                'csrf_token' => $this->generateCSRFToken()
            ];
            $this->render('auth/login', $data);
        }
    }
    
    public function logout() {
        session_destroy();
        session_start();
        $this->redirect('home', 'index');
    }
    
    private function processLogin() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->redirect('auth', 'login', ['error' => 'Token invalide']);
        }
        
        $email = $this->sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $this->redirect('auth', 'login', ['error' => 'Tous les champs sont requis']);
        }
        
        // Vérification des identifiants
        $stmt = $this->db->prepare("SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            $this->redirect('home', 'dashboard');
        } else {
            $this->redirect('auth', 'login', ['error' => 'Identifiants invalides']);
        }
    }
}
?>