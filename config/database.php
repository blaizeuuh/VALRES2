<?php
// config/database.php - Configuration et initialisation de la base de données

$conn = new mysqli('localhost', 'root', '', '');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Classe pour gérer les réservations
class ReservationManager {
    
    public function getAllReservations($filtreEtat = '', $filtreSalle = '') {
    }
    
    public function createReservation($data) {
    }
    
    public function updateEtatReservation($id, $nouvelEtat) {
    }
    
    public function deleteReservation($id) {
    }
    
    public function checkDisponibilite($salle, $date, $heureDebut, $heureFin, $excludeId = null) {
    }
    
    public function getDisponibilites($date) {
    }
    
    public function getReservationsBySalleAndDate($salle, $date) {
    }
    
    public function getSalles() {
    }
    
    public function getReservationsConfirmeesParPeriode($dateDebut, $dateFin) {
    }
}
?>