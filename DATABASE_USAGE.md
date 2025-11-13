# Documentation - Utilisation de la base de données

## Structure de la base de données

Votre base de données `valres_db` contient les tables suivantes :

### Tables principales
- `utilisateur` : Gestion des utilisateurs du système
- `reservation` : Gestion des réservations
- `salle` : Gestion des salles disponibles
- `structure` : Types d'organisations (Ligue, Club, Association, etc.)
- `categorie_salle` : Catégories des salles (Réunion, avec équipements, Amphi)

## Utilisation des classes PHP

### 1. Connexion à la base de données
```php
// Création de la connexion
$database = new Database();

// Ou avec des paramètres spécifiques
$database = new Database('localhost', 'valres_db', 'root', '');
```

### 2. Gestion des utilisateurs
```php
$userManager = new UserManager($database);

// Vérifier les identifiants de connexion
$isValid = $userManager->verifyLogin('BANDILELLA', 'motdepasse');

// Récupérer un utilisateur par son nom
$user = $userManager->getUserByName('BANDILELLA');
```

### 3. Gestion des réservations
```php
$reservationManager = new ReservationManager($database);

// Créer une nouvelle réservation
$newReservation = [
    'utilisateur_id' => 1,
    'salle_id' => 6, // Amphithéâtre
    'date' => '2025-12-01 00:00:00',
    'periode' => 2 // Période 2
];
$reservationId = $reservationManager->createReservation($newReservation);

// Vérifier la disponibilité d'une salle
$isDisponible = $reservationManager->checkDisponibilite(6, '2025-12-01', 2);

// Récupérer toutes les réservations
$reservations = $reservationManager->getAllReservations();

// Récupérer les réservations par date
$reservationsDate = $reservationManager->getReservationsByDate('2025-12-01');

// Récupérer les salles disponibles
$salles = $reservationManager->getSalles();
```

### 4. Gestion des structures
```php
$structureManager = new StructureManager($database);

// Récupérer toutes les structures
$structures = $structureManager->getAllStructures();
// Résultat : Ligue, Club sportif, Comité départemental, etc.

// Récupérer une structure par ID
$structure = $structureManager->getStructureById(1); // Ligue
```

### 5. Gestion des catégories de salles
```php
$categorieManager = new CategorieManager($database);

// Récupérer toutes les catégories
$categories = $categorieManager->getAllCategories();
// Résultat : Réunion, avec équipements, Amphi

// Récupérer une catégorie par ID
$categorie = $categorieManager->getCategorieById(1); // Réunion
```

## Exemples d'utilisation complète

### Exemple 1 : Afficher toutes les réservations avec détails
```php
$reservationManager = new ReservationManager($database);
$reservations = $reservationManager->getAllReservations();

foreach ($reservations as $reservation) {
    echo "Réservation ID: " . $reservation['id'] . "\n";
    echo "Utilisateur: " . $reservation['utilisateur_nom'] . " " . $reservation['utilisateur_prenom'] . "\n";
    echo "Salle: " . $reservation['salle_nom'] . "\n";
    echo "Catégorie: " . $reservation['categorie_nom'] . "\n";
    echo "Date: " . $reservation['date'] . "\n";
    echo "Période: " . $reservation['periode'] . "\n\n";
}
```

### Exemple 2 : Créer une nouvelle réservation avec vérification
```php
$utilisateurId = $_SESSION['user']['user_id'];
$salleId = 5; // Multimédia
$date = '2025-12-15';
$periode = 1;

// Vérifier la disponibilité
if ($reservationManager->checkDisponibilite($salleId, $date, $periode)) {
    // Créer la réservation
    $data = [
        'utilisateur_id' => $utilisateurId,
        'salle_id' => $salleId,
        'date' => $date . ' 00:00:00',
        'periode' => $periode
    ];
    
    $reservationId = $reservationManager->createReservation($data);
    echo "Réservation créée avec succès (ID: $reservationId)";
} else {
    echo "Désolé, cette salle n'est pas disponible pour cette période";
}
```

### Exemple 3 : Afficher les salles par catégorie
```php
$salles = $reservationManager->getSalles();
$categoriesGrouped = [];

foreach ($salles as $salle) {
    $categoriesGrouped[$salle['categorie_nom']][] = $salle;
}

foreach ($categoriesGrouped as $categorie => $sallesList) {
    echo "<h3>$categorie</h3>\n";
    foreach ($sallesList as $salle) {
        echo "- " . $salle['salle_nom'] . "\n";
    }
}
```

## Notes importantes

1. **Période** : Le champ `periode` dans votre base utilise des entiers (1, 2, 3). Vous devrez définir ce que représentent ces valeurs (ex: 1=Matin, 2=Après-midi, 3=Soir).

2. **Date** : Les dates sont stockées au format DATETIME. Utilisez le format `YYYY-MM-DD HH:MM:SS`.

3. **Sécurité** : Les mots de passe sont hashés avec `password_hash()` et vérifiés avec `password_verify()`.

4. **Sessions** : La méthode `verifyLogin` stocke automatiquement les informations utilisateur en session, incluant l'ID utilisateur (`user_id`).

5. **Relations** : Toutes les requêtes importantes utilisent des JOINs pour récupérer les noms lisibles plutôt que les IDs.