<?php
// config/database.php - Configuration et initialisation de la base de données

class Database {
    private $db;
    private $dbPath = __DIR__ . '/../data/valres2.db';
    
    public function __construct() {
        $this->initDatabase();
    }
    
    private function initDatabase() {
        // Créer le dossier data s'il n'existe pas
        $dataDir = dirname($this->dbPath);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        try {
            $this->db = new PDO('sqlite:' . $this->dbPath);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTables();
            $this->insertSampleData();
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }
    
    private function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS reservations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            salle VARCHAR(50) NOT NULL,
            date DATE NOT NULL,
            heure_debut TIME NOT NULL,
            heure_fin TIME NOT NULL,
            responsable VARCHAR(100) NOT NULL,
            motif TEXT NOT NULL,
            etat VARCHAR(20) DEFAULT 'Provisoire',
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS salles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom VARCHAR(50) NOT NULL UNIQUE,
            capacite INTEGER,
            equipements TEXT,
            actif BOOLEAN DEFAULT 1
        );
        ";
        
        $this->db->exec($sql);
    }
    
    private function insertSampleData() {
        // Vérifier si des salles existent déjà
        $count = $this->db->query("SELECT COUNT(*) FROM salles")->fetchColumn();
        
        if ($count == 0) {
            // Insérer les salles
            $salles = [
                ['Salle A', 20, 'Projecteur, Tableau'],
                ['Salle B', 15, 'Projecteur'],
                ['Salle C', 25, 'Projecteur, Tableau, Ordinateurs'],
                ['Amphithéâtre', 100, 'Projecteur, Micro, Sonorisation']
            ];
            
            $stmt = $this->db->prepare("INSERT INTO salles (nom, capacite, equipements) VALUES (?, ?, ?)");
            foreach ($salles as $salle) {
                $stmt->execute($salle);
            }
            
            // Insérer quelques réservations de démonstration
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            
            $reservationsDemo = [
                ['Salle A', $today, '09:00', '11:00', 'Jean Dupont', 'Réunion équipe développement', 'Confirmé'],
                ['Amphithéâtre', $tomorrow, '14:00', '16:00', 'Marie Martin', 'Présentation client', 'Provisoire'],
                ['Salle B', $today, '15:00', '17:00', 'Pierre Durand', 'Formation interne', 'Provisoire']
            ];
            
            $stmt = $this->db->prepare("INSERT INTO reservations (salle, date, heure_debut, heure_fin, responsable, motif, etat) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($reservationsDemo as $reservation) {
                $stmt->execute($reservation);
            }
        }
    }
    
    public function getConnection() {
        return $this->db;
    }
}

// Classe pour gérer les réservations
class ReservationManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    public function getAllReservations($filtreEtat = '', $filtreSalle = '') {
        $sql = "SELECT * FROM reservations WHERE 1=1";
        $params = [];
        
        if (!empty($filtreEtat)) {
            $sql .= " AND etat = ?";
            $params[] = $filtreEtat;
        }
        
        if (!empty($filtreSalle)) {
            $sql .= " AND salle = ?";
            $params[] = $filtreSalle;
        }
        
        $sql .= " ORDER BY date, heure_debut";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createReservation($data) {
        // Vérifier la disponibilité
        if (!$this->checkDisponibilite($data['salle'], $data['date'], $data['heure_debut'], $data['heure_fin'])) {
            return ['success' => false, 'message' => 'La salle n\'est pas disponible sur ce créneau !'];
        }
        
        $sql = "INSERT INTO reservations (salle, date, heure_debut, heure_fin, responsable, motif) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([
                $data['salle'],
                $data['date'],
                $data['heure_debut'],
                $data['heure_fin'],
                $data['responsable'],
                $data['motif']
            ]);
            return ['success' => true, 'message' => 'Réservation créée avec succès !'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la création : ' . $e->getMessage()];
        }
    }
    
    public function updateEtatReservation($id, $nouvelEtat) {
        $sql = "UPDATE reservations SET etat = ?, date_modification = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([$nouvelEtat, $id]);
            return ['success' => true, 'message' => "Réservation {$nouvelEtat} !"];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()];
        }
    }
    
    public function deleteReservation($id) {
        $sql = "DELETE FROM reservations WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Réservation supprimée avec succès !'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()];
        }
    }
    
    public function checkDisponibilite($salle, $date, $heureDebut, $heureFin, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM reservations 
                WHERE salle = ? AND date = ? AND etat != 'Annulé'
                AND ((heure_debut < ? AND heure_fin > ?) OR (heure_debut < ? AND heure_fin > ?))";
        $params = [$salle, $date, $heureFin, $heureDebut, $heureDebut, $heureFin];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }
    
    public function getDisponibilites($date) {
        $salles = $this->getSalles();
        $disponibilites = [];
        
        foreach ($salles as $salle) {
            $reservations = $this->getReservationsBySalleAndDate($salle['nom'], $date);
            $disponibilites[] = [
                'salle' => $salle['nom'],
                'reservations' => $reservations
            ];
        }
        
        return $disponibilites;
    }
    
    public function getReservationsBySalleAndDate($salle, $date) {
        $sql = "SELECT * FROM reservations WHERE salle = ? AND date = ? AND etat != 'Annulé' ORDER BY heure_debut";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$salle, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSalles() {
        $sql = "SELECT * FROM salles WHERE actif = 1 ORDER BY nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getReservationsConfirmeesParPeriode($dateDebut, $dateFin) {
        $sql = "SELECT * FROM reservations WHERE etat = 'Confirmé' AND date BETWEEN ? AND ? ORDER BY date, heure_debut";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateDebut, $dateFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>