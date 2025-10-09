# VALRES2 - Jeu d'Essai et Tests

## 🎯 Objectif des Tests

Ce document définit le jeu d'essai complet pour valider l'application VALRES2 selon le cahier des charges, en respectant la répartition des tâches entre les deux étudiants.

## 📋 Scénarios de Test par Étudiant

### 🔵 Étudiant 1 - Tests Administration

#### Test 1 : Gestion des Utilisateurs
**Objectif** : Valider le CRUD complet des utilisateurs

**Prérequis** : Connexion en tant qu'administrateur

**Étapes de test :**
1. **Création d'utilisateur valide**
   - Aller sur `/admin/ajouter_utilisateur`
   - Remplir : Nom="Testeur", Prénom="Jean", Email="test@m2l.fr", Rôle="responsable", MDP="test123"
   - Soumettre le formulaire
   - **Résultat attendu** : Utilisateur créé, redirection vers liste avec message de succès

2. **Tentative de création avec email existant**
   - Même formulaire avec email déjà utilisé
   - **Résultat attendu** : Message d'erreur "Cet email existe déjà"

3. **Modification d'utilisateur**
   - Cliquer "Modifier" sur un utilisateur existant
   - Changer le nom et le rôle
   - **Résultat attendu** : Modifications sauvegardées

4. **Désactivation d'utilisateur**
   - Cliquer "Supprimer" sur un utilisateur
   - Confirmer l'action
   - **Résultat attendu** : Utilisateur désactivé (actif=0)

**Données de test** :
```
Utilisateur 1 : Jean Testeur, test1@m2l.fr, responsable
Utilisateur 2 : Marie Test, test2@m2l.fr, utilisateur  
Utilisateur 3 : Paul Demo, test3@m2l.fr, secretariat
```

#### Test 2 : Authentification et Sécurité
**Objectif** : Valider la sécurité du système d'authentification

**Étapes de test :**
1. **Connexion valide**
   - Email : admin@m2l.fr, MDP : admin123
   - **Résultat attendu** : Connexion réussie, redirection dashboard

2. **Connexion invalide**
   - Email : admin@m2l.fr, MDP : mauvais_mdp
   - **Résultat attendu** : Message "Identifiants invalides"

3. **Protection CSRF**
   - Modifier le token CSRF dans le formulaire de connexion
   - **Résultat attendu** : Erreur "Token invalide"

4. **Timeout de session**
   - Attendre expiration session (simuler)
   - Accéder à une page protégée
   - **Résultat attendu** : Redirection vers login

5. **Contrôle d'accès par rôle**
   - Se connecter en tant qu'utilisateur simple
   - Tenter d'accéder à `/admin/utilisateurs`
   - **Résultat attendu** : Erreur "Accès non autorisé"

#### Test 3 : Export XML Utilisateurs
**Objectif** : Valider la génération du fichier XML des utilisateurs

**Étapes de test :**
1. **Export avec données**
   - Aller sur `/admin/generer_xml_utilisateurs`
   - **Résultat attendu** : Téléchargement fichier XML valide

2. **Validation du contenu XML**
   - Vérifier structure XML conforme
   - Vérifier encodage UTF-8
   - Vérifier tous les utilisateurs actifs présents

**Structure XML attendue :**
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

#### Test 4 : Consultation des Réservations (Admin)
**Objectif** : Valider la vue d'ensemble des réservations pour l'administrateur

**Étapes de test :**
1. **Affichage de toutes les réservations**
   - Aller sur `/admin/reservations`
   - **Résultat attendu** : Liste complète des réservations avec détails

2. **Filtrage par état**
   - Filtrer sur "Provisoire"
   - **Résultat attendu** : Seules les réservations provisoires affichées

3. **Recherche par utilisateur**
   - Rechercher réservations d'un utilisateur spécifique
   - **Résultat attendu** : Filtrage correct

---

### 🟢 Étudiant 2 - Tests Réservations

#### Test 5 : Consultation des Salles Disponibles
**Objectif** : Valider la recherche de disponibilités

**Prérequis** : Connexion en tant que responsable

**Étapes de test :**
1. **Recherche salle libre**
   - Aller sur `/reservation/consulter`
   - Date : demain, Heure début : 09:00, Heure fin : 11:00
   - **Résultat attendu** : Liste des salles disponibles

