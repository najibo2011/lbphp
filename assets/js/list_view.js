/**
 * Script pour la gestion de la vue d'une liste de profils
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const profileSearch = document.getElementById('profile-search');
    const sortProfiles = document.getElementById('sort-profiles');
    const profileCards = document.querySelectorAll('.profile-card');
    const editNotesButtons = document.querySelectorAll('.edit-notes');
    const removeFromListButtons = document.querySelectorAll('.remove-from-list');
    const editNotesModal = document.getElementById('edit-notes-modal');
    const removeProfileModal = document.getElementById('remove-profile-modal');
    const editNotesForm = document.getElementById('edit-notes-form');
    const closeModalButtons = document.querySelectorAll('.close-modal');
    const confirmRemoveButton = document.getElementById('confirm-remove');
    
    // Variables globales
    let currentProfileId = null;
    
    // Initialisation
    initSearch();
    initSort();
    initNotes();
    initRemoveProfile();
    initModals();
    
    /**
     * Initialiser la recherche de profils
     */
    function initSearch() {
        if (profileSearch) {
            profileSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                profileCards.forEach(card => {
                    const name = card.querySelector('.profile-name').textContent.toLowerCase();
                    const username = card.querySelector('.profile-username').textContent.toLowerCase();
                    const bio = card.querySelector('.profile-bio').textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || username.includes(searchTerm) || bio.includes(searchTerm)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    }
    
    /**
     * Initialiser le tri des profils
     */
    function initSort() {
        if (sortProfiles) {
            sortProfiles.addEventListener('change', function() {
                const sortValue = this.value;
                const profilesContainer = document.querySelector('.profiles-grid');
                const profiles = Array.from(profileCards);
                
                profiles.sort((a, b) => {
                    switch (sortValue) {
                        case 'date-desc':
                            // Par défaut, les profils sont déjà triés par date d'ajout décroissante
                            return 0;
                        case 'date-asc':
                            return -1; // Inverser l'ordre
                        case 'name-asc':
                            const nameA = a.querySelector('.profile-name').textContent.toLowerCase();
                            const nameB = b.querySelector('.profile-name').textContent.toLowerCase();
                            return nameA.localeCompare(nameB);
                        case 'name-desc':
                            const nameC = a.querySelector('.profile-name').textContent.toLowerCase();
                            const nameD = b.querySelector('.profile-name').textContent.toLowerCase();
                            return nameD.localeCompare(nameC);
                        case 'followers-desc':
                            const followersA = parseInt(a.querySelector('.profile-stats span:first-child').textContent.replace(/[^0-9]/g, ''));
                            const followersB = parseInt(b.querySelector('.profile-stats span:first-child').textContent.replace(/[^0-9]/g, ''));
                            return followersB - followersA;
                        case 'followers-asc':
                            const followersC = parseInt(a.querySelector('.profile-stats span:first-child').textContent.replace(/[^0-9]/g, ''));
                            const followersD = parseInt(b.querySelector('.profile-stats span:first-child').textContent.replace(/[^0-9]/g, ''));
                            return followersC - followersD;
                        default:
                            return 0;
                    }
                });
                
                // Réorganiser les profils dans le DOM
                profiles.forEach(profile => {
                    profilesContainer.appendChild(profile);
                });
            });
        }
    }
    
    /**
     * Initialiser la gestion des notes
     */
    function initNotes() {
        // Ouvrir le modal d'édition des notes
        editNotesButtons.forEach(button => {
            button.addEventListener('click', function() {
                const profileId = this.dataset.profileId;
                const profileCard = document.querySelector(`.profile-card[data-profile-id="${profileId}"]`);
                const notesElement = profileCard.querySelector('.profile-notes p');
                
                // Récupérer les notes actuelles
                let currentNotes = '';
                if (notesElement) {
                    currentNotes = notesElement.textContent;
                }
                
                // Mettre à jour le modal
                document.getElementById('profile-id-notes').value = profileId;
                document.getElementById('profile-notes-edit').value = currentNotes;
                
                // Ouvrir le modal
                openModal(editNotesModal);
                
                // Stocker l'ID du profil courant
                currentProfileId = profileId;
            });
        });
        
        // Soumettre le formulaire d'édition des notes
        if (editNotesForm) {
            editNotesForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const profileId = document.getElementById('profile-id-notes').value;
                const notes = document.getElementById('profile-notes-edit').value;
                const listId = getListId();
                
                if (!profileId || !listId) {
                    showNotification('error', 'Erreur: données manquantes');
                    return;
                }
                
                // Envoyer les données au serveur
                const formData = new FormData();
                formData.append('profile_id', profileId);
                formData.append('list_id', listId);
                formData.append('notes', notes);
                
                fetch('/lb1/ajax/lists/update_notes.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mettre à jour l'affichage des notes
                        updateProfileNotes(profileId, notes);
                        
                        // Fermer le modal
                        closeModal(editNotesModal);
                        
                        // Afficher une notification
                        showNotification('success', 'Notes mises à jour avec succès');
                    } else {
                        showNotification('error', data.message || 'Erreur lors de la mise à jour des notes');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('error', 'Erreur de connexion au serveur');
                });
            });
        }
    }
    
    /**
     * Initialiser la suppression de profils de la liste
     */
    function initRemoveProfile() {
        // Ouvrir le modal de confirmation
        removeFromListButtons.forEach(button => {
            button.addEventListener('click', function() {
                const profileId = this.dataset.profileId;
                
                // Ouvrir le modal
                openModal(removeProfileModal);
                
                // Stocker l'ID du profil courant
                currentProfileId = profileId;
            });
        });
        
        // Confirmer la suppression
        if (confirmRemoveButton) {
            confirmRemoveButton.addEventListener('click', function() {
                if (!currentProfileId) {
                    closeModal(removeProfileModal);
                    return;
                }
                
                const listId = getListId();
                
                if (!listId) {
                    showNotification('error', 'Erreur: ID de liste manquant');
                    closeModal(removeProfileModal);
                    return;
                }
                
                // Envoyer la requête de suppression
                const formData = new FormData();
                formData.append('profile_id', currentProfileId);
                formData.append('list_id', listId);
                
                fetch('/lb1/ajax/lists/remove_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Supprimer la carte du profil du DOM
                        const profileCard = document.querySelector(`.profile-card[data-profile-id="${currentProfileId}"]`);
                        if (profileCard) {
                            profileCard.remove();
                        }
                        
                        // Mettre à jour le compteur de profils
                        updateProfileCount();
                        
                        // Fermer le modal
                        closeModal(removeProfileModal);
                        
                        // Afficher une notification
                        showNotification('success', 'Profil retiré de la liste avec succès');
                    } else {
                        showNotification('error', data.message || 'Erreur lors de la suppression du profil');
                        closeModal(removeProfileModal);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('error', 'Erreur de connexion au serveur');
                    closeModal(removeProfileModal);
                });
            });
        }
    }
    
    /**
     * Initialiser les modals
     */
    function initModals() {
        // Fermer les modals
        closeModalButtons.forEach(button => {
            button.addEventListener('click', function() {
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
            forms.forEach(form => {
                if (form) form.reset();
            });
            
            // Réinitialiser l'ID du profil courant
            currentProfileId = null;
        }
    }
    
    /**
     * Mettre à jour l'affichage des notes d'un profil
     */
    function updateProfileNotes(profileId, notes) {
        const profileCard = document.querySelector(`.profile-card[data-profile-id="${profileId}"]`);
        if (!profileCard) return;
        
        const profileBody = profileCard.querySelector('.profile-body');
        let notesElement = profileCard.querySelector('.profile-notes');
        
        if (notes.trim() === '') {
            // Supprimer l'élément des notes s'il existe
            if (notesElement) {
                notesElement.remove();
            }
        } else {
            if (notesElement) {
                // Mettre à jour les notes existantes
                notesElement.querySelector('p').textContent = notes;
            } else {
                // Créer un nouvel élément pour les notes
                notesElement = document.createElement('div');
                notesElement.className = 'profile-notes';
                notesElement.innerHTML = `
                    <h4>Notes:</h4>
                    <p>${notes}</p>
                `;
                profileBody.appendChild(notesElement);
            }
        }
    }
    
    /**
     * Mettre à jour le compteur de profils
     */
    function updateProfileCount() {
        const remainingProfiles = document.querySelectorAll('.profile-card').length;
        const statItem = document.querySelector('.stat-item:first-child');
        
        if (statItem) {
            statItem.innerHTML = `<i class="fas fa-users"></i> ${remainingProfiles} profils`;
        }
        
        // Afficher l'état vide si plus aucun profil
        if (remainingProfiles === 0) {
            const profilesGrid = document.querySelector('.profiles-grid');
            if (profilesGrid) {
                profilesGrid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-users fa-3x"></i>
                        <p>Cette liste ne contient aucun profil.</p>
                        <a href="search.php" class="btn btn-primary">Rechercher des profils</a>
                    </div>
                `;
            }
        }
    }
    
    /**
     * Récupérer l'ID de la liste depuis l'URL
     */
    function getListId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id');
    }
    
    /**
     * Afficher une notification
     */
    function showNotification(type, message) {
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
