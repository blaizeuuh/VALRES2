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

    /**
     * Summary of getUserByName
     * @param mixed $username
     *
     * Summary of getUserByName
     * @param mixed $username
     */
    public function getUserByName($username) {
        $sql = "SELECT * FROM utilisateur WHERE nom = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    /**
      * Vérifie les identifiants de connexion.
     * @param string $nom
     * @param string $password
     * @return bool
     */
    public function verifyLogin($nom, $password) {
        // Retourne true si les identifiants sont corrects, false sinon. Recherche dans la base de données (table utilisateur)
        $pdo = $this->pdo;
        $stmt = $pdo->prepare("SELECT mdp, prenom, acteur, mail, structure_nom, structure_adresse, utilisateur_id FROM utilisateur WHERE nom = :username");
        $stmt->execute(['username' => $nom]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mdp'])) {
            // Récupérer les données de l'utilisateur
            $_SESSION['user']['user_id'] = $user['utilisateur_id'];
            $_SESSION['user']['username'] = $nom;
            $_SESSION['user']['prenom'] = $user['prenom'] ?? '';
            $_SESSION['user']['role'] = $user['acteur'] ?? '';
            $_SESSION['user']['mail'] = $user['mail'] ?? '';
            $_SESSION['user']['structure'] = $user['structure_nom'] ?? '';
            $_SESSION['user']['structure_adresse'] = $user['structure_adresse'] ?? '';
            
            return true;
        } else {
            return false;
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
    public function __construct(?Database $database = null)
    {
        if ($database instanceof Database) {
            $this->pdo = $database->getPdo();
        } else {
            // Si aucune instance fournie, crée une connexion par défaut
            $db = new Database();
            $this->pdo = $db->getPdo();
        }
    }
    
    public function getAllReservations($filtreUtilisateur = '', $filtreSalle = '') {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, 
                       s.salle_nom, cs.libelle as categorie_nom
                FROM reservation r
                JOIN utilisateur u ON r.utilisateur_id = u.utilisateur_id
                JOIN salle s ON r.salle_id = s.id
                JOIN categorie_salle cs ON s.categorie = cs.id
                WHERE 1=1";
        $params = [];
        
        if ($filtreUtilisateur) {
            $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ?)";
            $params[] = '%' . $filtreUtilisateur . '%';
            $params[] = '%' . $filtreUtilisateur . '%';
        }
        
        if ($filtreSalle) {
            $sql .= " AND s.salle_nom LIKE ?";
            $params[] = '%' . $filtreSalle . '%';
        }
        
        $sql .= " ORDER BY r.date DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function createReservation($data) {
        $sql = "INSERT INTO reservation (utilisateur_id, salle_id, date, periode) 
                VALUES (:utilisateur_id, :salle_id, :date, :periode)";
        $params = [
            'utilisateur_id' => $data['utilisateur_id'],
            'salle_id' => $data['salle_id'],
            'date' => $data['date'],
            'periode' => $data['periode']
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }
    
    public function updateReservation($id, $data) {
        $sql = "UPDATE reservation SET utilisateur_id = :utilisateur_id, salle_id = :salle_id, 
                date = :date, periode = :periode WHERE id = :id";
        $params = [
            'utilisateur_id' => $data['utilisateur_id'],
            'salle_id' => $data['salle_id'],
            'date' => $data['date'],
            'periode' => $data['periode'],
            'id' => $id
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    public function deleteReservation($id) {
        $sql = "DELETE FROM reservation WHERE id = :id";
        $params = ['id' => $id];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    public function checkDisponibilite($salleId, $date, $periode, $excludeId = null) {
        $sql = "SELECT * FROM reservation WHERE salle_id = :salle_id AND date = :date AND periode = :periode";
        $params = [
            'salle_id' => $salleId,
            'date' => $date,
            'periode' => $periode
        ];
        
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        return count($result) === 0;
    }
    
    public function getReservationsByDate($date) {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, 
                       s.salle_nom, cs.libelle as categorie_nom
                FROM reservation r
                JOIN utilisateur u ON r.utilisateur_id = u.utilisateur_id
                JOIN salle s ON r.salle_id = s.id
                JOIN categorie_salle cs ON s.categorie = cs.id
                WHERE DATE(r.date) = :date 
                ORDER BY r.periode ASC, s.salle_nom ASC";
        $params = ['date' => $date];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getReservationsBySalleAndDate($salleId, $date) {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom
                FROM reservation r
                JOIN utilisateur u ON r.utilisateur_id = u.utilisateur_id
                WHERE r.salle_id = :salle_id AND DATE(r.date) = :date 
                ORDER BY r.periode ASC";
        $params = [
            'salle_id' => $salleId,
            'date' => $date
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getSalles() {
        $sql = "SELECT s.*, cs.libelle as categorie_nom 
                FROM salle s 
                JOIN categorie_salle cs ON s.categorie = cs.id 
                ORDER BY s.salle_nom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getSalleById($id) {
        $sql = "SELECT s.*, cs.libelle as categorie_nom 
                FROM salle s 
                JOIN categorie_salle cs ON s.categorie = cs.id 
                WHERE s.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function getReservationsParPeriode($dateDebut, $dateFin) {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, 
                       s.salle_nom, cs.libelle as categorie_nom
                FROM reservation r
                JOIN utilisateur u ON r.utilisateur_id = u.utilisateur_id
                JOIN salle s ON r.salle_id = s.id
                JOIN categorie_salle cs ON s.categorie = cs.id
                WHERE DATE(r.date) BETWEEN :dateDebut AND :dateFin 
                ORDER BY r.date ASC, r.periode ASC";
        $params = [
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getReservationsByUtilisateur($utilisateurId) {
        $sql = "SELECT r.*, s.salle_nom, cs.libelle as categorie_nom
                FROM reservation r
                JOIN salle s ON r.salle_id = s.id
                JOIN categorie_salle cs ON s.categorie = cs.id
                WHERE r.utilisateur_id = :utilisateur_id 
                ORDER BY r.date DESC";
        $params = ['utilisateur_id' => $utilisateurId];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

class StructureManager {
    /** @var \PDO */
    private $pdo;

    public function __construct(Database $database) {
        $this->pdo = $database->getPdo();
    }

    public function getAllStructures() {
        $sql = "SELECT * FROM structure ORDER BY libelle";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStructureById($id) {
        $sql = "SELECT * FROM structure WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}

class CategorieManager {
    /** @var \PDO */
    private $pdo;

    public function __construct(Database $database) {
        $this->pdo = $database->getPdo();
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM categorie_salle ORDER BY libelle";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategorieById($id) {
        $sql = "SELECT * FROM categorie_salle WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
