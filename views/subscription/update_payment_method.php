<div class="payment-container">
    <div class="payment-card">
        <div class="payment-header">
            <h1>Mettre à jour la méthode de paiement</h1>
            <p>Veuillez fournir vos nouvelles informations de paiement</p>
        </div>
        
        <div class="payment-form">
            <div id="payment-element">
                <!-- Stripe Elements sera injecté ici -->
            </div>
            
            <button id="submit-button" class="btn-update">
                <div class="spinner hidden" id="spinner"></div>
                <span id="button-text">Mettre à jour</span>
            </button>
            
            <div id="payment-message" class="payment-message hidden"></div>
        </div>
        
        <div class="payment-footer">
            <a href="manage_subscription.php" class="btn-back">Retour à mon abonnement</a>
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
    const submitButton = document.getElementById('submit-button');
    const spinner = document.getElementById('spinner');
    const buttonText = document.getElementById('button-text');
    const paymentMessage = document.getElementById('payment-message');
    
    submitButton.addEventListener('click', async () => {
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
    .payment-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .payment-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    
    .payment-header {
        padding: 30px;
        border-bottom: 1px solid #f3f4f6;
        text-align: center;
    }
    
    .payment-header h1 {
        font-size: 24px;
        color: #111827;
        margin-bottom: 10px;
        margin-top: 0;
    }
    
    .payment-header p {
        font-size: 16px;
        color: #6b7280;
        margin: 0;
    }
    
    .payment-form {
        padding: 30px;
    }
    
    #payment-element {
        margin-bottom: 24px;
    }
    
    .btn-update {
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
    
    .btn-update:hover {
        background-color: #4338ca;
    }
    
    .btn-update:disabled {
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
    
    .payment-footer {
        padding: 20px 30px;
        text-align: center;
        border-top: 1px solid #f3f4f6;
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
