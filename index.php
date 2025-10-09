<?php
/**
 * VALRES2 - Application de réservation de salles M2L
 * Point d'entrée principal de l'application
 * Architecture MVC
 */

// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrage de la session
session_start();

// Chargement de la configuration
require_once 'config/config.php';
require_once 'config/database.php';

// Chargement des classes
spl_autoload_register(function ($class) {
    $directories = ['controllers', 'models'];
    foreach ($directories as $directory) {
        $file = $directory . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Routage principal
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

try {
    // Sécurité : validation des paramètres
    $controller = preg_replace('/[^a-zA-Z0-9_]/', '', $controller);
    $action = preg_replace('/[^a-zA-Z0-9_]/', '', $action);
    
    $controllerName = ucfirst($controller) . 'Controller';
    
    if (class_exists($controllerName)) {
        $controllerInstance = new $controllerName();
        
        if (method_exists($controllerInstance, $action)) {
            $controllerInstance->$action();
        } else {
            throw new Exception("Action non trouvée : $action");
        }
    } else {
        throw new Exception("Contrôleur non trouvé : $controllerName");
    }
    
} catch (Exception $e) {
    // Gestion des erreurs
    require_once 'views/errors/404.php';
}
?>
