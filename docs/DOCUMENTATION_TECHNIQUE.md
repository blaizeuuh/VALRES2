# VALRES2 - Documentation Technique

## Présentation du Projet

**VALRES2** est une application web de réservation de salles développée pour la Maison des Ligues de Lorraine (M2L) dans le cadre de l'AP3 du BTS SIO.

### Objectifs
- Remplacer l'ancienne application non sécurisée
- Implémenter une architecture MVC robuste
- Respecter les critères de sécurité modernes
- Diviser le développement entre deux étudiants

## Architecture Technique

### Structure MVC
```
VALRES2/
├── index.php                 # Point d'entrée principal
├── config/                   # Configuration
│   ├── config.php           # Configuration générale
│   └── database.php         # Gestionnaire de base de données
├── controllers/              # Contrôleurs MVC
│   ├── BaseController.php   # Contrôleur de base
│   ├── HomeController.php   # Page d'accueil
│   ├── AuthController.php   # Authentification
│   ├── AdminController.php  # Administration (Étudiant 1)
│   └── ReservationController.php # Réservations (Étudiant 2)
├── models/                   # Modèles métier
│   ├── User.php             # Gestion utilisateurs
│   ├── Reservation.php      # Gestion réservations
│   └── Salle.php           # Gestion salles
├── views/                    # Vues (interface utilisateur)
│   ├── layout/              # Templates généraux
│   ├── home/                # Pages d'accueil
│   ├── auth/                # Authentification
│   ├── admin/               # Administration
│   ├── reservation/         # Réservations
│   └── errors/              # Pages d'erreur
├── assets/                   # Ressources statiques
│   ├── css/                 # Feuilles de style
│   └── js/                  # Scripts JavaScript
├── exports/                  # Fichiers XML générés
├── tests/                    # Tests et jeux d'essai
├── docs/                     # Documentation
└── database/                 # Scripts SQL
```

### Technologies Utilisées
- **Backend** : PHP 8.x avec architecture MVC
- **Base de données** : MySQL 8.x
- **Frontend** : HTML5, CSS3, JavaScript ES6, Bootstrap 5
- **Sécurité** : Hashage bcrypt, protection CSRF, sessions sécurisées
- **Export** : Génération XML avec DOMDocument

## Répartition des Tâches

### Étudiant 1 - Partie Administration
**Responsabilité** : `AdminController.php` et vues associées

#### Fonctionnalités
1. **Gestion des accès utilisateurs**
   - Ajouter un utilisateur (`/admin/ajouter_utilisateur`)
   - Modifier un utilisateur (`/admin/modifier_utilisateur`)
   - Supprimer/désactiver un utilisateur (`/admin/supprimer_utilisateur`)
   - Lister les utilisateurs (`/admin/utilisateurs`)

2. **Gestion de l'authentification**
   - Implémentation dans `AuthController.php`
   - Connexion sécurisée avec protection CSRF
   - Gestion des sessions et timeouts

3. **Consultation des réservations**
   - Vue d'ensemble de toutes les réservations (`/admin/reservations`)
   - Filtrage par état (provisoire, confirmé, annulé)
   - Tableau de bord avec statistiques

4. **Export XML des utilisateurs**
   - Génération du fichier XML des utilisateurs actifs (`/admin/generer_xml_utilisateurs`)
   - Format standardisé avec date de génération

#### Vues à développer
- `views/admin/dashboard.php`
- `views/admin/utilisateurs/liste.php`
- `views/admin/utilisateurs/ajouter.php`
- `views/admin/utilisateurs/modifier.php`
- `views/admin/reservations/consulter.php`

### Étudiant 2 - Partie Réservations
**Responsabilité** : `ReservationController.php` et vues associées

#### Fonctionnalités
1. **Gestion des réservations**
   - Ajouter une réservation (`/reservation/ajouter`)
   - Modifier une réservation (`/reservation/modifier`)
   - Supprimer une réservation (`/reservation/supprimer`)
   - Lister ses réservations (`/reservation/mes_reservations`)

2. **Validation des réservations (Secrétariat)**
   - Confirmer une réservation (`/reservation/confirmer`)
   - Annuler une réservation (`/reservation/annuler`)
   - Tableau de bord de gestion (`/reservation/dashboard`)

3. **Consultation des salles disponibles**
   - Recherche par date et créneaux horaires (`/reservation/consulter`)
   - Vérification des conflits de réservation
   - Affichage des équipements et capacités

