<?php
// export_xml.php - Générateur d'export XML des réservations confirmées
require_once 'config/database.php';

$database = new Database();
$reservationManager = new ReservationManager($database);

// Traitement du formulaire
if ($_POST) {
    $dateDebut = $_POST['date_debut'];
    $dateFin = $_POST['date_fin'];
    
    // Validation des dates
    if ($dateDebut && $dateFin && $dateDebut <= $dateFin) {
        $reservations = $reservationManager->getReservationsConfirmeesParPeriode($dateDebut, $dateFin);
        
        if (count($reservations) > 0) {
            // Générer le XML
            $xml = genererXML($reservations, $dateDebut, $dateFin);
            
            // Envoyer le fichier en téléchargement
            $filename = "reservations_{$dateDebut}_{$dateFin}.xml";
            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($xml));
            echo $xml;
            exit;
        } else {
            $error = "Aucune réservation confirmée trouvée pour cette période.";
        }
    } else {
        $error = "Veuillez saisir des dates valides (date de fin >= date de début).";
    }
}

function genererXML($reservations, $dateDebut, $dateFin) {
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    
    // Élément racine
    $root = $xml->createElement('reservations');
    $xml->appendChild($root);
    
    // Métadonnées
    $metadata = $xml->createElement('metadata');
    $root->appendChild($metadata);
    
    $metadata->appendChild($xml->createElement('dateExport', date('c')));
    $metadata->appendChild($xml->createElement('periodeDebut', $dateDebut));
    $metadata->appendChild($xml->createElement('periodeFin', $dateFin));
    $metadata->appendChild($xml->createElement('nombreReservations', count($reservations)));
    
    // Liste des réservations
    $listeReservations = $xml->createElement('listeReservations');
    $root->appendChild($listeReservations);
    
    foreach ($reservations as $res) {
        $reservation = $xml->createElement('reservation');
        $reservation->setAttribute('id', $res['id']);
        
        $reservation->appendChild($xml->createElement('salle', htmlspecialchars($res['salle'])));
        $reservation->appendChild($xml->createElement('date', $res['date']));
        $reservation->appendChild($xml->createElement('heureDebut', $res['heure_debut']));
        $reservation->appendChild($xml->createElement('heureFin', $res['heure_fin']));
        $reservation->appendChild($xml->createElement('responsable', htmlspecialchars($res['responsable'])));
        $reservation->appendChild($xml->createElement('motif', htmlspecialchars($res['motif'])));
        $reservation->appendChild($xml->createElement('etat', $res['etat']));
        $reservation->appendChild($xml->createElement('dateCreation', $res['date_creation']));
        
        $listeReservations->appendChild($reservation);
    }
    
    return $xml->saveXML();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export XML - VALRES2</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📄 Export XML des Réservations</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">← Retour</a>
            </nav>
        </header>

        <main>
            <?php if (isset($error)): ?>
                <div class="message message-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <section class="section">
                <h2>Exporter les Réservations Confirmées</h2>
                <p>Sélectionnez la période pour laquelle vous souhaitez exporter les réservations confirmées au format XML.</p>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_debut">Date de début :</label>
                            <input type="date" id="date_debut" name="date_debut" required 
                                   value="<?= $_POST['date_debut'] ?? date('Y-m-d', strtotime('monday this week')) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_fin">Date de fin :</label>
                            <input type="date" id="date_fin" name="date_fin" required 
                                   value="<?= $_POST['date_fin'] ?? date('Y-m-d', strtotime('sunday this week')) ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">📥 Télécharger XML</button>
                    </div>
                </form>
            </section>

            <!-- Aperçu des réservations confirmées -->
            <section class="section">
                <h2>Aperçu des Réservations Confirmées</h2>
                <?php
                $dateDebut = $_GET['date_debut'] ?? date('Y-m-d', strtotime('monday this week'));
                $dateFin = $_GET['date_fin'] ?? date('Y-m-d', strtotime('sunday this week'));
                $reservationsApercu = $reservationManager->getReservationsConfirmeesParPeriode($dateDebut, $dateFin);
                ?>
                
                <form method="GET" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="apercu_debut">Période d'aperçu - Début :</label>
                            <input type="date" id="apercu_debut" name="date_debut" 
                                   value="<?= $dateDebut ?>" onchange="this.form.submit()">
                        </div>
                        
                        <div class="form-group">
                            <label for="apercu_fin">Fin :</label>
                            <input type="date" id="apercu_fin" name="date_fin" 
                                   value="<?= $dateFin ?>" onchange="this.form.submit()">
                        </div>
                    </div>
                </form>

                <?php if (empty($reservationsApercu)): ?>
                    <p class="no-results">Aucune réservation confirmée pour cette période.</p>
                <?php else: ?>
                    <div class="reservations-summary">
                        <p><strong><?= count($reservationsApercu) ?></strong> réservation(s) confirmée(s) du 
                           <?= date('d/m/Y', strtotime($dateDebut)) ?> au <?= date('d/m/Y', strtotime($dateFin)) ?></p>
                        
                        <div class="reservations-list">
                            <?php foreach ($reservationsApercu as $res): ?>
                                <div class="reservation-card reservation-card-small">
                                    <div class="reservation-info">
                                        <h4><?= htmlspecialchars($res['salle']) ?></h4>
                                        <p>📅 <?= date('d/m/Y', strtotime($res['date'])) ?> 
                                           🕐 <?= $res['heure_debut'] ?>-<?= $res['heure_fin'] ?></p>
                                        <p>👤 <?= htmlspecialchars($res['responsable']) ?></p>
                                        <p>📝 <?= htmlspecialchars($res['motif']) ?></p>
                                    </div>
                                    <span class="etat etat-confirme">Confirmé</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>