/**
 * Gestion des listes de profils
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const addToListModal = document.getElementById('add-to-list-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const addToListForm = document.getElementById('add-to-list-form');
    const createListForm = document.getElementById('create-list-form');
    const listSelect = document.getElementById('list-select');
    const createListToggle = document.getElementById('create-list-toggle');
    const existingListSection = document.getElementById('existing-list-section');
    const newListSection = document.getElementById('new-list-section');
    
    // Initialisation
    initModals();
    initListManagement();
    
    /**
     * Initialiser les modals
     */
    function initModals() {
        // Fermer les modals
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            });
        });
        
        // Fermer les modals en cliquant en dehors
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target);
            }
        });
    }
    
    /**
     * Initialiser la gestion des listes
     */
    function initListManagement() {
        // Toggle entre sélection de liste existante et création de nouvelle liste
        if (createListToggle) {
            createListToggle.addEventListener('change', function() {
                if (this.checked) {
                    existingListSection.style.display = 'none';
                    newListSection.style.display = 'block';
                } else {
                    existingListSection.style.display = 'block';
                    newListSection.style.display = 'none';
                }
            });
        }
        
        // Soumission du formulaire d'ajout à une liste
        if (addToListForm) {
            addToListForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const isCreatingNewList = createListToggle.checked;
                
                if (isCreatingNewList) {
                    // Créer une nouvelle liste puis ajouter les profils
                    createListAndAddProfiles();
                } else {
                    // Ajouter les profils à une liste existante
                    addProfilesToExistingList();
                }
            });
        }
    }
    
    /**
     * Créer une nouvelle liste et y ajouter les profils
     */
    function createListAndAddProfiles() {
        const listName = document.getElementById('new-list-name').value;
        const listDescription = document.getElementById('new-list-description').value;
        const isPublic = document.getElementById('new-list-public').checked ? 1 : 0;
        const notes = document.getElementById('profile-notes').value;
        const selectedProfiles = getSelectedProfileIds();
        
        if (!listName) {
            showNotification('Veuillez saisir un nom pour la liste', 'error');
            return;
        }
        
        if (selectedProfiles.length === 0) {
            showNotification('Aucun profil sélectionné', 'error');
            return;
        }
        
        // Créer la liste via AJAX
        const formData = new FormData();
        formData.append('name', listName);
        formData.append('description', listDescription);
        if (isPublic) {
            formData.append('is_public', isPublic);
        }
        
        fetch('/lb1/ajax/lists/create.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Ajouter les profils à la liste créée
                addProfilesToList(data.list.id, selectedProfiles, notes);
            } else {
                showNotification(data.message || 'Erreur lors de la création de la liste', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur de connexion au serveur', 'error');
        });
    }
    
    /**
     * Ajouter les profils à une liste existante
     */
    function addProfilesToExistingList() {
        const listId = listSelect.value;
        const notes = document.getElementById('profile-notes').value;
        const selectedProfiles = getSelectedProfileIds();
        
        if (!listId) {
            showNotification('Veuillez sélectionner une liste', 'error');
            return;
        }
        
        if (selectedProfiles.length === 0) {
            showNotification('Aucun profil sélectionné', 'error');
            return;
        }
        
        addProfilesToList(listId, selectedProfiles, notes);
    }
    
    /**
     * Ajouter des profils à une liste
     */
    function addProfilesToList(listId, profileIds, notes) {
        const formData = new FormData();
        formData.append('list_id', listId);
        formData.append('profile_ids', profileIds.join(','));
        formData.append('notes', notes);
        
        fetch('/lb1/ajax/lists/add_profiles.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Profils ajoutés avec succès', 'success');
                closeModal(addToListModal);
                resetForm(addToListForm);
            } else {
                showNotification(data.message || 'Erreur lors de l\'ajout des profils', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur de connexion au serveur', 'error');
        });
    }
    
    /**
     * Récupérer les IDs des profils sélectionnés
     */
    function getSelectedProfileIds() {
        const selectedProfiles = [];
        const checkboxes = document.querySelectorAll('.profile-checkbox:checked');
        
        checkboxes.forEach(checkbox => {
            selectedProfiles.push(checkbox.value);
        });
        
        return selectedProfiles;
    }
    
    /**
     * Charger les listes de l'utilisateur
     */
    function loadUserLists() {
        fetch('/lb1/ajax/lists/get_user_lists.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.lists) {
                    updateListSelect(data.lists);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
    }
    
    /**
     * Mettre à jour le select des listes
     */
    function updateListSelect(lists) {
        // Vider le select
        listSelect.innerHTML = '';
        
        // Option par défaut
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Sélectionnez une liste';
        listSelect.appendChild(defaultOption);
        
        // Ajouter les listes
        lists.forEach(list => {
            const option = document.createElement('option');
            option.value = list.id;
            option.textContent = list.name + ' (' + list.profile_count + ' profils)';
            listSelect.appendChild(option);
        });
    }
    
    /**
     * Ouvrir le modal d'ajout à une liste
     */
    window.openAddToListModal = function() {
        const selectedCount = document.querySelectorAll('.profile-checkbox:checked').length;
        
        if (selectedCount === 0) {
            showNotification('Veuillez sélectionner au moins un profil', 'warning');
            return;
        }
        
        // Mettre à jour le texte du modal
        const modalTitle = document.querySelector('#add-to-list-modal .modal-header h3');
        if (modalTitle) {
            modalTitle.textContent = `Ajouter ${selectedCount} profil(s) à une liste`;
        }
        
        // Charger les listes de l'utilisateur
        loadUserLists();
        
        // Afficher le modal
        openModal(addToListModal);
    };
    
    /**
     * Ouvrir un modal
     */
    function openModal(modal) {
        if (modal) {
            modal.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    }
    
    /**
     * Fermer un modal
     */
    function closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            
            // Réinitialiser les formulaires dans le modal
            const forms = modal.querySelectorAll('form');
            forms.forEach(form => resetForm(form));
            
            // Réinitialiser le toggle de création de liste
            if (createListToggle) {
                createListToggle.checked = false;
                if (existingListSection) existingListSection.style.display = 'block';
                if (newListSection) newListSection.style.display = 'none';
            }
        }
    }
    
    /**
     * Réinitialiser un formulaire
     */
    function resetForm(form) {
        if (form) {
            form.reset();
        }
    }
    
    /**
     * Afficher une notification
     */
    function showNotification(message, type = 'info') {
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
    }
});
