<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<div class="help-container">
    <div class="help-header">
        <h1>Aide - Suivi des prospects</h1>
        <a href="followup.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour au suivi
        </a>
    </div>
    
    <div class="followup-tabs">
        <?php 
        $tabs = [
            ['label' => 'Tous les prospects', 'href' => 'followup.php', 'icon' => 'fas fa-users'],
            ['label' => 'Tableau de bord', 'href' => 'followup.php?action=dashboard', 'icon' => 'fas fa-chart-pie'],
            ['label' => 'Aide', 'href' => 'followup.php?action=help', 'icon' => 'fas fa-question-circle'],
        ];
        
        $currentUrl = $_SERVER['REQUEST_URI'];
        foreach ($tabs as $tab): 
            $isActive = strpos($currentUrl, $tab['href']) !== false;
            if ($tab['href'] === 'followup.php' && $currentUrl !== 'followup.php') {
                $isActive = false;
            }
        ?>
        <div class="tab <?= $isActive ? 'active' : '' ?>">
            <a href="<?= $tab['href'] ?>"><i class="<?= $tab['icon'] ?>"></i> <?= $tab['label'] ?></a>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="help-content">
        <section class="help-section">
            <h2>Introduction</h2>
            <p>Le module de suivi des prospects de LeadsBuilder vous permet de suivre efficacement vos interactions avec les prospects Instagram et de gérer votre processus de prospection.</p>
        </section>
        
        <section class="help-section">
            <h2>Ajout de prospects au suivi</h2>
            <p>Pour ajouter un prospect à votre suivi :</p>
            <ul>
                <li>Recherchez un profil dans la section "Recherche"</li>
                <li>Cliquez sur "Ajouter au suivi" dans la fiche du profil</li>
                <li>Sélectionnez la liste à laquelle vous souhaitez ajouter le profil</li>
            </ul>
            <div class="help-image">
                <img src="/assets/images/help/add-to-followup.jpg" alt="Ajouter au suivi" />
            </div>
        </section>
        
        <section class="help-section">
            <h2>Gestion des statuts</h2>
            <p>Chaque prospect peut avoir l'un des statuts suivants :</p>
            <ul>
                <li><span class="status-badge non-contacte">Non contacté</span> : Vous n'avez pas encore interagi avec ce prospect</li>
                <li><span class="status-badge contacte">Contacté</span> : Vous avez envoyé un premier message au prospect</li>
                <li><span class="status-badge interesse">Intéressé</span> : Le prospect a répondu positivement</li>
                <li><span class="status-badge pas-interesse">Pas intéressé</span> : Le prospect a répondu négativement</li>
                <li><span class="status-badge client">Client</span> : Le prospect est devenu client</li>
                <li><span class="status-badge relance">À relancer</span> : Le prospect nécessite une relance</li>
            </ul>
            <p>Pour modifier le statut d'un prospect :</p>
            <ol>
                <li>Cliquez sur le bouton de modification de statut (icône d'étiquette)</li>
                <li>Sélectionnez le nouveau statut dans la liste déroulante</li>
                <li>Cliquez sur "Enregistrer"</li>
            </ol>
        </section>
        
        <section class="help-section">
            <h2>Enregistrement des interactions</h2>
            <p>Vous pouvez enregistrer différents types d'interactions avec vos prospects :</p>
            <ul>
                <li><span class="action-badge 1er-message">1er message</span> : Premier contact avec le prospect</li>
                <li><span class="action-badge relance">Relance</span> : Message de suivi après un premier contact</li>
                <li><span class="action-badge appel">Appel</span> : Conversation téléphonique</li>
                <li><span class="action-badge reunion">Réunion</span> : Rencontre en personne ou virtuelle</li>
                <li><span class="action-badge email">Email</span> : Communication par email</li>
                <li><span class="action-badge autre">Autre</span> : Tout autre type d'interaction</li>
            </ul>
            <p>Pour ajouter une interaction :</p>
            <ol>
                <li>Cliquez sur le bouton d'ajout d'interaction (icône +)</li>
                <li>Sélectionnez le type d'interaction</li>
                <li>Ajoutez des notes si nécessaire</li>
                <li>Cliquez sur "Enregistrer"</li>
            </ol>
        </section>
        
        <section class="help-section">
            <h2>Visualisation des interactions</h2>
            <p>Pour voir toutes les interactions avec un prospect :</p>
            <ul>
                <li>Cliquez sur le bouton d'historique (icône d'horloge)</li>
                <li>Un calendrier des interactions s'affichera</li>
            </ul>
        </section>
        
        <section class="help-section">
            <h2>Exportation des données</h2>
            <p>Vous pouvez exporter vos données de suivi au format CSV :</p>
            <ul>
                <li>Pour exporter tous vos prospects suivis, cliquez sur "Exporter tous les prospects"</li>
                <li>Pour exporter les interactions d'un prospect spécifique, cliquez sur le bouton d'exportation (icône de téléchargement) à côté du prospect</li>
            </ul>
            <p>Les fichiers CSV peuvent être ouverts dans Excel, Google Sheets ou tout autre tableur.</p>
        </section>
        
        <section class="help-section">
            <h2>Conseils d'utilisation</h2>
            <ul>
                <li>Mettez à jour régulièrement le statut de vos prospects pour garder une vue d'ensemble claire</li>
                <li>Ajoutez des notes détaillées à chaque interaction pour vous souvenir des points importants</li>
                <li>Utilisez les filtres pour retrouver rapidement les prospects qui vous intéressent</li>
                <li>Exportez régulièrement vos données pour les sauvegarder</li>
            </ul>
        </section>
        
        <section class="help-section">
            <h2>Tableau de bord</h2>
            <p>Le tableau de bord vous offre une vue d'ensemble de votre activité de prospection :</p>
            <ul>
                <li><strong>Statistiques générales</strong> : Nombre total de prospects, interactions mensuelles, taux de conversion, etc.</li>
                <li><strong>Répartition par statut</strong> : Visualisation graphique de la distribution de vos prospects par statut</li>
                <li><strong>Activité récente</strong> : Graphique montrant votre activité de prospection sur les 7 derniers jours</li>
                <li><strong>Recommandations</strong> : Suggestions personnalisées pour améliorer votre prospection</li>
            </ul>
            <p>Pour accéder au tableau de bord, cliquez sur le bouton "Tableau de bord" dans l'en-tête de la page de suivi.</p>
        </section>
        
        <section class="help-section">
            <h2>Alertes et rappels</h2>
            <p>Le système d'alertes vous aide à ne pas oublier de suivre vos prospects :</p>
            <ul>
                <li><strong>Prospects à relancer</strong> : Liste des prospects qui n'ont pas eu d'interaction récente</li>
                <li><strong>Interactions planifiées</strong> : Rappels des interactions que vous avez programmées pour aujourd'hui</li>
            </ul>
            <p>Ces alertes sont visibles dans le tableau de bord.</p>
        </section>
        
        <section class="help-section">
            <h2>Planification d'interactions</h2>
            <p>Vous pouvez planifier des interactions futures avec vos prospects :</p>
            <ol>
                <li>Cliquez sur le bouton "+" pour ajouter une interaction</li>
                <li>Remplissez les détails de l'interaction</li>
                <li>Cochez la case "Planifier pour une date future"</li>
                <li>Sélectionnez la date à laquelle vous souhaitez effectuer cette interaction</li>
                <li>Cliquez sur "Enregistrer"</li>
            </ol>
            <p>L'interaction planifiée apparaîtra dans la section "Interactions planifiées aujourd'hui" le jour prévu.</p>
            <p>Une fois l'interaction effectuée, cliquez sur "Marquer comme terminée" pour la valider.</p>
        </section>
    </div>
