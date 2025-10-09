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
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏢 VALRES2 - Gestion des Réservations</h1>
            <nav>
                <a href="?section=nouvelle" class="btn btn-primary <?= $section == 'nouvelle' ? 'active' : '' ?>">
                    ➕ Nouvelle Réservation
                </a>
                <a href="?section=liste" class="btn btn-secondary <?= $section == 'liste' ? 'active' : '' ?>">
                    📋 Liste des Réservations
                </a>
                <a href="?section=disponibilites" class="btn btn-secondary <?= $section == 'disponibilites' ? 'active' : '' ?>">
                    📅 Consulter Disponibilités
                </a>
                <a href="export_xml.php" class="btn btn-success" target="_blank">
                    📄 Exporter XML
                </a>
            </nav>
        </header>

        <main>
            <?php if ($message): ?>
                <div class="message message-<?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($section == 'nouvelle'): ?>
                <!-- Section Nouvelle Réservation -->
                <section class="section">
                    <h2>Créer une Nouvelle Réservation</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-group">
                            <label for="salle">Salle :</label>
                            <select id="salle" name="salle" required>
                                <option value="">Sélectionner une salle</option>
                                <?php foreach ($salles as $salle): ?>
                                    <option value="<?= htmlspecialchars($salle['nom']) ?>">
                                        <?= htmlspecialchars($salle['nom']) ?> (<?= $salle['capacite'] ?> places)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Date :</label>
                            <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="heure_debut">Heure de début :</label>
                                <input type="time" id="heure_debut" name="heure_debut" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="heure_fin">Heure de fin :</label>
                                <input type="time" id="heure_fin" name="heure_fin" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="responsable">Responsable :</label>
                            <input type="text" id="responsable" name="responsable" required 
                                   placeholder="Nom du responsable" maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="motif">Motif :</label>
                            <textarea id="motif" name="motif" required 
                                      placeholder="Raison de la réservation" rows="3"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Créer la Réservation</button>
                            <a href="?section=liste" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </section>

            <?php elseif ($section == 'disponibilites'): ?>
                <!-- Section Disponibilités -->
                <section class="section">
                    <h2>Consulter les Disponibilités</h2>
                    <form method="GET" action="">
                        <input type="hidden" name="section" value="disponibilites">
                        <div class="form-group">
                            <label for="date_consultation">Date :</label>
                            <input type="date" id="date_consultation" name="date_consultation" 
                                   value="<?= $_GET['date_consultation'] ?? date('Y-m-d') ?>" 
                                   min="<?= date('Y-m-d') ?>">
                            <button type="submit" class="btn btn-primary">Vérifier</button>
                        </div>
                    </form>

                    <?php if (isset($_GET['date_consultation'])): ?>
                        <?php $disponibilites = $reservationManager->getDisponibilites($_GET['date_consultation']); ?>
                        <div class="disponibilite-grid">
                            <?php foreach ($disponibilites as $dispo): ?>
                                <div class="salle-disponibilite">
                                    <h4><?= htmlspecialchars($dispo['salle']) ?></h4>
                                    <?php if (empty($dispo['reservations'])): ?>
                                        <div class="creneau creneau-libre">🟢 Entièrement libre</div>
                                    <?php else: ?>
                                        <?php foreach ($dispo['reservations'] as $res): ?>
                                            <div class="creneau creneau-occupe">
                                                🔴 <?= $res['heure_debut'] ?>-<?= $res['heure_fin'] ?> 
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
                <section class="section">
                    <h2>Liste des Réservations</h2>
                    
                    <!-- Filtres -->
                    <form method="GET" action="" class="filtres-form">
                        <input type="hidden" name="section" value="liste">
                        <div class="filtres">
                            <select name="filtre_etat" onchange="this.form.submit()">
                                <option value="">Tous les états</option>
                                <option value="Provisoire" <?= $filtreEtat == 'Provisoire' ? 'selected' : '' ?>>Provisoire</option>
                                <option value="Confirmé" <?= $filtreEtat == 'Confirmé' ? 'selected' : '' ?>>Confirmé</option>
                                <option value="Annulé" <?= $filtreEtat == 'Annulé' ? 'selected' : '' ?>>Annulé</option>
                            </select>
                            <select name="filtre_salle" onchange="this.form.submit()">
                                <option value="">Toutes les salles</option>
                                <?php foreach ($salles as $salle): ?>
                                    <option value="<?= htmlspecialchars($salle['nom']) ?>" 
                                            <?= $filtreSalle == $salle['nom'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($salle['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($filtreEtat || $filtreSalle): ?>
                                <a href="?section=liste" class="btn btn-secondary btn-small">Effacer filtres</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Liste des réservations -->
                    <?php if (empty($reservations)): ?>
                        <p class="no-results">Aucune réservation trouvée.</p>
                    <?php else: ?>
                        <div class="reservations-list">
                            <?php foreach ($reservations as $reservation): ?>
                                <div class="reservation-card">
                                    <div class="reservation-header">
                                        <div class="reservation-info">
                                            <h3><?= htmlspecialchars($reservation['salle']) ?></h3>
                                            <p><strong>📅 Date :</strong> <?= date('l j F Y', strtotime($reservation['date'])) ?></p>
                                            <p><strong>🕐 Horaire :</strong> <?= $reservation['heure_debut'] ?> - <?= $reservation['heure_fin'] ?></p>
                                            <p><strong>👤 Responsable :</strong> <?= htmlspecialchars($reservation['responsable']) ?></p>
                                            <p><strong>📝 Motif :</strong> <?= htmlspecialchars($reservation['motif']) ?></p>
                                        </div>
                                        <div class="reservation-actions-container">
                                            <span class="etat etat-<?= strtolower(str_replace(['é', 'ô'], ['e', 'o'], $reservation['etat'])) ?>">
                                                <?= htmlspecialchars($reservation['etat']) ?>
                                            </span>
                                            <div class="reservation-actions">
                                                <?php if ($reservation['etat'] == 'Provisoire'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_etat">
                                                        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                        <input type="hidden" name="etat" value="Confirmé">
                                                        <button type="submit" class="btn btn-success btn-small">✅ Confirmer</button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_etat">
                                                        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                        <input type="hidden" name="etat" value="Annulé">
                                                        <button type="submit" class="btn btn-warning btn-small">❌ Annuler</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-small">🗑️ Supprimer</button>
                                                </form>
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