<?php
/**
 * Page pour les demandes RGPD
 */
require_once 'includes/Gdpr.php';
require_once 'includes/Security.php';

// Initialiser les classes nécessaires
$gdpr = new Gdpr();
$security = new Security();

// Récupérer les erreurs et les données du formulaire de la session
$errors = $_SESSION['gdpr_request_errors'] ?? [];
$formData = $_SESSION['gdpr_request_data'] ?? [];
$success = $_SESSION['gdpr_request_success'] ?? null;

// Nettoyer les données de session
unset($_SESSION['gdpr_request_errors']);
unset($_SESSION['gdpr_request_data']);
unset($_SESSION['gdpr_request_success']);

// Générer un jeton CSRF
$csrfToken = $security->generateCsrfToken();

// Inclure l'en-tête
include 'views/layouts/header.php';

// Afficher le formulaire de demande RGPD
echo $gdpr->renderGdprRequestForm();

// Inclure le pied de page
include 'views/layouts/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher les erreurs s'il y en a
    <?php if (!empty($errors)): ?>
    const errorMessages = <?php echo json_encode($errors); ?>;
    const errorContainer = document.createElement('div');
    errorContainer.className = 'alert alert-danger';
    
    const errorList = document.createElement('ul');
    errorMessages.forEach(function(error) {
        const errorItem = document.createElement('li');
        errorItem.textContent = error;
        errorList.appendChild(errorItem);
    });
    
    errorContainer.appendChild(errorList);
    
    const formElement = document.querySelector('.gdpr-form');
    formElement.insertBefore(errorContainer, formElement.firstChild);
    <?php endif; ?>
    
    // Afficher le message de succès s'il y en a un
    <?php if ($success): ?>
    const successContainer = document.createElement('div');
    successContainer.className = 'alert alert-success';
    successContainer.textContent = <?php echo json_encode($success); ?>;
    
    const formContainer = document.querySelector('.gdpr-request-container');
    formContainer.insertBefore(successContainer, formContainer.firstChild);
    <?php endif; ?>
    
    // Pré-remplir le formulaire avec les données précédentes
    <?php if (!empty($formData)): ?>
    const formData = <?php echo json_encode($formData); ?>;
    
    for (const field in formData) {
        if (formData.hasOwnProperty(field)) {
            const element = document.getElementById(field);
            if (element) {
                element.value = formData[field];
            }
        }
    }
    <?php endif; ?>
    
    // Ajouter le jeton CSRF au formulaire
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = <?php echo json_encode($csrfToken); ?>;
    
    document.querySelector('.gdpr-form').appendChild(csrfInput);
});
</script>

<style>
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 6px;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

.alert li {
    margin-bottom: 5px;
}
</style>