4. **Export XML des réservations**
   - Génération du fichier XML des réservations validées d'une semaine
   - Format avec détails complets des réservations

#### Vues à développer
- `views/reservation/dashboard.php`
- `views/reservation/consulter.php`
- `views/reservation/mes_reservations.php`
- `views/reservation/ajouter.php`
- `views/reservation/modifier.php`

## Rôles et Permissions

### Administrateur
- **Accès** : Toutes les fonctionnalités d'administration
- **Permissions** :
  - CRUD complet sur les utilisateurs
  - Consultation de toutes les réservations
  - Export XML des utilisateurs
  - Accès au tableau de bord administrateur

### Secrétariat
- **Accès** : Gestion des réservations
- **Permissions** :
  - Validation/annulation des réservations
  - Création de réservations directement confirmées
  - Consultation des salles
  - Export XML des réservations

### Responsable
- **Accès** : Gestion de ses propres réservations
- **Permissions** :
  - Création de réservations (état provisoire)
  - Modification/suppression de ses réservations
  - Consultation des salles disponibles

### Utilisateur
- **Accès** : Consultation uniquement
- **Permissions** :
  - Consultation des salles et leur disponibilité
  - Lecture seule des réservations publiques

## Base de Données

### Tables Principales

#### `utilisateurs`
```sql
id INT PRIMARY KEY AUTO_INCREMENT
nom VARCHAR(100) NOT NULL
prenom VARCHAR(100) NOT NULL
email VARCHAR(255) UNIQUE NOT NULL
mot_de_passe VARCHAR(255) NOT NULL
role ENUM('administrateur', 'secretariat', 'responsable', 'utilisateur')
actif BOOLEAN DEFAULT TRUE
date_creation TIMESTAMP
date_modification TIMESTAMP
```

#### `salles`
```sql
id INT PRIMARY KEY AUTO_INCREMENT
nom VARCHAR(100) NOT NULL
description TEXT
capacite INT NOT NULL
equipements TEXT
active BOOLEAN DEFAULT TRUE
date_creation TIMESTAMP
date_modification TIMESTAMP
```

#### `reservations`
```sql
id INT PRIMARY KEY AUTO_INCREMENT
salle_id INT FOREIGN KEY
utilisateur_id INT FOREIGN KEY
date_debut DATETIME NOT NULL
date_fin DATETIME NOT NULL
objet VARCHAR(255) NOT NULL
description TEXT
etat ENUM('provisoire', 'confirme', 'annule') DEFAULT 'provisoire'
date_creation TIMESTAMP
date_modification TIMESTAMP
```

### Index et Optimisations
- Index sur les emails pour l'authentification
- Index composites pour les recherches de disponibilité
- Contraintes de cohérence des dates

## Sécurité Implémentée

### Authentification
- Hashage des mots de passe avec `password_hash()` (bcrypt)
- Vérification avec `password_verify()`
- Gestion des sessions sécurisées

### Protection CSRF
- Génération de tokens uniques par session
- Vérification obligatoire sur tous les formulaires
- Implémentation dans `BaseController`

### Validation des Données
- Sanitisation des entrées avec `htmlspecialchars()`
- Requêtes préparées pour éviter les injections SQL
- Validation des formats (email, dates, etc.)

### Gestion des Sessions
- Timeout automatique après inactivité
- Régénération des ID de session
- Variables de session sécurisées

### Contrôle d'Accès
- Vérification des rôles par contrôleur
- Redirection automatique selon les permissions
- Protection des actions sensibles

## API et Fonctionnalités Avancées

### Export XML

#### Utilisateurs (`/admin/generer_xml_utilisateurs`)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<utilisateurs date_generation="2024-10-09 14:30:00">
    <utilisateur>
        <nom>Dupont</nom>
        <prenom>Marie</prenom>
        <email>marie.dupont@m2l.fr</email>
        <role>secretariat</role>
        <date_creation>2024-01-15 09:00:00</date_creation>
    </utilisateur>
</utilisateurs>
```

#### Réservations (`/reservation/generer_xml_reservations`)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<reservations_semaine semaine_debut="2024-10-07" semaine_fin="2024-10-13" date_generation="2024-10-09 14:30:00">
    <reservation>
        <id>1</id>
        <salle>Salle de Conférence A</salle>
        <capacite>50</capacite>
        <responsable>Pierre Martin</responsable>
        <date_debut>2024-10-08 09:00:00</date_debut>
        <date_fin>2024-10-08 12:00:00</date_fin>
        <objet>Réunion équipe marketing</objet>
        <etat>confirme</etat>
    </reservation>
</reservations_semaine>
```

