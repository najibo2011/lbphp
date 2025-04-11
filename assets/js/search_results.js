/**
 * Script pour la gestion des résultats de recherche
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const profileCards = document.querySelectorAll('.profile-card');
    const selectionBtn = document.querySelector('.btn-selection');
    const addToListBtns = document.querySelectorAll('.btn-add-to-list');
    const listModal = document.getElementById('list-modal');
    const closeModalBtn = document.querySelector('.close');
    const createListForm = document.getElementById('create-list-form');
    
    // Variables globales
    let selectedProfiles = [];
    let currentProfileId = null;
    
    // Initialisation
    initProfileSelection();
    initAddToListButtons();
    initModal();
    
    /**
     * Initialise la sélection des profils
     */
    function initProfileSelection() {
        // Ajouter un événement de clic sur les cartes de profil
        profileCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Ne pas déclencher si on clique sur un bouton ou un lien
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('button')) return;
                
                // Toggle la classe selected
                this.classList.toggle('selected');
                
                // Récupérer l'ID du profil
                const profileUsername = this.querySelector('.profile-username').textContent.trim();
                const profileId = profileUsername.substring(1); // Enlever le @ du début
                
                // Ajouter ou retirer de la liste des profils sélectionnés
                if (this.classList.contains('selected')) {
                    if (!selectedProfiles.includes(profileId)) {
                        selectedProfiles.push(profileId);
                    }
                } else {
                    selectedProfiles = selectedProfiles.filter(id => id !== profileId);
                }
                
                // Mettre à jour le bouton de sélection
                updateSelectionButton();
            });
        });
        
        // Bouton de sélection
        if (selectionBtn) {
            selectionBtn.addEventListener('click', function() {
                if (selectedProfiles.length > 0) {
                    openAddToListModal(selectedProfiles);
                } else {
                    showNotification('warning', 'Veuillez sélectionner au moins un profil');
                }
            });
        }
    }
    
    /**
     * Initialise les boutons d'ajout à une liste
     */
    function initAddToListButtons() {
        addToListBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // Empêcher la propagation au parent (carte)
                
                const profileId = this.dataset.profileId;
                currentProfileId = profileId;
                openAddToListModal([profileId]);
            });
        });
    }
    
    /**
     * Met à jour le texte du bouton de sélection
     */
    function updateSelectionButton() {
        if (selectionBtn) {
            if (selectedProfiles.length > 0) {
                selectionBtn.textContent = `${selectedProfiles.length} profil(s) sélectionné(s)`;
                selectionBtn.classList.add('active');
            } else {
                selectionBtn.textContent = 'Sélectionner des profils';
                selectionBtn.classList.remove('active');
            }
        }
    }
    
    /**
     * Initialise le modal d'ajout à une liste
     */
    function initModal() {
        // Fermer le modal
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', function() {
                listModal.style.display = 'none';
            });
        }
        
        // Cliquer en dehors du modal pour le fermer
        window.addEventListener('click', function(event) {
            if (event.target === listModal) {
                listModal.style.display = 'none';
            }
        });
        
        // Soumission du formulaire de création de liste
        if (createListForm) {
            createListForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const listName = document.getElementById('list-name').value;
                
                // Appel AJAX pour créer une liste
                fetch('api/lists.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'create',
                        name: listName,
                        profiles: currentProfileId ? [currentProfileId] : selectedProfiles
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', 'Liste créée avec succès');
                        listModal.style.display = 'none';
                        
                        // Réinitialiser la sélection
                        selectedProfiles = [];
                        currentProfileId = null;
                        profileCards.forEach(card => card.classList.remove('selected'));
                        updateSelectionButton();
                    } else {
                        showNotification('error', data.message || 'Erreur lors de la création de la liste');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('error', 'Une erreur est survenue');
                });
            });
        }
    }
    
    /**
     * Ouvre le modal d'ajout à une liste
     */
    function openAddToListModal(profileIds = null) {
        if (!listModal) return;
        
        // Si des IDs de profils sont fournis, les utiliser, sinon utiliser les profils sélectionnés
        const profilesToAdd = profileIds || selectedProfiles;
        
        if (profilesToAdd.length === 0) {
            showNotification('warning', 'Veuillez sélectionner au moins un profil');
            return;
        }
        
        // Charger les listes de l'utilisateur
        loadUserLists();
        
        // Afficher le modal
        listModal.style.display = 'block';
    }
    
    /**
     * Charge les listes de l'utilisateur
     */
    function loadUserLists() {
        const listsContainer = document.querySelector('.lists-list');
        if (!listsContainer) return;
        
        listsContainer.innerHTML = '<li class="loading">Chargement des listes...</li>';
        
        // Appel AJAX pour récupérer les listes
        fetch('api/lists.php?action=get_lists')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.lists) {
                    updateListsContainer(data.lists);
                } else {
                    listsContainer.innerHTML = '<li class="error">Erreur lors du chargement des listes</li>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                listsContainer.innerHTML = '<li class="error">Erreur lors du chargement des listes</li>';
            });
    }
    
    /**
     * Met à jour le conteneur des listes
     */
    function updateListsContainer(lists) {
        const listsContainer = document.querySelector('.lists-list');
        if (!listsContainer) return;
        
        if (lists.length === 0) {
            listsContainer.innerHTML = '<li class="empty">Aucune liste trouvée</li>';
            return;
        }
        
        let html = '';
        lists.forEach(list => {
            html += `<li class="list-item" data-list-id="${list.id}">${list.name} (${list.profiles_count || 0} profils)</li>`;
        });
        
        listsContainer.innerHTML = html;
        
        // Ajouter des événements de clic sur les éléments de liste
        document.querySelectorAll('.list-item').forEach(item => {
            item.addEventListener('click', function() {
                const listId = this.dataset.listId;
                addProfilesToList(listId);
            });
        });
    }
    
    /**
     * Ajoute les profils sélectionnés à une liste
     */
    function addProfilesToList(listId) {
        // Appel AJAX pour ajouter les profils à la liste
        fetch('api/lists.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'add_profiles',
                list_id: listId,
                profiles: currentProfileId ? [currentProfileId] : selectedProfiles
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Profils ajoutés à la liste avec succès');
                listModal.style.display = 'none';
                
                // Réinitialiser la sélection
                selectedProfiles = [];
                currentProfileId = null;
                profileCards.forEach(card => card.classList.remove('selected'));
                updateSelectionButton();
            } else {
                showNotification('error', data.message || 'Erreur lors de l\'ajout des profils à la liste');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('error', 'Une erreur est survenue');
        });
    }
    
    /**
     * Affiche une notification
     */
    function showNotification(type, message) {
        // Créer l'élément de notification s'il n'existe pas
        let notification = document.getElementById('notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'notification';
            document.body.appendChild(notification);
            
            // Styles CSS pour la notification
            notification.style.position = 'fixed';
            notification.style.bottom = '20px';
            notification.style.right = '20px';
            notification.style.padding = '15px 20px';
            notification.style.borderRadius = '8px';
            notification.style.color = '#fff';
            notification.style.fontWeight = '500';
            notification.style.zIndex = '9999';
            notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            notification.style.transition = 'all 0.3s ease';
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(20px)';
        }
        
        // Définir le type de notification
        switch (type) {
            case 'success':
                notification.style.backgroundColor = '#28a745';
                break;
            case 'error':
                notification.style.backgroundColor = '#dc3545';
                break;
            case 'warning':
                notification.style.backgroundColor = '#ffc107';
                notification.style.color = '#333';
                break;
            default:
                notification.style.backgroundColor = '#4f46e5';
        }
        
        // Définir le message
        notification.textContent = message;
        
        // Afficher la notification
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 10);
        
        // Masquer la notification après 3 secondes
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(20px)';
        }, 3000);
    }
});
