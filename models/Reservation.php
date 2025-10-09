<?php
/**
 * Modèle Réservation
 */

class Reservation {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crée une nouvelle réservation
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO reservations (salle_id, utilisateur_id, date_debut, date_fin, objet, etat, date_creation) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $data['salle_id'],
            $data['utilisateur_id'],
            $data['date_debut'],
            $data['date_fin'],
            $data['objet'],
            $data['etat']
        ]);
    }
    
    /**
     * Met à jour une réservation
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE reservations 
            SET salle_id = ?, date_debut = ?, date_fin = ?, objet = ?, etat = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['salle_id'],
            $data['date_debut'],
            $data['date_fin'],
            $data['objet'],
            $data['etat'],
            $id
        ]);
    }
    
    /**
     * Supprime une réservation
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Récupère une réservation par ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT r.*, s.nom as salle_nom, u.nom as user_nom, u.prenom as user_prenom
            FROM reservations r
            JOIN salles s ON r.salle_id = s.id
            JOIN utilisateurs u ON r.utilisateur_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupère les réservations d'un utilisateur
     */
    public function getByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT r.*, s.nom as salle_nom
            FROM reservations r
            JOIN salles s ON r.salle_id = s.id
            WHERE r.utilisateur_id = ?
            ORDER BY r.date_debut DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les réservations par état
     */
    public function getByEtat($etat) {
        $stmt = $this->db->prepare("
            SELECT r.*, s.nom as salle_nom, u.nom as user_nom, u.prenom as user_prenom
            FROM reservations r
            JOIN salles s ON r.salle_id = s.id
            JOIN utilisateurs u ON r.utilisateur_id = u.id
            WHERE r.etat = ?
            ORDER BY r.date_debut ASC
        ");
        $stmt->execute([$etat]);
        return $stmt->fetchAll();
    }
    
    /**
     * Vérifie les conflits de réservation
     */
    public function checkConflict($salle_id, $date_debut, $date_fin, $exclude_id = null) {
        $sql = "
            SELECT COUNT(*) as conflicts FROM reservations 
            WHERE salle_id = ? 
            AND etat IN (?, ?)
            AND (
                (date_debut <= ? AND date_fin > ?) OR
                (date_debut < ? AND date_fin >= ?) OR
                (date_debut >= ? AND date_fin <= ?)
            )
        ";
        
        $params = [
            $salle_id,
            ETAT_CONFIRME, ETAT_PROVISOIRE,
            $date_debut, $date_debut,
            $date_fin, $date_fin,
            $date_debut, $date_fin
        ];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['conflicts'] > 0;
    }
    
    /**
     * Change l'état d'une réservation
     */
    public function changeEtat($id, $nouvel_etat) {
        $stmt = $this->db->prepare("UPDATE reservations SET etat = ? WHERE id = ?");
        return $stmt->execute([$nouvel_etat, $id]);
    }
}
?>