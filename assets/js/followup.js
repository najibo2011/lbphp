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
    const editStatusBtns = document.querySelectorAll('.btn-edit-status');
    const deleteFollowupBtns = document.querySelectorAll('.btn-delete-followup');
    
    // Modales
    const statusModal = document.getElementById('statusModal');
    const interactionModal = document.getElementById('interactionModal');
    const deleteModal = document.getElementById('deleteModal');
    const viewInteractionsModal = document.getElementById('viewInteractionsModal');
    
    // Formulaires
    const statusForm = document.getElementById('statusForm');
    const interactionForm = document.getElementById('interactionForm');
    const deleteForm = document.getElementById('deleteForm');
    
    // Champs de formulaire
    const statusFollowupId = document.getElementById('statusFollowupId');
    const interactionFollowupId = document.getElementById('interactionFollowupId');
    const interactionDate = document.getElementById('interactionDate');
    const deleteFollowupId = document.getElementById('deleteFollowupId');
    const interactionDateDisplay = document.getElementById('interactionDateDisplay');
    const interactionsList = document.getElementById('interactionsList');
    const addInteractionBtn = document.getElementById('addInteractionBtn');
    
    // Champs pour les options avancées
    const scheduleInteractionCheckbox = document.getElementById('scheduleInteraction');
    const scheduledDateGroup = document.querySelector('.scheduled-date-group');
    const updateStatusCheckbox = document.getElementById('updateStatus');
    const statusUpdateGroup = document.querySelector('.status-update-group');
    
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
        showNotification('Actualisation en cours...', 'info');
        
        // Recharger la page après un court délai
        setTimeout(function() {
            window.location.reload();
        }, 500);
    }
    
    // Fonction pour mettre à jour le statut d'un prospect
    function updateStatus(event) {
        const statusBadge = event.currentTarget;
        const row = statusBadge.closest('tr');
        const followupId = row.dataset.followupId;
        const currentStatus = statusBadge.dataset.status;
        
        // Mettre à jour le formulaire
        statusFollowupId.value = followupId;
        
        // Sélectionner le statut actuel dans le menu déroulant
        const statusSelect = document.getElementById('status');
        for (let i = 0; i < statusSelect.options.length; i++) {
            if (statusSelect.options[i].value === currentStatus) {
                statusSelect.selectedIndex = i;
                break;
            }
        }
        
        // Afficher la modale
        showModal(statusModal);
    }
    
    // Fonction pour ajouter une interaction à une cellule de date
    function toggleAction(event) {
        const cell = event.currentTarget;
        const row = cell.closest('tr');
        const followupId = row.dataset.followupId;
        const date = cell.dataset.date;
        
        // Si la cellule a déjà une action, afficher les détails
        if (cell.classList.contains('has-action')) {
            // Afficher la modale de visualisation des interactions
            interactionDateDisplay.textContent = formatDate(date);
            interactionFollowupId.value = followupId;
            interactionDate.value = date;
            
            // Charger les interactions pour cette date
            loadInteractions(followupId, date);
            
            // Configurer le bouton d'ajout d'interaction
            addInteractionBtn.onclick = function() {
                hideModal(viewInteractionsModal);
                showAddInteractionModal(followupId, date);
            };
            
            showModal(viewInteractionsModal);
        } else {
            // Afficher la modale d'ajout d'interaction
            showAddInteractionModal(followupId, date);
        }
    }
    
    // Fonction pour afficher la modale d'ajout d'interaction
    function showAddInteractionModal(followupId, date) {
        interactionFollowupId.value = followupId;
        interactionDate.value = date;
        
        // Réinitialiser le formulaire
        interactionForm.reset();
        
        // Cacher les groupes optionnels
        scheduledDateGroup.style.display = 'none';
        statusUpdateGroup.style.display = 'none';
        
        // Afficher la modale
        showModal(interactionModal);
    }
    
    // Fonction pour charger les interactions d'une date
    function loadInteractions(followupId, date) {
        // Vider la liste
        interactionsList.innerHTML = '';
        
        // Simuler le chargement des interactions (à remplacer par un appel AJAX)
        const loadingMessage = document.createElement('p');
        loadingMessage.textContent = 'Chargement des interactions...';
        interactionsList.appendChild(loadingMessage);
        
        // Simuler un délai de chargement
        setTimeout(function() {
            // Vider la liste
            interactionsList.innerHTML = '';
            
            // Récupérer les interactions pour cette date et ce suivi
            fetch(`followup.php?action=getInteractions&followup_id=${followupId}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.interactions.length > 0) {
                        // Créer la liste des interactions
                        const list = document.createElement('ul');
                        list.className = 'interactions-list';
                        
                        data.interactions.forEach(interaction => {
                            const item = document.createElement('li');
                            item.className = 'interaction-item';
                            
                            const header = document.createElement('div');
                            header.className = 'interaction-header';
                            
                            const type = document.createElement('span');
                            type.className = 'interaction-type';
                            type.textContent = interaction.type;
                            
                            const time = document.createElement('span');
                            time.className = 'interaction-time';
                            time.textContent = formatTime(interaction.created_at);
                            
                            header.appendChild(type);
                            header.appendChild(time);
                            
                            const content = document.createElement('div');
                            content.className = 'interaction-content';
                            content.textContent = interaction.notes || 'Aucune note';
                            
                            const actions = document.createElement('div');
                            actions.className = 'interaction-actions';
                            
                            const deleteBtn = document.createElement('button');
                            deleteBtn.className = 'btn-text btn-danger';
                            deleteBtn.textContent = 'Supprimer';
                            deleteBtn.dataset.id = interaction.id;
                            deleteBtn.addEventListener('click', function() {
                                deleteInteraction(interaction.id);
                            });
                            
                            actions.appendChild(deleteBtn);
                            
                            item.appendChild(header);
                            item.appendChild(content);
                            item.appendChild(actions);
                            
                            list.appendChild(item);
                        });
                        
                        interactionsList.appendChild(list);
                    } else {
                        // Aucune interaction
                        const message = document.createElement('p');
                        message.className = 'no-interactions';
                        message.textContent = 'Aucune interaction pour cette date.';
                        interactionsList.appendChild(message);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des interactions:', error);
                    const errorMessage = document.createElement('p');
                    errorMessage.className = 'error-message';
                    errorMessage.textContent = 'Erreur lors du chargement des interactions.';
                    interactionsList.appendChild(errorMessage);
                });
        }, 500);
    }
    
    // Fonction pour supprimer une interaction
    function deleteInteraction(interactionId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette interaction ?')) {
            // Récupérer le token CSRF
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;
            
            // Préparer les données
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('interaction_id', interactionId);
            
            // Envoyer la requête
            fetch('followup.php?action=deleteInteraction', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Interaction supprimée avec succès.', 'success');
                    
                    // Recharger les interactions
                    const followupId = interactionFollowupId.value;
                    const date = interactionDate.value;
                    loadInteractions(followupId, date);
                } else {
                    showError(data.message || 'Erreur lors de la suppression de l\'interaction.');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression de l\'interaction:', error);
                showError('Erreur lors de la suppression de l\'interaction.');
            });
        }
    }
    
    // Fonction pour supprimer un suivi
    function deleteFollowup(event) {
        const button = event.currentTarget;
        const row = button.closest('tr');
        const followupId = row.dataset.followupId;
        
        // Mettre à jour le formulaire
        deleteFollowupId.value = followupId;
        
        // Afficher la modale
        showModal(deleteModal);
    }
    
    // Fonction pour afficher une modale
    function showModal(modal) {
        modal.style.display = 'block';
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }
    
    // Fonction pour masquer une modale
    function hideModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    // Fonction pour formater une date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    // Fonction pour formater une heure
    function formatTime(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Fonction pour afficher une notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Fonction pour afficher une erreur
    function showError(message) {
        showNotification(message, 'error');
    }
    
    // Fonction pour exporter une table en CSV
    function exportTableToCSV(table, filename) {
        const rows = table.querySelectorAll('tr');
        const csvContent = [];
        
        // Ajouter les en-têtes
        const headers = [];
        const headerCells = rows[0].querySelectorAll('th');
        headerCells.forEach(cell => {
            headers.push(cell.textContent.trim());
        });
        csvContent.push(headers.join(','));
        
        // Ajouter les données
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const rowData = [];
            
            // Vérifier si c'est une ligne vide
            if (row.querySelector('.empty-table')) {
                continue;
            }
            
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                // Traiter différemment selon le type de cellule
                if (cell.classList.contains('account-cell')) {
                    const account = cell.querySelector('.account-link').textContent.trim();
                    const description = cell.querySelector('.account-description').textContent.trim();
                    rowData.push(`"${account}"`);
                    rowData.push(`"${description}"`);
                } else if (cell.classList.contains('list-cell')) {
                    rowData.push(`"${cell.textContent.trim()}"`);
                } else if (cell.classList.contains('status-cell')) {
                    const status = cell.querySelector('.status-badge').textContent.trim();
                    rowData.push(`"${status}"`);
                } else if (cell.classList.contains('date-cell')) {
                    if (cell.classList.contains('has-action')) {
                        const action = cell.querySelector('.action-badge').textContent.trim();
                        rowData.push(`"${action}"`);
                    } else {
                        rowData.push('""');
                    }
                } else if (cell.classList.contains('actions-cell')) {
                    // Ignorer cette cellule
                } else {
                    rowData.push(`"${cell.textContent.trim()}"`);
                }
            });
            
            csvContent.push(rowData.join(','));
        }
        
        // Créer un lien de téléchargement
        const csvString = csvContent.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        // Créer une URL pour le blob
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        
        // Ajouter le lien au document
        document.body.appendChild(link);
        
        // Cliquer sur le lien
        link.click();
        
        // Nettoyer
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        // Afficher une notification
        showNotification('Export CSV réussi.', 'success');
    }
    
    // Écouteurs d'événements
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshPage);
    }
    
    // Écouteurs pour les badges de statut
    statusBadges.forEach(badge => {
        badge.addEventListener('click', updateStatus);
    });
    
    // Écouteurs pour les cellules de date
    dateCells.forEach(cell => {
        cell.addEventListener('click', toggleAction);
    });
    
    // Écouteurs pour les boutons d'édition de statut
    editStatusBtns.forEach(btn => {
        btn.addEventListener('click', function(event) {
            const row = btn.closest('tr');
            const statusBadge = row.querySelector('.status-badge');
            updateStatus({ currentTarget: statusBadge });
        });
    });
    
    // Écouteurs pour les boutons de suppression
    deleteFollowupBtns.forEach(btn => {
        btn.addEventListener('click', deleteFollowup);
    });
    
    // Écouteurs pour les liens de pagination
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            if (link.classList.contains('disabled')) {
                event.preventDefault();
            }
        });
    });
    
    // Écouteurs pour les modales
    document.querySelectorAll('.modal .close, .modal .close-modal').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = closeBtn.closest('.modal');
            hideModal(modal);
        });
    });
    
    // Fermer les modales en cliquant en dehors
    window.addEventListener('click', function(event) {
        document.querySelectorAll('.modal').forEach(modal => {
            if (event.target === modal) {
                hideModal(modal);
            }
        });
    });
    
    // Soumettre le formulaire de statut
    statusForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Récupérer les données du formulaire
        const formData = new FormData(statusForm);
        
        // Envoyer la requête
        fetch('followup.php?action=updateStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'interface
                const followupId = formData.get('followup_id');
                const status = formData.get('status');
                const row = document.querySelector(`tr[data-followup-id="${followupId}"]`);
                const statusBadge = row.querySelector('.status-badge');
                
                // Mettre à jour le texte et les classes
                statusBadge.textContent = status;
                statusBadge.dataset.status = status;
                
                // Supprimer toutes les classes de statut
                statusBadge.className = 'status-badge';
                
                // Ajouter la nouvelle classe de statut
                statusBadge.classList.add(status.replace(/ /g, '-'));
                
                // Fermer la modale
                hideModal(statusModal);
                
                // Afficher une notification
                showNotification('Statut mis à jour avec succès.', 'success');
            } else {
                showError(data.message || 'Erreur lors de la mise à jour du statut.');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la mise à jour du statut:', error);
            showError('Erreur lors de la mise à jour du statut.');
        });
    });
    
    // Soumettre le formulaire d'interaction
    interactionForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Récupérer les données du formulaire
        const formData = new FormData(interactionForm);
        
        // Si la planification est activée mais aucune date n'est sélectionnée
        if (scheduleInteractionCheckbox.checked && !formData.get('scheduled_date')) {
            showNotification('Veuillez sélectionner une date pour l\'interaction planifiée', 'error');
            return;
        }
        
        // Si la mise à jour du statut est activée mais aucun statut n'est sélectionné
        if (updateStatusCheckbox.checked && !formData.get('status')) {
            showNotification('Veuillez sélectionner un statut', 'error');
            return;
        }
        
        // Supprimer les champs inutiles
        if (!scheduleInteractionCheckbox.checked) {
            formData.delete('scheduled_date');
        }
        
        if (!updateStatusCheckbox.checked) {
            formData.delete('status');
            formData.set('update_status', '0');
        }
        
        // Envoyer les données
        fetch('followup.php?action=addInteraction', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Interaction ajoutée avec succès', 'success');
                hideModal(interactionModal);
                
                // Rafraîchir la page après un court délai
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Erreur lors de l\'ajout de l\'interaction', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors de l\'ajout de l\'interaction', 'error');
        });
    });
    
    // Soumettre le formulaire de suppression
    deleteForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Récupérer les données du formulaire
        const formData = new FormData(deleteForm);
        
        // Envoyer la requête
        fetch('followup.php?action=deleteFollowup', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'interface
                const followupId = formData.get('followup_id');
                const row = document.querySelector(`tr[data-followup-id="${followupId}"]`);
                
                // Supprimer la ligne
                row.remove();
                
                // Fermer la modale
                hideModal(deleteModal);
                
                // Afficher une notification
                showNotification('Prospect supprimé du suivi avec succès.', 'success');
                
                // Recharger la page si la table est vide
                const rows = followupTable.querySelectorAll('tbody tr');
                if (rows.length === 0) {
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                showError(data.message || 'Erreur lors de la suppression du prospect.');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la suppression du prospect:', error);
            showError('Erreur lors de la suppression du prospect.');
        });
    });
    
    // Gérer l'affichage des champs optionnels
    if (scheduleInteractionCheckbox) {
        scheduleInteractionCheckbox.addEventListener('change', function() {
            scheduledDateGroup.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    if (updateStatusCheckbox) {
        updateStatusCheckbox.addEventListener('change', function() {
            statusUpdateGroup.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Initialisation
    // addExportButton(); // Commenté car nous avons déjà un bouton d'export dans le HTML
});
