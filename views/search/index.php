<?php
$currentPage = 'search';
$activeMenu = 'search';
?>

<link rel="stylesheet" href="assets/css/search_form.css">

<div class="search-container">
    <h1 class="page-title">Recherche de profils</h1>
    
    <div class="search-count">
        <?= $searchCount ?> / <?= $maxSearches ?? 30 ?> recherches
    </div>

    <form id="search-form" action="search.php" method="GET" class="search-form">
        <div class="form-group">
            <label for="name">Rechercher par nom</label>
            <div class="input-with-button">
                <input type="text" id="name" name="name_input" class="form-control" placeholder="Ajouter un mot-clé pour le nom...">
                <button type="button" class="btn btn-add btn-add-name">+ Ajouter</button>
            </div>
            <div id="name-tags" class="keyword-tags"></div>
            <input type="hidden" id="name_keywords" name="name_keywords" value="">
        </div>

        <div class="form-group">
            <label for="bio">Rechercher dans la bio (recherche combinée)</label>
            <div class="input-with-button">
                <input type="text" id="bio" name="bio_input" class="form-control" placeholder="Ajouter un mot-clé pour la bio...">
                <button type="button" class="btn btn-add btn-add-bio">+ Ajouter</button>
            </div>
            <div id="bio-tags" class="keyword-tags"></div>
            <input type="hidden" id="bio_keywords" name="bio_keywords" value="">
        </div>

        <div class="form-group">
            <label>Filtres de followers</label>
            <div class="followers-filter">
                <div class="filter-item">
                    <label for="min_followers">Min. followers</label>
                    <input type="number" id="min_followers" name="min_followers" class="form-control" placeholder="0" value="0">
                </div>
                <div class="filter-item">
                    <label for="max_followers">Max. followers</label>
                    <input type="text" id="max_followers" name="max_followers" class="form-control" placeholder="∞" value="∞">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary search-button">
            Lancer la recherche
        </button>
    </form>
</div>

<script src="assets/js/search_form.js"></script>
