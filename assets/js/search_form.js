/**
 * Script pour gérer le formulaire de recherche
 */
document.addEventListener('DOMContentLoaded', function() {
    // Stockage des mots-clés
    const keywords = {
        name: [],
        bio: []
    };

    // Éléments du DOM
    const nameInput = document.getElementById('name');
    const bioInput = document.getElementById('bio');
    const nameTagsContainer = document.getElementById('name-tags');
    const bioTagsContainer = document.getElementById('bio-tags');
    const addNameBtn = document.querySelector('.btn-add-name');
    const addBioBtn = document.querySelector('.btn-add-bio');
    const searchForm = document.getElementById('search-form');

    // Ajouter un mot-clé pour le nom
    if (addNameBtn) {
        addNameBtn.addEventListener('click', function() {
            addKeyword('name', nameInput, nameTagsContainer);
        });
    }

    // Ajouter un mot-clé pour la bio
    if (addBioBtn) {
        addBioBtn.addEventListener('click', function() {
            addKeyword('bio', bioInput, bioTagsContainer);
        });
    }

    // Gérer la soumission du formulaire
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Ajouter les mots-clés aux champs cachés
            const nameKeywordsInput = document.getElementById('name_keywords');
            const bioKeywordsInput = document.getElementById('bio_keywords');
            
            if (nameKeywordsInput) {
                nameKeywordsInput.value = keywords.name.join(',');
            }
            
            if (bioKeywordsInput) {
                bioKeywordsInput.value = keywords.bio.join(',');
            }
            
            // Soumettre le formulaire
            this.submit();
        });
    }

    // Fonction pour ajouter un mot-clé
    function addKeyword(type, input, container) {
        const value = input.value.trim();
        
        if (value && !keywords[type].includes(value)) {
            // Ajouter le mot-clé au tableau
            keywords[type].push(value);
            
            // Créer un tag visuel
            const tag = document.createElement('div');
            tag.className = 'keyword-tag';
            tag.innerHTML = `
                <span>${value}</span>
                <span class="remove-tag" data-type="${type}" data-value="${value}">&times;</span>
            `;
            
            // Ajouter le tag au conteneur
            container.appendChild(tag);
            
            // Vider l'input
            input.value = '';
            
            // Ajouter un écouteur d'événement pour supprimer le tag
            const removeBtn = tag.querySelector('.remove-tag');
            removeBtn.addEventListener('click', function() {
                const type = this.dataset.type;
                const value = this.dataset.value;
                removeKeyword(type, value, this.parentNode);
            });
        }
    }

    // Fonction pour supprimer un mot-clé
    function removeKeyword(type, value, tagElement) {
        // Supprimer du tableau
        const index = keywords[type].indexOf(value);
        if (index !== -1) {
            keywords[type].splice(index, 1);
        }
        
        // Supprimer le tag visuel
        tagElement.remove();
    }

    // Gérer la touche Entrée dans les champs de saisie
    if (nameInput) {
        nameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addKeyword('name', nameInput, nameTagsContainer);
            }
        });
    }

    if (bioInput) {
        bioInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addKeyword('bio', bioInput, bioTagsContainer);
            }
        });
    }
});
