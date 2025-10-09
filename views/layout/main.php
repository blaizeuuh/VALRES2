<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-alt me-2"></i>
                <?= APP_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Menu selon le rôle -->
                        <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cogs me-1"></i> Administration
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="index.php?controller=admin&action=dashboard">
                                        <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?controller=admin&action=utilisateurs">
                                        <i class="fas fa-users me-2"></i> Gestion utilisateurs
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?controller=admin&action=reservations">
                                        <i class="fas fa-eye me-2"></i> Consulter réservations
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?controller=admin&action=generer_xml_utilisateurs">
                                        <i class="fas fa-file-export me-2"></i> Export XML utilisateurs
                                    </a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (in_array($_SESSION['user_role'], [ROLE_SECRETARIAT, ROLE_RESPONSABLE])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="reservationDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-calendar me-1"></i> Réservations
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if ($_SESSION['user_role'] === ROLE_SECRETARIAT): ?>
                                        <li><a class="dropdown-item" href="index.php?controller=reservation&action=dashboard">
                                            <i class="fas fa-tachometer-alt me-2"></i> Gestion réservations
                                        </a></li>
                                    <?php endif; ?>
                                    
                                    <li><a class="dropdown-item" href="index.php?controller=reservation&action=consulter">
                                        <i class="fas fa-search me-2"></i> Consulter salles
                                    </a></li>
                                    
                                    <?php if ($_SESSION['user_role'] === ROLE_RESPONSABLE): ?>
                                        <li><a class="dropdown-item" href="index.php?controller=reservation&action=mes_reservations">
                                            <i class="fas fa-list me-2"></i> Mes réservations
                                        </a></li>
                                    <?php endif; ?>
                                    
                                    <li><a class="dropdown-item" href="index.php?controller=reservation&action=ajouter">
                                        <i class="fas fa-plus me-2"></i> Nouvelle réservation
                                    </a></li>
                                    
                                    <?php if ($_SESSION['user_role'] === ROLE_SECRETARIAT): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="index.php?controller=reservation&action=generer_xml_reservations">
                                            <i class="fas fa-file-export me-2"></i> Export XML réservations
                                        </a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['user_role'] === ROLE_USER): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=reservation&action=consulter">
                                    <i class="fas fa-search me-1"></i> Consulter salles
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?= htmlspecialchars($_SESSION['user_name']) ?>
                                <span class="badge bg-secondary ms-1"><?= ucfirst($_SESSION['user_role']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?controller=auth&action=logout">
                                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?controller=auth&action=login">
                                <i class="fas fa-sign-in-alt me-1"></i> Connexion
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="container mt-4">
        <!-- Messages d'alerte -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['info'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?= htmlspecialchars($_GET['info']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Contenu de la page -->
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-light mt-5 py-4">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-6">
                    <h6>M2L - Maison des Ligues de Lorraine</h6>
                    <p class="text-muted small">Système de réservation de salles</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small">
                        <?= APP_NAME ?> v<?= APP_VERSION ?><br>
                        Développé dans le cadre de l'AP3 - BTS SIO
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>