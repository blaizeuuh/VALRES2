<?php
/**
 * Modèle Utilisateur
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Récupère un utilisateur par son email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Récupère un utilisateur par son ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE id = ? AND actif = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Crée un nouvel utilisateur
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, actif, date_creation) 
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        
        return $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['email'],
            password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
            $data['role']
        ]);
    }
    
    /**
     * Met à jour un utilisateur
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach (['nom', 'prenom', 'email', 'role'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (isset($data['mot_de_passe'])) {
            $fields[] = "mot_de_passe = ?";
            $params[] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }
        
        $params[] = $id;
        
        $sql = "UPDATE utilisateurs SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
    
    /**
     * Désactive un utilisateur
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE utilisateurs SET actif = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Récupère tous les utilisateurs actifs
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM utilisateurs WHERE actif = 1 ORDER BY nom, prenom");
        return $stmt->fetchAll();
    }
}
?>