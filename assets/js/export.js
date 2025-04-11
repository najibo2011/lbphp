/**
 * Fonctions d'exportation de données pour LeadsBuilder
 */

/**
 * Exporte les données d'un tableau HTML vers un fichier CSV
 * @param {HTMLTableElement} table - Le tableau à exporter
 * @param {string} filename - Nom du fichier d'exportation
 */
function exportTableToCSV(table, filename = 'export.csv') {
    // Vérifier que la table existe
    if (!table || !table.rows) {
        showError('Tableau non trouvé pour l\'exportation');
        return;
    }
    
    // Créer un tableau pour stocker les données
    const rows = [];
    
    // Parcourir les lignes du tableau
    for (let i = 0; i < table.rows.length; i++) {
        const row = [];
        const cols = table.rows[i].cells;
        
        // Parcourir les cellules de la ligne
        for (let j = 0; j < cols.length; j++) {
            // Récupérer le texte de la cellule (sans les balises HTML)
            let cellText = cols[j].innerText.trim();
            
            // Échapper les guillemets et ajouter des guillemets autour du texte
            cellText = cellText.replace(/"/g, '""');
            row.push(`"${cellText}"`);
        }
        
        // Ajouter la ligne au tableau
        rows.push(row.join(','));
    }
    
    // Créer le contenu CSV
    const csvContent = rows.join('\n');
    
    // Créer un objet Blob pour le téléchargement
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    
    // Créer un lien de téléchargement
    const link = document.createElement('a');
    
    // Vérifier si le navigateur prend en charge l'API URL
    if (window.URL && window.URL.createObjectURL) {
        link.href = window.URL.createObjectURL(blob);
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showSuccess(`Exportation réussie : ${filename}`);
    } else {
        showError('Votre navigateur ne prend pas en charge l\'exportation de fichiers');
    }
}

/**
 * Exporte les données d'un tableau de bord CRM vers un fichier CSV
 * @param {NodeList} cards - Les cartes CRM à exporter
 * @param {string} filename - Nom du fichier d'exportation
 */
function exportCRMToCSV(cards, filename = 'crm_export.csv') {
    // Vérifier que les cartes existent
    if (!cards || cards.length === 0) {
        showError('Aucune donnée CRM trouvée pour l\'exportation');
        return;
    }
    
    // Créer les en-têtes
    const headers = ['Liste', 'Contacts', '1er message', 'Relances', 'Rendez-vous', 'Messages totaux'];
    
    // Créer un tableau pour stocker les données
    const rows = [headers.join(',')];
    
    // Parcourir les cartes
    cards.forEach(card => {
        const row = [];
        
        // Nom de la liste
        const listName = card.querySelector('.list-name').textContent.trim();
        row.push(`"${listName.replace(/"/g, '""')}"`);
        
        // Nombre de contacts
        const contactCount = card.querySelector('.contact-count').textContent.trim().split(' ')[0];
        row.push(contactCount);
        
        // Récupérer les valeurs des étapes
        const progressItems = card.querySelectorAll('.progress-item');
        const stageValues = {};
        
        progressItems.forEach(item => {
            const stageName = item.querySelector('.stage-name').textContent.trim();
            const stageCount = item.querySelector('.stage-count').textContent.trim();
            stageValues[stageName] = stageCount;
        });
        
        // Ajouter les valeurs dans l'ordre
        row.push(stageValues['1er message'] || '0');
        row.push(stageValues['Relances'] || '0');
        row.push(stageValues['Rendez-vous'] || '0');
        
        // Messages totaux
        const totalMessages = card.querySelector('.total-messages').textContent.trim().split(' ')[0];
        row.push(totalMessages);
        
        // Ajouter la ligne au tableau
        rows.push(row.join(','));
    });
    
    // Créer le contenu CSV
    const csvContent = rows.join('\n');
    
    // Créer un objet Blob pour le téléchargement
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    
    // Créer un lien de téléchargement
    const link = document.createElement('a');
    
    // Vérifier si le navigateur prend en charge l'API URL
    if (window.URL && window.URL.createObjectURL) {
        link.href = window.URL.createObjectURL(blob);
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showSuccess(`Exportation réussie : ${filename}`);
    } else {
        showError('Votre navigateur ne prend pas en charge l\'exportation de fichiers');
    }
}
