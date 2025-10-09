<?php 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erreur
                </h5>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-4x text-danger mb-4"></i>
                <h4>Une erreur est survenue</h4>
                <p class="text-muted">
                    <?= htmlspecialchars($message ?? 'Erreur inconnue') ?>
                </p>
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>
                        Retour à l'accueil
                    </a>
                    <button onclick="history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Page précédente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once VIEWS_PATH . '/layout/main.php';
?>