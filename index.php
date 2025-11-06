<?php
// index.php - Page principale de l'application
require_once 'config/database.php';

$database = new Database();
$reservationManager = new ReservationManager($database);

// Traitement des actions
$message = '';
$messageType = '';

if ($_POST) {
    switch ($_POST['action']) {
        case 'create':
            $result = $reservationManager->createReservation($_POST);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'update_etat':
            $result = $reservationManager->updateEtatReservation($_POST['id'], $_POST['etat']);
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
$filtreEtat = $_GET['filtre_etat'] ?? '';
$filtreSalle = $_GET['filtre_salle'] ?? '';
$reservations = $reservationManager->getAllReservations($filtreEtat, $filtreSalle);
$salles = $reservationManager->getSalles();

// Section active
$section = $_GET['section'] ?? 'liste';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VALRES2 - Gestion des Réservations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <header class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">

            <h1 class="text-3xl font-bold text-gray-900 mb-6 text-center">Gestion des Réservations</h1>

            <nav class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="?section=nouvelle" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'nouvelle' ? 'bg-blue-700 hover:bg-blue-800 focus:ring-blue-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' ?>">
                    ➕ Nouvelle Réservation
                </a>
                <a href="?section=liste" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'liste' ? 'bg-blue-700 text-white hover:bg-blue-800 focus:ring-blue-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500' ?>">
                    📋 Liste des Réservations
                </a>
                <a href="?section=disponibilites" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $section == 'disponibilites' ? 'bg-blue-700 text-white hover:bg-blue-800 focus:ring-blue-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500' ?>">
                    📅 Consulter Disponibilités
                </a>
                <a href="export_xml.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:ring-green-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2" target="_blank">
                    📄 Exporter XML
                </a>
            </nav>
            
        </header>

        <main>
            <?php if ($message): ?>
                <div class="px-4 py-3 rounded-lg mb-6 border-l-4 <?= $messageType == 'success' ? 'bg-green-50 border-green-400 text-green-700' : 'bg-red-50 border-red-400 text-red-700' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($section == 'nouvelle'): ?>
                <!-- Section Nouvelle Réservation -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Créer une Nouvelle Réservation</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-4">
                            <label for="salle" class="block text-sm font-medium text-gray-700 mb-2">Salle :</label>
                            <select id="salle" name="salle" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Sélectionner une salle</option>
                                <?php foreach ($salles as $salle): ?>
                                    <option value="<?= htmlspecialchars($salle['nom']) ?>">
                                        <?= htmlspecialchars($salle['nom']) ?> (<?= $salle['capacite'] ?> places)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date :</label>
                            <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="heure_debut" class="block text-sm font-medium text-gray-700 mb-2">Heure de début :</label>
                                <input type="time" id="heure_debut" name="heure_debut" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div class="mb-4">
                                <label for="heure_fin" class="block text-sm font-medium text-gray-700 mb-2">Heure de fin :</label>
                                <input type="time" id="heure_fin" name="heure_fin" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="responsable" class="block text-sm font-medium text-gray-700 mb-2">Responsable :</label>
                            <input type="text" id="responsable" name="responsable" required 
                                   placeholder="Nom du responsable" maxlength="100" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div class="mb-4">
                            <label for="motif" class="block text-sm font-medium text-gray-700 mb-2">Motif :</label>
                            <textarea id="motif" name="motif" required 
                                      placeholder="Raison de la réservation" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm resize-vertical"></textarea>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3 mt-6">
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">Créer la Réservation</button>
                            <a href="?section=liste" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">Annuler</a>
                        </div>
                    </form>
                </section>

            <?php elseif ($section == 'disponibilites'): ?>
                <!-- Section Disponibilités -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Consulter les Disponibilités</h2>
                    <form method="GET" action="">
                        <input type="hidden" name="section" value="disponibilites">
                        <div class="flex flex-col sm:flex-row gap-4 items-end">
                            <div class="flex-1">
                                <label for="date_consultation" class="block text-sm font-medium text-gray-700 mb-2">Date :</label>
                                <input type="date" id="date_consultation" name="date_consultation" 
                                       value="<?= $_GET['date_consultation'] ?? date('Y-m-d') ?>" 
                                       min="<?= date('Y-m-d') ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">Vérifier</button>
                        </div>
                    </form>

                    <?php if (isset($_GET['date_consultation'])): ?>
                        <?php $disponibilites = $reservationManager->getDisponibilites($_GET['date_consultation']); ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                            <?php foreach ($disponibilites as $dispo): ?>
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3"><?= htmlspecialchars($dispo['salle']) ?></h4>
                                    <?php if (empty($dispo['reservations'])): ?>
                                        <div class="px-3 py-2 rounded-md text-sm mb-2 bg-green-100 text-green-800"> Entièrement libre</div>
                                    <?php else: ?>
                                        <?php foreach ($dispo['reservations'] as $res): ?>
                                            <div class="px-3 py-2 rounded-md text-sm mb-2 bg-red-100 text-red-800">
                                                <?= $res['heure_debut'] ?>-<?= $res['heure_fin'] ?> 
                                                (<?= htmlspecialchars($res['responsable']) ?>)
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            <?php else: ?>
                <!-- Section Liste des Réservations -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Liste des Réservations</h2>
                    
                    <!-- Filtres -->
                    <form method="GET" action="" class="mb-6">
                        <input type="hidden" name="section" value="liste">
                        <div class="flex flex-col sm:flex-row gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <select name="filtre_etat" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tous les états</option>
                                <option value="Provisoire" <?= $filtreEtat == 'Provisoire' ? 'selected' : '' ?>>Provisoire</option>
                                <option value="Confirmé" <?= $filtreEtat == 'Confirmé' ? 'selected' : '' ?>>Confirmé</option>
                                <option value="Annulé" <?= $filtreEtat == 'Annulé' ? 'selected' : '' ?>>Annulé</option>
                            </select>
                            <select name="filtre_salle" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Toutes les salles</option>
                                <?php foreach ($salles as $salle): ?>
                                    <option value="<?= htmlspecialchars($salle['nom']) ?>" 
                                            <?= $filtreSalle == $salle['nom'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($salle['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($filtreEtat || $filtreSalle): ?>
                                <a href="?section=liste" class="inline-flex items-center justify-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">Effacer filtres</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Liste des réservations -->
                    <?php if (empty($reservations)): ?>
                        <p class="text-center text-gray-500 py-8 bg-gray-50 rounded-lg border border-gray-200">Aucune réservation trouvée.</p>
                    <?php else: ?> trouvée.</p>
                        <div class="space-y-4">
                            <?php foreach ($reservations as $reservation): ?>
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                    <div class="p-6">
                                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                            <div class="flex-1">
                                                <h3 class="text-xl font-semibold text-gray-900 mb-3"><?= htmlspecialchars($reservation['salle']) ?></h3>
                                                <div class="space-y-2">
                                                    <p class="text-sm text-gray-600"><strong>📅 Date :</strong> <?= date('l j F Y', strtotime($reservation['date'])) ?></p>
                                                    <p class="text-sm text-gray-600"><strong>🕐 Horaire :</strong> <?= $reservation['heure_debut'] ?> - <?= $reservation['heure_fin'] ?></p>
                                                    <p class="text-sm text-gray-600"><strong>👤 Responsable :</strong> <?= htmlspecialchars($reservation['responsable']) ?></p>
                                                    <p class="text-sm text-gray-600"><strong>📝 Motif :</strong> <?= htmlspecialchars($reservation['motif']) ?></p>
                                                </div>
                                            </div>
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between lg:flex-col lg:items-end gap-3">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php 
                                                    echo $reservation['etat'] == 'Provisoire' ? 'bg-yellow-100 text-yellow-800' : 
                                                         ($reservation['etat'] == 'Confirmé' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); 
                                                ?>">
                                                    <?= htmlspecialchars($reservation['etat']) ?>
                                                </span>
                                                <div class="flex flex-wrap gap-2">
                                                    <?php if ($reservation['etat'] == 'Provisoire'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="update_etat">
                                                            <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                            <input type="hidden" name="etat" value="Confirmé">
                                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:ring-green-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">Confirmer</button>
                                                        </form>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="update_etat">
                                                            <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                            <input type="hidden" name="etat" value="Annulé">
                                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">Annuler</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:ring-red-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">Supprimer</button>
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

    <script src="assets/script.js"></script>
</body>
</html>