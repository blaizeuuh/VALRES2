#!/bin/bash

# ============================================================================
# VALRES2 - Script d'installation et de configuration
# ============================================================================

echo "🚀 Installation de VALRES2 - Application de réservation M2L"
echo "============================================================"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_status() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Vérification des prérequis
print_status "Vérification des prérequis..."

# Vérifier PHP
if ! command -v php &> /dev/null; then
    print_error "PHP n'est pas installé. Veuillez installer PHP 8.x"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
print_success "PHP $PHP_VERSION détecté"

# Vérifier MySQL
if ! command -v mysql &> /dev/null; then
    print_warning "MySQL n'est pas installé ou pas dans le PATH"
    print_status "Assurez-vous d'avoir MySQL/MariaDB installé"
fi

# Créer les dossiers nécessaires
print_status "Création des dossiers..."

mkdir -p exports
chmod 755 exports
print_success "Dossier exports/ créé"

mkdir -p logs
chmod 755 logs
print_success "Dossier logs/ créé"

# Configuration de la base de données
print_status "Configuration de la base de données..."

read -p "Host MySQL (défaut: localhost): " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "Nom de la base (défaut: valres2): " DB_NAME
DB_NAME=${DB_NAME:-valres2}

read -p "Utilisateur MySQL: " DB_USER

read -s -p "Mot de passe MySQL: " DB_PASS
echo

# Génération du salt de sécurité
SECURITY_SALT=$(openssl rand -hex 32)

# Création du fichier de configuration local
print_status "Création du fichier de configuration..."

cat > config/config.local.php << EOF
<?php
/**
 * Configuration locale pour VALRES2
 * Généré automatiquement le $(date)
 */

// Configuration de la base de données
define('DB_HOST', '$DB_HOST');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');

// Salt de sécurité unique
define('SECURITY_SALT', '$SECURITY_SALT');

// Configuration locale
define('DEBUG_MODE', true);
define('LOCAL_ENV', true);
EOF

print_success "Configuration locale créée dans config/config.local.php"

# Création de la base de données
print_status "Création de la base de données..."

if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null; then
    print_success "Base de données '$DB_NAME' créée"
else
    print_warning "Impossible de créer la base automatiquement"
    print_status "Créez manuellement la base : CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
fi

# Import des données
print_status "Import des données initiales..."

if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/valres2.sql 2>/dev/null; then
    print_success "Données importées avec succès"
else
    print_warning "Erreur lors de l'import - vérifiez les paramètres MySQL"
    print_status "Importez manuellement : mysql -u $DB_USER -p $DB_NAME < database/valres2.sql"
fi

# Configuration du serveur web
print_status "Configuration du serveur web..."

# Vérifier si Apache/Nginx est configuré
if [ -d "/etc/apache2/sites-available" ]; then
    print_status "Apache détecté - Configuration recommandée :"
    echo "
<VirtualHost *:80>
    ServerName valres2.local
    DocumentRoot $(pwd)
    DirectoryIndex index.php
    
    <Directory $(pwd)>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"
    print_warning "Ajoutez cette configuration à Apache et redémarrez le service"
    print_warning "N'oubliez pas d'ajouter '127.0.0.1 valres2.local' dans /etc/hosts"
fi

# Création du fichier .htaccess pour Apache
print_status "Création du fichier .htaccess..."

cat > .htaccess << 'EOF'
# VALRES2 - Configuration Apache

# Réécriture d'URL
RewriteEngine On

# Redirection HTTPS (à décommenter en production)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Sécurité
# Masquer les fichiers sensibles
<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>

<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.log">
    Order allow,deny
    Deny from all
</Files>

# Headers de sécurité
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Cache des ressources statiques
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
EOF

print_success "Fichier .htaccess créé"

# Vérification des permissions
print_status "Vérification des permissions..."

# Permissions des dossiers
chmod 755 assets/css assets/js exports logs
chmod 644 assets/css/* assets/js/* 2>/dev/null || true

print_success "Permissions configurées"

# Test de connectivité PHP
print_status "Test de la configuration PHP..."

php -r "
try {
    \$pdo = new PDO('mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4', '$DB_USER', '$DB_PASS');
    echo '✅ Connexion à la base de données réussie\n';
    
    \$stmt = \$pdo->query('SELECT COUNT(*) as nb FROM utilisateurs');
    \$result = \$stmt->fetch();
    echo '✅ ' . \$result['nb'] . ' utilisateurs trouvés dans la base\n';
    
} catch (Exception \$e) {
    echo '❌ Erreur de connexion : ' . \$e->getMessage() . '\n';
}
" || print_warning "Erreur lors du test de connexion"

# Résumé de l'installation
echo
echo "============================================================"
print_success "🎉 Installation de VALRES2 terminée !"
echo "============================================================"
echo
print_status "📋 Récapitulatif :"
echo "   • Base de données : $DB_NAME sur $DB_HOST"
echo "   • Configuration : config/config.local.php"
echo "   • Logs : logs/"
echo "   • Exports : exports/"
echo
print_status "🔐 Comptes de test disponibles :"
echo "   • Administrateur : admin@m2l.fr / admin123"
echo "   • Secrétariat : secretariat@m2l.fr / secret123"  
echo "   • Responsable : responsable@m2l.fr / resp123"
echo "   • Utilisateur : utilisateur@m2l.fr / user123"
echo
print_status "🌐 Accès à l'application :"
if [ -d "/etc/apache2/sites-available" ]; then
    echo "   • URL : http://valres2.local (après config Apache)"
fi
echo "   • Ou via serveur PHP : cd $(pwd) && php -S localhost:8080"
echo "   • Puis : http://localhost:8080"
echo
print_warning "⚠️  En production :"
echo "   • Changez les mots de passe par défaut"
echo "   • Configurez HTTPS"
echo "   • Désactivez le mode debug"
echo "   • Vérifiez les permissions de fichiers"
echo
print_status "📚 Documentation disponible :"
echo "   • docs/DOCUMENTATION_TECHNIQUE.md"
echo "   • docs/PLANNING.md"
echo "   • tests/JEU_ESSAI.md"
echo
print_success "✅ Installation réussie ! Bon développement ! 🚀"