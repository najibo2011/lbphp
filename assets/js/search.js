/**
 * Script pour la gestion de la recherche de profils
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const searchForm = document.getElementById('search-form');
    const nameSearchInput = document.getElementById('name-search');
    const bioSearchInput = document.getElementById('bio-search');
    const addNameKeywordBtn = document.getElementById('add-name-keyword');
    const addBioKeywordBtn = document.getElementById('add-bio-keyword');
    const nameKeywordsContainer = document.getElementById('name-keywords-container');
    const bioKeywordsContainer = document.getElementById('bio-keywords-container');
    const minFollowersInput = document.getElementById('min-followers');
    const maxFollowersInput = document.getElementById('max-followers');
    const searchResultsContainer = document.getElementById('search-results');
    
    // Tableaux pour stocker les mots-clés
    let nameKeywords = [];
    let bioKeywords = [];
    
    // Fonction pour ajouter un mot-clé au nom
    function addNameKeyword() {
        const keyword = nameSearchInput.value.trim();
        if (keyword && !nameKeywords.includes(keyword)) {
            nameKeywords.push(keyword);
            renderNameKeywords();
            nameSearchInput.value = '';
        }
        nameSearchInput.focus();
    }
    
    // Fonction pour ajouter un mot-clé à la bio
    function addBioKeyword() {
        const keyword = bioSearchInput.value.trim();
        if (keyword && !bioKeywords.includes(keyword)) {
            bioKeywords.push(keyword);
            renderBioKeywords();
            bioSearchInput.value = '';
        }
        bioSearchInput.focus();
    }
    
    // Fonction pour supprimer un mot-clé du nom
    function removeNameKeyword(keyword) {
        nameKeywords = nameKeywords.filter(k => k !== keyword);
        renderNameKeywords();
    }
    
    // Fonction pour supprimer un mot-clé de la bio
    function removeBioKeyword(keyword) {
        bioKeywords = bioKeywords.filter(k => k !== keyword);
        renderBioKeywords();
    }
    
    // Fonction pour afficher les mots-clés du nom
    function renderNameKeywords() {
        nameKeywordsContainer.innerHTML = '';
        nameKeywords.forEach(keyword => {
            const tag = document.createElement('div');
            tag.className = 'keyword-tag';
            tag.innerHTML = `
                ${keyword}
                <span class="remove-keyword" data-keyword="${keyword}">×</span>
            `;
            nameKeywordsContainer.appendChild(tag);
        });
        
        // Ajouter les écouteurs d'événements pour la suppression
        document.querySelectorAll('#name-keywords-container .remove-keyword').forEach(btn => {
            btn.addEventListener('click', function() {
                removeNameKeyword(this.dataset.keyword);
            });
        });
    }
    
    // Fonction pour afficher les mots-clés de la bio
    function renderBioKeywords() {
        bioKeywordsContainer.innerHTML = '';
        bioKeywords.forEach(keyword => {
            const tag = document.createElement('div');
            tag.className = 'keyword-tag';
            tag.innerHTML = `
                ${keyword}
                <span class="remove-keyword" data-keyword="${keyword}">×</span>
            `;
            bioKeywordsContainer.appendChild(tag);
        });
        
        // Ajouter les écouteurs d'événements pour la suppression
        document.querySelectorAll('#bio-keywords-container .remove-keyword').forEach(btn => {
            btn.addEventListener('click', function() {
                removeBioKeyword(this.dataset.keyword);
            });
        });
    }
    
    // Fonction pour effectuer la recherche
    function performSearch(e) {
        e.preventDefault();
        
        // Récupérer les valeurs des filtres
        const minFollowers = parseInt(minFollowersInput.value) || 0;
        const maxFollowers = maxFollowersInput.value === '∞' ? null : parseInt(maxFollowersInput.value);
        
        // Préparer les données pour l'envoi
        const formData = new FormData();
        // Utiliser une chaîne unique pour les mots-clés au lieu d'un tableau
        formData.append('name_keywords', nameKeywords.join(' '));
        formData.append('bio_keywords', bioKeywords.join(' '));
        formData.append('min_followers', minFollowers);
        formData.append('max_followers', maxFollowers !== null ? maxFollowers : '∞');
        
        // Afficher un indicateur de chargement
        searchResultsContainer.innerHTML = '<div class="loading">Recherche en cours...</div>';
        
        // Envoyer la requête AJAX
        fetch('search.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour le compteur de recherches
                const searchCounter = document.querySelector('.search-counter');
                if (searchCounter) {
                    searchCounter.textContent = `${data.searchCount} / ${data.maxSearches} recherches`;
                }
                
                // Afficher les résultats
                displayResults(data.results);
            } else {
                searchResultsContainer.innerHTML = `
                    <div class="error-message">
                        ${data.message || 'Une erreur est survenue lors de la recherche.'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            searchResultsContainer.innerHTML = `
                <div class="error-message">
                    Une erreur est survenue lors de la communication avec le serveur.
                </div>
            `;
        });
    }
    
    // Fonction pour afficher les résultats
    function displayResults(results) {
        if (results.length === 0) {
            searchResultsContainer.innerHTML = `
                <div class="no-results">
                    Aucun résultat trouvé pour votre recherche.
                </div>
            `;
            return;
        }
        
        let html = `<div class="results-count">${results.length} profils trouvés</div>`;
        
        results.forEach(profile => {
            // Adapter les noms de champs pour correspondre à la structure de Supabase
            const name = profile.full_name || profile.username || 'Sans nom';
            const followers = profile.followers || 0;
            const following = profile.following || 0;
            const avatar = profile.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=128`;
            
            html += `
                <div class="profile-card">
                    <img src="${avatar}" alt="${name}" class="profile-avatar">
                    <div class="profile-info">
                        <h3 class="profile-name">${name}</h3>
                        <p class="profile-bio">${profile.bio || 'Aucune bio disponible'}</p>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <i class="fas fa-users"></i> ${followers.toLocaleString()} followers
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-user-plus"></i> ${following.toLocaleString()} following
                            </div>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-add" data-profile-id="${profile.id}">
                            <i class="fas fa-plus"></i> Ajouter à la liste
                        </button>
                    </div>
                </div>
            `;
        });
        
        searchResultsContainer.innerHTML = html;
        
        // Ajouter les écouteurs d'événements pour les boutons d'action
        document.querySelectorAll('.profile-actions .btn-add').forEach(btn => {
            btn.addEventListener('click', function() {
                const profileId = this.dataset.profileId;
                addToList(profileId);
            });
        });
    }
    
    // Fonction pour ajouter un profil à une liste
    function addToList(profileId) {
        alert(`Fonctionnalité à venir: Ajouter le profil ${profileId} à une liste`);
    }
    
    // Écouteurs d'événements
    addNameKeywordBtn.addEventListener('click', addNameKeyword);
    addBioKeywordBtn.addEventListener('click', addBioKeyword);
    
    // Ajouter un mot-clé en appuyant sur Entrée
    nameSearchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addNameKeyword();
        }
    });
    
    bioSearchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addBioKeyword();
        }
    });
    
    // Soumettre le formulaire
    searchForm.addEventListener('submit', performSearch);
});
