<div class="manage-container">
    <div class="manage-card">
        <div class="manage-header">
            <h1>Gérer mon abonnement</h1>
            <p>Plan actuel : <strong><?= $plan['name'] ?></strong></p>
        </div>
        
        <div class="subscription-details">
            <h2>Détails de l'abonnement</h2>
            
            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Statut</span>
                    <span class="detail-value status-badge <?= $subscription['status'] ?>">
                        <?php
                        $statusLabels = [
                            'active' => 'Actif',
                            'canceled' => 'Annulé',
                            'incomplete' => 'Incomplet',
                            'incomplete_expired' => 'Expiré',
                            'past_due' => 'En retard',
                            'trialing' => 'Période d\'essai',
                            'unpaid' => 'Impayé'
                        ];
                        echo isset($statusLabels[$subscription['status']]) ? $statusLabels[$subscription['status']] : $subscription['status'];
                        ?>
                    </span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Prix mensuel</span>
                    <span class="detail-value"><?= number_format($plan['price'], 2) ?> €</span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Prochaine facturation</span>
                    <span class="detail-value"><?= date('d/m/Y', strtotime($subscription['current_period_end'])) ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Date d'abonnement</span>
                    <span class="detail-value"><?= date('d/m/Y', strtotime($subscription['created_at'])) ?></span>
                </div>
            </div>
            
            <div class="plan-features">
                <h3>Fonctionnalités incluses</h3>
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
                    <?php endif; ?>
                    
                    <?php if ($plan['features']['crm']): ?>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Tableau de bord CRM</span>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($plan['features']['export']): ?>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Exportation des données</span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="payment-method">
            <h2>Méthode de paiement</h2>
            
            <?php if (isset($stripeSubscription->default_payment_method) && $stripeSubscription->default_payment_method): ?>
                <?php
                $paymentMethod = \Stripe\PaymentMethod::retrieve($stripeSubscription->default_payment_method);
                $card = $paymentMethod->card;
                ?>
                <div class="card-details">
                    <div class="card-icon">
                        <i class="fab fa-cc-<?= strtolower($card->brand) ?>"></i>
                    </div>
                    <div class="card-info">
                        <div class="card-number">**** **** **** <?= $card->last4 ?></div>
                        <div class="card-expiry">Expire <?= sprintf('%02d/%d', $card->exp_month, $card->exp_year % 100) ?></div>
                    </div>
                </div>
                
                <div class="payment-actions">
                    <a href="update_payment_method.php" class="btn-update">Mettre à jour la méthode de paiement</a>
                </div>
            <?php else: ?>
                <p>Aucune méthode de paiement enregistrée.</p>
                <div class="payment-actions">
                    <a href="update_payment_method.php" class="btn-update">Ajouter une méthode de paiement</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="billing-history">
            <h2>Historique de facturation</h2>
            
            <?php
            // Récupérer les factures depuis Stripe
            $invoices = \Stripe\Invoice::all([
                'customer' => $subscription['stripe_customer_id'],
                'limit' => 5
            ]);
            ?>
            
            <?php if (count($invoices->data) > 0): ?>
                <div class="invoices-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices->data as $invoice): ?>
                                <tr>
                                    <td><?= date('d/m/Y', $invoice->created) ?></td>
                                    <td><?= number_format($invoice->amount_paid / 100, 2) ?> €</td>
                                    <td>
                                        <span class="invoice-status <?= $invoice->status ?>">
                                            <?php
                                            $invoiceStatusLabels = [
                                                'paid' => 'Payée',
                                                'open' => 'En attente',
                                                'void' => 'Annulée',
                                                'draft' => 'Brouillon',
                                                'uncollectible' => 'Non recouvrable'
                                            ];
                                            echo isset($invoiceStatusLabels[$invoice->status]) ? $invoiceStatusLabels[$invoice->status] : $invoice->status;
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($invoice->hosted_invoice_url): ?>
                                            <a href="<?= $invoice->hosted_invoice_url ?>" target="_blank" class="btn-invoice">Voir</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($invoices->data) >= 5): ?>
                    <div class="view-all">
                        <a href="billing_history.php" class="btn-view-all">Voir tout l'historique</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>Aucune facture disponible.</p>
            <?php endif; ?>
        </div>
        
        <div class="subscription-actions">
            <h2>Actions</h2>
            
            <div class="actions-grid">
                <a href="plans.php" class="btn-action btn-change-plan">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Changer de plan</span>
                </a>
                
                <?php if ($subscription['status'] === 'active'): ?>
                    <button class="btn-action btn-cancel" data-toggle="modal" data-target="#cancelModal">
                        <i class="fas fa-times-circle"></i>
                        <span>Annuler l'abonnement</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation d'annulation -->
<div class="modal" id="cancelModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmer l'annulation</h2>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir annuler votre abonnement ?</p>
            <p>Votre abonnement restera actif jusqu'à la fin de la période en cours (<?= date('d/m/Y', strtotime($subscription['current_period_end'])) ?>), après quoi vous serez automatiquement rétrogradé vers le plan gratuit.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary close-modal">Annuler</button>
            <a href="cancel_subscription.php" class="btn-danger">Confirmer l'annulation</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de la modal
        const modal = document.getElementById('cancelModal');
        const openModalBtn = document.querySelector('.btn-cancel');
        const closeModalBtns = document.querySelectorAll('.close-modal');
        
        if (openModalBtn) {
            openModalBtn.addEventListener('click', function() {
                modal.style.display = 'flex';
            });
        }
        
        closeModalBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        // Fermer la modal en cliquant en dehors
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>

