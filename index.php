<?php
session_start();
require_once "config/database.php";

// Traitement de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Redirection si déjà connecté
if (isset($_SESSION['user'])) {
    header('Location: controller/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nom_utilisateur = $_POST['nom_utilisateur'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if ($nom_utilisateur && $mot_de_passe) {
        try {
            $database = new Database();
            $userManager = new UserManager($database);
            
            $ok = $userManager->verifyLogin($nom_utilisateur, $mot_de_passe); // Renvoie true si les identifiants sont corrects
            if ($ok) {
                $user = $_SESSION['user'];
                
                header("Location : controller/main.php");
                exit;
            } else {
                $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
            }
        } catch (Exception $e) {
            $error = 'Erreur de connexion : ' . $e->getMessage();
        }
    } else {
        $error = 'Veuillez saisir votre nom d\'utilisateur et votre mot de passe.';
    }
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Connectez vous</title>
        <style>
            .group {
            position: relative;
            }

            .form {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            border: 1px solid white;
            padding: 120px 40px;
            padding-top: 60px;
            padding-bottom: 90px;
            padding-right: 40px;
            padding-left: 40px;
            background-color: black;
            border-radius: 20px;
            position: relative;

            width: 300px;
            height: 400px;
            }

            .form p {
            padding-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: .5px;
            color: white;
            }

            .container-1 {
            padding-top: 30px;
            }

            .main-input {
            font-size: 16px;
            padding: 10px 10px 10px 5px;
            display: block;
            width: 185px;
            border: none;
            border-bottom: 1px solid #6c6c6c;
            background: transparent;
            color: #ffffff;
            }

            .main-input:focus {
            outline: none;
            border-bottom-color: #42ff1c;
            }

            .lebal-email {
            color: #999999;
            font-size: 18px;
            font-weight: normal;
            position: absolute;
            pointer-events: none;
            left: 5px;
            top: 10px;
            transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -webkit-transition: 0.2s ease all;
            }

            .main-input:focus ~ .lebal-email,
            .main-input:valid ~ .lebal-email {
            top: -20px;
            font-size: 14px;
            color: #42ff1c;
            }

            .highlight-span {
            position: absolute;
            height: 60%;
            width: 0px;
            top: 25%;
            left: 0;
            pointer-events: none;
            opacity: 0.5;
            }

            .main-input:focus ~ .highlight-span {
            -webkit-animation: input-focus 0.3s ease;
            animation: input-focus 0.3s ease;
            }

            @keyframes input-focus {
            from {
                background: #42ff1c;
            }

            to {
                width: 185px;
            }
            }

            .submit {
            margin-top: 1.2rem;
            padding: 10px 20px;
            border-radius: 10px;
            }
        </style>
    </head>
    <body style="margin: 0; padding: 0; min-height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f0f0f0;">
        
        <?php if ($error): ?>
            <div style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 15px 20px; border-radius: 5px; z-index: 1000;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="color: #333; margin-bottom: 10px;">🏢 VALRES2</h1>
            <p style="color: #666; margin-bottom: 30px;">Système de Gestion des Réservations de Salles</p>
            
            <!-- Informations de connexion -->
            <div style="background: #e8f4fd; border: 1px solid #b3d9f7; border-radius: 8px; padding: 15px; margin-bottom: 20px; text-align: left; max-width: 400px;">
                <h3 style="margin: 0 0 10px 0; color: #1a5490;">Comptes de démonstration :</h3>
                <div style="font-size: 12px; color: #4a5568; line-height: 1.4;">
                    <strong>Secrétariat :</strong> BANDILELLA / 123<br>
                    <strong>Responsable :</strong> BIACQUEL / 123<br>
                    <strong>Administrateur :</strong> SILBERT / 123<br>
                    <strong>Utilisateur :</strong> PERNOT / 123
                </div>
            </div>
        </div>
        
        <form class="form" action="index.php" method="post">
            <p>Se connecter</p>
            <div class="group">
                <input required="true" class="main-input" type="text" name="nom_utilisateur" value="<?= htmlspecialchars($_POST['nom_utilisateur'] ?? '') ?>" required>
                <span class="highlight-span"></span>
                <label class="lebal-email">Nom d'utilisateur</label>
            </div>
            <div class="container-1">
                <div class="group">
                <input required="true" class="main-input" type="password" name="mot_de_passe" required>
                <span class="highlight-span"></span>
                <label class="lebal-email">Mot de passe</label>
                </div>
            </div>
            <button class="submit" type="submit">Se connecter</button>
        </form>
    </body>
</html>