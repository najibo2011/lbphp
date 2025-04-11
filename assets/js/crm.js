/**
 * Script pour la gestion du tableau de bord CRM
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const refreshBtn = document.querySelector('.btn-refresh');
    const progressBars = document.querySelectorAll('.progress-bar');
    const crmCards = document.querySelectorAll('.crm-card');
    
    // Ajouter un bouton d'exportation
    function addExportButton() {
        const header = document.querySelector('.crm-header');
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
            if (crmCards && crmCards.length > 0) {
                const date = new Date().toISOString().slice(0, 10);
                exportCRMToCSV(crmCards, `crm_dashboard_${date}.csv`);
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
        showInfo('Actualisation du tableau de bord CRM...', 1000);
        
        // Simuler un chargement
        setTimeout(() => {
            // Arrêter l'animation et recharger la page
            icon.classList.remove('fa-spin');
            showSuccess('Tableau de bord CRM actualisé avec succès !');
            window.location.reload();
        }, 1000);
    }
    
    // Fonction pour animer les barres de progression au chargement
    function animateProgressBars() {
        progressBars.forEach(bar => {
            const targetWidth = bar.style.width;
            
            // Réinitialiser la largeur
            bar.style.width = '0%';
            
            // Animer jusqu'à la largeur cible
            setTimeout(() => {
                bar.style.transition = 'width 1s ease-in-out';
                bar.style.width = targetWidth;
            }, 100);
        });
    }
    
    // Fonction pour rendre les cartes cliquables
    function makeCardsClickable() {
        crmCards.forEach(card => {
            const listName = card.querySelector('.list-name').textContent;
            
            // Ajouter un style de survol
            card.style.cursor = 'pointer';
            card.title = 'Cliquer pour voir les détails de la liste';
            
            // Ajouter un écouteur d'événement
            card.addEventListener('click', function() {
                // Dans une application réelle, on redirigerait vers la page de la liste
                console.log(`Afficher les détails de la liste : ${listName}`);
                
                // Afficher une notification
                showInfo(`Ouverture de la liste "${listName}"...`);
                
                // Simuler une redirection
                setTimeout(() => {
                    showWarning(`Fonctionnalité en développement : détails de la liste "${listName}"`);
                }, 1000);
            });
        });
    }
    
    // Écouteurs d'événements
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshPage);
    }
    
    // Animer les barres de progression au chargement
    animateProgressBars();
    
    // Rendre les cartes cliquables
    makeCardsClickable();
    
    // Ajouter des info-bulles pour les barres de progression
    const progressItems = document.querySelectorAll('.progress-item');
    progressItems.forEach(item => {
        const stageName = item.querySelector('.stage-name').textContent;
        const stageCount = item.querySelector('.stage-count').textContent;
        const progressBar = item.querySelector('.progress-bar');
        
        // Créer une info-bulle
        progressBar.title = `${stageName}: ${stageCount}`;
        
        // Ajouter un écouteur d'événement pour afficher les détails
        progressBar.style.cursor = 'pointer';
        progressBar.addEventListener('click', function(e) {
            e.stopPropagation(); // Empêcher le déclenchement du clic sur la carte
            showInfo(`${stageName}: ${stageCount} contacts`);
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
        showInfo('Bienvenue sur le tableau de bord CRM !');
    }, 500);
});

// Fonction pour exporter les données CRM au format CSV
function exportCRMToCSV(cards, filename) {
    const rows = [];
    cards.forEach(card => {
        const listName = card.querySelector('.list-name').textContent;
        const stageName = card.querySelector('.stage-name').textContent;
        const stageCount = card.querySelector('.stage-count').textContent;
        
        rows.push([listName, stageName, stageCount]);
    });
    
    const csvContent = rows.map(row => row.join(';')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}
