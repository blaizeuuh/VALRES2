<?php
// pages/administrateur/index.php - Interface principale de l'administrateur
session_start();

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administrateur') {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$database = new Database();
$reservationManager = new ReservationManager($database);
$structureManager = new StructureManager($database);

// Traitement des actions sur les utilisateurs
$message = '';
$messageType = '';

if ($_POST) {
    switch ($_POST['action']) {
        case 'create_user':
            // Créer un nouvel utilisateur
            try {
                $pdo = $database->getPdo();
                $sql = "INSERT INTO utilisateur (nom, prenom, structure_id, structure_nom, structure_adresse, mail, mdp, acteur) 
                        VALUES (:nom, :prenom, :structure_id, :structure_nom, :structure_adresse, :mail, :mdp, :acteur)";
                
                $stmt = $pdo->prepare($sql);
                $hashedPassword = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
                
                $stmt->execute([
                    'nom' => $_POST['nom'],
                    'prenom' => $_POST['prenom'],
                    'structure_id' => $_POST['structure_id'],
                    'structure_nom' => $_POST['structure_nom'],
                    'structure_adresse' => $_POST['structure_adresse'],
                    'mail' => $_POST['mail'],
                    'mdp' => $hashedPassword,
                    'acteur' => $_POST['acteur']
                ]);
                
                $message = "Utilisateur créé avec succès !";
                $messageType = 'success';
            } catch (Exception $e) {
                $message = "Erreur lors de la création de l'utilisateur : " . $e->getMessage();
                $messageType = 'error';
            }
            break;
            
        case 'update_user':
            // Modifier un utilisateur existant
            try {
                $pdo = $database->getPdo();
                
                if (!empty($_POST['mdp'])) {
                    // Si un nouveau mot de passe est fourni
                    $sql = "UPDATE utilisateur SET nom = :nom, prenom = :prenom, structure_id = :structure_id, 
                            structure_nom = :structure_nom, structure_adresse = :structure_adresse, 
                            mail = :mail, mdp = :mdp, acteur = :acteur 
                            WHERE utilisateur_id = :id";
                    $hashedPassword = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
                    $params = [
                        'nom' => $_POST['nom'],
                        'prenom' => $_POST['prenom'],
                        'structure_id' => $_POST['structure_id'],
                        'structure_nom' => $_POST['structure_nom'],
                        'structure_adresse' => $_POST['structure_adresse'],
                        'mail' => $_POST['mail'],
                        'mdp' => $hashedPassword,
                        'acteur' => $_POST['acteur'],
                        'id' => $_POST['utilisateur_id']
                    ];
                } else {
                    // Sans modifier le mot de passe
                    $sql = "UPDATE utilisateur SET nom = :nom, prenom = :prenom, structure_id = :structure_id, 
                            structure_nom = :structure_nom, structure_adresse = :structure_adresse, 
                            mail = :mail, acteur = :acteur 
                            WHERE utilisateur_id = :id";
                    $params = [
                        'nom' => $_POST['nom'],
                        'prenom' => $_POST['prenom'],
                        'structure_id' => $_POST['structure_id'],
                        'structure_nom' => $_POST['structure_nom'],
                        'structure_adresse' => $_POST['structure_adresse'],
                        'mail' => $_POST['mail'],
                        'acteur' => $_POST['acteur'],
                        'id' => $_POST['utilisateur_id']
                    ];
                }
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                $message = "Utilisateur modifié avec succès !";
                $messageType = 'success';
            } catch (Exception $e) {
                $message = "Erreur lors de la modification de l'utilisateur : " . $e->getMessage();
                $messageType = 'error';
            }
            break;
            
        case 'delete_user':
            // Supprimer un utilisateur
            try {
                $pdo = $database->getPdo();
                
                // Vérifier si l'utilisateur a des réservations
                $checkSql = "SELECT COUNT(*) as count FROM reservation WHERE utilisateur_id = :id";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute(['id' => $_POST['utilisateur_id']]);
                $result = $checkStmt->fetch();
                
                if ($result['count'] > 0) {
                    $message = "Impossible de supprimer cet utilisateur car il a des réservations associées.";
                    $messageType = 'error';
                } else {
                    $sql = "DELETE FROM utilisateur WHERE utilisateur_id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['id' => $_POST['utilisateur_id']]);
                    
                    $message = "Utilisateur supprimé avec succès !";
                    $messageType = 'success';
                }
            } catch (Exception $e) {
                $message = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
                $messageType = 'error';
            }
            break;
    }
}