2. **Recherche avec conflit**
   - Même recherche sur un créneau déjà réservé
   - **Résultat attendu** : Salle occupée non affichée

3. **Affichage des détails salle**
   - Cliquer sur une salle disponible
   - **Résultat attendu** : Capacité, équipements affichés

**Données de test** :
```
Recherche 1 : 15/11/2024, 09:00-11:00 (libre)
Recherche 2 : 15/11/2024, 09:30-10:30 (conflit avec test 1)
Recherche 3 : 16/11/2024, 14:00-16:00 (libre)
```

#### Test 6 : Gestion des Réservations
**Objectif** : Valider le CRUD des réservations

**Étapes de test :**
1. **Création réservation valide (Responsable)**
   - Aller sur `/reservation/ajouter`
   - Salle : "Salle A", Date : demain, 14:00-16:00, Objet : "Réunion test"
   - **Résultat attendu** : Réservation créée avec état "provisoire"

2. **Tentative réservation en conflit**
   - Même salle, horaires qui se chevauchent
   - **Résultat attendu** : Message "Salle non disponible"

3. **Modification de sa réservation**
   - Modifier l'objet de la réservation créée
   - **Résultat attendu** : Modification sauvegardée

4. **Suppression de sa réservation**
   - Supprimer la réservation de test
   - **Résultat attendu** : Réservation supprimée

**Cas d'erreur à tester :**
- Heure fin antérieure à heure début
- Date passée
- Champs obligatoires vides

#### Test 7 : Workflow de Validation (Secrétariat)
**Objectif** : Valider le processus de confirmation/annulation

**Prérequis** : Connexion en tant que secrétariat, réservations provisoires existantes

**Étapes de test :**
1. **Confirmation d'une réservation**
   - Dashboard secrétariat `/reservation/dashboard`
   - Cliquer "Confirmer" sur une réservation provisoire
   - **Résultat attendu** : État passe à "confirmé"

2. **Annulation d'une réservation**
   - Cliquer "Annuler" sur une réservation provisoire
   - **Résultat attendu** : État passe à "annulé"

3. **Création réservation directe**
   - Créer une réservation en tant que secrétariat
   - **Résultat attendu** : État directement "confirmé"

#### Test 8 : Export XML Réservations
**Objectif** : Valider l'export hebdomadaire des réservations

**Prérequis** : Réservations confirmées sur la semaine

**Étapes de test :**
1. **Export semaine courante**
   - Aller sur `/reservation/generer_xml_reservations`
   - **Résultat attendu** : Fichier XML téléchargé

2. **Validation contenu XML**
   - Vérifier structure XML
   - Vérifier seules les réservations confirmées
   - Vérifier période correcte

**Structure XML attendue :**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<reservations_semaine semaine_debut="2024-10-07" semaine_fin="2024-10-13">
    <reservation>
        <id>1</id>
        <salle>Salle de Conférence A</salle>
        <responsable>Pierre Martin</responsable>
        <date_debut>2024-10-08 09:00:00</date_debut>
        <date_fin>2024-10-08 12:00:00</date_fin>
        <objet>Réunion équipe</objet>
        <etat>confirme</etat>
    </reservation>
