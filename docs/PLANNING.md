# VALRES2 - Planning Prévisionnel de Développement

## 🎯 Objectif du Planning

Ce planning définit précisément les tâches de chaque étudiant pour le développement collaboratif de VALRES2 en respectant la séparation Administration/Réservations.

## 👥 Répartition des Responsabilités

### 🔵 Étudiant 1 - Partie Administration
**Responsable** : Gestion des utilisateurs et administration système

### 🟢 Étudiant 2 - Partie Réservations  
**Responsable** : Gestion des réservations et salles

---

## 📅 Planning Détaillé (6 Semaines)

### 🏗 Phase 1 : Architecture et Base (Semaines 1-2)

#### Semaine 1 : Mise en place
**Travail commun** (Pair programming recommandé)
- [x] ✅ Setup du repository Git
- [x] ✅ Structure MVC de base
- [x] ✅ Configuration base de données
- [x] ✅ Contrôleur de base (`BaseController.php`)
- [x] ✅ Système d'authentification (`AuthController.php`)
- [x] ✅ Layout principal et navigation

**Livrables communs :**
- [x] Structure de fichiers complète
- [x] Base de données initialisée avec jeu de test
- [x] Système d'authentification fonctionnel

#### Semaine 2 : Modèles et Fondations
**🔵 Étudiant 1 :**
- [x] ✅ Modèle `User.php` complet
- [x] ✅ Tests d'authentification
- [x] ✅ Page de connexion fonctionnelle
- [ ] 🔄 Documentation des API utilisateur

**🟢 Étudiant 2 :**
- [x] ✅ Modèles `Reservation.php` et `Salle.php`
- [x] ✅ Algorithmes de vérification des conflits
- [ ] 🔄 Tests de logique de réservation
- [ ] 🔄 Documentation des API réservation

---

### 💻 Phase 2 : Développement Parallèle (Semaines 3-5)

#### Semaine 3 : Interfaces principales

**🔵 Étudiant 1 - Administration des Utilisateurs**
- [ ] 📋 Interface de liste des utilisateurs (`/admin/utilisateurs`)
  - Tableau avec pagination
  - Filtres par rôle et statut
  - Actions (modifier, supprimer, activer/désactiver)
- [ ] ➕ Formulaire d'ajout d'utilisateur (`/admin/ajouter_utilisateur`)
  - Validation côté client et serveur
  - Gestion des erreurs
  - Interface intuitive avec Bootstrap
- [ ] ✏️ Formulaire de modification d'utilisateur (`/admin/modifier_utilisateur`)
  - Pré-remplissage des champs
  - Option changement de mot de passe
  - Validation des permissions

**🟢 Étudiant 2 - Consultation des Salles**
- [ ] 🔍 Interface de recherche de salles (`/reservation/consulter`)
  - Formulaire de recherche par date/heure
  - Affichage des salles disponibles
  - Détails des équipements et capacités
- [ ] 📅 Calendrier des réservations
  - Vue mensuelle/hebdomadaire
  - Codes couleur par statut
  - Navigation intuitive
- [ ] 📋 Interface de mes réservations (`/reservation/mes_reservations`)
  - Liste filtrée par utilisateur
  - Actions selon le rôle
  - Statuts visuels

**Livrables Semaine 3 :**
- Interface d'administration des utilisateurs (Étudiant 1)
- Interface de consultation des salles (Étudiant 2)

#### Semaine 4 : Fonctionnalités avancées

**🔵 Étudiant 1 - Tableaux de Bord et Consultation**
- [ ] 📊 Tableau de bord administrateur (`/admin/dashboard`)
  - Statistiques en temps réel
  - Graphiques avec Chart.js
  - Raccourcis vers actions fréquentes
- [ ] 👀 Consultation des réservations (`/admin/reservations`)
  - Vue globale toutes réservations
  - Filtres avancés (date, salle, utilisateur, statut)
  - Export CSV/PDF optionnel
- [ ] 🔐 Gestion avancée des permissions
  - Interface de modification des rôles
  - Journalisation des actions sensibles