### Recherche de Disponibilités
Algorithme de vérification des conflits :
```sql
SELECT s.* FROM salles s
WHERE s.active = 1
AND s.id NOT IN (
    SELECT r.salle_id FROM reservations r
    WHERE r.etat IN ('confirme', 'provisoire')
    AND (
        (r.date_debut <= ? AND r.date_fin > ?) OR
        (r.date_debut < ? AND r.date_fin >= ?) OR
        (r.date_debut >= ? AND r.date_fin <= ?)
    )
)
```

## Tests et Validation

### Comptes de Test
```
Administrateur:
- Email: admin@m2l.fr
- Mot de passe: admin123

Secrétariat:
- Email: secretariat@m2l.fr
- Mot de passe: secret123

Responsable:
- Email: responsable@m2l.fr
- Mot de passe: resp123

Utilisateur:
- Email: utilisateur@m2l.fr
- Mot de passe: user123
```

### Jeux d'Essai Recommandés

#### Pour l'Étudiant 1 (Administration)
1. **Test de création d'utilisateur**
   - Créer un utilisateur avec tous les rôles
   - Vérifier l'unicité des emails
   - Tester la validation des données

2. **Test de modification d'utilisateur**
   - Modifier sans changer le mot de passe
   - Modifier avec nouveau mot de passe
   - Tester les contraintes de sécurité

3. **Test d'export XML**
   - Générer l'export avec différents jeux de données
   - Vérifier le format et l'encodage
   - Tester avec une base vide

#### Pour l'Étudiant 2 (Réservations)
1. **Test de création de réservation**
   - Réservation simple sans conflit
   - Tentative de réservation en conflit
   - Validation des créneaux horaires

2. **Test de recherche de disponibilités**
   - Recherche sur différentes périodes
   - Vérification des résultats avec/sans réservations
   - Test des cas limites (même heure de début/fin)

3. **Test de workflow de validation**
   - Création → Validation → Confirmation
   - Annulation à différentes étapes
   - Export XML des réservations validées

## Installation et Déploiement

### Prérequis
- Serveur web avec PHP 8.x
- Base de données MySQL 8.x
- Extensions PHP : PDO, XML, JSON

### Installation
1. Cloner le repository
2. Configurer `config/config.php` avec les paramètres locaux
3. Exécuter le script `database/valres2.sql`
4. Configurer les permissions des dossiers `exports/`
5. Configurer le serveur web (DocumentRoot vers le projet)

### Configuration de Production
- Modifier `SECURITY_SALT` dans `config.php`
- Désactiver l'affichage des erreurs PHP
- Configurer HTTPS obligatoire
- Modifier les mots de passe par défaut

## Planning Prévisionnel

### Phase 1 - Structure (Semaines 1-2)
- [x] Architecture MVC de base
- [x] Base de données et modèles
- [x] Système d'authentification
- [x] Layout et navigation

### Phase 2 - Développement Parallèle (Semaines 3-5)

#### Étudiant 1
- [ ] Interface d'administration des utilisateurs
- [ ] Système de gestion des rôles
- [ ] Consultation des réservations pour admin
- [ ] Export XML des utilisateurs

#### Étudiant 2
- [ ] Interface de réservation
- [ ] Système de recherche de salles
- [ ] Workflow de validation des réservations
- [ ] Export XML des réservations

### Phase 3 - Tests et Intégration (Semaine 6)
- [ ] Tests unitaires des deux parties
- [ ] Tests d'intégration
- [ ] Validation du jeu d'essai
- [ ] Documentation finale

## Maintenance et Évolutions

### Points d'Amélioration Possibles
- Interface AJAX pour la recherche en temps réel
- Notifications par email
- Gestion des récurrences de réservations
- API REST pour intégrations futures
- Interface mobile dédiée

### Bonnes Pratiques de Code
- Respect des standards PSR
- Commentaires en français pour le contexte académique
- Séparation claire des responsabilités
- Gestion centralisée des erreurs
- Logs des actions sensibles

---

**Auteurs** : Étudiants BTS SIO - AP3 VALRES2  
**Date** : Octobre 2024  
**Version** : 2.0