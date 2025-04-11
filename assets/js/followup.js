/**
 * Script pour la gestion de la page de suivi des prospects
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const refreshBtn = document.querySelector('.btn-refresh');
    const statusBadges = document.querySelectorAll('.status-badge');
    const dateCells = document.querySelectorAll('.date-cell');
    const paginationLinks = document.querySelectorAll('.pagination-number, .pagination-prev, .pagination-next');
    const followupTable = document.querySelector('.followup-table');
    
    // Ajouter un bouton d'exportation
    function addExportButton() {
        const header = document.querySelector('.followup-header');
        if (!header) return;
        
        const exportBtn = document.createElement('button');
        exportBtn.className = 'btn-export';
        exportBtn.innerHTML = '<i class="fas fa-download"></i> Exporter CSV';
        
        // Insérer le bouton avant le bouton de rafraîchissement
        if (refreshBtn) {
            const btnContainer = document.createElement('div');
            btnContainer.className = 'header-buttons';
            btnContainer.appendChild(exportBtn);
            btnContainer.appendChild(refreshBtn);
            
            // Remplacer le bouton de rafraîchissement par le conteneur
            refreshBtn.parentNode.replaceChild(btnContainer, refreshBtn);
        } else {
            header.appendChild(exportBtn);
        }
        
        // Ajouter un écouteur d'événement
        exportBtn.addEventListener('click', function() {
            if (followupTable) {
                const date = new Date().toISOString().slice(0, 10);
                exportTableToCSV(followupTable, `suivi_prospects_${date}.csv`);
            } else {
                showError('Aucune donnée à exporter');
            }
        });
    }
    
    // Fonction pour rafraîchir la page
    function refreshPage() {
        // Ajouter une animation de rotation à l'icône
        const icon = refreshBtn.querySelector('i');
        icon.classList.add('fa-spin');
        
        // Afficher une notification
        showInfo('Actualisation de la liste des prospects...', 1000);
        
        // Simuler un chargement
        setTimeout(() => {
            // Arrêter l'animation et recharger la page
            icon.classList.remove('fa-spin');
            showSuccess('Liste des prospects actualisée avec succès !');
            window.location.reload();
        }, 1000);
    }
    
    // Fonction pour mettre à jour le statut d'un prospect
    function updateStatus(event) {
        const badge = event.currentTarget;
        const statusOptions = ['non contacté', 'contacté', 'rendez-vous', 'pas intéressé'];
        const currentStatus = badge.textContent.trim();
        
        // Trouver l'index du statut actuel
        const currentIndex = statusOptions.indexOf(currentStatus);
        
        // Passer au statut suivant (ou revenir au premier)
        const nextIndex = (currentIndex + 1) % statusOptions.length;
        const nextStatus = statusOptions[nextIndex];
        
        // Mettre à jour le texte et la classe du badge
        badge.textContent = nextStatus;
        
        // Supprimer toutes les classes de statut
        statusOptions.forEach(status => {
            badge.classList.remove(status.replace(' ', '-'));
        });
        
        // Ajouter la nouvelle classe de statut
        badge.classList.add(nextStatus.replace(' ', '-'));
        
        // Afficher une notification
        showSuccess(`Statut mis à jour : ${nextStatus}`);
        
        // Dans une application réelle, on enverrait une requête AJAX ici
        console.log(`Statut mis à jour : ${nextStatus}`);
    }
    
    // Fonction pour ajouter une action à une cellule de date
    function toggleAction(event) {
        const cell = event.currentTarget;
        
        // Si la cellule a déjà une action
        if (cell.classList.contains('has-action')) {
            // Supprimer l'action
            cell.classList.remove('has-action');
            cell.innerHTML = '<i class="far fa-circle"></i>';
            showInfo('Action supprimée');
        } else {
            // Ajouter une action (1er message par défaut)
            cell.classList.add('has-action');
            cell.innerHTML = '<span class="action-badge 1er-message">1er message</span>';
            showSuccess('Action "1er message" ajoutée');
        }
        
        // Dans une application réelle, on enverrait une requête AJAX ici
        console.log(`Action modifiée pour la date`);
    }
    
    // Écouteurs d'événements
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshPage);
    }
    
    // Rendre les badges de statut cliquables
    statusBadges.forEach(badge => {
        badge.style.cursor = 'pointer';
        badge.title = 'Cliquer pour changer le statut';
        badge.addEventListener('click', updateStatus);
    });
    
    // Rendre les cellules de date cliquables
    dateCells.forEach(cell => {
        cell.style.cursor = 'pointer';
        cell.title = 'Cliquer pour ajouter/supprimer une action';
        cell.addEventListener('click', toggleAction);
    });
    
    // Ajouter une animation de chargement lors du clic sur les liens de pagination
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Ne pas ajouter d'animation aux liens désactivés
            if (this.classList.contains('disabled') || this.classList.contains('active')) {
                e.preventDefault();
                return;
            }
            
            // Ajouter une classe pour l'animation de chargement
            document.body.classList.add('page-loading');
            showInfo('Chargement de la page...');
        });
    });
    
    // Ajouter le bouton d'exportation
    addExportButton();
    
    // Ajouter des styles pour le bouton d'exportation
    const style = document.createElement('style');
    style.textContent = `
        .header-buttons {
            display: flex;
            gap: 10px;
        }
        .btn-export {
            background-color: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-export:hover {
            background-color: #4338ca;
        }
    `;
    document.head.appendChild(style);
    
    // Afficher une notification de bienvenue
    setTimeout(() => {
        showInfo('Bienvenue sur la page de suivi des prospects !');
    }, 500);
});

// Fonction pour exporter une table en CSV
function exportTableToCSV(table, filename) {
    const rows = table.rows;
    const csv = [];
    
    // Ajouter les en-têtes
    const headers = Array.from(rows[0].cells).map(cell => cell.textContent);
    csv.push(headers.join(';'));
    
    // Ajouter les lignes
    for (let i = 1; i < rows.length; i++) {
        const row = Array.from(rows[i].cells).map(cell => cell.textContent);
        csv.push(row.join(';'));
    }
    
    // Créer un lien pour télécharger le fichier
    const link = document.createElement('a');
    link.href = `data:text/csv;charset=utf-8,${csv.join('\n')}`;
    link.download = filename;
    link.click();
}
