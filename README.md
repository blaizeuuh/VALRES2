# 🏢 VALRES2 - Système de Gestion des Réservations de Salles (Version PHP)

## 📋 Description

VALRES2 est une application web robuste pour gérer les réservations de salles. Développée en PHP avec une base de données SQLite, elle permet de créer, consulter, modifier et exporter les réservations avec une architecture côté serveur solide.

## ✨ Fonctionnalités

### 1. 🆕 Créer une réservation
- Sélection de la salle avec informations (capacité, équipements)
- Choix de la date et des horaires
- Saisie du responsable et du motif
- Vérification automatique de la disponibilité côté serveur
- État initial "Provisoire"

### 2. 📋 Gérer les réservations
- **Visualiser** toutes les réservations avec filtres avancés
- **Confirmer** une réservation (Provisoire → Confirmé)
- **Annuler** une réservation (Provisoire → Annulé)
- **Supprimer** une réservation avec confirmation

### 3. 📅 Consulter les disponibilités
- Vérification des créneaux libres par salle
- Affichage des occupations par date
- Vue claire des conflits potentiels
- Interface responsive pour tous les écrans

### 4. 📄 Export XML
- Export des réservations confirmées d'une période donnée
- Format XML structuré avec métadonnées complètes
- Téléchargement automatique du fichier
- Aperçu des données avant export

## 🚀 Installation et Utilisation

### Prérequis
- **Serveur web** avec support PHP 7.4+ (Apache, Nginx, ou serveur de développement PHP)
- **Extension PDO SQLite** (généralement incluse)
- Navigateur web moderne

### Installation
1. Télécharger/cloner le projet dans votre dossier web
2. S'assurer que PHP a les permissions d'écriture dans le dossier `data/`
3. Ouvrir `index.php` dans votre navigateur
4. La base de données SQLite sera créée automatiquement

### Démarrage Rapide (Serveur de développement)
```bash
# Dans le dossier du projet
php -S localhost:8000
# Puis ouvrir http://localhost:8000 dans le navigateur
```

## 📁 Structure du Projet

```
VALRES2/
├── index.php                    # Interface principale
├── export_xml.php              # Page d'export XML
├── config/
│   └── database.php            # Configuration BDD et classes métier
├── assets/
│   ├── style.css              # Styles et mise en forme
│   └── script.js              # Scripts côté client
├── data/
│   └── valres2.db            # Base de données SQLite (auto-créée)
└── README.md                  # Documentation
```

## 🔧 Technologies Utilisées

- **PHP 7.4+** - Logique serveur et traitement des données
- **SQLite** - Base de données légère et autonome
- **PDO** - Interface d'accès aux données sécurisée
- **HTML5** - Structure de l'interface
- **CSS3** - Styles et responsive design
- **JavaScript ES6** - Amélioration de l'expérience utilisateur

## 💾 Stockage des Données

L'application utilise une **base de données SQLite** avec les tables suivantes :

### Table `reservations`
- `id` : Identifiant unique (auto-incrémenté)
- `salle` : Nom de la salle réservée
- `date` : Date de la réservation
- `heure_debut` / `heure_fin` : Créneaux horaires
- `responsable` : Nom du responsable
- `motif` : Raison de la réservation
- `etat` : État (Provisoire, Confirmé, Annulé)
- `date_creation` / `date_modification` : Horodatage

### Table `salles`
- `id` : Identifiant unique
- `nom` : Nom de la salle
- `capacite` : Nombre de places
- `equipements` : Description des équipements
- `actif` : Salle active (booléen)

## 🔒 Sécurité et Validation

### Côté Serveur (PHP)
- **Requêtes préparées** (PDO) pour éviter les injections SQL
- **Validation des données** avant insertion en base
- **Échappement HTML** pour prévenir les attaques XSS
- **Vérification des conflits** de réservation

### Côté Client (JavaScript)
- Validation des formulaires en temps réel
- Contrôle des dates (pas de réservation dans le passé)
- Vérification que l'heure de fin > heure de début

## 🎯 États des Réservations

| État | Description | Actions possibles |
|------|-------------|-------------------|
| **Provisoire** | Réservation créée, en attente de validation | Confirmer, Annuler, Supprimer |
| **Confirmé** | Réservation validée par le secrétariat | Supprimer |
| **Annulé** | Réservation annulée | Supprimer |

## 📊 Format d'Export XML

```xml
<?xml version="1.0" encoding="UTF-8"?>
<reservations>
    <metadata>
        <dateExport>2025-10-09T...</dateExport>
        <periodeDebut>2025-10-06</periodeDebut>
        <periodeFin>2025-10-12</periodeFin>
        <nombreReservations>5</nombreReservations>
    </metadata>
    <listeReservations>
        <reservation id="1">
            <salle>Salle A</salle>
            <date>2025-10-09</date>
            <heureDebut>09:00</heureDebut>
            <heureFin>11:00</heureFin>
            <responsable>Jean Dupont</responsable>
            <motif>Réunion équipe</motif>
            <etat>Confirmé</etat>
            <dateCreation>2025-10-09T...</dateCreation>
        </reservation>
    </listeReservations>
</reservations>
```

## 🔒 Règles de Gestion

### Créneaux Horaires
- Vérification automatique des conflits
- Impossible de réserver une salle déjà occupée
- Dates antérieures à aujourd'hui non autorisées

### Permissions
- Suppression possible par le créateur de la réservation
- Confirmation/Annulation accessible à tous (simulation secrétariat)

## 🎨 Interface Utilisateur

### Navigation
- **➕ Nouvelle Réservation** : Formulaire de création
- **📅 Consulter Disponibilités** : Vue des créneaux libres
- **📄 Exporter XML** : Génération du fichier d'export

### Filtres
- Par état : Tous, Provisoire, Confirmé, Annulé
- Par salle : Toutes, Salle A, B, C, Amphithéâtre

## 📱 Responsive Design

L'application s'adapte automatiquement à tous les écrans :
- Desktop
- Tablette
- Mobile

## 🔮 Évolutions Futures

Cette version offre une base solide pour les évolutions. Les améliorations prévues incluront :
- Module d'administration avec authentification
- Gestion des utilisateurs et permissions
- API REST pour intégrations tierces
- Notifications par email
- Récurrence des réservations
- Statistiques et tableaux de bord
- Import/Export en différents formats
- Intégration avec des calendriers externes

## �️ Développement

### Structure du Code
- **Séparation des responsabilités** : Base de données, logique métier, présentation
- **Classes PHP** réutilisables et extensibles
- **Code modulaire** facile à maintenir
- **Standards de codage** respectés

### Base de Données
- **Migration automatique** des tables
- **Données de démonstration** générées automatiquement
- **Requêtes optimisées** avec index appropriés

## 🐛 Support et Maintenance

### Logs et Debug
- Gestion des erreurs PDO
- Messages d'erreur informatifs
- Validation des données entrantes

### Backup
La base de données SQLite peut être sauvegardée simplement en copiant le fichier `data/valres2.db`

---

*Application développée pour VALRES2 - Version PHP 2.0*