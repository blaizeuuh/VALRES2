<?php 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-search me-2"></i>
                    Page non trouvée
                </h5>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-search fa-4x text-warning mb-4"></i>
                <h4>Erreur 404</h4>
                <p class="text-muted">
                    La page que vous recherchez n'existe pas ou a été déplacée.
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