// Récupération des données
$section = $_GET['section'] ?? 'tableau_bord';

// Récupérer tous les utilisateurs
$pdo = $database->getPdo();
$sqlUsers = "SELECT u.*, s.libelle as structure_libelle 
             FROM utilisateur u 
             LEFT JOIN structure s ON u.structure_id = s.id 
             ORDER BY u.nom, u.prenom";
$stmtUsers = $pdo->prepare($sqlUsers);
$stmtUsers->execute();
$utilisateurs = $stmtUsers->fetchAll();

// Récupérer toutes les structures
$structures = $structureManager->getAllStructures();

// Récupérer les statistiques
$statsUsers = [
    'total' => count($utilisateurs),
    'administrateurs' => 0,
    'secretariat' => 0,
    'utilisateurs' => 0
];

foreach ($utilisateurs as $user) {
    switch($user['acteur']) {
        case 'administrateur':
            $statsUsers['administrateurs']++;
            break;
        case 'secretariat':
            $statsUsers['secretariat']++;
            break;
        case 'utilisateur':
            $statsUsers['utilisateurs']++;
            break;
    }
}

// Récupérer toutes les réservations pour la consultation
$reservations = $reservationManager->getAllReservations();
$salles = $reservationManager->getSalles();

// Filtres pour la consultation des salles
$filtreDate = $_GET['date_consultation'] ?? '';
$filtreSalle = $_GET['filtre_salle'] ?? '';

if ($section === 'disponibilites' && $filtreDate) {
    $disponibilites = $reservationManager->getReservationsByDate($filtreDate);
} else {
    $disponibilites = [];
}

