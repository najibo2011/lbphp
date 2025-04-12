<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Méthodes de paiement - LeadsBuilder' ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <?php include 'views/layouts/header.php'; ?>
    
    <div class="payment-methods-container">
        <h1>Gérer mes méthodes de paiement</h1>
        
        <?php if (isset($flash['success'])): ?>
        <div class="alert alert-success">
            <?= $flash['success'] ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($flash['error'])): ?>
        <div class="alert alert-danger">
            <?= $flash['error'] ?>
        </div>
        <?php endif; ?>
        
        <div class="payment-methods-content">
            <div class="current-payment-methods">
                <h2>Méthodes de paiement actuelles</h2>
                
                <?php if (empty($paymentMethods)): ?>
                <p class="no-methods">Aucune méthode de paiement enregistrée.</p>
                <?php else: ?>
                <div class="methods-list">
                    <?php foreach ($paymentMethods as $method): ?>
                    <div class="payment-method-card">
                        <div class="card-info">
                            <div class="card-brand">
                                <?php if ($method['card']['brand'] === 'visa'): ?>
                                <i class="fab fa-cc-visa"></i>
                                <?php elseif ($method['card']['brand'] === 'mastercard'): ?>
                                <i class="fab fa-cc-mastercard"></i>
                                <?php elseif ($method['card']['brand'] === 'amex'): ?>
                                <i class="fab fa-cc-amex"></i>
                                <?php else: ?>
                                <i class="far fa-credit-card"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-details">
                                <p class="card-number">•••• •••• •••• <?= $method['card']['last4'] ?></p>
                                <p class="card-expiry">Expire le <?= $method['card']['exp_month'] ?>/<?= $method['card']['exp_year'] ?></p>
                            </div>
                        </div>
                        <div class="card-actions">
                            <?php if ($method['id'] === $defaultPaymentMethod): ?>
                            <span class="default-badge">Par défaut</span>
                            <?php else: ?>
                            <form action="set_default_payment_method.php" method="post">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="payment_method_id" value="<?= $method['id'] ?>">
                                <button type="submit" class="btn-text">Définir par défaut</button>
                            </form>
                            <?php endif; ?>
                            
                            <form action="delete_payment_method.php" method="post" class="delete-method-form">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="payment_method_id" value="<?= $method['id'] ?>">
                                <button type="submit" class="btn-text btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="add-payment-method">
                <h2>Ajouter une nouvelle méthode de paiement</h2>
                
                <form id="payment-form" action="add_payment_method.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="form-group">
                        <label for="cardholder-name">Nom du titulaire de la carte</label>
                        <input type="text" id="cardholder-name" name="cardholder_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="card-element">Informations de carte</label>
                        <div id="card-element" class="form-control card-element"></div>
                        <div id="card-errors" class="card-errors" role="alert"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" name="set_default" id="set-default" checked>
                            <span class="checkmark"></span>
                            Définir comme méthode de paiement par défaut
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" id="submit-button" class="btn-primary">Ajouter cette carte</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="payment-methods-footer">
            <a href="manage_subscription.php" class="btn-secondary">Retour à la gestion de l'abonnement</a>
        </div>
    </div>
    
    <?php include 'views/layouts/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser Stripe
            const stripe = Stripe('<?= $stripePublicKey ?>');
            const elements = stripe.elements();
            
            // Créer l'élément de carte
            const cardElement = elements.create('card', {
                style: {
                    base: {
                        color: '#32325d',
                        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                            color: '#aab7c4'
                        }
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a'
                    }
                }
            });
            
            // Monter l'élément de carte dans le DOM
            cardElement.mount('#card-element');
            
            // Gérer les erreurs de validation en temps réel
            cardElement.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
            
            // Gérer la soumission du formulaire
            const form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const submitButton = document.getElementById('submit-button');
                submitButton.disabled = true;
                submitButton.textContent = 'Traitement en cours...';
                
                const cardholderName = document.getElementById('cardholder-name').value;
                
                stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        name: cardholderName
                    }
                }).then(function(result) {
                    if (result.error) {
                        // Afficher l'erreur
                        const errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                        
                        // Réactiver le bouton
                        submitButton.disabled = false;
                        submitButton.textContent = 'Ajouter cette carte';
                    } else {
                        // Ajouter le payment method ID au formulaire
                        const hiddenInput = document.createElement('input');
                        hiddenInput.setAttribute('type', 'hidden');
                        hiddenInput.setAttribute('name', 'payment_method_id');
                        hiddenInput.setAttribute('value', result.paymentMethod.id);
                        form.appendChild(hiddenInput);
                        
                        // Soumettre le formulaire
                        form.submit();
                    }
                });
            });
            
            // Confirmer la suppression d'une méthode de paiement
            const deleteForms = document.querySelectorAll('.delete-method-form');
            deleteForms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cette méthode de paiement ?')) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
    
    <style>
        .payment-methods-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .payment-methods-container h1 {
            font-size: 28px;
            color: #111827;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .payment-methods-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        .current-payment-methods,
        .add-payment-method {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
        }
        
        .current-payment-methods h2,
        .add-payment-method h2 {
            font-size: 20px;
            color: #111827;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        
        .no-methods {
            color: #6b7280;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        
        .methods-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .payment-method-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background-color: #f9fafb;
        }
        
        .card-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .card-brand i {
            font-size: 24px;
            color: #4f46e5;
        }
        
        .card-details p {
            margin: 0;
        }
        
        .card-number {
            font-weight: 500;
            color: #111827;
        }
        
        .card-expiry {
            font-size: 14px;
            color: #6b7280;
        }
        
        .card-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .default-badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #d1fae5;
            color: #065f46;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .btn-text {
            background: none;
            border: none;
            color: #4f46e5;
            font-size: 14px;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
        }
        
        .btn-text:hover {
            color: #4338ca;
        }
        
        .btn-text.btn-danger {
            color: #ef4444;
        }
        
        .btn-text.btn-danger:hover {
            color: #dc2626;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #111827;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
        }
        
        .card-element {
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: white;
        }
        
        .card-errors {
            color: #ef4444;
            font-size: 14px;
            margin-top: 8px;
            min-height: 20px;
        }
        
        .checkbox-container {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
            font-size: 14px;
            color: #4b5563;
        }
        
        .checkbox-container input {
            margin-right: 10px;
            margin-top: 3px;
        }
        
        .form-actions {
            margin-top: 25px;
        }
        
        .btn-primary,
        .btn-secondary {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #4338ca;
        }
        
        .btn-primary:disabled {
            background-color: #a5b4fc;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background-color: #e5e7eb;
        }
        
        .payment-methods-footer {
            margin-top: 30px;
            text-align: center;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 6px;
        }
        
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }
        
        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }
        
        @media (min-width: 768px) {
            .payment-methods-content {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .payment-method-card {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-actions {
                margin-top: 15px;
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</body>
</html>
