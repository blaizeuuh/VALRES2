/**
 * VALRES2 - JavaScript Application
 * Fonctionnalités côté client
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialisation de l'application
    initApp();
    
    // Gestion des formulaires
    initForms();
    
    // Gestion des confirmations
    initConfirmations();
    
    // Gestion de la recherche de salles
    initSalleSearch();
    
    // Auto-dismiss des alertes après 5 secondes
    autoHideAlerts();
});

/**
 * Initialisation générale de l'application
 */
function initApp() {
    // Animation d'entrée pour les éléments
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
    
    // Tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popovers Bootstrap
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Initialisation des formulaires
 */
function initForms() {
    // Validation des formulaires
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Gestion des dates minimum (aujourd'hui)
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(input => {
        if (!input.hasAttribute('data-allow-past')) {
            input.min = today;
        }
    });
    
    // Validation des heures (fin après début)
    const heureDebutInputs = document.querySelectorAll('input[name="heure_debut"]');
    const heureFinInputs = document.querySelectorAll('input[name="heure_fin"]');
    
    if (heureDebutInputs.length && heureFinInputs.length) {
        heureDebutInputs[0].addEventListener('change', validateHeures);
        heureFinInputs[0].addEventListener('change', validateHeures);
    }
}

/**
 * Validation des heures de réservation
 */
function validateHeures() {
    const heureDebut = document.querySelector('input[name="heure_debut"]');
    const heureFin = document.querySelector('input[name="heure_fin"]');
    
    if (heureDebut && heureFin && heureDebut.value && heureFin.value) {
        if (heureDebut.value >= heureFin.value) {
            heureFin.setCustomValidity('L\'heure de fin doit être après l\'heure de début');
        } else {
            heureFin.setCustomValidity('');
        }
    }
}

/**
 * Initialisation des confirmations
 */
function initConfirmations() {
    // Confirmation des suppressions
    const deleteLinks = document.querySelectorAll('.btn-delete, .delete-confirm');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const message = this.getAttribute('data-message') || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
            
            if (confirm(message)) {
                // Si c'est un lien, rediriger
                if (this.tagName === 'A') {
                    window.location.href = this.href;
                }
                // Si c'est dans un formulaire, soumettre
                else if (this.form) {
                    this.form.submit();
                }
            }
        });
    });
    
    // Confirmation des changements d'état
    const stateChangeLinks = document.querySelectorAll('.state-change');
    stateChangeLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const action = this.getAttribute('data-action') || 'modifier';
            const message = `Êtes-vous sûr de vouloir ${action} cette réservation ?`;
            
            if (confirm(message)) {
                window.location.href = this.href;
            }
        });
    });
}

/**
 * Initialisation de la recherche de salles
 */
function initSalleSearch() {
    const searchForm = document.getElementById('salle-search-form');
    if (!searchForm) return;
    
    const dateInput = searchForm.querySelector('input[name="date"]');
    const heureDebutInput = searchForm.querySelector('input[name="heure_debut"]');
    const heureFinInput = searchForm.querySelector('input[name="heure_fin"]');
    const resultsContainer = document.getElementById('search-results');
    
    if (dateInput && heureDebutInput && heureFinInput) {
        [dateInput, heureDebutInput, heureFinInput].forEach(input => {
            input.addEventListener('change', function() {
                if (dateInput.value && heureDebutInput.value && heureFinInput.value) {
                    searchSalles();
                }
            });
        });
    }
}

/**
 * Recherche AJAX des salles disponibles
 */
function searchSalles() {
    const form = document.getElementById('salle-search-form');
    const formData = new FormData(form);
    const resultsContainer = document.getElementById('search-results');
    
    // Affichage du loader
    if (resultsContainer) {
        resultsContainer.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Recherche en cours...</span>
                </div>
                <p class="mt-2">Recherche des salles disponibles...</p>
            </div>
        `;
    }
    
    // Simulation de la recherche (en attendant l'implémentation AJAX)
    setTimeout(() => {
        if (resultsContainer) {
            resultsContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Fonctionnalité de recherche AJAX à implémenter.
                    Utilisez le bouton "Rechercher" pour voir les résultats.
                </div>
            `;
        }
    }, 1000);
}

/**
 * Auto-masquage des alertes
 */
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

/**
 * Fonction utilitaire pour formater les dates
 */
function formatDate(date, format = 'dd/mm/yyyy') {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    
    switch (format) {
        case 'dd/mm/yyyy':
            return `${day}/${month}/${year}`;
        case 'yyyy-mm-dd':
            return `${year}-${month}-${day}`;
        default:
            return d.toLocaleDateString('fr-FR');
    }
}

/**
 * Fonction utilitaire pour afficher des messages
 */
function showMessage(message, type = 'info') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
    alertContainer.setAttribute('role', 'alert');
    
    alertContainer.innerHTML = `
        <i class="fas fa-${getIconForType(type)} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('main .container');
    if (container) {
        container.insertBefore(alertContainer, container.firstChild);
        
        // Auto-masquage après 5 secondes
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertContainer);
            bsAlert.close();
        }, 5000);
    }
}

/**
 * Retourne l'icône appropriée selon le type de message
 */
function getIconForType(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle',
        'primary': 'info-circle',
        'secondary': 'info-circle'
    };
    
    return icons[type] || 'info-circle';
}

/**
 * Fonction pour copier du texte dans le presse-papiers
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showMessage('Texte copié dans le presse-papiers', 'success');
    }).catch(() => {
        showMessage('Erreur lors de la copie', 'danger');
    });
}

/**
 * Fonction pour imprimer une section spécifique
 */
function printSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Impression - ${document.title}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link href="assets/css/style.css" rel="stylesheet">
                </head>
                <body>
                    ${section.outerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
}

// Export des fonctions utilitaires pour utilisation globale
window.VALRES2 = {
    formatDate,
    showMessage,
    copyToClipboard,
    printSection,
    searchSalles
};