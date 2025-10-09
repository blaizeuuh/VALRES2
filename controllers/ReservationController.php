<?php
/**
 * CONTRÔLEUR RÉSERVATIONS
 * Partie attribuée à l'Étudiant 2
 * 
 * Fonctionnalités :
 * - Gestion des réservations (CRUD)
 * - Confirmation/annulation des réservations
 * - Consultation des salles disponibles
 * - Génération XML des réservations
 */

require_once 'BaseController.php';

class ReservationController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        // Tous les utilisateurs connectés peuvent accéder aux réservations
        $this->requireAuth();
    }
    
    /**
     * Tableau de bord réservations (pour secrétariat)
     */
    public function dashboard() {
        $this->requireRole(ROLE_SECRETARIAT);
        
        // Réservations en attente de validation
        $stmt = $this->db->query("
            SELECT r.*, s.nom as salle_nom, u.nom as user_nom, u.prenom as user_prenom
            FROM reservations r
            JOIN salles s ON r.salle_id = s.id
            JOIN utilisateurs u ON r.utilisateur_id = u.id
            WHERE r.etat = '" . ETAT_PROVISOIRE . "'
            ORDER BY r.date_debut ASC
        ");
        $reservations_attente = $stmt->fetchAll();
        
        $data = [
            'title' => 'Gestion des réservations - ' . APP_NAME,
            'reservations_attente' => $reservations_attente
        ];
        
        $this->render('reservation/dashboard', $data);
    }
    
    /**
     * Consultation des salles disponibles
     */
    public function consulter() {
        $date = $_GET['date'] ?? date('Y-m-d');
        $heure_debut = $_GET['heure_debut'] ?? '';
        $heure_fin = $_GET['heure_fin'] ?? '';
        
        // Liste des salles
        $stmt = $this->db->query("SELECT * FROM salles WHERE active = 1 ORDER BY nom");
        $salles = $stmt->fetchAll();
        
        // Si une recherche est effectuée
        $salles_disponibles = [];
        if (!empty($heure_debut) && !empty($heure_fin)) {
            $salles_disponibles = $this->getSallesDisponibles($date, $heure_debut, $heure_fin);
        }
        
        $data = [
            'title' => 'Consultation des salles - ' . APP_NAME,
            'salles' => $salles,
            'salles_disponibles' => $salles_disponibles,
            'date' => $date,
            'heure_debut' => $heure_debut,
            'heure_fin' => $heure_fin
        ];
        
        $this->render('reservation/consulter', $data);
    }
    
    /**
     * Mes réservations (pour responsables)
     */
    public function mes_reservations() {
        $this->requireRole(ROLE_RESPONSABLE);
        
        $stmt = $this->db->prepare("
            SELECT r.*, s.nom as salle_nom
            FROM reservations r
            JOIN salles s ON r.salle_id = s.id
            WHERE r.utilisateur_id = ?
            ORDER BY r.date_debut DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $reservations = $stmt->fetchAll();
        
        $data = [
            'title' => 'Mes réservations - ' . APP_NAME,
            'reservations' => $reservations
        ];
        
        $this->render('reservation/mes_reservations', $data);
    }
    
    /**
     * Ajouter une réservation
     */
    public function ajouter() {
        // Seuls les responsables et le secrétariat peuvent réserver
        if (!in_array($_SESSION['user_role'], [ROLE_RESPONSABLE, ROLE_SECRETARIAT])) {
            $this->redirect('home', 'error', ['message' => 'Accès non autorisé']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processAjouterReservation();
        } else {
            // Liste des salles disponibles
            $stmt = $this->db->query("SELECT * FROM salles WHERE active = 1 ORDER BY nom");
            $salles = $stmt->fetchAll();
            
            $data = [
                'title' => 'Nouvelle réservation - ' . APP_NAME,
                'salles' => $salles,
                'csrf_token' => $this->generateCSRFToken()
            ];
            
            $this->render('reservation/ajouter', $data);
        }
    }
    
    /**
     * Modifier une réservation
     */
    public function modifier() {
        $id = intval($_GET['id'] ?? 0);
        
        // Vérification des droits
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        $reservation = $stmt->fetch();
        
        if (!$reservation) {
            $this->redirect('reservation', 'mes_reservations', ['error' => 'Réservation non trouvée']);
        }
        
        // Seul le propriétaire ou le secrétariat peut modifier
        if ($reservation['utilisateur_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] != ROLE_SECRETARIAT) {
            $this->redirect('reservation', 'mes_reservations', ['error' => 'Accès non autorisé']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processModifierReservation($id);
        } else {
            // Liste des salles
            $stmt = $this->db->query("SELECT * FROM salles WHERE active = 1 ORDER BY nom");
            $salles = $stmt->fetchAll();
            
            $data = [
                'title' => 'Modifier la réservation - ' . APP_NAME,
                'reservation' => $reservation,
                'salles' => $salles,
                'csrf_token' => $this->generateCSRFToken()
            ];
            
            $this->render('reservation/modifier', $data);
        }
    }
    
    /**
     * Supprimer une réservation
     */
    public function supprimer() {
        $id = intval($_GET['id'] ?? 0);
        
        // Vérification des droits
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        $reservation = $stmt->fetch();
        
        if (!$reservation) {
            $this->redirect('reservation', 'mes_reservations', ['error' => 'Réservation non trouvée']);
        }
        
        // Seul le propriétaire ou le secrétariat peut supprimer
        if ($reservation['utilisateur_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] != ROLE_SECRETARIAT) {
            $this->redirect('reservation', 'mes_reservations', ['error' => 'Accès non autorisé']);
        }
        
        $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = ?");
        if ($stmt->execute([$id])) {
            $redirect = ($_SESSION['user_role'] == ROLE_SECRETARIAT) ? 'dashboard' : 'mes_reservations';
            $this->redirect('reservation', $redirect, ['success' => 'Réservation supprimée avec succès']);
        } else {
            $redirect = ($_SESSION['user_role'] == ROLE_SECRETARIAT) ? 'dashboard' : 'mes_reservations';
            $this->redirect('reservation', $redirect, ['error' => 'Erreur lors de la suppression']);
        }
    }
    
    /**
     * Confirmer une réservation (secrétariat uniquement)
     */
    public function confirmer() {
        $this->requireRole(ROLE_SECRETARIAT);
        
        $id = intval($_GET['id'] ?? 0);
        
        $stmt = $this->db->prepare("UPDATE reservations SET etat = ? WHERE id = ?");
        if ($stmt->execute([ETAT_CONFIRME, $id])) {
            $this->redirect('reservation', 'dashboard', ['success' => 'Réservation confirmée']);
        } else {
            $this->redirect('reservation', 'dashboard', ['error' => 'Erreur lors de la confirmation']);
        }
    }
    
    /**
     * Annuler une réservation (secrétariat uniquement)
     */
    public function annuler() {
        $this->requireRole(ROLE_SECRETARIAT);
        
        $id = intval($_GET['id'] ?? 0);
        
        $stmt = $this->db->prepare("UPDATE reservations SET etat = ? WHERE id = ?");
        if ($stmt->execute([ETAT_ANNULE, $id])) {
            $this->redirect('reservation', 'dashboard', ['success' => 'Réservation annulée']);
        } else {
            $this->redirect('reservation', 'dashboard', ['error' => 'Erreur lors de l\'annulation']);
        }
    }
    
    /**
     * Génération XML des réservations validées d'une semaine
     */
    public function generer_xml_reservations() {
        $this->requireRole(ROLE_SECRETARIAT);
        
        $semaine_debut = $_GET['semaine'] ?? date('Y-m-d', strtotime('monday this week'));
        $semaine_fin = date('Y-m-d', strtotime($semaine_debut . ' +6 days'));
        
        $stmt = $this->db->prepare("
            SELECT r.*, s.nom as salle_nom, s.capacite, u.nom as user_nom, u.prenom as user_prenom
            FROM reservations r
            JOIN salles s ON r.salle_id = s.id
            JOIN utilisateurs u ON r.utilisateur_id = u.id
            WHERE r.etat = ? 
            AND DATE(r.date_debut) BETWEEN ? AND ?
            ORDER BY r.date_debut ASC
        ");
        $stmt->execute([ETAT_CONFIRME, $semaine_debut, $semaine_fin]);
        $reservations = $stmt->fetchAll();
        
        // Génération du XML
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        $root = $xml->createElement('reservations_semaine');
        $root->setAttribute('semaine_debut', $semaine_debut);
        $root->setAttribute('semaine_fin', $semaine_fin);
        $root->setAttribute('date_generation', date('Y-m-d H:i:s'));
        $xml->appendChild($root);
        
        foreach ($reservations as $res) {
            $resElement = $xml->createElement('reservation');
            
            $resElement->appendChild($xml->createElement('id', $res['id']));
            $resElement->appendChild($xml->createElement('salle', htmlspecialchars($res['salle_nom'])));
            $resElement->appendChild($xml->createElement('capacite', $res['capacite']));
            $resElement->appendChild($xml->createElement('responsable', htmlspecialchars($res['user_prenom'] . ' ' . $res['user_nom'])));
            $resElement->appendChild($xml->createElement('date_debut', $res['date_debut']));
            $resElement->appendChild($xml->createElement('date_fin', $res['date_fin']));
            $resElement->appendChild($xml->createElement('objet', htmlspecialchars($res['objet'])));
            $resElement->appendChild($xml->createElement('etat', $res['etat']));
            
            $root->appendChild($resElement);
        }
        
        // Sauvegarde du fichier
        $filename = 'reservations_semaine_' . $semaine_debut . '.xml';
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
    
    private function getSallesDisponibles($date, $heure_debut, $heure_fin) {
        $datetime_debut = $date . ' ' . $heure_debut;
        $datetime_fin = $date . ' ' . $heure_fin;
        
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
            $datetime_debut, $datetime_debut,
            $datetime_fin, $datetime_fin,
            $datetime_debut, $datetime_fin
        ]);
        
        return $stmt->fetchAll();
    }
    
    private function processAjouterReservation() {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->redirect('reservation', 'ajouter', ['error' => 'Token invalide']);
        }
        
        $salle_id = intval($_POST['salle_id'] ?? 0);
        $date_debut = $this->sanitize($_POST['date_debut'] ?? '');
        $heure_debut = $this->sanitize($_POST['heure_debut'] ?? '');
        $date_fin = $this->sanitize($_POST['date_fin'] ?? '');
        $heure_fin = $this->sanitize($_POST['heure_fin'] ?? '');
        $objet = $this->sanitize($_POST['objet'] ?? '');
        
        // Validation
        if (empty($salle_id) || empty($date_debut) || empty($heure_debut) || empty($date_fin) || empty($heure_fin) || empty($objet)) {
            $this->redirect('reservation', 'ajouter', ['error' => 'Tous les champs sont requis']);
        }
        
        $datetime_debut = $date_debut . ' ' . $heure_debut;
        $datetime_fin = $date_fin . ' ' . $heure_fin;
        
        // Vérification de disponibilité
        if (!$this->verifierDisponibilite($salle_id, $datetime_debut, $datetime_fin)) {
            $this->redirect('reservation', 'ajouter', ['error' => 'La salle n\'est pas disponible sur ce créneau']);
        }
        
        // État initial selon le rôle
        $etat = ($_SESSION['user_role'] == ROLE_SECRETARIAT) ? ETAT_CONFIRME : ETAT_PROVISOIRE;
        
        $stmt = $this->db->prepare("
            INSERT INTO reservations (salle_id, utilisateur_id, date_debut, date_fin, objet, etat, date_creation) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$salle_id, $_SESSION['user_id'], $datetime_debut, $datetime_fin, $objet, $etat])) {
            $redirect = ($_SESSION['user_role'] == ROLE_SECRETARIAT) ? 'dashboard' : 'mes_reservations';
            $this->redirect('reservation', $redirect, ['success' => 'Réservation créée avec succès']);
        } else {
            $this->redirect('reservation', 'ajouter', ['error' => 'Erreur lors de la création']);
        }
    }
    
    private function processModifierReservation($id) {
        if (!$this->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->redirect('reservation', 'modifier', ['id' => $id, 'error' => 'Token invalide']);
        }
        
        $salle_id = intval($_POST['salle_id'] ?? 0);
        $date_debut = $this->sanitize($_POST['date_debut'] ?? '');
        $heure_debut = $this->sanitize($_POST['heure_debut'] ?? '');
        $date_fin = $this->sanitize($_POST['date_fin'] ?? '');
        $heure_fin = $this->sanitize($_POST['heure_fin'] ?? '');
        $objet = $this->sanitize($_POST['objet'] ?? '');
        
        $datetime_debut = $date_debut . ' ' . $heure_debut;
        $datetime_fin = $date_fin . ' ' . $heure_fin;
        
        // Vérification de disponibilité (en excluant la réservation actuelle)
        if (!$this->verifierDisponibilite($salle_id, $datetime_debut, $datetime_fin, $id)) {
            $this->redirect('reservation', 'modifier', ['id' => $id, 'error' => 'La salle n\'est pas disponible sur ce créneau']);
        }
        
        $stmt = $this->db->prepare("
            UPDATE reservations 
            SET salle_id = ?, date_debut = ?, date_fin = ?, objet = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$salle_id, $datetime_debut, $datetime_fin, $objet, $id])) {
            $redirect = ($_SESSION['user_role'] == ROLE_SECRETARIAT) ? 'dashboard' : 'mes_reservations';
            $this->redirect('reservation', $redirect, ['success' => 'Réservation modifiée avec succès']);
        } else {
            $this->redirect('reservation', 'modifier', ['id' => $id, 'error' => 'Erreur lors de la modification']);
        }
    }
    
    private function verifierDisponibilite($salle_id, $datetime_debut, $datetime_fin, $exclude_id = null) {
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
            $datetime_debut, $datetime_debut,
            $datetime_fin, $datetime_fin,
            $datetime_debut, $datetime_fin
        ];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['conflicts'] == 0;
    }
}
?>