<?php
// test_config.php - Script de test de la configuration

echo "<h1>Test de Configuration VALRES2</h1>";

// Test de la version PHP
echo "<h2>Version PHP</h2>";
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "PHP " . PHP_VERSION . " (Compatible)";
} else {
    echo "PHP " . PHP_VERSION . " (Requiert PHP 7.4+)";
}

// Test de l'extension PDO SQLite
echo "<h2>Extensions PHP</h2>";
if (extension_loaded('pdo_sqlite')) {
    echo "PDO SQLite disponible<br>";
} else {
    echo "PDO SQLite manquant<br>";
}

if (extension_loaded('pdo')) {
    echo "PDO disponible<br>";
} else {
    echo "PDO manquant<br>";
}

// Test des permissions de fichier
echo "<h2>Permissions</h2>";
$dataDir = __DIR__ . '/data';
if (is_writable(__DIR__)) {
    echo "Dossier racine accessible en écriture<br>";
} else {
    echo "Dossier racine non accessible en écriture<br>";
}

if (is_dir($dataDir)) {
    if (is_writable($dataDir)) {
        echo "Dossier data/ accessible en écriture<br>";
    } else {
        echo "Dossier data/ non accessible en écriture<br>";
    }
} else {
    echo "ℹDossier data/ sera créé automatiquement<br>";
}

// Test de création de la base de données
echo "<h2>Test Base de Données</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $reservationManager = new ReservationManager($database);
    
    echo "Connexion à la base de données réussie<br>";
    
    // Test d'une requête
    $salles = $reservationManager->getSalles();
    echo "Requête test réussie - " . count($salles) . " salle(s) trouvée(s)<br>";
    
    // Test des données de démonstration
    $reservations = $reservationManager->getAllReservations();
    echo count($reservations) . " réservation(s) de démonstration chargée(s)<br>";
    
} catch (Exception $e) {
    echo "Erreur base de données : " . $e->getMessage() . "<br>";
}

echo "<h2>Résumé</h2>";
echo "<p>Si tous les tests sont, votre installation est prête !</p>";
echo "<p><a href='index.php'>🚀 Lancer l'application</a></p>";

// Style basique
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    .error { color: red; }
    .success { color: green; }
    .info { color: blue; }
</style>";
?>