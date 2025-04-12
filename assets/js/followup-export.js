/**
 * Fonctions pour l'exportation des données de suivi
 */

/**
 * Exporte les données de suivi au format CSV
 * @param {Array} data Données à exporter
 * @param {String} filename Nom du fichier
 */
function exportFollowupData(data, filename) {
    // Définir les en-têtes
    const headers = [
        'ID',
        'Nom d\'utilisateur',
        'Description',
        'Liste',
        'Statut',
        'Date d\'ajout',
        'Dernière interaction'
    ];
    
    // Créer le contenu CSV
    let csvContent = headers.join(';') + '\n';
    
    // Ajouter les données
    data.forEach(row => {
        const values = [
            row.id,
            row.username,
            row.description,
            row.list_name,
            row.status,
            formatDate(row.created_at),
            formatDate(row.last_interaction)
        ];
        
        csvContent += values.join(';') + '\n';
    });
    
    // Créer un Blob avec le contenu CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    
    // Créer un lien pour télécharger le fichier
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    // Afficher une notification
    showNotification('Export CSV réussi.', 'success');
}

/**
 * Exporte les interactions d'un prospect au format CSV
 * @param {Number} followupId ID du suivi
 * @param {String} username Nom d'utilisateur du prospect
 */
function exportInteractions(followupId, username) {
    // Récupérer les interactions
    fetch(`followup.php?action=getInteractionsForExport&followup_id=${followupId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Définir les en-têtes
                const headers = [
                    'Date',
                    'Type',
                    'Notes'
                ];
                
                // Créer le contenu CSV
                let csvContent = headers.join(';') + '\n';
                
                // Ajouter les données
                data.interactions.forEach(interaction => {
                    const values = [
                        formatDate(interaction.interaction_date),
                        interaction.type,
                        interaction.notes
                    ];
                    
                    csvContent += values.join(';') + '\n';
                });
                
                // Créer un Blob avec le contenu CSV
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                
                // Créer un lien pour télécharger le fichier
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                
                const filename = `interactions_${username}_${formatDateForFilename(new Date())}.csv`;
                
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                
                document.body.appendChild(link);
                link.click();
                
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
                
                // Afficher une notification
                showNotification('Export des interactions réussi.', 'success');
            } else {
                showNotification('Erreur lors de l\'export des interactions.', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'export des interactions:', error);
            showNotification('Erreur lors de l\'export des interactions.', 'error');
        });
}

/**
 * Formate une date pour l'affichage
 * @param {String} dateString Date au format ISO
 * @returns {String} Date formatée
 */
function formatDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

/**
 * Formate une date pour un nom de fichier
 * @param {Date} date Date à formater
 * @returns {String} Date formatée
 */
function formatDateForFilename(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
}
