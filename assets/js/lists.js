/**
 * Script pour la gestion des listes
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM pour la modal de suppression
    const deleteModal = document.getElementById('delete-list-modal');
    const listNameToDelete = document.getElementById('list-name-to-delete');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const closeModalBtn = document.querySelector('.close-modal');
    
    // ID de la liste à supprimer
    let listIdToDelete = null;
    
    // Fonction pour ouvrir la modal de suppression
    function openDeleteModal(id, name) {
        listIdToDelete = id;
        listNameToDelete.textContent = name;
        deleteModal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Empêcher le défilement
    }
    
    // Fonction pour fermer la modal
    function closeDeleteModal() {
        deleteModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Réactiver le défilement
        listIdToDelete = null;
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
    
    // Écouteurs d'événements pour les boutons de suppression
    document.querySelectorAll('.delete-list').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            openDeleteModal(id, name);
        });
    });
    
    // Écouteurs d'événements pour la modal
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    }
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', deleteList);
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeDeleteModal);
    }
    
    // Fermer la modal en cliquant en dehors
    window.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });
    
    // Fermer la modal avec la touche Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && deleteModal.style.display === 'flex') {
            closeDeleteModal();
        }
    });
});
