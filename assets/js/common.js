/**
 * Script commun pour l'application LeadsBuilder
 */
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du menu utilisateur
    const userMenu = document.querySelector('.user-profile');
    if (userMenu) {
        userMenu.addEventListener('click', function(e) {
            e.preventDefault();
            // Implémenter l'affichage du menu déroulant
            alert('Menu utilisateur à implémenter');
        });
    }
    
    // Formatage des nombres
    function formatNumber(number) {
        if (number >= 1000000) {
            return (number / 1000000).toFixed(1) + 'M';
        } else if (number >= 1000) {
            return (number / 1000).toFixed(1) + 'K';
        }
        return number.toString();
    }
    
    // Formater tous les éléments avec la classe 'format-number'
    const numberElements = document.querySelectorAll('.format-number');
    if (numberElements.length > 0) {
        numberElements.forEach(el => {
            const value = parseInt(el.textContent.replace(/[^\d]/g, ''), 10);
            if (!isNaN(value)) {
                el.textContent = formatNumber(value);
            }
        });
    }
    
    // Gestion des formulaires avec confirmation
    const confirmForms = document.querySelectorAll('form[data-confirm]');
    if (confirmForms.length > 0) {
        confirmForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const message = this.dataset.confirm;
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Gestion des notifications
    const notifications = document.querySelectorAll('.notification');
    if (notifications.length > 0) {
        notifications.forEach(notification => {
            const closeBtn = notification.querySelector('.close-notification');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    notification.classList.add('fade-out');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                });
            }
            
            // Auto-fermeture après 5 secondes
            if (notification.classList.contains('auto-close')) {
                setTimeout(() => {
                    notification.classList.add('fade-out');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 5000);
            }
        });
    }
    
    // Fonction globale pour afficher des notifications
    window.showNotification = function(type, message) {
        // Créer l'élément de notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Icône en fonction du type
        let icon = '';
        switch (type) {
            case 'success':
                icon = '✓';
                break;
            case 'error':
                icon = '✕';
                break;
            case 'warning':
                icon = '⚠';
                break;
            default:
                icon = 'ℹ';
        }
        
        // Structure de la notification
        notification.innerHTML = `
            <span class="notification-icon">${icon}</span>
            <span class="notification-message">${message}</span>
        `;
        
        // Ajouter au DOM
        document.body.appendChild(notification);
        
        // Supprimer après 3 secondes
        setTimeout(() => {
            notification.remove();
        }, 3000);
    };
});
