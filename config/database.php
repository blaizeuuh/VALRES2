<?php
// config/database.php - Configuration et initialisation de la base de données (PDO)

// Paramètres par défaut (modifiable selon l'environnement)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', '');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

class Database {
    /** @var \PDO */
    private $pdo;

    /**
     * Database constructor.
     * @param string $host
     * @param string $dbname
     * @param string $user
     * @param string $pass
     * @throws \Exception
     */
    public function __construct($host = DB_HOST, $dbname = DB_NAME, $user = DB_USER, $pass = DB_PASS) {
        try {
            // Construire le DSN en omettant dbname si vide (connexion au serveur MySQL seulement)
            $dsn = 'mysql:host=' . $host . ';charset=utf8mb4';
            if (!empty($dbname)) {
                $dsn .= ';dbname=' . $dbname;
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // En production, on préférerait journaliser l'erreur plutôt que d'exposer les détails.
            throw new Exception('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }

    /**
     * Retourne l'instance PDO
     * @return \PDO
     */
    public function getPdo(){
        return $this->pdo;
    }

    /**
     * Helper simple pour exécuter une requête et retourner toutes les lignes.
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Helper simple pour exécuter une requête et retourner une ligne.
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function fetch(string $sql, array $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Exécute une requête (INSERT/UPDATE/DELETE) et retourne le nombre de lignes affectées.
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function execute(string $sql, array $params = []): int {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}

// Classe pour gérer les réservations
class ReservationManager {
    /** @var \PDO */
    private $pdo;

    /**
     * ReservationManager constructor.
     * Accepte une instance Database (ou null si on veut créer la DB interne)
     * @param Database|null $database
     */
    public function __construct(Database $database = null)
    {
        if ($database instanceof Database) {
            $this->pdo = $database->getPdo();
        } else {
            // Si aucune instance fournie, crée une connexion par défaut
            $db = new Database();
            $this->pdo = $db->getPdo();
        }
    }
    
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