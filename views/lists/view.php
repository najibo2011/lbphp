<?php
$currentPage = 'lists';
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="list-view-container">
    <div class="list-header">
        <div class="list-header-left">
            <a href="lists.php" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> Retour aux listes
            </a>
            <h1 class="page-title"><?= htmlspecialchars($list['name']) ?></h1>
            <span class="list-visibility <?= $list['is_public'] ? 'public' : 'private' ?>">
                <i class="fas <?= $list['is_public'] ? 'fa-globe' : 'fa-lock' ?>"></i>
                <?= $list['is_public'] ? 'Public' : 'Privé' ?>
            </span>
        </div>
        <div class="list-header-right">
            <a href="edit_list.php?id=<?= $list['id'] ?>" class="btn btn-outline">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <button class="btn btn-danger delete-list" data-id="<?= $list['id'] ?>" data-name="<?= htmlspecialchars($list['name']) ?>">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </div>
    </div>

    <div class="list-description">
        <?php if (!empty($list['description'])): ?>
            <p><?= nl2br(htmlspecialchars($list['description'])) ?></p>
        <?php else: ?>
            <p class="text-muted">Aucune description</p>
        <?php endif; ?>
    </div>

    <div class="list-stats">
        <div class="stat-item">
            <i class="fas fa-users"></i> <?= count($profiles) ?> profils
        </div>
        <div class="stat-item">
            <i class="fas fa-calendar-alt"></i> Créée le <?= date('d/m/Y', strtotime($list['created_at'])) ?>
        </div>
        <div class="stat-item">
            <i class="fas fa-clock"></i> Mise à jour le <?= date('d/m/Y', strtotime($list['updated_at'])) ?>
        </div>
    </div>

    <div class="list-actions">
        <div class="search-box">
            <input type="text" id="profile-search" placeholder="Rechercher un profil dans cette liste...">
            <i class="fas fa-search"></i>
        </div>
        <div class="list-filters">
            <select id="sort-profiles">
                <option value="date-desc">Date d'ajout (récent d'abord)</option>
                <option value="date-asc">Date d'ajout (ancien d'abord)</option>
                <option value="name-asc">Nom (A-Z)</option>
                <option value="name-desc">Nom (Z-A)</option>
                <option value="followers-desc">Followers (décroissant)</option>
                <option value="followers-asc">Followers (croissant)</option>
            </select>
        </div>
    </div>

    <?php if (empty($profiles)): ?>
        <div class="empty-state">
            <i class="fas fa-users fa-3x"></i>
            <p>Cette liste ne contient aucun profil.</p>
            <a href="search.php" class="btn btn-primary">Rechercher des profils</a>
        </div>
    <?php else: ?>
        <div class="profiles-grid">
            <?php foreach ($profiles as $profile): ?>
                <div class="profile-card" data-profile-id="<?= $profile['id'] ?>">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php if (!empty($profile['avatar_url'])): ?>
                                <img src="<?= htmlspecialchars($profile['avatar_url']) ?>" alt="<?= htmlspecialchars($profile['name']) ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?= strtoupper(substr($profile['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-info">
                            <h3 class="profile-name"><?= htmlspecialchars($profile['name']) ?></h3>
                            <div class="profile-username">@<?= htmlspecialchars($profile['username']) ?></div>
                            <div class="profile-stats">
                                <span><i class="fas fa-users"></i> <?= number_format($profile['followers_count']) ?></span>
                                <span><i class="fas fa-user-friends"></i> <?= number_format($profile['following_count']) ?></span>
                            </div>
                        </div>
                        <div class="profile-actions">
                            <button class="btn btn-sm btn-outline edit-notes" data-profile-id="<?= $profile['id'] ?>">
                                <i class="fas fa-edit"></i> Notes
                            </button>
                            <button class="btn btn-sm btn-danger remove-from-list" data-profile-id="<?= $profile['id'] ?>">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="profile-body">
                        <div class="profile-bio">
                            <?php if (!empty($profile['bio'])): ?>
                                <p><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
                            <?php else: ?>
                                <p class="text-muted">Aucune biographie</p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($profile['notes'])): ?>
                            <div class="profile-notes">
                                <h4>Notes:</h4>
                                <p><?= nl2br(htmlspecialchars($profile['notes'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-footer">
                        <a href="profile.php?id=<?= $profile['id'] ?>" class="btn btn-sm btn-outline" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Voir le profil
                        </a>
                        <a href="https://twitter.com/<?= htmlspecialchars($profile['username']) ?>" class="btn btn-sm btn-outline" target="_blank">
                            <i class="fab fa-twitter"></i> Voir sur Twitter
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal pour éditer les notes -->
<div id="edit-notes-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier les notes</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="edit-notes-form">
                <input type="hidden" id="profile-id-notes" value="">
                <div class="form-group">
                    <label for="profile-notes-edit">Notes sur ce profil</label>
                    <textarea id="profile-notes-edit" class="form-control" placeholder="Ajoutez vos notes sur ce profil..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal">Annuler</button>
            <button type="submit" form="edit-notes-form" class="btn btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal" id="remove-profile-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmer la suppression</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir retirer ce profil de la liste ?</p>
            <p>Cette action ne supprime pas le profil de la base de données, seulement de cette liste.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline close-modal">Annuler</button>
            <button class="btn btn-danger" id="confirm-remove">Supprimer</button>
        </div>
    </div>
</div>

<script src="assets/js/list_view.js"></script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
