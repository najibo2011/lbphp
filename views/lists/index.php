<?php
$currentPage = 'lists';
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="lists-container">
    <div class="lists-header">
        <h1 class="page-title">Mes Listes</h1>
        <a href="create_list.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Créer une liste
        </a>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Liste créée avec succès.
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> 
        <?php
        $error = $_GET['error'];
        switch ($error) {
            case 'name_required':
                echo 'Le nom de la liste est obligatoire.';
                break;
            default:
                echo 'Une erreur est survenue.';
        }
        ?>
    </div>
    <?php endif; ?>

    <div class="lists-grid">
        <?php if (empty($lists)): ?>
            <div class="empty-state">
                <i class="fas fa-list fa-3x"></i>
                <p>Vous n'avez pas encore créé de liste.</p>
                <a href="create_list.php" class="btn btn-primary">Créer ma première liste</a>
            </div>
        <?php else: ?>
            <?php foreach ($lists as $list): ?>
                <div class="list-card">
                    <div class="list-card-header">
                        <h3 class="list-name"><?= htmlspecialchars($list['name']) ?></h3>
                        <span class="list-visibility <?= $list['is_public'] ? 'public' : 'private' ?>">
                            <i class="fas <?= $list['is_public'] ? 'fa-globe' : 'fa-lock' ?>"></i>
                            <?= $list['is_public'] ? 'Public' : 'Privé' ?>
                        </span>
                    </div>
                    <div class="list-card-body">
                        <p class="list-description">
                            <?= !empty($list['description']) ? htmlspecialchars($list['description']) : 'Aucune description' ?>
                        </p>
                        <div class="list-stats">
                            <div class="stat-item">
                                <i class="fas fa-users"></i> <?= $list['profile_count'] ?> profils
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-calendar-alt"></i> Créée le <?= date('d/m/Y', strtotime($list['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <div class="list-card-footer">
                        <a href="view_list.php?id=<?= $list['id'] ?>" class="btn btn-outline">
                            <i class="fas fa-eye"></i> Voir
                        </a>
                        <a href="edit_list.php?id=<?= $list['id'] ?>" class="btn btn-outline">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <button class="btn btn-outline btn-danger delete-list" data-id="<?= $list['id'] ?>" data-name="<?= htmlspecialchars($list['name']) ?>">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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
