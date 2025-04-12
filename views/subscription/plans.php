<div class="plans-container">
    <div class="plans-header">
        <h1>Choisissez votre plan</h1>
        <p>Sélectionnez le plan qui correspond le mieux à vos besoins</p>
    </div>
    
    <div class="plans-grid">
        <?php foreach ($plans as $planId => $plan): ?>
        <div class="plan-card <?= $planId === $_SESSION['user_subscription'] ? 'current-plan' : '' ?>">
            <div class="plan-header">
                <h2 class="plan-name"><?= $plan['name'] ?></h2>
                <div class="plan-price">
                    <span class="price"><?= $plan['price'] > 0 ? number_format($plan['price'], 2) . ' €' : 'Gratuit' ?></span>
                    <?php if ($plan['price'] > 0): ?>
                    <span class="period">/ mois</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="plan-features">
                <ul>
                    <li>
                        <i class="fas fa-check"></i>
                        <span><?= $plan['search_limit'] ?> recherches par jour</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span><?= $plan['list_limit'] ?> listes maximum</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span><?= $plan['profile_per_list_limit'] ?> profils par liste</span>
                    </li>
                    <?php if ($plan['features']['followup']): ?>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Suivi des prospects</span>
                    </li>
                    <?php else: ?>
                    <li class="feature-disabled">
                        <i class="fas fa-times"></i>
                        <span>Suivi des prospects</span>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($plan['features']['crm']): ?>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Tableau de bord CRM</span>
                    </li>
                    <?php else: ?>
                    <li class="feature-disabled">
                        <i class="fas fa-times"></i>
                        <span>Tableau de bord CRM</span>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($plan['features']['export']): ?>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Exportation des données</span>
                    </li>
                    <?php else: ?>
                    <li class="feature-disabled">
                        <i class="fas fa-times"></i>
                        <span>Exportation des données</span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="plan-footer">
                <?php if ($planId === $_SESSION['user_subscription']): ?>
                <button class="btn-current" disabled>Plan actuel</button>
                <?php elseif ($plan['price'] > 0): ?>
                <a href="checkout.php?plan=<?= $planId ?>" class="btn-subscribe">S'abonner</a>
                <?php else: ?>
                <a href="switch_to_free.php" class="btn-subscribe">Choisir ce plan</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($_SESSION['user_subscription'] !== 'free' && $this->subscriptionModel->hasActiveSubscription($_SESSION['user_id'])): ?>
    <div class="current-subscription">
        <p>Vous avez actuellement un abonnement actif. <a href="manage_subscription.php">Gérer mon abonnement</a></p>
    </div>
    <?php endif; ?>
</div>

<style>
    .plans-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .plans-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .plans-header h1 {
        font-size: 32px;
        color: #111827;
        margin-bottom: 10px;
    }
    
    .plans-header p {
        font-size: 16px;
        color: #6b7280;
    }
    
    .plans-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .plan-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    
    .plan-card.current-plan {
        border: 2px solid #4f46e5;
        position: relative;
    }
    
    .plan-card.current-plan::after {
        content: "Plan actuel";
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #4f46e5;
        color: white;
        font-size: 12px;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 4px;
    }
    
    .plan-header {
        padding: 24px;
        text-align: center;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .plan-name {
        font-size: 24px;
        color: #111827;
        margin-bottom: 10px;
    }
    
    .plan-price {
        font-size: 18px;
        color: #6b7280;
    }
    
    .price {
        font-size: 32px;
        font-weight: 700;
        color: #111827;
    }
    
    .period {
        font-size: 16px;
        color: #6b7280;
    }
    
    .plan-features {
        padding: 24px;
        flex-grow: 1;
    }
    
    .plan-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .plan-features li {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        color: #111827;
    }
    
    .plan-features li:last-child {
        margin-bottom: 0;
    }
    
    .plan-features li i {
        margin-right: 10px;
        color: #10b981;
    }
    
    .plan-features li.feature-disabled i {
        color: #ef4444;
    }
    
    .plan-features li.feature-disabled span {
        color: #9ca3af;
    }
    
    .plan-footer {
        padding: 24px;
        text-align: center;
        border-top: 1px solid #f3f4f6;
    }
    
    .btn-subscribe {
        display: inline-block;
        background-color: #4f46e5;
        color: white;
        font-size: 16px;
        font-weight: 500;
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        transition: background-color 0.2s ease;
        width: 100%;
        text-align: center;
    }
    
    .btn-subscribe:hover {
        background-color: #4338ca;
    }
    
    .btn-current {
        display: inline-block;
        background-color: #e5e7eb;
        color: #6b7280;
        font-size: 16px;
        font-weight: 500;
        padding: 12px 24px;
        border-radius: 6px;
        border: none;
        width: 100%;
        cursor: not-allowed;
    }
    
    .current-subscription {
        text-align: center;
        margin-top: 20px;
        padding: 16px;
        background-color: #f3f4f6;
        border-radius: 8px;
    }
    
    .current-subscription p {
        margin: 0;
        color: #4b5563;
    }
    
    .current-subscription a {
        color: #4f46e5;
        text-decoration: none;
        font-weight: 500;
    }
    
    .current-subscription a:hover {
        text-decoration: underline;
    }
    
    @media (max-width: 768px) {
        .plans-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
