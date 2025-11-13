<?php
// pages/secretariat/index.php - Interface principale du secrétariat
session_start();

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'secretariat') {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$database = new Database();
$reservationManager = new ReservationManager($database);
$userManager = new UserManager($database);

// Traitement des actions
$message = '';
$messageType = '';

if ($_POST) {
    switch ($_POST['action']) {
        case 'create':
            // Le secrétariat peut créer une réservation directement confirmée
            $_POST['etat'] = $_POST['etat_initial'] ?? 'Confirmé';
            $_POST['utilisateur_createur'] = $_SESSION['user']['nom_utilisateur'];
            $result = $reservationManager->createReservation($_POST);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
             
        case 'validate':
            // Validation d'une réservation provisoire
            $result = $reservationManager->updateEtatReservation($_POST['id'], 'Confirmé');
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'reject':
            // Rejet d'une réservation provisoire
            $result = $reservationManager->updateEtatReservation($_POST['id'], 'Annulé');
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'delete':
            $result = $reservationManager->deleteReservation($_POST['id']);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
    }
}

// Récupération des données
$section = $_GET['section'] ?? 'tableau_bord';
$reservations = $reservationManager->getAllReservations();
$reservationsProvisoires = $reservationManager->getReservationsProvisoires();
$salles = $reservationManager->getSalles();
$statistiques = $reservationManager->getStatistiques();

// Filtres pour la liste complète
$filtreEtat = $_GET['filtre_etat'] ?? '';
$filtreSalle = $_GET['filtre_salle'] ?? '';
if ($section === 'liste' && ($filtreEtat || $filtreSalle)) {
    $reservations = $reservationManager->getAllReservations($filtreEtat, $filtreSalle);
}

// S'assurer que les variables sont des tableaux
$reservations = is_array($reservations) ? $reservations : [];
$reservationsProvisoires = is_array($reservationsProvisoires) ? $reservationsProvisoires : [];
$salles = is_array($salles) ? $salles : [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VALRES2 - Secrétariat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <!-- En-tête avec informations utilisateur -->
        <header class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">🏢 VALRES2 - Interface Secrétariat</h1>
                    <p class="text-gray-600">
                        Connecté en tant que : <strong><?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?></strong>
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?= htmlspecialchars($_SESSION['user']['role_nom']) ?>
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
                <a href="?section=tableau_bord" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'tableau_bord' ? 'bg-blue-700 text-white hover:bg-blue-800 focus:ring-blue-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500' ?>">
                    📊 Tableau de bord
                </a>
                <a href="?section=validation" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'validation' ? 'bg-blue-700 text-white hover:bg-blue-800 focus:ring-blue-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500' ?>">
                    ✅ Validation des Réservations <?php if(count($reservationsProvisoires) > 0): ?><span class="ml-1 bg-red-500 text-white rounded-full px-2 py-0.5 text-xs"><?= count($reservationsProvisoires) ?></span><?php endif; ?>
                </a>
                <a href="?section=nouvelle" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'nouvelle' ? 'bg-blue-700 text-white hover:bg-blue-800 focus:ring-blue-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500' ?>">
                    ➕ Nouvelle Réservation
                </a>
                <a href="?section=liste" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'liste' ? 'bg-blue-700 text-white hover:bg-blue-800 focus:ring-blue-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500' ?>">
                    📋 Toutes les Réservations
                </a>
                <a href="?section=disponibilites" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'disponibilites' ? 'bg-blue-700 text-white hover:bg-blue-800 focus:ring-blue-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500' ?>">
                    📅 Consulter Disponibilités
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
                    <?php 
                    $provisoires = 0;
                    $confirmes = 0;
                    $annules = 0;
                    foreach ($statistiques['par_etat'] as $stat) {
                        switch($stat['etat']) {
                            case 'Provisoire': $provisoires = $stat['nombre']; break;
                            case 'Confirmé': $confirmes = $stat['nombre']; break;
                            case 'Annulé': $annules = $stat['nombre']; break;
                        }
                    }
                    ?>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 rounded-md bg-yellow-500 text-white">⏳</div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">En attente</div>
                                <div class="text-2xl font-bold text-gray-900"><?= $provisoires ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 rounded-md bg-green-500 text-white">✅</div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Confirmées</div>
                                <div class="text-2xl font-bold text-gray-900"><?= $confirmes ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 rounded-md bg-blue-500 text-white">📅</div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Aujourd'hui</div>
                                <div class="text-2xl font-bold text-gray-900"><?= $statistiques['aujourd_hui'] ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 rounded-md bg-purple-500 text-white">📊</div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Cette semaine</div>
                                <div class="text-2xl font-bold text-gray-900"><?= $statistiques['cette_semaine'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Réservations en attente de validation -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">🔔 Réservations en attente de validation</h2>
                    
                    <?php if (empty($reservationsProvisoires)): ?>
                        <p class="text-gray-500 text-center py-8 bg-gray-50 rounded-lg">Aucune réservation en attente de validation.</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($reservationsProvisoires as $reservation): ?>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($reservation['salle']) ?></h3>
                                            <div class="mt-2 space-y-1">
                                                <p class="text-sm text-gray-600"><strong>📅 Date :</strong> <?= date('l j F Y', strtotime($reservation['date'])) ?></p>
                                                <p class="text-sm text-gray-600"><strong>🕐 Horaire :</strong> <?= $reservation['heure_debut'] ?> - <?= $reservation['heure_fin'] ?></p>
                                                <p class="text-sm text-gray-600"><strong>👤 Responsable :</strong> <?= htmlspecialchars($reservation['responsable']) ?></p>
                                                <p class="text-sm text-gray-600"><strong>📝 Motif :</strong> <?= htmlspecialchars($reservation['motif']) ?></p>
                                                <p class="text-sm text-gray-500"><strong>📅 Demandé le :</strong> <?= date('d/m/Y à H:i', strtotime($reservation['date_creation'])) ?></p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col sm:flex-row gap-2">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="validate">
                                                <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:ring-green-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                                    ✅ Valider
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir rejeter cette réservation ?')">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:ring-red-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                                    ❌ Rejeter
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            <?php elseif ($section == 'validation'): ?>
                <!-- Section Validation des Réservations -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">✅ Validation des Réservations</h2>
                    
                    <?php if (empty($reservationsProvisoires)): ?>
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">🎉</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune réservation en attente !</h3>
                            <p class="text-gray-500">Toutes les réservations ont été traitées.</p>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600 mb-6">Vous avez <strong><?= count($reservationsProvisoires) ?></strong> réservation(s) en attente de validation.</p>
                        
                        <div class="space-y-6">
                            <?php foreach ($reservationsProvisoires as $reservation): ?>
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <div class="flex flex-col lg:flex-row lg:justify-between gap-6">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-3">
                                                <h3 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($reservation['salle']) ?></h3>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    En attente
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <p class="text-sm text-gray-600 mb-1"><strong>📅 Date :</strong></p>
                                                    <p class="text-base"><?= date('l j F Y', strtotime($reservation['date'])) ?></p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600 mb-1"><strong>🕐 Horaire :</strong></p>
                                                    <p class="text-base"><?= $reservation['heure_debut'] ?> - <?= $reservation['heure_fin'] ?></p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600 mb-1"><strong>👤 Responsable :</strong></p>
                                                    <p class="text-base"><?= htmlspecialchars($reservation['responsable']) ?></p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600 mb-1"><strong>📅 Demandé le :</strong></p>
                                                    <p class="text-base"><?= date('d/m/Y à H:i', strtotime($reservation['date_creation'])) ?></p>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <p class="text-sm text-gray-600 mb-1"><strong>📝 Motif :</strong></p>
                                                <p class="text-base bg-gray-50 p-3 rounded"><?= htmlspecialchars($reservation['motif']) ?></p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-3 lg:w-48">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="validate">
                                                <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:ring-green-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                                    ✅ Valider la réservation
                                                </button>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir rejeter cette réservation ?')">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:ring-red-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                                    ❌ Rejeter la réservation
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            <?php elseif ($section == 'nouvelle'): ?>
                <!-- Section Nouvelle Réservation -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">➕ Créer une Nouvelle Réservation</h2>
                    <p class="text-gray-600 mb-6">En tant que secrétariat, vous pouvez créer une réservation directement confirmée ou la laisser en état provisoire.</p>
                    
                    <form method="POST" action="?create">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label for="salle" class="block text-sm font-medium text-gray-700 mb-2">Salle :</label>
                                <select id="salle" name="salle" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Sélectionner une salle</option>
                                    <?php foreach ($salles as $salle): ?>
                                        <option value="<?= htmlspecialchars($salle['nom']) ?>">
                                            <?= htmlspecialchars($salle['nom']) ?> (<?= $salle['capacite'] ?> places) - <?= htmlspecialchars($salle['equipements']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date :</label>
                                <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="heure_debut" class="block text-sm font-medium text-gray-700 mb-2">Heure de début :</label>
                                <input type="time" id="heure_debut" name="heure_debut" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="heure_fin" class="block text-sm font-medium text-gray-700 mb-2">Heure de fin :</label>
                                <input type="time" id="heure_fin" name="heure_fin" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="responsable" class="block text-sm font-medium text-gray-700 mb-2">Responsable :</label>
                                <input type="text" id="responsable" name="responsable" required 
                                       placeholder="Nom du responsable" maxlength="100" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="etat_initial" class="block text-sm font-medium text-gray-700 mb-2">État initial :</label>
                                <select id="etat_initial" name="etat_initial" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="Confirmé">Confirmé (validation immédiate)</option>
                                    <option value="Provisoire">Provisoire (validation ultérieure)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label for="motif" class="block text-sm font-medium text-gray-700 mb-2">Motif :</label>
                            <textarea id="motif" name="motif" required 
                                      placeholder="Raison de la réservation" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm resize-vertical"></textarea>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3 mt-8">
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                ➕ Créer la Réservation
                            </button>
                            <a href="?section=tableau_bord" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:ring-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                Annuler
                            </a>
                        </div>
                    </form>
                </section>

            <?php elseif ($section == 'disponibilites'): ?>
                <!-- Section Disponibilités -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">📅 Consulter les Disponibilités</h2>
                    
                    <form method="GET" action="">
                        <input type="hidden" name="section" value="disponibilites">
                        <div class="flex flex-col sm:flex-row gap-4 items-end mb-6">
                            <div class="flex-1">
                                <label for="date_consultation" class="block text-sm font-medium text-gray-700 mb-2">Date à consulter :</label>
                                <input type="date" id="date_consultation" name="date_consultation" 
                                       value="<?= $_GET['date_consultation'] ?? date('Y-m-d') ?>" 
                                       min="<?= date('Y-m-d') ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                🔍 Vérifier les disponibilités
                            </button>
                        </div>
                    </form>

                    <?php if (isset($_GET['date_consultation'])): ?>
                        <?php 
                        $disponibilites = $reservationManager->getDisponibilites($_GET['date_consultation']); 
                        $disponibilites = is_array($disponibilites) ? $disponibilites : [];
                        ?>
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Disponibilités pour le <?= date('l j F Y', strtotime($_GET['date_consultation'])) ?>
                            </h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($disponibilites as $dispo): ?>
                                <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        🏛️ <?= htmlspecialchars($dispo['salle']) ?>
                                    </h4>
                                    
                                    <?php if (empty($dispo['reservations'])): ?>
                                        <div class="flex items-center p-3 rounded-md bg-green-100 text-green-800">
                                            <span class="mr-2">🟢</span>
                                            <span class="font-medium">Entièrement libre</span>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-2">Aucune réservation prévue</p>
                                    <?php else: ?>
                                        <div class="space-y-2">
                                            <?php 
                                            $reservations_dispo = is_array($dispo['reservations']) ? $dispo['reservations'] : [];
                                            foreach ($reservations_dispo as $res): ?>
                                                <div class="flex items-center justify-between p-3 rounded-md <?= $res['etat'] == 'Confirmé' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                    <div>
                                                        <span class="mr-2"><?= $res['etat'] == 'Confirmé' ? '🔴' : '🟡' ?></span>
                                                        <span class="font-medium"><?= $res['heure_debut'] ?>-<?= $res['heure_fin'] ?></span>
                                                    </div>
                                                    <span class="text-xs"><?= $res['etat'] ?></span>
                                                </div>
                                                <p class="text-xs text-gray-600 ml-6">
                                                    👤 <?= htmlspecialchars($res['responsable']) ?><br>
                                                    📝 <?= htmlspecialchars(substr($res['motif'], 0, 50)) ?><?= strlen($res['motif']) > 50 ? '...' : '' ?>
                                                </p>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            <?php else: ?>
                <!-- Section Liste Complète -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">📋 Toutes les Réservations</h2>
                    
                    <!-- Filtres -->
                    <form method="GET" action="" class="mb-6">
                        <input type="hidden" name="section" value="liste">
                        <div class="flex flex-col sm:flex-row gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex-1">
                                <label for="filtre_etat" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par état :</label>
                                <select name="filtre_etat" id="filtre_etat" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tous les états</option>
                                    <option value="Provisoire" <?= $filtreEtat == 'Provisoire' ? 'selected' : '' ?>>Provisoire</option>
                                    <option value="Confirmé" <?= $filtreEtat == 'Confirmé' ? 'selected' : '' ?>>Confirmé</option>
                                    <option value="Annulé" <?= $filtreEtat == 'Annulé' ? 'selected' : '' ?>>Annulé</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label for="filtre_salle" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par salle :</label>
                                <select name="filtre_salle" id="filtre_salle" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Toutes les salles</option>
                                    <?php foreach ($salles as $salle): ?>
                                        <option value="<?= htmlspecialchars($salle['nom']) ?>" 
                                                <?= $filtreSalle == $salle['nom'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($salle['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($filtreEtat || $filtreSalle): ?>
                                <div class="flex items-end">
                                    <a href="?section=liste" class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                        🗑️ Effacer filtres
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Liste des réservations -->
                    <?php if (empty($reservations)): ?>
                        <p class="text-center text-gray-500 py-8 bg-gray-50 rounded-lg border border-gray-200">Aucune réservation trouvée.</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($reservations as $reservation): ?>
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="p-6">
                                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-3">
                                                    <h3 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($reservation['salle']) ?></h3>
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php 
                                                        echo $reservation['etat'] == 'Provisoire' ? 'bg-yellow-100 text-yellow-800' : 
                                                             ($reservation['etat'] == 'Confirmé' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); 
                                                    ?>">
                                                        <?= htmlspecialchars($reservation['etat']) ?>
                                                    </span>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <p class="text-sm text-gray-600"><strong>📅 Date :</strong> <?= date('l j F Y', strtotime($reservation['date'])) ?></p>
                                                    <p class="text-sm text-gray-600"><strong>🕐 Horaire :</strong> <?= $reservation['heure_debut'] ?> - <?= $reservation['heure_fin'] ?></p>
                                                    <p class="text-sm text-gray-600"><strong>👤 Responsable :</strong> <?= htmlspecialchars($reservation['responsable']) ?></p>
                                                    <p class="text-sm text-gray-600"><strong>📅 Créé le :</strong> <?= date('d/m/Y', strtotime($reservation['date_creation'])) ?></p>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-2"><strong>📝 Motif :</strong> <?= htmlspecialchars($reservation['motif']) ?></p>
                                            </div>
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between lg:flex-col lg:items-end gap-3">
                                                <div class="flex flex-wrap gap-2">
                                                    <?php if ($reservation['etat'] == 'Provisoire'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="validate">
                                                            <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:ring-green-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">✅ Valider</button>
                                                        </form>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">❌ Rejeter</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')" action="?delete">
                                                        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:ring-red-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">🗑️ Supprimer</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>
    <script src="../../assets/script.js"></script>
</body>
</html>