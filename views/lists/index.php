<?php
$currentPage = 'lists';
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="lists-container">
    <div class="lists-header">
        <div class="search-bar">
            <input type="text" placeholder="Rechercher une liste..." id="list-search">
            <i class="fas fa-search search-icon"></i>
        </div>
        <button class="btn btn-create-list">
            <i class="fas fa-plus"></i> Créer une nouvelle liste
        </button>
    </div>

    <div class="lists-grid">
        <?php if (empty($lists)): ?>
            <div class="empty-state">
                <i class="fas fa-list fa-3x"></i>
                <p>Vous n'avez pas encore créé de liste.</p>
                <button class="btn btn-primary btn-create-list">Créer ma première liste</button>
            </div>
        <?php else: ?>
            <?php foreach ($lists as $list): ?>
                <div class="list-card">
                    <div class="list-card-header">
                        <div class="list-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h3 class="list-name"><?= htmlspecialchars($list['name']) ?></h3>
                        <div class="list-actions">
                            <a href="edit_list.php?id=<?= $list['id'] ?>" class="edit-icon"><i class="fas fa-pencil-alt"></i></a>
                            <a href="#" class="delete-icon" data-id="<?= $list['id'] ?>" data-name="<?= htmlspecialchars($list['name']) ?>"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                    
                    <div class="list-profiles">
                        <?php 
                        // Récupérer les profils de la liste (limités à 6 pour l'affichage)
                        $profiles = isset($list['profiles']) ? array_slice($list['profiles'], 0, 6) : [];
                        
                        // Afficher les avatars des profils
                        foreach ($profiles as $profile): 
                            $username = isset($profile['username']) ? $profile['username'] : '';
                            $initial = substr($username, 0, 1);
                            $avatarColors = ['#4a6cf7', '#f7734a', '#4af7a1', '#f74a6c', '#f7d54a'];
                            $colorIndex = crc32($username) % count($avatarColors);
                            $avatarColor = $avatarColors[$colorIndex];
                        ?>
                            <div class="profile-avatar" title="@<?= htmlspecialchars($username) ?>">
                                <div class="avatar" style="background-color: <?= $avatarColor ?>;">
                                    <?= strtoupper($initial) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php 
                        // Si moins de 6 profils, ajouter des espaces vides
                        for ($i = count($profiles); $i < 6; $i++): 
                        ?>
                            <div class="profile-avatar empty"></div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="list-card-footer">
                        <span class="profile-count"><?= $list['profile_count'] ?? 0 ?> profils</span>
                        <a href="view_list.php?id=<?= $list['id'] ?>" class="btn btn-add-to-campaign">
                            Ajouter à la campagne
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de création de liste -->
<div class="modal" id="create-list-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Créer une nouvelle liste</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="create-list-form" action="create_list.php" method="post">
                <div class="form-group">
                    <label for="list-name">Nom de la liste</label>
                    <input type="text" id="list-name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="list-description">Description (optionnelle)</label>
                    <textarea id="list-description" name="description" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="is_public" id="list-public">
                        <span class="checkmark"></span>
                        Liste publique
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Créer la liste</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal" id="delete-list-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmer la suppression</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer la liste "<span id="list-name-to-delete"></span>" ?</p>
            <p class="warning">Cette action est irréversible et supprimera tous les profils associés à cette liste.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancel-delete">Annuler</button>
            <button class="btn btn-danger" id="confirm-delete">Supprimer</button>
        </div>
    </div>
</div>

<script src="assets/js/lists.js"></script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
