/**
 * Fonctions pour gérer les opérations sur les listes
 */

// Fonction pour ajouter un profil à une liste
function addProfileToList(profileId, listId, notes = '') {
    return fetch('lists.php?action=addProfile', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            'profile_id': profileId,
            'list_id': listId,
            'notes': notes
        })
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Erreur lors de l\'ajout du profil à la liste:', error);
        return { success: false, message: 'Erreur de connexion' };
    });
}

// Fonction pour supprimer un profil d'une liste
function removeProfileFromList(profileId, listId) {
    return fetch('lists.php?action=removeProfile', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            'profile_id': profileId,
            'list_id': listId
        })
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Erreur lors de la suppression du profil de la liste:', error);
        return { success: false, message: 'Erreur de connexion' };
    });
}

// Fonction pour afficher le modal d'ajout à une liste
function showAddToListModal(profileId) {
    // Récupérer les listes de l'utilisateur
    fetch('lists.php?action=getUserLists', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Créer le contenu du modal
            let modalContent = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Ajouter à une liste</h3>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="profile-id-to-add" value="${profileId}">
                        
                        <div class="form-group">
                            <label for="list-select">Choisir une liste</label>
                            <select id="list-select" class="form-control">
                                <option value="">-- Sélectionner une liste --</option>
                                ${data.lists.map(list => `<option value="${list.id}">${list.name}</option>`).join('')}
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="profile-notes">Notes (optionnel)</label>
                            <textarea id="profile-notes" class="form-control" placeholder="Ajouter des notes sur ce profil..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button id="create-new-list-btn" class="btn btn-outline">
                                <i class="fas fa-plus"></i> Créer une nouvelle liste
                            </button>
                        </div>
                        
                        <div id="new-list-form" style="display: none;">
                            <div class="form-group">
                                <label for="new-list-name">Nom de la liste</label>
                                <input type="text" id="new-list-name" class="form-control" placeholder="Entrez le nom de la liste">
                            </div>
                            <div class="form-group">
                                <label for="new-list-description">Description (optionnel)</label>
                                <textarea id="new-list-description" class="form-control" placeholder="Description de la liste..."></textarea>
                            </div>
                            <div class="form-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" id="new-list-public">
                                    <span class="checkmark"></span>
                                    Liste publique
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline" id="cancel-add-to-list">Annuler</button>
                        <button class="btn btn-primary" id="confirm-add-to-list">Ajouter</button>
                    </div>
                </div>
            `;
            
            // Afficher le modal
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.id = 'add-to-list-modal';
            modal.innerHTML = modalContent;
            document.body.appendChild(modal);
            modal.style.display = 'block';
            
            // Gérer les événements du modal
            setupAddToListModalEvents();
        } else {
            showNotification('error', 'Erreur lors de la récupération des listes');
        }
    })
    .catch(error => {
        console.error('Erreur lors de la récupération des listes:', error);
        showNotification('error', 'Erreur de connexion');
    });
}

// Configurer les événements du modal d'ajout à une liste
function setupAddToListModalEvents() {
    const modal = document.getElementById('add-to-list-modal');
    const closeBtn = modal.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-add-to-list');
    const confirmBtn = document.getElementById('confirm-add-to-list');
    const createNewListBtn = document.getElementById('create-new-list-btn');
    const newListForm = document.getElementById('new-list-form');
    
    // Fermer le modal
    function closeModal() {
        document.body.removeChild(modal);
    }
    
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    // Afficher/masquer le formulaire de création de liste
    createNewListBtn.addEventListener('click', function() {
        const isVisible = newListForm.style.display !== 'none';
        newListForm.style.display = isVisible ? 'none' : 'block';
        this.innerHTML = isVisible 
            ? '<i class="fas fa-plus"></i> Créer une nouvelle liste' 
            : '<i class="fas fa-times"></i> Annuler la création';
    });
    
    // Ajouter à une liste existante ou créer une nouvelle liste
    confirmBtn.addEventListener('click', function() {
        const profileId = document.getElementById('profile-id-to-add').value;
        const listSelect = document.getElementById('list-select');
        const notes = document.getElementById('profile-notes').value;
        
        // Vérifier si on crée une nouvelle liste
        if (newListForm.style.display !== 'none') {
            const newListName = document.getElementById('new-list-name').value;
            const newListDescription = document.getElementById('new-list-description').value;
            const newListPublic = document.getElementById('new-list-public').checked ? 1 : 0;
            
            if (!newListName) {
                showNotification('error', 'Le nom de la liste est obligatoire');
                return;
            }
            
            // Créer une nouvelle liste puis ajouter le profil
            fetch('lists.php?action=createAjax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    'name': newListName,
                    'description': newListDescription,
                    'is_public': newListPublic
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ajouter le profil à la nouvelle liste
                    return addProfileToList(profileId, data.list_id, notes);
                } else {
                    showNotification('error', data.message || 'Erreur lors de la création de la liste');
                    return { success: false };
                }
            })
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Profil ajouté à la nouvelle liste');
                    closeModal();
                }
            })
            .catch(error => {
                console.error('Erreur lors de la création de la liste:', error);
                showNotification('error', 'Erreur de connexion');
            });
        } else {
            // Ajouter à une liste existante
            const listId = listSelect.value;
            
            if (!listId) {
                showNotification('error', 'Veuillez sélectionner une liste');
                return;
            }
            
            addProfileToList(profileId, listId, notes)
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message || 'Profil ajouté à la liste');
                        closeModal();
                    } else {
                        showNotification('error', data.message || 'Erreur lors de l\'ajout du profil');
                    }
                });
        }
    });
}

// Fonction pour afficher une notification
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        </div>
        <div class="notification-message">${message}</div>
    `;
    
    document.body.appendChild(notification);
    
    // Animer l'apparition
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Supprimer après 3 secondes
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