</div>

<style>
.help-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.help-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background-color: #f3f4f6;
    color: #374151;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-back:hover {
    background-color: #e5e7eb;
}

.followup-tabs {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.tab {
    margin-right: 20px;
}

.tab a {
    display: inline-block;
    padding: 8px 16px;
    background-color: #f3f4f6;
    color: #374151;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s;
}

.tab a:hover {
    background-color: #e5e7eb;
}

.tab.active a {
    background-color: #1f2937;
    color: #fff;
}

.help-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e5e7eb;
}

.help-section:last-child {
    border-bottom: none;
}

.help-section h2 {
    margin-bottom: 20px;
    color: #1f2937;
}

.help-section ul, .help-section ol {
    margin-left: 20px;
    margin-bottom: 20px;
}

.help-section li {
    margin-bottom: 10px;
}

.help-image {
    margin: 20px 0;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.help-image img {
    max-width: 100%;
    display: block;
}

.status-badge, .action-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-badge.non-contacte {
    background-color: #f3f4f6;
    color: #374151;
}

.status-badge.contacte {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-badge.interesse {
    background-color: #d1fae5;
    color: #065f46;
}

.status-badge.pas-interesse {
    background-color: #fee2e2;
    color: #b91c1c;
}

.status-badge.client {
    background-color: #c7d2fe;
    color: #3730a3;
}

.status-badge.relance {
    background-color: #fef3c7;
    color: #92400e;
}
</style>
