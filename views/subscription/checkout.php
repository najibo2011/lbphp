<div class="checkout-container">
    <div class="checkout-card">
        <div class="checkout-header">
            <h1>Finaliser votre abonnement</h1>
            <p>Plan sélectionné : <strong><?= $plan['name'] ?></strong> - <?= $plan['price'] > 0 ? number_format($plan['price'], 2) . ' € / mois' : 'Gratuit' ?></p>
        </div>
        
        <div class="checkout-details">
            <h2>Récapitulatif</h2>
            <div class="checkout-summary">
                <div class="summary-item">
                    <span class="item-label">Plan</span>
                    <span class="item-value"><?= $plan['name'] ?></span>
                </div>
                <div class="summary-item">
                    <span class="item-label">Prix mensuel</span>
                    <span class="item-value"><?= number_format($plan['price'], 2) ?> €</span>
                </div>
                <div class="summary-item">
                    <span class="item-label">Recherches par jour</span>
                    <span class="item-value"><?= $plan['search_limit'] ?></span>
                </div>
                <div class="summary-item">
                    <span class="item-label">Listes maximum</span>
                    <span class="item-value"><?= $plan['list_limit'] ?></span>
                </div>
                <div class="summary-item">
                    <span class="item-label">Profils par liste</span>
                    <span class="item-value"><?= $plan['profile_per_list_limit'] ?></span>
                </div>
                
                <div class="summary-total">
                    <span class="total-label">Total aujourd'hui</span>
                    <span class="total-value"><?= number_format($plan['price'], 2) ?> €</span>
                </div>
            </div>
            
            <div class="checkout-info">
                <p>En vous abonnant, vous acceptez les <a href="terms.php" target="_blank">conditions d'utilisation</a> et la <a href="privacy.php" target="_blank">politique de confidentialité</a>.</p>
                <p>Vous pouvez annuler votre abonnement à tout moment depuis votre espace client.</p>
            </div>
        </div>
        
        <div class="checkout-payment">
            <h2>Paiement</h2>
            <div id="payment-form">
                <div id="payment-element">
                    <!-- Stripe Elements sera injecté ici -->
                </div>
                <button id="submit-button" class="btn-pay">
                    <div class="spinner hidden" id="spinner"></div>
                    <span id="button-text">Payer <?= number_format($plan['price'], 2) ?> €</span>
                </button>
                <div id="payment-message" class="payment-message hidden"></div>
            </div>
        </div>
        
        <div class="checkout-footer">
            <a href="plans.php" class="btn-back">Retour aux plans</a>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    // Initialiser Stripe
    const stripe = Stripe('<?= $stripePublicKey ?>');
    const elements = stripe.elements();
    const sessionId = '<?= $sessionId ?>';
    
    // Créer et monter l'élément de paiement
    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');
    
    // Gérer la soumission du formulaire
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');
    const spinner = document.getElementById('spinner');
    const buttonText = document.getElementById('button-text');
    const paymentMessage = document.getElementById('payment-message');
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Désactiver le bouton pendant le traitement
        setLoading(true);
        
        // Rediriger vers la session Checkout
        stripe.redirectToCheckout({
            sessionId: sessionId
        }).then(function (result) {
            if (result.error) {
                // Afficher l'erreur
                showMessage(result.error.message);
                setLoading(false);
            }
        });
    });
    
    // Fonctions utilitaires
    function setLoading(isLoading) {
        if (isLoading) {
            submitButton.disabled = true;
            spinner.classList.remove('hidden');
            buttonText.classList.add('hidden');
        } else {
            submitButton.disabled = false;
            spinner.classList.add('hidden');
            buttonText.classList.remove('hidden');
        }
    }
    
    function showMessage(messageText) {
        paymentMessage.classList.remove('hidden');
        paymentMessage.textContent = messageText;
        
        setTimeout(function () {
            paymentMessage.classList.add('hidden');
            paymentMessage.textContent = '';
        }, 4000);
    }
