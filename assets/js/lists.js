/**
 * Script pour la gestion des listes
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM pour la modal de suppression
    const deleteModal = document.getElementById('delete-list-modal');
    const createModal = document.getElementById('create-list-modal');
    const listNameToDelete = document.getElementById('list-name-to-delete');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const createListBtns = document.querySelectorAll('.btn-create-list');
    const searchInput = document.getElementById('list-search');
    
    // ID de la liste à supprimer
    let listIdToDelete = null;
    
    // Fonction pour ouvrir la modal de suppression
    function openDeleteModal(id, name) {
        listIdToDelete = id;
        listNameToDelete.textContent = name;
        deleteModal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Empêcher le défilement
    }
    
    // Fonction pour fermer les modals
    function closeModals() {
        deleteModal.style.display = 'none';
        if (createModal) createModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Réactiver le défilement
        listIdToDelete = null;
    }
    
    // Fonction pour ouvrir la modal de création de liste
    function openCreateModal() {
        if (createModal) {
            createModal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Empêcher le défilement
            // Focus sur le champ de nom
            setTimeout(() => {
                const nameInput = document.getElementById('list-name');
                if (nameInput) nameInput.focus();
            }, 100);
        }
    }
    
    // Fonction pour supprimer une liste
    function deleteList() {
        if (!listIdToDelete) return;
        
        // Créer un formulaire pour envoyer la requête
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete_list.php';
        form.style.display = 'none';
        
        // Ajouter l'ID de la liste
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'list_id';
        idInput.value = listIdToDelete;
        form.appendChild(idInput);
        
        // Ajouter le jeton CSRF (à implémenter)
        // const csrfInput = document.createElement('input');
        // csrfInput.type = 'hidden';
        // csrfInput.name = 'csrf_token';
        // csrfInput.value = 'TOKEN_ICI';
        // form.appendChild(csrfInput);
        
        // Ajouter le formulaire au document et le soumettre
        document.body.appendChild(form);
        form.submit();
    }
    
    // Fonction de recherche de listes
    function searchLists(query) {
        query = query.toLowerCase().trim();
        const listCards = document.querySelectorAll('.list-card');
        
        listCards.forEach(card => {
            const listName = card.querySelector('.list-name').textContent.toLowerCase();
            if (listName.includes(query) || query === '') {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Écouteurs d'événements pour les boutons de suppression
    document.querySelectorAll('.delete-icon').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const name = this.dataset.name;
            openDeleteModal(id, name);
        });
    });
    
    // Écouteurs d'événements pour les boutons de création de liste
    if (createListBtns.length > 0) {
        createListBtns.forEach(btn => {
            btn.addEventListener('click', openCreateModal);
        });
    }
    
    // Écouteur pour la recherche
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchLists(this.value);
        });
    }
    
    // Écouteurs d'événements pour la modal de suppression
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', closeModals);
    }
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', deleteList);
    }
    
    // Fermer les modals avec le bouton de fermeture
    if (closeModalBtns.length > 0) {
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', closeModals);
        });
    }
    
    // Fermer les modals en cliquant en dehors
    window.addEventListener('click', function(event) {
        if (event.target === deleteModal || event.target === createModal) {
            closeModals();
        }
    });
    
    // Fermer les modals avec la touche Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && (deleteModal.style.display === 'flex' || (createModal && createModal.style.display === 'flex'))) {
            closeModals();
        }
    });
    
    // Gestion du formulaire de création de liste
    const createListForm = document.getElementById('create-list-form');
    if (createListForm) {
        createListForm.addEventListener('submit', function(e) {
            const nameInput = document.getElementById('list-name');
            if (!nameInput || !nameInput.value.trim()) {
                e.preventDefault();
                alert('Le nom de la liste est obligatoire.');
            }
        });
    }
});