**🟢 Étudiant 2 - Gestion des Réservations**
- [ ] ➕ Interface de création de réservation (`/reservation/ajouter`)
  - Formulaire avec validation temps réel
  - Vérification de disponibilité AJAX
  - Prévisualisation avant validation
- [ ] ✏️ Interface de modification de réservation (`/reservation/modifier`)
  - Modification selon les droits
  - Gestion des changements d'état
  - Historique des modifications
- [ ] ✅ Dashboard de validation (Secrétariat) (`/reservation/dashboard`)
  - Queue des réservations en attente
  - Actions rapides (approuver/rejeter)
  - Notifications visuelles

**Livrables Semaine 4 :**
- Tableaux de bord complets (Étudiant 1)
- Système de réservation fonctionnel (Étudiant 2)

#### Semaine 5 : Export XML et Finitions

**🔵 Étudiant 1 - Export et Sécurité**
- [ ] 📤 Système d'export XML des utilisateurs
  - Génération XML conforme
  - Filtres par période/rôle
  - Téléchargement sécurisé
- [ ] 🛡️ Renforcement de la sécurité
  - Audit des contrôles d'accès
  - Tests de pénétration basiques
  - Validation finale des formulaires
- [ ] 📝 Logs et traçabilité
  - Journalisation des actions admin
  - Interface de consultation des logs

**🟢 Étudiant 2 - Workflow et Export**
- [ ] 🔄 Workflow complet de réservation
  - États : Provisoire → Confirmé/Annulé
  - Notifications par email (optionnel)
  - Gestion des annulations tardives
- [ ] 📤 Export XML des réservations
  - Export hebdomadaire paramétrable
  - Format XML validé
  - Intégration planning externe
- [ ] 🔍 Recherche avancée et optimisations
  - Recherche multicritères
  - Cache des résultats fréquents
  - Performance des requêtes

**Livrables Semaine 5 :**
- Système d'export XML complet (Étudiant 1)
- Workflow de réservation finalisé (Étudiant 2)

---

### 🧪 Phase 3 : Tests et Intégration (Semaine 6)

#### Semaine 6 : Tests et Finalisation

**Travail commun** (Tests croisés recommandés)

**🔵 Étudiant 1 - Tests Administration**
- [ ] 🧪 Tests unitaires gestion utilisateurs
  - Test création avec données valides/invalides
  - Test modification permissions
  - Test désactivation/suppression
- [ ] 🔍 Tests d'intégration authentification
  - Test connexion/déconnexion
  - Test gestion des sessions
  - Test sécurité (CSRF, injections)
- [ ] 📊 Tests export XML utilisateurs
  - Test génération différents formats
  - Test gestion erreurs
  - Validation XML généré

**🟢 Étudiant 2 - Tests Réservations**
- [ ] 🧪 Tests unitaires réservations
  - Test création réservations
  - Test gestion conflits
  - Test changements d'état
- [ ] 🔄 Tests workflow complet
  - Test cycle provisoire → confirmé
  - Test annulations
  - Test permissions par rôle
- [ ] 📊 Tests export XML réservations
  - Test export hebdomadaire
  - Test filtres et paramètres
  - Validation format XML

**Tests Communs :**
- [ ] 🌐 Tests d'intégration complète
- [ ] 📱 Tests responsive design
- [ ] 🔒 Tests de sécurité globaux
- [ ] 📋 Validation du jeu d'essai final

**Livrables Finaux :**
- Application complètement testée
- Documentation de tests
- Jeu d'essai validé
- Manuel utilisateur

---

## 📋 Checklist des Livrables par Étudiant

### 🔵 Étudiant 1 - Administration
- [ ] **Contrôleurs**
  - [x] ✅ `AdminController.php` complet
  - [x] ✅ `AuthController.php` sécurisé
- [ ] **Vues Administration**
  - [ ] `views/admin/dashboard.php`
  - [ ] `views/admin/utilisateurs/liste.php`
  - [ ] `views/admin/utilisateurs/ajouter.php`
  - [ ] `views/admin/utilisateurs/modifier.php`
  - [ ] `views/admin/reservations/consulter.php`