// Récupération de l'utilisateur à modifier (si demandé)
$userToEdit = null;
if ($section === 'modifier_user' && isset($_GET['id'])) {
    $sqlEditUser = "SELECT * FROM utilisateur WHERE utilisateur_id = :id";
    $stmtEditUser = $pdo->prepare($sqlEditUser);
    $stmtEditUser->execute(['id' => $_GET['id']]);
    $userToEdit = $stmtEditUser->fetch();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VALRES2 - Administrateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <!-- En-tête avec informations utilisateur -->
        <header class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">🔧 VALRES2 - Interface Administrateur</h1>
                    <p class="text-gray-600">
                        Connecté en tant que : <strong><?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['username']) ?></strong>
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            Administrateur
                        </span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="../../export_xml.php" target="_blank" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:ring-green-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                        📄 Export XML
                    </a>
                    <a href="../../index.php?action=logout" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:ring-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                        🚪 Déconnexion
                    </a>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex flex-wrap gap-3 mt-6">
                <a href="?section=tableau_bord" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'tableau_bord' ? 'bg-purple-700 text-white hover:bg-purple-800 focus:ring-purple-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-purple-500' ?>">
                    📊 Tableau de bord
                </a>
                <a href="?section=liste_users" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'liste_users' ? 'bg-purple-700 text-white hover:bg-purple-800 focus:ring-purple-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-purple-500' ?>">
                    👥 Gérer les Utilisateurs
                </a>
                <a href="?section=nouveau_user" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'nouveau_user' ? 'bg-purple-700 text-white hover:bg-purple-800 focus:ring-purple-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-purple-500' ?>">
                    ➕ Nouvel Utilisateur
                </a>
                <a href="?section=disponibilites" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'disponibilites' ? 'bg-purple-700 text-white hover:bg-purple-800 focus:ring-purple-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-purple-500' ?>">
                    📅 Consulter l'État des Salles
                </a>
            </nav>
        </header>

        <main>
            <?php if ($message): ?>
                <div class="px-4 py-3 rounded-lg mb-6 border-l-4 <?= $messageType == 'success' ? 'bg-green-50 border-green-400 text-green-700' : 'bg-red-50 border-red-400 text-red-700' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($section == 'tableau_bord'): ?>
                <!-- Tableau de bord -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Utilisateurs</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $statsUsers['total'] ?></p>
                            </div>
                            <div class="text-4xl">👥</div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Administrateurs</p>
                                <p class="text-3xl font-bold text-purple-600 mt-2"><?= $statsUsers['administrateurs'] ?></p>
                            </div>
                            <div class="text-4xl">🔧</div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Secrétariat</p>
                                <p class="text-3xl font-bold text-blue-600 mt-2"><?= $statsUsers['secretariat'] ?></p>
                            </div>
                            <div class="text-4xl">🏢</div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Utilisateurs</p>
                                <p class="text-3xl font-bold text-green-600 mt-2"><?= $statsUsers['utilisateurs'] ?></p>
                            </div>
                            <div class="text-4xl">👤</div>
                        </div>
                    </div>
                </div>

                <!-- Derniers utilisateurs créés -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">📋 Derniers Utilisateurs</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prénom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Structure</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php 
                                $derniers_users = array_slice($utilisateurs, -5);
                                $derniers_users = array_reverse($derniers_users);
                                foreach ($derniers_users as $user): 
                                ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($user['nom']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($user['prenom']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($user['mail']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                    echo $user['acteur'] == 'administrateur' ? 'bg-purple-100 text-purple-800' : 
                                                         ($user['acteur'] == 'secretariat' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); 
                                                ?>">
                                                <?= htmlspecialchars($user['acteur']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($user['structure_nom']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Statistiques des salles -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">🏛️ Aperçu des Salles</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($salles as $salle): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <h3 class="font-semibold text-gray-900 mb-2"><?= htmlspecialchars($salle['salle_nom']) ?></h3>
                                <p class="text-sm text-gray-600">📁 <?= htmlspecialchars($salle['categorie_nom']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

            <?php elseif ($section == 'liste_users'): ?>
                <!-- Section Liste des Utilisateurs -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">👥 Gestion des Utilisateurs</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prénom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Structure</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($utilisateurs as $user): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($user['utilisateur_id']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($user['nom']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($user['prenom']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($user['mail']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                    echo $user['acteur'] == 'administrateur' ? 'bg-purple-100 text-purple-800' : 
                                                         ($user['acteur'] == 'secretariat' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); 
                                                ?>">
                                                <?= htmlspecialchars($user['acteur']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($user['structure_nom']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex gap-2">
                                                <a href="?section=modifier_user&id=<?= $user['utilisateur_id'] ?>" 
                                                   class="text-blue-600 hover:text-blue-900">✏️ Modifier</a>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="utilisateur_id" value="<?= $user['utilisateur_id'] ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-900">🗑️ Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            <?php elseif ($section == 'nouveau_user'): ?>
                <!-- Section Nouvel Utilisateur -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">➕ Créer un Nouvel Utilisateur</h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="create_user">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">Nom :</label>
                                <input type="text" id="nom" name="nom" required 
                                       placeholder="Nom de famille" maxlength="32" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2">Prénom :</label>
                                <input type="text" id="prenom" name="prenom" required 
                                       placeholder="Prénom" maxlength="32" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="mail" class="block text-sm font-medium text-gray-700 mb-2">Email :</label>
                                <input type="email" id="mail" name="mail" required 
                                       placeholder="email@exemple.com" maxlength="50" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="mdp" class="block text-sm font-medium text-gray-700 mb-2">Mot de passe :</label>
                                <input type="password" id="mdp" name="mdp" required 
                                       placeholder="Mot de passe" minlength="6"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="acteur" class="block text-sm font-medium text-gray-700 mb-2">Rôle :</label>
                                <select id="acteur" name="acteur" required 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="utilisateur">Utilisateur</option>
                                    <option value="secretariat">Secrétariat</option>
                                    <option value="administrateur">Administrateur</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="structure_id" class="block text-sm font-medium text-gray-700 mb-2">Structure :</label>
                                <select id="structure_id" name="structure_id" required 
                                        onchange="updateStructureFields()"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                    <option value="">Sélectionner une structure</option>
                                    <?php foreach ($structures as $structure): ?>
                                        <option value="<?= $structure['id'] ?>" 
                                                data-libelle="<?= htmlspecialchars($structure['libelle']) ?>">
                                            <?= htmlspecialchars($structure['libelle']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="structure_nom" class="block text-sm font-medium text-gray-700 mb-2">Nom de la structure :</label>
                                <input type="text" id="structure_nom" name="structure_nom" required 
                                       placeholder="Nom de la structure" maxlength="80" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="structure_adresse" class="block text-sm font-medium text-gray-700 mb-2">Adresse de la structure :</label>
                                <input type="text" id="structure_adresse" name="structure_adresse" required 
                                       placeholder="Adresse complète" maxlength="80" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3 mt-8">
                            <button type="submit" 
                                    class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:ring-purple-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                ➕ Créer l'Utilisateur
                            </button>
                            <a href="?section=liste_users" 
                               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:ring-purple-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                Annuler
                            </a>
                        </div>
                    </form>
                </section>

            <?php elseif ($section == 'modifier_user' && $userToEdit): ?>
                <!-- Section Modifier Utilisateur -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">✏️ Modifier l'Utilisateur</h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="utilisateur_id" value="<?= $userToEdit['utilisateur_id'] ?>">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">Nom :</label>
                                <input type="text" id="nom" name="nom" required 
                                       value="<?= htmlspecialchars($userToEdit['nom']) ?>"
                                       placeholder="Nom de famille" maxlength="32" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2">Prénom :</label>
                                <input type="text" id="prenom" name="prenom" required 
                                       value="<?= htmlspecialchars($userToEdit['prenom']) ?>"
                                       placeholder="Prénom" maxlength="32" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="mail" class="block text-sm font-medium text-gray-700 mb-2">Email :</label>
                                <input type="email" id="mail" name="mail" required 
                                       value="<?= htmlspecialchars($userToEdit['mail']) ?>"
                                       placeholder="email@exemple.com" maxlength="50" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="mdp" class="block text-sm font-medium text-gray-700 mb-2">Nouveau mot de passe :</label>
                                <input type="password" id="mdp" name="mdp" 
                                       placeholder="Laisser vide pour ne pas changer" minlength="6"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Laisser vide si vous ne souhaitez pas changer le mot de passe</p>
                            </div>
                            
                            <div>
                                <label for="acteur" class="block text-sm font-medium text-gray-700 mb-2">Rôle :</label>
                                <select id="acteur" name="acteur" required 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                    <option value="utilisateur" <?= $userToEdit['acteur'] == 'utilisateur' ? 'selected' : '' ?>>Utilisateur</option>
                                    <option value="secretariat" <?= $userToEdit['acteur'] == 'secretariat' ? 'selected' : '' ?>>Secrétariat</option>
                                    <option value="administrateur" <?= $userToEdit['acteur'] == 'administrateur' ? 'selected' : '' ?>>Administrateur</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="structure_id" class="block text-sm font-medium text-gray-700 mb-2">Structure :</label>
                                <select id="structure_id" name="structure_id" required 
                                        onchange="updateStructureFields()"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                    <option value="">Sélectionner une structure</option>
                                    <?php foreach ($structures as $structure): ?>
                                        <option value="<?= $structure['id'] ?>" 
                                                data-libelle="<?= htmlspecialchars($structure['libelle']) ?>"
                                                <?= $userToEdit['structure_id'] == $structure['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($structure['libelle']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="structure_nom" class="block text-sm font-medium text-gray-700 mb-2">Nom de la structure :</label>
                                <input type="text" id="structure_nom" name="structure_nom" required 
                                       value="<?= htmlspecialchars($userToEdit['structure_nom']) ?>"
                                       placeholder="Nom de la structure" maxlength="80" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="structure_adresse" class="block text-sm font-medium text-gray-700 mb-2">Adresse de la structure :</label>
                                <input type="text" id="structure_adresse" name="structure_adresse" required 
                                       value="<?= htmlspecialchars($userToEdit['structure_adresse']) ?>"
                                       placeholder="Adresse complète" maxlength="80" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3 mt-8">
                            <button type="submit" 
                                    class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:ring-purple-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                💾 Enregistrer les Modifications
                            </button>
                            <a href="?section=liste_users" 
                               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:ring-purple-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                Annuler
                            </a>
                        </div>
                    </form>
                </section>

            <?php elseif ($section == 'disponibilites'): ?>
                <!-- Section Consultation de l'État des Salles -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">📅 Consulter l'État des Salles</h2>
                    
                    <form method="GET" action="">
                        <input type="hidden" name="section" value="disponibilites">
                        <div class="flex flex-col sm:flex-row gap-4 items-end mb-6">
                            <div class="flex-1">
                                <label for="date_consultation" class="block text-sm font-medium text-gray-700 mb-2">Date à consulter :</label>
                                <input type="date" id="date_consultation" name="date_consultation" 
                                       value="<?= $_GET['date_consultation'] ?? date('Y-m-d') ?>" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:ring-purple-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                🔍 Consulter
                            </button>
                        </div>
                    </form>

                    <?php if ($filtreDate): ?>
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                État des salles pour le <?= date('l j F Y', strtotime($filtreDate)) ?>
                            </h3>
                        </div>
                        
                        <?php if (empty($disponibilites)): ?>
                            <div class="text-center py-8 bg-green-50 rounded-lg border border-green-200">
                                <div class="text-6xl mb-4">✅</div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune réservation ce jour !</h3>
                                <p class="text-gray-500">Toutes les salles sont libres.</p>
                            </div>
                        <?php else: ?>
                            <!-- Grouper les réservations par salle -->
                            <?php 
                            $reservationsParSalle = [];
                            foreach ($disponibilites as $res) {
                                if (!isset($reservationsParSalle[$res['salle_nom']])) {
                                    $reservationsParSalle[$res['salle_nom']] = [];
                                }
                                $reservationsParSalle[$res['salle_nom']][] = $res;
                            }
                            
                            // Ajouter les salles sans réservation
                            foreach ($salles as $salle) {
                                if (!isset($reservationsParSalle[$salle['salle_nom']])) {
                                    $reservationsParSalle[$salle['salle_nom']] = [];
                                }
                            }
                            ksort($reservationsParSalle);
                            ?>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($reservationsParSalle as $salleNom => $reservationsSalle): ?>
                                    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                            🏛️ <?= htmlspecialchars($salleNom) ?>
                                        </h4>
                                        
                                        <?php if (empty($reservationsSalle)): ?>
                                            <div class="flex items-center p-3 rounded-md bg-green-100 text-green-800">
                                                <span class="mr-2">🟢</span>
                                                <span class="font-medium">Libre toute la journée</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="space-y-2">
                                                <?php foreach ($reservationsSalle as $res): ?>
                                                    <div class="p-3 rounded-md bg-red-100 text-red-800">
                                                        <div class="flex items-center justify-between mb-1">
                                                            <span class="font-medium">🔴 Période <?= $res['periode'] ?></span>
                                                        </div>
                                                        <p class="text-xs text-gray-700 mt-1">
                                                            👤 <?= htmlspecialchars($res['utilisateur_nom'] . ' ' . $res['utilisateur_prenom']) ?>
                                                        </p>
                                                        <p class="text-xs text-gray-600">
                                                            📅 <?= date('H:i', strtotime($res['date'])) ?>
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>

            <?php else: ?>
                <!-- Section par défaut si aucune section ne correspond -->
                <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
                    <p class="text-gray-500">Section non trouvée.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        // Fonction pour mettre à jour automatiquement les champs de structure
        function updateStructureFields() {
            const select = document.getElementById('structure_id');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const libelle = selectedOption.getAttribute('data-libelle');
                document.getElementById('structure_nom').value = libelle || '';
            }
        }
    </script>
    <script src="../../assets/script.js"></script>
</body>
</html>
