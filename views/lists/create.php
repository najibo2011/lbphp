<?php
$currentPage = 'lists';
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="create-list-container">
    <div class="page-header">
        <h1 class="page-title">Créer une nouvelle liste</h1>
        <a href="lists.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Retour aux listes
        </a>
    </div>

    <?php if ($dbInstance->isSupabase()): ?>
    <div class="alert alert-info">
        <p>Vous utilisez actuellement Supabase comme base de données. Certaines fonctionnalités peuvent être limitées par rapport à MySQL.</p>
    </div>
    <?php endif; ?>

    <div class="form-container">
        <form action="create_list.php" method="POST" class="list-form">
            <div class="form-group">
                <label for="name">Nom de la liste <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <?php if (!$dbInstance->isSupabase()): ?>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                <small class="form-text">Une description claire aidera à comprendre l'objectif de cette liste.</small>
            </div>
            <?php endif; ?>

            <?php if (!$dbInstance->isSupabase()): ?>
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_public" name="is_public" class="form-checkbox">
                    <label for="is_public">Liste publique</label>
                </div>
                <small class="form-text">Les listes publiques peuvent être partagées avec d'autres utilisateurs.</small>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Créer la liste
                </button>
                <a href="lists.php" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