- [ ] **Fonctionnalités**
  - [ ] CRUD utilisateurs complet
  - [ ] Système d'authentification sécurisé
  - [ ] Export XML utilisateurs
  - [ ] Tableaux de bord avec statistiques
- [ ] **Tests**
  - [ ] Tests unitaires gestion utilisateurs
  - [ ] Tests sécurité authentification
  - [ ] Tests export XML

### 🟢 Étudiant 2 - Réservations
- [ ] **Contrôleurs**
  - [x] ✅ `ReservationController.php` complet
- [ ] **Vues Réservations**
  - [ ] `views/reservation/dashboard.php`
  - [ ] `views/reservation/consulter.php`
  - [ ] `views/reservation/mes_reservations.php`
  - [ ] `views/reservation/ajouter.php`
  - [ ] `views/reservation/modifier.php`
- [ ] **Fonctionnalités**
  - [ ] CRUD réservations complet
  - [ ] Système de validation (workflow)
  - [ ] Recherche salles disponibles
  - [ ] Export XML réservations
- [ ] **Tests**
  - [ ] Tests unitaires réservations
  - [ ] Tests gestion conflits
  - [ ] Tests workflow validation
  - [ ] Tests export XML

---

## 🔧 Outils et Méthodologie

### Gestion de Version (Git)
```bash
# Branches principales
main                    # Branche de production
develop                # Branche de développement
feature/admin-users     # Fonctionnalités Étudiant 1
feature/reservations    # Fonctionnalités Étudiant 2
```

### Convention de Commits
```
feat: Ajout nouvelle fonctionnalité
fix: Correction de bug
refactor: Refactorisation code
test: Ajout de tests
docs: Documentation
style: Formatage code
```

### Réunions Hebdomadaires
- **Lundi 9h** : Point de synchronisation
- **Mercredi 14h** : Revue de code croisée
- **Vendredi 16h** : Demo et planning semaine suivante

### Communication
- **Slack/Discord** : Communication quotidienne
- **GitHub Issues** : Suivi des bugs et fonctionnalités
- **GitLab/Tuleap** : Gestion de projet académique

---

## 🚀 Critères de Succès

### Techniques
- [x] ✅ Architecture MVC respectée
- [x] ✅ Sécurité : CSRF, hashage, sessions
- [ ] 🔄 Code documenté et commenté en français
- [ ] 🔄 Tests unitaires couvrant 80%+ du code
- [ ] 🔄 Interface responsive Bootstrap
- [ ] 🔄 Export XML conformes aux spécifications

### Fonctionnels
- [ ] 🔄 Toutes les fonctionnalités du cahier des charges
- [ ] 🔄 Gestion complète des 4 types d'utilisateurs
- [ ] 🔄 Workflow de réservation opérationnel
- [ ] 🔄 Exports XML générés correctement

### Académiques
- [ ] 🔄 Séparation claire des responsabilités
- [ ] 🔄 Documentation technique complète
- [ ] 🔄 Jeu d'essai validé par les tests
- [ ] 🔄 Démonstration fonctionnelle

---

## 📊 Suivi d'Avancement

| Semaine | Étudiant 1 (Admin) | Étudiant 2 (Réservations) | Status Global |
|---------|-------------------|---------------------------|---------------|
| 1 | ✅ Structure MVC | ✅ Structure MVC | ✅ Terminé |
| 2 | ✅ Modèles utilisateur | ✅ Modèles réservation | ✅ Terminé |
| 3 | 🔄 Interfaces admin | 🔄 Consultation salles | 🔄 En cours |
| 4 | ⏳ Tableaux de bord | ⏳ CRUD réservations | ⏳ À venir |
| 5 | ⏳ Export XML | ⏳ Workflow validation | ⏳ À venir |
| 6 | ⏳ Tests finaux | ⏳ Tests finaux | ⏳ À venir |

**Légende :** ✅ Terminé | 🔄 En cours | ⏳ À venir | ❌ Bloqué

---

**Dernière mise à jour :** 9 octobre 2024  
**Prochaine révision :** 16 octobre 2024