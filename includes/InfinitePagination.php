<?php
/**
 * Classe pour la gestion de la pagination infinie
 */
class InfinitePagination {
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $totalPages;
    
    /**
     * Constructeur
     */
    public function __construct($totalItems, $itemsPerPage = 20, $currentPage = 1) {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = ceil($totalItems / $itemsPerPage);
    }
    
    /**
     * Obtenir le nombre d'éléments à sauter
     */
    public function getOffset() {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    /**
     * Obtenir le nombre d'éléments par page
     */
    public function getLimit() {
        return $this->itemsPerPage;
    }
    
    /**
     * Obtenir la page actuelle
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    /**
     * Obtenir le nombre total de pages
     */
    public function getTotalPages() {
        return $this->totalPages;
    }
    
    /**
     * Vérifier s'il y a une page suivante
     */
    public function hasNextPage() {
        return $this->currentPage < $this->totalPages;
    }
    
    /**
     * Obtenir le numéro de la page suivante
     */
    public function getNextPage() {
        return $this->hasNextPage() ? $this->currentPage + 1 : $this->currentPage;
    }
    
    /**
     * Vérifier s'il y a une page précédente
     */
    public function hasPreviousPage() {
        return $this->currentPage > 1;
    }
    
    /**
     * Obtenir le numéro de la page précédente
     */
    public function getPreviousPage() {
        return $this->hasPreviousPage() ? $this->currentPage - 1 : 1;
    }
    
    /**
     * Obtenir les informations de pagination pour l'API
     */
    public function getPaginationInfo() {
        return [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'items_per_page' => $this->itemsPerPage,
            'total_items' => $this->totalItems,
            'has_next_page' => $this->hasNextPage(),
            'has_previous_page' => $this->hasPreviousPage()
        ];
    }
    
    /**
     * Générer le HTML pour la pagination infinie
     */
    public function renderInfiniteScroll($containerId, $loadMoreButtonId, $loadingId, $endMessageId, $apiEndpoint) {
        $html = '
        <div id="' . $loadingId . '" class="loading-indicator" style="display: none;">
            <div class="spinner"></div>
            <p>Chargement en cours...</p>
        </div>
        
        <div id="' . $endMessageId . '" class="end-message" style="display: none;">
            <p>Vous avez atteint la fin des résultats</p>
        </div>
        
        <button id="' . $loadMoreButtonId . '" class="btn-load-more">
            Charger plus
        </button>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const container = document.getElementById("' . $containerId . '");
                const loadMoreButton = document.getElementById("' . $loadMoreButtonId . '");
                const loadingIndicator = document.getElementById("' . $loadingId . '");
                const endMessage = document.getElementById("' . $endMessageId . '");
                
                let currentPage = ' . $this->currentPage . ';
                let totalPages = ' . $this->totalPages . ';
                let isLoading = false;
                
                // Masquer le bouton s\'il n\'y a pas de page suivante
                if (currentPage >= totalPages) {
                    loadMoreButton.style.display = "none";
                    endMessage.style.display = "block";
                }
                
                // Fonction pour charger plus de résultats
                function loadMoreResults() {
                    if (isLoading || currentPage >= totalPages) {
                        return;
                    }
                    
                    isLoading = true;
                    loadMoreButton.style.display = "none";
                    loadingIndicator.style.display = "block";
                    
                    // Incrémenter la page
                    currentPage++;
                    
                    // Effectuer la requête AJAX
                    fetch("' . $apiEndpoint . '?page=" + currentPage)
                        .then(response => response.json())
                        .then(data => {
                            // Ajouter les nouveaux éléments au conteneur
                            container.insertAdjacentHTML("beforeend", data.html);
                            
                            // Mettre à jour les informations de pagination
                            totalPages = data.pagination.total_pages;
                            
                            // Masquer l\'indicateur de chargement
                            loadingIndicator.style.display = "none";
                            
                            // Afficher le bouton ou le message de fin
                            if (currentPage >= totalPages) {
                                endMessage.style.display = "block";
                            } else {
                                loadMoreButton.style.display = "block";
                            }
                            
                            isLoading = false;
                        })
                        .catch(error => {
                            console.error("Erreur lors du chargement des résultats :", error);
                            loadingIndicator.style.display = "none";
                            loadMoreButton.style.display = "block";
                            isLoading = false;
                        });
                }
                
                // Écouter le clic sur le bouton "Charger plus"
                loadMoreButton.addEventListener("click", loadMoreResults);
                
                // Activer le chargement automatique au défilement (optionnel)
                const enableAutoLoad = true;
                
                if (enableAutoLoad) {
                    window.addEventListener("scroll", function() {
                        const scrollPosition = window.innerHeight + window.scrollY;
                        const bodyHeight = document.body.offsetHeight;
                        
                        // Charger plus de résultats lorsque l\'utilisateur atteint le bas de la page
                        if (scrollPosition >= bodyHeight - 500 && !isLoading && currentPage < totalPages) {
                            loadMoreResults();
                        }
                    });
                }
            });
        </script>
        
        <style>
            .loading-indicator {
                text-align: center;
                padding: 20px;
            }
            
            .spinner {
                display: inline-block;
                width: 40px;
                height: 40px;
                border: 4px solid rgba(0, 0, 0, 0.1);
                border-radius: 50%;
                border-top-color: #4f46e5;
                animation: spin 1s ease-in-out infinite;
                margin-bottom: 10px;
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            
            .end-message {
                text-align: center;
                padding: 20px;
                color: #6b7280;
                font-style: italic;
            }
            
            .btn-load-more {
                display: block;
                margin: 20px auto;
                padding: 10px 20px;
                background-color: #4f46e5;
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: background-color 0.2s ease;
            }
            
            .btn-load-more:hover {
                background-color: #4338ca;
            }
        </style>
        ';
        
        return $html;
    }
}
