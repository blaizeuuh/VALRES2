<?php
// config/database.php - Configuration et initialisation de la base de données (PDO)

// Paramètres par défaut (modifiable selon l'environnement)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'valres_db');
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

class UserManager {
    /** @var \PDO */
    private $pdo;

    /**
     * UserManager constructor.
     * @param Database $database
     */
    public function __construct(Database $database) {
        $this->pdo = $database->getPdo();
    }

    public function getUserByName($username) {
    }

    public function verifyLogin($nom, $password) {
        // Retourne true si les identifiants sont corrects, false sinon. Recherche dans la base de données (table utilisateur)
        $pdo = $this->pdo;
        $stmt = $pdo->prepare("SELECT mdp FROM utilisateur WHERE nom = :username");
        $stmt->execute(['username' => $nom]);
        $user = $stmt->fetch();

        // Récupérer les données de l'utilisateur
        $prenom = $user['prenom'] ?? '';
        $role = $user['acteur'] ?? '';
        $mail = $user['mail'] ?? '';
        $structure = $user['structure'] ?? '';
        $structure_adresse = $user['structure_adresse'] ?? '';


        if ($user && password_verify($password, $user['mdp'])) {
            $_SESSION['user']['username'] = $nom;
            $_SEESION['user']['prenom'] = $prenom;
            $_SESSION['user']['role'] = $role;
            $_SESSION['user']['mail'] = $mail;
            $_SESSION['user']['structure'] = $structure;
            $_SESSION['user']['structure_adresse'] = $structure_adresse;
            
            return true;
        } else {
            return false;
        }
        
    }
    }
}

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
<<<<<<< HEAD
        $this->initializeTables();
    }
    
    public function getAllReservations($filtreEtat = '', $filtreSalle = '') {
        $sql = "SELECT * FROM reservations WHERE 1=1";
        $params = [];
        
        if ($filtreEtat) {
            $sql .= " AND etat = ?";
            $params[] = $filtreEtat;
        }
        
        if ($filtreSalle) {
            $sql .= " AND salle = ?";
            $params[] = $filtreSalle;
        }
        
        $sql .= " ORDER BY date DESC, heure_debut ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
=======
    }
    
    public function getAllReservations($filtreEtat = '', $filtreSalle = '') {
>>>>>>> b25a6297a703374c77a6fc14a4615da3ea977b03
    }
    
    /**
     * Penser a heure dbut heure fin a ajouter dans la requete en sous 
     * et aussi dans la BDD
     */

    public function createReservation($data) {
<<<<<<< HEAD
        $sql = "INSERT INTO reservations (salle, date, heure, utilisateur_id, etat) 
                VALUES (:salle, :date, :heure, :utilisateur_id, :etat)";
        $params = [
            'salle' => $data['salle'],
            'date' => $data['date'],
            'heure' => $data['heure'],
            'utilisateur_id' => $data['utilisateur_id'],
            'etat' => 'en_attente'
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function updateEtatReservation($id, $nouvelEtat) {
        $sql = "UPDATE reservations SET etat = :etat WHERE id = :id";
        $params = [
            'etat' => $nouvelEtat,
            'id' => $id
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    public function deleteReservation($id) {
        $sql = "DELETE FROM reservations WHERE id = :id";
        $params = ['id' => $id];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    public function checkDisponibilite($salle, $date, $heureDebut, $heureFin, $excludeId = null) {
        $sql = "SELECT * FROM reservations WHERE salle = :salle AND date = :date 
                AND ((heure_debut < :heureFin AND heure_fin > :heureDebut))";
        $params = [
            'salle' => $salle,
            'date' => $date,
            'heureDebut' => $heureDebut,
            'heureFin' => $heureFin
            
        ];
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        return count($result) === 0;
        
=======
    }
    
    public function updateEtatReservation($id, $nouvelEtat) {
    }
    
    public function deleteReservation($id) {
    }
    
    public function checkDisponibilite($salle, $date, $heureDebut, $heureFin, $excludeId = null) {
>>>>>>> b25a6297a703374c77a6fc14a4615da3ea977b03
    }

    
    public function getDisponibilites($date) {
<<<<<<< HEAD
        $sql = "SELECT * FROM reservations WHERE date = :date AND etat = 'disponible'";
        $params = ['date' => $date];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    }
    
    public function getReservationsBySalleAndDate($salle, $date) {
        $sql = "SELECT * FROM reservations WHERE salle = :salle AND date = :date";
        $params = [
            'salle' => $salle,
            'date' => $date
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
=======
    }
    
    public function getReservationsBySalleAndDate($salle, $date) {
>>>>>>> b25a6297a703374c77a6fc14a4615da3ea977b03
    }
    
    public function getSalles() {
    }
    
    public function getReservationsConfirmeesParPeriode($dateDebut, $dateFin) {
    }
}
?>