</script>

<style>
    .checkout-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .checkout-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    
    .checkout-header {
        padding: 30px;
        border-bottom: 1px solid #f3f4f6;
        text-align: center;
    }
    
    .checkout-header h1 {
        font-size: 24px;
        color: #111827;
        margin-bottom: 10px;
    }
    
    .checkout-header p {
        font-size: 16px;
        color: #6b7280;
        margin: 0;
    }
    
    .checkout-details {
        padding: 30px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .checkout-details h2 {
        font-size: 18px;
        color: #111827;
        margin-top: 0;
        margin-bottom: 20px;
    }
    
    .checkout-summary {
        margin-bottom: 20px;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
    }
    
    .item-label {
        color: #6b7280;
    }
    
    .item-value {
        color: #111827;
        font-weight: 500;
    }
    
    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f3f4f6;
        font-size: 16px;
        font-weight: 600;
    }
    
    .total-label {
        color: #111827;
    }
    
    .total-value {
        color: #111827;
    }
    
    .checkout-info {
        margin-top: 20px;
        font-size: 14px;
        color: #6b7280;
    }
    
    .checkout-info p {
        margin-bottom: 10px;
    }
    
    .checkout-info a {
        color: #4f46e5;
        text-decoration: none;
    }
    
    .checkout-info a:hover {
        text-decoration: underline;
    }
    
    .checkout-payment {
        padding: 30px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .checkout-payment h2 {
        font-size: 18px;
        color: #111827;
        margin-top: 0;
        margin-bottom: 20px;
    }
    
    #payment-form {
        width: 100%;
    }
    
    #payment-element {
        margin-bottom: 24px;
    }
    
    .btn-pay {
        background-color: #4f46e5;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 12px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        display: block;
        width: 100%;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .btn-pay:hover {
        background-color: #4338ca;
    }
    
    .btn-pay:disabled {
        opacity: 0.5;
        cursor: default;
    }
    
    .spinner,
    .spinner:before,
    .spinner:after {
        border-radius: 50%;
    }
    
    .spinner {
        color: #ffffff;
        font-size: 22px;
        text-indent: -99999px;
        margin: 0 auto;
        position: relative;
        width: 20px;
        height: 20px;
        box-shadow: inset 0 0 0 2px;
        -webkit-transform: translateZ(0);
        -ms-transform: translateZ(0);
        transform: translateZ(0);
    }
    
    .spinner:before,
    .spinner:after {
        position: absolute;
        content: "";
    }
    
    .spinner:before {
        width: 10.4px;
        height: 20.4px;
        background: #4f46e5;
        border-radius: 20.4px 0 0 20.4px;
        top: -0.2px;
        left: -0.2px;
        -webkit-transform-origin: 10.4px 10.2px;
        transform-origin: 10.4px 10.2px;
        -webkit-animation: loading 2s infinite ease 1.5s;
        animation: loading 2s infinite ease 1.5s;
    }
    
    .spinner:after {
        width: 10.4px;
        height: 10.2px;
        background: #4f46e5;
        border-radius: 0 10.2px 10.2px 0;
        top: -0.1px;
        left: 10.2px;
        -webkit-transform-origin: 0px 10.2px;
        transform-origin: 0px 10.2px;
        -webkit-animation: loading 2s infinite ease;
        animation: loading 2s infinite ease;
    }
    
    @-webkit-keyframes loading {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
    
    @keyframes loading {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
    
    .hidden {
        display: none;
    }
    
    .payment-message {
        color: #ef4444;
        font-size: 14px;
        line-height: 20px;
        padding-top: 12px;
        text-align: center;
    }
    
    .checkout-footer {
        padding: 20px 30px;
        text-align: center;
    }
    
    .btn-back {
        display: inline-block;
        background-color: #f3f4f6;
        color: #4b5563;
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .btn-back:hover {
        background-color: #e5e7eb;
    }
</style>
