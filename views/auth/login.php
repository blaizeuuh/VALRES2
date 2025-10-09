<?php 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Connexion
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?controller=auth&action=login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>
                            Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>
                            Mot de passe
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Se connecter
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center text-muted">
                <small>
                    <i class="fas fa-shield-alt me-1"></i>
                    Connexion sécurisée - VALRES2
                </small>
            </div>
        </div>
        
        <!-- Informations de test -->
        <div class="card mt-4 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Comptes de test
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary">Administrateur</h6>
                        <p class="small mb-2">
                            <strong>Email:</strong> admin@m2l.fr<br>
                            <strong>Mot de passe:</strong> admin123
                        </p>
                        
                        <h6 class="text-success">Secrétariat</h6>
                        <p class="small mb-2">
                            <strong>Email:</strong> secretariat@m2l.fr<br>
                            <strong>Mot de passe:</strong> secret123
                        </p>
                        
                        <h6 class="text-warning">Responsable</h6>
                        <p class="small mb-2">
                            <strong>Email:</strong> responsable@m2l.fr<br>
                            <strong>Mot de passe:</strong> resp123
                        </p>
                        
                        <h6 class="text-secondary">Utilisateur</h6>
                        <p class="small mb-0">
                            <strong>Email:</strong> utilisateur@m2l.fr<br>
                            <strong>Mot de passe:</strong> user123
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once VIEWS_PATH . '/layout/main.php';
?>