<style>
    .manage-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .manage-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    
    .manage-header {
        padding: 30px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .manage-header h1 {
        font-size: 24px;
        color: #111827;
        margin-top: 0;
        margin-bottom: 10px;
    }
    
    .manage-header p {
        font-size: 16px;
        color: #6b7280;
        margin: 0;
    }
    
    .subscription-details,
    .payment-method,
    .billing-history,
    .subscription-actions {
        padding: 30px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .subscription-details h2,
    .payment-method h2,
    .billing-history h2,
    .subscription-actions h2 {
        font-size: 18px;
        color: #111827;
        margin-top: 0;
        margin-bottom: 20px;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
    }
    
    .detail-label {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-size: 16px;
        color: #111827;
        font-weight: 500;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        text-align: center;
    }
    
    .status-badge.active {
        background-color: #d1fae5;
        color: #10b981;
    }
    
    .status-badge.canceled {
        background-color: #fee2e2;
        color: #ef4444;
    }
    
    .status-badge.past_due,
    .status-badge.incomplete,
    .status-badge.unpaid {
        background-color: #fef3c7;
        color: #f59e0b;
    }
    
    .status-badge.trialing {
        background-color: #e0f2fe;
        color: #0ea5e9;
    }
    
    .plan-features {
        margin-top: 20px;
    }
    
    .plan-features h3 {
        font-size: 16px;
        color: #111827;
        margin-top: 0;
        margin-bottom: 15px;
    }
    
    .plan-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .plan-features li {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        color: #111827;
    }
    
    .plan-features li:last-child {
        margin-bottom: 0;
    }
    
    .plan-features li i {
        margin-right: 10px;
        color: #10b981;
    }
    
    .card-details {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .card-icon {
        font-size: 24px;
        margin-right: 15px;
    }
    
    .card-info {
        font-size: 16px;
    }
    
    .card-number {
        font-weight: 500;
        margin-bottom: 5px;
    }
    
    .card-expiry {
        color: #6b7280;
        font-size: 14px;
    }
    
    .payment-actions {
        margin-top: 20px;
    }
    
    .btn-update {
        display: inline-block;
        background-color: #4f46e5;
        color: white;
        font-size: 14px;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }
    
    .btn-update:hover {
        background-color: #4338ca;
    }
    
    .invoices-table {
        width: 100%;
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th {
        text-align: left;
        padding: 12px;
        background-color: #f9fafb;
        color: #6b7280;
        font-weight: 500;
        font-size: 14px;
    }
    
    td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
    }
    
    tr:last-child td {
        border-bottom: none;
    }
    
    .invoice-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .invoice-status.paid {
        background-color: #d1fae5;
        color: #10b981;
    }
    
    .invoice-status.open {
        background-color: #e0f2fe;
        color: #0ea5e9;
    }
    
    .invoice-status.void,
    .invoice-status.uncollectible {
        background-color: #fee2e2;
        color: #ef4444;
    }
    
    .btn-invoice {
        display: inline-block;
        background-color: #f3f4f6;
        color: #4b5563;
        font-size: 12px;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }
    
    .btn-invoice:hover {
        background-color: #e5e7eb;
    }
    
    .view-all {
        margin-top: 20px;
        text-align: center;
    }
    
    .btn-view-all {
        display: inline-block;
        background-color: #f3f4f6;
        color: #4b5563;
        font-size: 14px;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }
    
    .btn-view-all:hover {
        background-color: #e5e7eb;
    }
    
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .btn-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        font-family: inherit;
    }
    
    .btn-action i {
        font-size: 24px;
        margin-bottom: 10px;
    }
    
    .btn-action span {
        font-size: 14px;
        font-weight: 500;
    }
    
    .btn-change-plan {
        background-color: #e0f2fe;
        color: #0ea5e9;
    }
    
    .btn-change-plan:hover {
        background-color: #bae6fd;
    }
    
    .btn-cancel {
        background-color: #fee2e2;
        color: #ef4444;
    }
    
    .btn-cancel:hover {
        background-color: #fecaca;
    }
    
    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    
    .modal-content {
        background-color: #fff;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h2 {
        font-size: 18px;
        color: #111827;
        margin: 0;
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 24px;
        color: #6b7280;
        cursor: pointer;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-body p {
        margin-top: 0;
        margin-bottom: 15px;
        color: #4b5563;
    }
    
    .modal-body p:last-child {
        margin-bottom: 0;
    }
    
    .modal-footer {
        padding: 20px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .btn-secondary {
        background-color: #f3f4f6;
        color: #4b5563;
        border: none;
        border-radius: 6px;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .btn-secondary:hover {
        background-color: #e5e7eb;
    }
    
    .btn-danger {
        background-color: #ef4444;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }
    
    .btn-danger:hover {
        background-color: #dc2626;
    }
    
    @media (max-width: 768px) {
        .details-grid,
        .actions-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
