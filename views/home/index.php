<?php 
ob_start(); 
?>

<div class="row">
    <div class="col-12">
        <div class="jumbotron bg-primary text-white rounded p-5 mb-4">
            <h1 class="display-4">
                <i class="fas fa-calendar-alt me-3"></i>
                Bienvenue sur VALRES2
            </h1>
            <p class="lead">
                Système de réservation de salles de la Maison des Ligues de Lorraine
            </p>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">
            <p>
                Application sécurisée de gestion des réservations développée en architecture MVC.
            </p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a class="btn btn-light btn-lg" href="index.php?controller=auth&action=login" role="button">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Se connecter
                </a>
            <?php else: ?>
                <a class="btn btn-light btn-lg" href="index.php?controller=home&action=dashboard" role="button">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Accéder au tableau de bord
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!isset($_SESSION['user_id'])): ?>
<div class="row">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Gestion des utilisateurs</h5>
                <p class="card-text">
                    Administration complète des accès avec différents niveaux de droits :
                    Administrateur, Secrétariat, Responsable, Utilisateur.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                <h5 class="card-title">Réservations</h5>
                <p class="card-text">
                    Système complet de réservation avec gestion des états 
                    (provisoire, confirmé, annulé) et consultation des disponibilités.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-shield-alt fa-2x text-warning mb-3"></i>
                <h6 class="card-title">Sécurité renforcée</h6>
                <p class="card-text small">
                    Protection CSRF, hashage des mots de passe, gestion des sessions sécurisée.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-file-export fa-2x text-info mb-3"></i>
                <h6 class="card-title">Export XML</h6>
                <p class="card-text small">
                    Génération automatique de fichiers XML pour les utilisateurs et réservations.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-mobile-alt fa-2x text-secondary mb-3"></i>
                <h6 class="card-title">Interface responsive</h6>
                <p class="card-text small">
                    Design adaptatif pour tous les appareils avec Bootstrap 5.
                </p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Votre profil
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nom :</strong> <?= htmlspecialchars($_SESSION['user_name']) ?><br>
                        <strong>Email :</strong> <?= htmlspecialchars($_SESSION['user_email']) ?><br>
                        <strong>Rôle :</strong> 
                        <span class="badge bg-primary"><?= ucfirst($_SESSION['user_role']) ?></span>
                    </div>
                    <div class="col-md-6">
                        <h6>Actions disponibles :</h6>
                        <ul class="list-unstyled">
                            <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                                <li><i class="fas fa-check text-success me-2"></i> Gestion des utilisateurs</li>
                                <li><i class="fas fa-check text-success me-2"></i> Consultation des réservations</li>
                                <li><i class="fas fa-check text-success me-2"></i> Export XML utilisateurs</li>
                            <?php elseif ($_SESSION['user_role'] === ROLE_SECRETARIAT): ?>
                                <li><i class="fas fa-check text-success me-2"></i> Validation des réservations</li>
                                <li><i class="fas fa-check text-success me-2"></i> Création de réservations</li>
                                <li><i class="fas fa-check text-success me-2"></i> Export XML réservations</li>
                            <?php elseif ($_SESSION['user_role'] === ROLE_RESPONSABLE): ?>
                                <li><i class="fas fa-check text-success me-2"></i> Création de réservations</li>
                                <li><i class="fas fa-check text-success me-2"></i> Gestion de ses réservations</li>
                            <?php else: ?>
                                <li><i class="fas fa-check text-success me-2"></i> Consultation des salles</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-info">
            <h6><i class="fas fa-graduation-cap me-2"></i>Projet Académique</h6>
            <p class="mb-0">
                Cette application a été développée dans le cadre de l'AP3 (Activité Professionnelle de Synthèse) 
                du BTS SIO (Services Informatiques aux Organisations). Le projet illustre la mise en œuvre 
                d'une architecture MVC sécurisée avec séparation des responsabilités entre deux développeurs.
            </p>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once VIEWS_PATH . '/layout/main.php';
?>