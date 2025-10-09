# VALRES2 - Application de Réservation de Salles M2L

![PHP](https://img.shields.io/badge/PHP-8.x-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.x-orange)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.1-purple)
![License](https://img.shields.io/badge/License-Academic-green)

## 📋 Présentation

**VALRES2** est une application web sécurisée de réservation de salles développée pour la **Maison des Ligues de Lorraine (M2L)** dans le cadre de l'AP3 du BTS SIO.

Cette nouvelle version remplace l'ancienne application non conforme aux critères de sécurité actuels et implémente une architecture MVC robuste avec séparation des responsabilités entre deux développeurs.

## 🎯 Objectifs du Projet

- ✅ **Sécurité renforcée** : Protection CSRF, hashage bcrypt, sessions sécurisées
- ✅ **Architecture MVC** : Séparation claire des responsabilités
- ✅ **Travail collaboratif** : Division du développement entre 2 étudiants
- ✅ **Gestion complète** : Administration des utilisateurs et réservations
- ✅ **Export XML** : Génération automatique de rapports

## 🚀 Fonctionnalités

### 👨‍💼 Partie Administration (Étudiant 1)
- **Gestion des utilisateurs** : CRUD complet avec gestion des rôles
- **Authentification sécurisée** : Connexion/déconnexion avec protection CSRF
- **Consultation des réservations** : Vue d'ensemble avec filtres
- **Export XML** : Génération du fichier des utilisateurs de début d'année

### 📅 Partie Réservations (Étudiant 2)
- **Gestion des réservations** : Création, modification, suppression
- **Validation des réservations** : Workflow provisoire → confirmé/annulé
- **Consultation des salles** : Recherche de disponibilités
- **Export XML** : Génération du fichier des réservations validées

## 👥 Rôles Utilisateurs

| Rôle | Permissions |
|------|-------------|
| **Administrateur** | Gestion complète des utilisateurs, consultation toutes réservations |
| **Secrétariat** | Validation des réservations, création réservations confirmées |
| **Responsable** | Création réservations provisoires, gestion de ses réservations |
| **Utilisateur** | Consultation des salles disponibles uniquement |

## 🛠 Technologies

- **Backend** : PHP 8.x (Architecture MVC)
- **Base de données** : MySQL 8.x
- **Frontend** : HTML5, CSS3, JavaScript ES6, Bootstrap 5
- **Sécurité** : bcrypt, protection CSRF, sessions sécurisées
- **Export** : XML avec DOMDocument

## 📦 Installation

### Prérequis
- PHP 8.x avec extensions PDO, XML, JSON
- MySQL 8.x
- Serveur web (Apache/Nginx)

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone https://github.com/votre-username/VALRES2.git
cd VALRES2
```

2. **Configuration de la base de données**
```bash
mysql -u root -p < database/valres2.sql
```

3. **Configuration de l'application**
Modifier `config/config.php` avec vos paramètres :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'valres2');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_password');
define('SECURITY_SALT', 'votre_salt_unique');
```

4. **Permissions des dossiers**
```bash
chmod 755 exports/
chmod 755 assets/
```

5. **Accès à l'application**
Ouvrir dans le navigateur : `http://localhost/VALRES2`

## 🔐 Comptes de Test

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Administrateur | admin@m2l.fr | admin123 |
| Secrétariat | secretariat@m2l.fr | secret123 |
| Responsable | responsable@m2l.fr | resp123 |
| Utilisateur | utilisateur@m2l.fr | user123 |

> ⚠️ **Important** : Changer ces mots de passe en production !

## 📁 Structure du Projet

```
VALRES2/
├── 📄 index.php                    # Point d'entrée
├── 📁 config/                      # Configuration
├── 📁 controllers/                 # Contrôleurs MVC
│   ├── AdminController.php         # 👨‍💼 Partie Étudiant 1
│   └── ReservationController.php   # 📅 Partie Étudiant 2
├── 📁 models/                      # Modèles métier
├── 📁 views/                       # Vues utilisateur
├── 📁 assets/                      # CSS, JS, images
├── 📁 exports/                     # Fichiers XML générés
├── 📁 database/                    # Scripts SQL
├── 📁 tests/                       # Tests et jeux d'essai
└── 📁 docs/                        # Documentation
```

## 🧪 Tests

### Jeux d'Essai Recommandés

#### Étudiant 1 - Administration
- [x] Création d'utilisateurs avec différents rôles
- [x] Modification des informations utilisateur
- [x] Désactivation/suppression d'utilisateurs
- [x] Export XML des utilisateurs
- [x] Consultation des réservations avec filtres

#### Étudiant 2 - Réservations
- [x] Création de réservations sans conflit
- [x] Gestion des conflits de réservation
- [x] Workflow de validation (provisoire → confirmé)
- [x] Recherche de salles disponibles
- [x] Export XML des réservations validées

### Lancer les Tests
```bash
# Tests unitaires (à implémenter)
php tests/run_tests.php

# Tests manuels avec jeu de données
# Utiliser les comptes de test ci-dessus
```

## 📊 Captures d'Écran

### Page d'Accueil
![Accueil](docs/screenshots/accueil.png)

### Administration des Utilisateurs
![Administration](docs/screenshots/admin.png)

### Gestion des Réservations
![Réservations](docs/screenshots/reservations.png)

## 🤝 Contribution

Ce projet est développé dans un cadre académique avec répartition des tâches :

### Étudiant 1 - Administration
- 📋 Gestion des utilisateurs (`AdminController.php`)
- 🔐 Système d'authentification (`AuthController.php`)
- 📊 Tableaux de bord administrateur
- 📤 Export XML des utilisateurs

### Étudiant 2 - Réservations
- 📅 Gestion des réservations (`ReservationController.php`)
- 🔍 Recherche de disponibilités
- ✅ Workflow de validation
- 📤 Export XML des réservations

## 📚 Documentation

- [📖 Documentation Technique](docs/DOCUMENTATION_TECHNIQUE.md)
- [🏗 Architecture MVC](docs/ARCHITECTURE.md)
- [🔒 Guide de Sécurité](docs/SECURITE.md)
- [🧪 Guide de Tests](docs/TESTS.md)

## 🔮 Évolutions Futures

- [ ] Interface AJAX pour recherche temps réel
- [ ] Notifications par email
- [ ] Gestion des récurrences
- [ ] API REST
- [ ] Application mobile

## 📄 Licence

Ce projet est développé dans un cadre académique pour le BTS SIO.  
© 2024 - M2L - Maison des Ligues de Lorraine

## 📞 Support

Pour toute question concernant ce projet académique :
- 📧 Email : contact@m2l.fr
- 📚 Documentation : [docs/](docs/)
- 🐛 Issues : [GitHub Issues](https://github.com/votre-username/VALRES2/issues)

---

**Développé avec ❤️ par les étudiants BTS SIO dans le cadre de l'AP3**

![M2L Logo](docs/images/m2l-logo.png)