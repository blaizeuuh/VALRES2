<?php
/**
 * Modèle Salle
 */

class Salle {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Récupère toutes les salles actives
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM salles WHERE active = 1 ORDER BY nom");
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère une salle par ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM salles WHERE id = ? AND active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupère les salles disponibles sur une période
     */
    public function getDisponibles($date_debut, $date_fin) {
        $stmt = $this->db->prepare("
            SELECT s.* FROM salles s
            WHERE s.active = 1
            AND s.id NOT IN (
                SELECT r.salle_id FROM reservations r
                WHERE r.etat IN (?, ?)
                AND (
                    (r.date_debut <= ? AND r.date_fin > ?) OR
                    (r.date_debut < ? AND r.date_fin >= ?) OR
                    (r.date_debut >= ? AND r.date_fin <= ?)
                )
            )
            ORDER BY s.nom
        ");
        
        $stmt->execute([
            ETAT_CONFIRME, ETAT_PROVISOIRE,
            $date_debut, $date_debut,
            $date_fin, $date_fin,
            $date_debut, $date_fin
        ]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère le planning d'une salle
     */
    public function getPlanning($id, $date_debut, $date_fin) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.nom as user_nom, u.prenom as user_prenom
            FROM reservations r
            JOIN utilisateurs u ON r.utilisateur_id = u.id
            WHERE r.salle_id = ?
            AND r.etat IN (?, ?)
            AND DATE(r.date_debut) BETWEEN ? AND ?
            ORDER BY r.date_debut ASC
        ");
        
        $stmt->execute([$id, ETAT_CONFIRME, ETAT_PROVISOIRE, $date_debut, $date_fin]);
        return $stmt->fetchAll();
    }
}
?>