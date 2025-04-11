/**
 * Système de notifications pour LeadsBuilder
 */
class NotificationManager {
    constructor() {
        this.container = null;
        this.timeout = 3000; // Durée d'affichage par défaut (3 secondes)
        this.init();
    }
    
    init() {
        // Créer le conteneur de notifications s'il n'existe pas
        if (!document.querySelector('.notifications-container')) {
            this.container = document.createElement('div');
            this.container.className = 'notifications-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.querySelector('.notifications-container');
        }
    }
    
    /**
     * Affiche une notification
     * @param {string} message - Message à afficher
     * @param {string} type - Type de notification (success, error, warning, info)
     * @param {number} duration - Durée d'affichage en ms (optionnel)
     */
    show(message, type = 'info', duration = this.timeout) {
        // Créer l'élément de notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Déterminer l'icône en fonction du type
        let icon = '';
        switch (type) {
            case 'success':
                icon = '<i class="fas fa-check-circle"></i>';
                break;
            case 'error':
                icon = '<i class="fas fa-exclamation-circle"></i>';
                break;
            case 'warning':
                icon = '<i class="fas fa-exclamation-triangle"></i>';
                break;
            case 'info':
            default:
                icon = '<i class="fas fa-info-circle"></i>';
                break;
        }
        
        // Construire le contenu
        notification.innerHTML = `
            <div class="notification-icon">${icon}</div>
            <div class="notification-message">${message}</div>
            <button class="notification-close"><i class="fas fa-times"></i></button>
        `;
        
        // Ajouter au conteneur
        this.container.appendChild(notification);
        
        // Ajouter l'écouteur pour fermer la notification
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            this.close(notification);
        });
        
        // Fermer automatiquement après la durée spécifiée
        if (duration > 0) {
            setTimeout(() => {
                this.close(notification);
            }, duration);
        }
        
        return notification;
    }
    
    /**
     * Ferme une notification
     * @param {HTMLElement} notification - Élément de notification à fermer
     */
    close(notification) {
        notification.classList.add('notification-closing');
        
        // Supprimer après l'animation
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
    
    /**
     * Raccourcis pour les différents types de notifications
     */
    success(message, duration) {
        return this.show(message, 'success', duration);
    }
    
    error(message, duration) {
        return this.show(message, 'error', duration);
    }
    
    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }
    
    info(message, duration) {
        return this.show(message, 'info', duration);
    }
}

// Créer une instance globale
const notifications = new NotificationManager();

// Exposer l'API globalement
window.showNotification = (message, type, duration) => {
    return notifications.show(message, type, duration);
};

// Raccourcis globaux
window.showSuccess = (message, duration) => notifications.success(message, duration);
window.showError = (message, duration) => notifications.error(message, duration);
window.showWarning = (message, duration) => notifications.warning(message, duration);
window.showInfo = (message, duration) => notifications.info(message, duration);
