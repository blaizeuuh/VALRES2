// assets/script.js - Scripts côté client pour améliorer l'UX en version PHP

document.addEventListener('DOMContentLoaded', function() {
    initializeFormValidation();
    initializeAutoHideMessages();
    initializeUserExperience();
});

function initializeFormValidation() {
    // Validation des heures (fin > début) - côté client pour feedback immédiat
    const heureDebut = document.getElementById('heure_debut');
    const heureFin = document.getElementById('heure_fin');
    
    if (heureDebut && heureFin) {
        function validateHoraires() {
            if (heureDebut.value && heureFin.value) {
                if (heureFin.value <= heureDebut.value) {
                    heureFin.setCustomValidity('L\'heure de fin doit être postérieure à l\'heure de début');
                } else {
                    heureFin.setCustomValidity('');
                }
            }
        }
        
        heureDebut.addEventListener('change', validateHoraires);
        heureFin.addEventListener('change', validateHoraires);
    }
    
    // Validation des dates (pas dans le passé)
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < today) {
                this.setCustomValidity('La date ne peut pas être dans le passé');
            } else {
                this.setCustomValidity('');
            }
        });
    });
}

function initializeAutoHideMessages() {
    // Masquer automatiquement les messages après 5 secondes
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            if (message.parentNode) {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    if (message.parentNode) {
                        message.remove();
                    }
                }, 500);
            }
        }, 5000);
    });
}

function initializeUserExperience() {
    // Confirmation pour les suppressions (sécurité supplémentaire côté client)
    document.addEventListener('click', function(e) {
        if (e.target.matches('.btn-danger')) {
            const form = e.target.closest('form');
            if (form && !form.hasAttribute('onsubmit')) {
                if (!confirm('Êtes-vous sûr de vouloir effectuer cette action ?')) {
                    e.preventDefault();
                    return false;
                }
            }
        }
    });
    
    // Indicateur de chargement pour les filtres
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name="filtre_etat"], select[name="filtre_salle"]')) {
            const form = e.target.closest('form');
            if (form) {
                // Ajouter un indicateur visuel de chargement
                const existingIndicator = form.querySelector('.loading-indicator');
                if (existingIndicator) existingIndicator.remove();
                
                const indicator = document.createElement('span');
                indicator.innerHTML = ' ⏳';
                indicator.className = 'loading-indicator';
                e.target.parentNode.appendChild(indicator);
            }
        }
    });
}
function initializeUserExperience() {
    // Confirmation pour les suppressions (sécurité supplémentaire côté client)
    document.addEventListener('click', function(e) {
        if (e.target.matches('.btn-danger')) {
            const form = e.target.closest('form');
            if (form && !form.hasAttribute('onsubmit')) {
                if (!confirm('Êtes-vous sûr de vouloir effectuer cette action ?')) {
                    e.preventDefault();
                    return false;
                }
            }
        }
    });
    
    // Indicateur de chargement pour les filtres
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name="filtre_etat"], select[name="filtre_salle"]')) {
            const form = e.target.closest('form');
            if (form) {
                // Ajouter un indicateur visuel de chargement
                const existingIndicator = form.querySelector('.loading-indicator');
                if (existingIndicator) existingIndicator.remove();
                
                const indicator = document.createElement('span');
                indicator.innerHTML = ' ⏳';
                indicator.className = 'loading-indicator';
                e.target.parentNode.appendChild(indicator);
            }
        }
    });
}