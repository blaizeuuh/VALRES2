<?php
/**
 * CONTRÔLEUR ADMINISTRATION
 * Partie attribuée à l'Étudiant 1
 * 
 * Fonctionnalités :
 * - Gestion des accès utilisateurs (CRUD)
 * - Consultation des réservations
 * - Génération XML des utilisateurs
 */

require_once 'BaseController.php';

class AdminController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        // Seuls les administrateurs peuvent accéder à cette section
        $this->requireRole(ROLE_ADMIN);
    }
    
    /**
     * Tableau de bord administrateur
     */
    public function dashboard() {
        // Statistiques générales
        $stats = $this->getStatistics();
        
        $data = [
            'title' => 'Administration - ' . APP_NAME,
            'stats' => $stats
        ];
        
        $this->render('admin/dashboard', $data);
    }
    
    /**
     * Gestion des utilisateurs - Liste
     */
    public function utilisateurs() {
        $stmt = $this->db->query("
            SELECT id, nom, prenom, email, role, actif, date_creation 
            FROM utilisateurs 
            ORDER BY nom, prenom
        ");
        $utilisateurs = $stmt->fetchAll();
        
        $data = [
            'title' => 'Gestion des utilisateurs - ' . APP_NAME,
            'utilisateurs' => $utilisateurs
        ];
        
        $this->render('admin/utilisateurs/liste', $data);
    }
    
    /**
     * Ajouter un utilisateur
     */
    public function ajouter_utilisateur() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processAjouterUtilisateur();
        } else {
            $data = [
                'title' => 'Ajouter un utilisateur - ' . APP_NAME,
                'csrf_token' => $this->generateCSRFToken()
            ];
            $this->render('admin/utilisateurs/ajouter', $data);
        }
    }
    
    /**
     * Modifier un utilisateur
     */
    public function modifier_utilisateur() {
        $id = intval($_GET['id'] ?? 0);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processModifierUtilisateur($id);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
            $stmt->execute([$id]);
            $utilisateur = $stmt->fetch();
            
            if (!$utilisateur) {
                $this->redirect('admin', 'utilisateurs', ['error' => 'Utilisateur non trouvé']);
            }
            
            $data = [
                'title' => 'Modifier un utilisateur - ' . APP_NAME,
                'utilisateur' => $utilisateur,
                'csrf_token' => $this->generateCSRFToken()
            ];
            $this->render('admin/utilisateurs/modifier', $data);
        }
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function supprimer_utilisateur() {
        $id = intval($_GET['id'] ?? 0);
        
        if ($id === $_SESSION['user_id']) {
            $this->redirect('admin', 'utilisateurs', ['error' => 'Vous ne pouvez pas supprimer votre propre compte']);
        }
        
        $stmt = $this->db->prepare("UPDATE utilisateurs SET actif = 0 WHERE id = ?");
        if ($stmt->execute([$id])) {
            $this->redirect('admin', 'utilisateurs', ['success' => 'Utilisateur désactivé avec succès']);
        } else {
            $this->redirect('admin', 'utilisateurs', ['error' => 'Erreur lors de la désactivation']);
        }
    }
    
    /**
     * Consultation des réservations
     */
    public function reservations() {
        $filtre = $_GET['filtre'] ?? 'toutes';
        $sql = "
            SELECT r.*, s.nom as salle_nom, u.nom as user_nom, u.prenom as user_prenom
            FROM reservations r
            JOIN salles s ON r.salle_id = s.id
            JOIN utilisateurs u ON r.utilisateur_id = u.id
        ";
        
        switch ($filtre) {
            case 'provisoires':
                $sql .= " WHERE r.etat = '" . ETAT_PROVISOIRE . "'";
                break;
            case 'confirmees':
                $sql .= " WHERE r.etat = '" . ETAT_CONFIRME . "'";
                break;
            case 'annulees':
                $sql .= " WHERE r.etat = '" . ETAT_ANNULE . "'";
                break;
        }
        
        $sql .= " ORDER BY r.date_debut DESC";
        
        $stmt = $this->db->query($sql);
        $reservations = $stmt->fetchAll();
        
        $data = [
            'title' => 'Consultation des réservations - ' . APP_NAME,
            'reservations' => $reservations,
            'filtre' => $filtre
        ];
        
        $this->render('admin/reservations/consulter', $data);
    }
    
    /**
     * Génération XML des utilisateurs
     */
    public function generer_xml_utilisateurs() {
        $stmt = $this->db->query("
            SELECT nom, prenom, email, role, date_creation 
            FROM utilisateurs 
            WHERE actif = 1 
            ORDER BY nom, prenom
        ");
        $utilisateurs = $stmt->fetchAll();
        
        // Génération du XML
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        $root = $xml->createElement('utilisateurs');
        $root->setAttribute('date_generation', date('Y-m-d H:i:s'));
        $xml->appendChild($root);
        
        foreach ($utilisateurs as $user) {
            $userElement = $xml->createElement('utilisateur');
            
            $userElement->appendChild($xml->createElement('nom', htmlspecialchars($user['nom'])));
            $userElement->appendChild($xml->createElement('prenom', htmlspecialchars($user['prenom'])));
            $userElement->appendChild($xml->createElement('email', htmlspecialchars($user['email'])));
            $userElement->appendChild($xml->createElement('role', htmlspecialchars($user['role'])));
            $userElement->appendChild($xml->createElement('date_creation', $user['date_creation']));
            
            $root->appendChild($userElement);
        }
        
        // Sauvegarde du fichier
        $filename = 'utilisateurs_' . date('Y-m-d_H-i-s') . '.xml';
        $filepath = EXPORTS_PATH . '/' . $filename;
        $xml->save($filepath);
        
        // Téléchargement
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
    
    // Méthodes privées
    
    private function getStatistics() {
        $stats = [];
        
        // Nombre d'utilisateurs actifs
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM utilisateurs WHERE actif = 1");
        $stats['utilisateurs_actifs'] = $stmt->fetch()['total'];
        
        // Nombre de réservations du mois
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM reservations 
            WHERE MONTH(date_creation) = MONTH(CURRENT_DATE()) 
            AND YEAR(date_creation) = YEAR(CURRENT_DATE())
        ");
        $stats['reservations_mois'] = $stmt->fetch()['total'];
        
        // Réservations en attente
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM reservations WHERE etat = '" . ETAT_PROVISOIRE . "'");
        $stats['reservations_attente'] = $stmt->fetch()['total'];
        
        return $stats;
    }
    
    private function processAjouterUtilisateur() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin', 'ajouter_utilisateur', ['error' => 'Token invalide']);
        }
        
        $nom = $this->sanitize($_POST['nom'] ?? '');
        $prenom = $this->sanitize($_POST['prenom'] ?? '');
        $email = $this->sanitize($_POST['email'] ?? '');
        $role = $this->sanitize($_POST['role'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';
        
        // Validation
        if (empty($nom) || empty($prenom) || empty($email) || empty($role) || empty($mot_de_passe)) {
            $this->redirect('admin', 'ajouter_utilisateur', ['error' => 'Tous les champs sont requis']);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('admin', 'ajouter_utilisateur', ['error' => 'Email invalide']);
        }
        
        // Vérification unicité email
        $stmt = $this->db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $this->redirect('admin', 'ajouter_utilisateur', ['error' => 'Cet email existe déjà']);
        }
        
        // Insertion
        $stmt = $this->db->prepare("
            INSERT INTO utilisateurs (nom, prenom, email, role, mot_de_passe, actif, date_creation) 
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        
        if ($stmt->execute([$nom, $prenom, $email, $role, password_hash($mot_de_passe, PASSWORD_DEFAULT)])) {
            $this->redirect('admin', 'utilisateurs', ['success' => 'Utilisateur ajouté avec succès']);
        } else {
            $this->redirect('admin', 'ajouter_utilisateur', ['error' => 'Erreur lors de l\'ajout']);
        }
    }
    
    private function processModifierUtilisateur($id) {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin', 'modifier_utilisateur', ['id' => $id, 'error' => 'Token invalide']);
        }
        
        $nom = $this->sanitize($_POST['nom'] ?? '');
        $prenom = $this->sanitize($_POST['prenom'] ?? '');
        $email = $this->sanitize($_POST['email'] ?? '');
        $role = $this->sanitize($_POST['role'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';
        
        // Validation
        if (empty($nom) || empty($prenom) || empty($email) || empty($role)) {
            $this->redirect('admin', 'modifier_utilisateur', ['id' => $id, 'error' => 'Les champs obligatoires sont requis']);
        }
        
        // Mise à jour
        if (!empty($mot_de_passe)) {
            $stmt = $this->db->prepare("
                UPDATE utilisateurs 
                SET nom = ?, prenom = ?, email = ?, role = ?, mot_de_passe = ? 
                WHERE id = ?
            ");
            $params = [$nom, $prenom, $email, $role, password_hash($mot_de_passe, PASSWORD_DEFAULT), $id];
        } else {
            $stmt = $this->db->prepare("
                UPDATE utilisateurs 
                SET nom = ?, prenom = ?, email = ?, role = ? 
                WHERE id = ?
            ");
            $params = [$nom, $prenom, $email, $role, $id];
        }
        
        if ($stmt->execute($params)) {
            $this->redirect('admin', 'utilisateurs', ['success' => 'Utilisateur modifié avec succès']);
        } else {
            $this->redirect('admin', 'modifier_utilisateur', ['id' => $id, 'error' => 'Erreur lors de la modification']);
        }
    }
}
?>