</reservations_semaine>
```

---

## 🧪 Tests d'Intégration Communs

### Test 9 : Workflow Complet de Réservation
**Objectif** : Valider le processus bout en bout

**Acteurs** : Responsable + Secrétariat + Administrateur

**Scénario complet :**
1. **Responsable** : Créer une réservation (état provisoire)
2. **Secrétariat** : Consulter les demandes et confirmer
3. **Administrateur** : Consulter toutes les réservations
4. **Secrétariat** : Générer l'export XML de la semaine

**Résultat attendu** : Workflow fluide sans erreur

### Test 10 : Sécurité et Contrôles d'Accès
**Objectif** : Valider tous les contrôles de sécurité

**Tests transversaux :**
1. **Tentative d'accès non autorisé**
   - Utilisateur simple → pages admin
   - **Résultat attendu** : Redirection avec erreur

2. **Modification réservation d'autrui**
   - Responsable A → modifier réservation Responsable B
   - **Résultat attendu** : Accès refusé

3. **Injection SQL (test basique)**
   - Champs de recherche avec caractères spéciaux
   - **Résultat attendu** : Pas d'erreur, requêtes sécurisées

---

## 📊 Tableau de Validation des Tests

| Test | Fonction | Étudiant | Statut | Résultat | Commentaires |
|------|----------|----------|---------|----------|--------------|
| Test 1 | CRUD Utilisateurs | 1 | ⏳ | - | - |
| Test 2 | Authentification | 1 | ⏳ | - | - |
| Test 3 | Export XML Users | 1 | ⏳ | - | - |
| Test 4 | Consultation Résa Admin | 1 | ⏳ | - | - |
| Test 5 | Recherche Salles | 2 | ⏳ | - | - |
| Test 6 | CRUD Réservations | 2 | ⏳ | - | - |
| Test 7 | Workflow Validation | 2 | ⏳ | - | - |
| Test 8 | Export XML Résa | 2 | ⏳ | - | - |
| Test 9 | Workflow Complet | 1+2 | ⏳ | - | - |
| Test 10 | Sécurité Globale | 1+2 | ⏳ | - | - |

**Légende :** ⏳ À tester | ✅ Validé | ❌ Échec | 🔄 À reprendre

---

## 🗂 Jeu de Données de Test

### Utilisateurs de Test
```sql
-- Administrateur principal
admin@m2l.fr / admin123

-- Secrétariat
secretariat@m2l.fr / secret123
marie.dupont@m2l.fr / secret123

-- Responsables  
responsable@m2l.fr / resp123
pierre.martin@m2l.fr / resp123
jean.bernard@m2l.fr / resp123

-- Utilisateurs simples
utilisateur@m2l.fr / user123
claire.moreau@m2l.fr / user123
```

### Salles de Test
```sql
1. Salle de Conférence A (50 places) - Vidéoprojecteur, Audio
2. Salle de Réunion B (12 places) - Tableau blanc, Wifi
3. Salle de Formation C (20 places) - Ordinateurs, Vidéo
4. Amphithéâtre D (100 places) - Sonorisation, Éclairage
5. Salle de Réunion E (8 places) - Isolation phonique
6. Salle Polyvalente F (30 places) - Mobilier modulable
```

### Réservations de Test
```sql
-- Confirmées
15/11/2024 09:00-12:00 - Salle A - Pierre Martin - Réunion marketing
16/11/2024 10:00-17:00 - Salle C - Jean Bernard - Formation web
18/11/2024 13:00-18:00 - Salle D - Pierre Martin - Conférence publique

-- Provisoires
15/11/2024 14:00-16:00 - Salle B - Jean Bernard - Formation users
17/11/2024 08:30-11:30 - Salle A - Jean Bernard - Conférence annuelle
19/11/2024 09:00-11:00 - Salle B - Jean Bernard - Réunion direction
```

---

## 📝 Rapport de Tests

### Template de Rapport Individuel
```markdown
# Rapport de Tests - [Nom Étudiant] - [Partie]

## Tests Réalisés
- [ ] Test X : [Description] - [Résultat]
- [ ] Test Y : [Description] - [Résultat]

## Bugs Identifiés
1. [Description du bug] - [Priorité] - [Solution appliquée]

## Améliorations Suggérées
1. [Amélioration] - [Justification]

## Temps de Développement
- Phase 1 : X heures
- Phase 2 : X heures  
- Phase 3 : X heures
- Total : X heures

## Difficultés Rencontrées
[Description des obstacles et solutions]

## Auto-évaluation
[Note sur 20] - [Justification]
```

---

## 🔧 Outils de Test

### Tests Automatisés (optionnel)
```php
// Exemple de test unitaire PHPUnit
class UserControllerTest extends PHPUnit\Framework\TestCase {
    public function testCreateUser() {
        // Test création utilisateur
    }
    
    public function testAuthenticationSecurity() {
        // Test sécurité authentification
    }
}
```

### Tests Manuels
- **Navigateurs** : Chrome, Firefox, Safari
- **Responsive** : Desktop, Tablette, Mobile
- **Accessibilité** : Contraste, navigation clavier

### Validation XML
```bash
# Validation des fichiers XML générés
xmllint --valid exports/utilisateurs_*.xml
xmllint --valid exports/reservations_*.xml
```

---

**Dernière mise à jour :** 9 octobre 2024  
**Responsable** : Équipe VALRES2