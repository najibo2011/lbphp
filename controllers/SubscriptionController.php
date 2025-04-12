<?php
require_once __DIR__ . '/../includes/Controller.php';
require_once __DIR__ . '/../models/SubscriptionModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../includes/Security.php';

/**
 * Contrôleur pour la gestion des abonnements
 */
class SubscriptionController extends Controller {
    private $subscriptionModel;
    private $userModel;
    private $stripeSecretKey;
    private $security;
    
    /**
     * Constructeur
     */
    public function __construct() {
        parent::__construct();
        $this->subscriptionModel = new SubscriptionModel();
        $this->userModel = new UserModel();
        $this->stripeSecretKey = STRIPE_SECRET_KEY;
        $this->security = new Security();
        
        // Initialiser Stripe
        \Stripe\Stripe::setApiKey($this->stripeSecretKey);
    }
    
    /**
     * Afficher la page des plans d'abonnement
     */
    public function plans() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        $plans = $this->subscriptionModel->getAllPlans();
        $currentPlan = $this->subscriptionModel->getPlanDetails($_SESSION['user_subscription']);
        
        $data = [
            'title' => 'Plans d\'abonnement',
            'currentPage' => 'plans',
            'plans' => $plans,
            'currentPlan' => $currentPlan
        ];
        
        $this->render('subscription/plans', $data);
    }
    
    /**
     * Afficher la page de paiement pour un plan spécifique
     */
    public function checkout() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        $planId = $_GET['plan'] ?? '';
        
        if (empty($planId)) {
            $this->redirect('plans.php');
            return;
        }
        
        $plan = $this->subscriptionModel->getPlanDetails($planId);
        
        if (!$plan || $plan['price'] <= 0) {
            $this->setFlash('error', 'Plan d\'abonnement invalide.');
            $this->redirect('plans.php');
            return;
        }
        
        // Créer une intention de paiement avec Stripe
        try {
            $user = $this->userModel->findById($_SESSION['user_id']);
            
            // Créer ou récupérer le client Stripe
            $stripeCustomer = $this->getOrCreateStripeCustomer($user);
            
            // Créer une session de paiement
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'customer' => $stripeCustomer->id,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Abonnement ' . $plan['name'],
                            'description' => 'Abonnement mensuel au plan ' . $plan['name']
                        ],
                        'unit_amount' => $plan['price'] * 100, // En centimes
                        'recurring' => [
                            'interval' => 'month'
                        ]
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/subscription_success.php?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/plans.php',
                'metadata' => [
                    'user_id' => $user['id'],
                    'plan_id' => $planId
                ]
            ]);
            
            $data = [
                'title' => 'Paiement',
                'currentPage' => 'checkout',
                'plan' => $plan,
                'sessionId' => $session->id,
                'stripePublicKey' => STRIPE_PUBLIC_KEY
            ];
            
            $this->render('subscription/checkout', $data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de la création de la session de paiement : ' . $e->getMessage());
            $this->redirect('plans.php');
        }
    }
    
    /**
     * Traiter le succès d'un paiement
     */
    public function subscriptionSuccess() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        $sessionId = $_GET['session_id'] ?? '';
        
        if (empty($sessionId)) {
            $this->redirect('plans.php');
            return;
        }
        
        try {
            // Récupérer la session Stripe
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            
            // Vérifier que la session appartient à l'utilisateur connecté
            if ($session->metadata->user_id != $_SESSION['user_id']) {
                $this->setFlash('error', 'Session de paiement invalide.');
                $this->redirect('plans.php');
                return;
            }
            
            // Récupérer l'abonnement
            $subscription = \Stripe\Subscription::retrieve($session->subscription);
            
            // Mettre à jour l'abonnement dans la base de données
            $subscriptionData = [
                'user_id' => $_SESSION['user_id'],
                'plan_id' => $session->metadata->plan_id,
                'status' => $subscription->status,
                'stripe_customer_id' => $session->customer,
                'stripe_subscription_id' => $subscription->id,
                'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end)
            ];
            
            $subscriptionId = $this->subscriptionModel->createSubscription($subscriptionData);
            
            if ($subscriptionId) {
                // Mettre à jour le plan de l'utilisateur
                $this->userModel->update($_SESSION['user_id'], [
                    'subscription_plan' => $session->metadata->plan_id
                ]);
                
                // Mettre à jour la session
                $_SESSION['user_subscription'] = $session->metadata->plan_id;
                
                $this->setFlash('success', 'Votre abonnement a été activé avec succès !');
                $this->redirect('dashboard.php');
            } else {
                $this->setFlash('error', 'Une erreur est survenue lors de l\'activation de votre abonnement.');
                $this->redirect('plans.php');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de la vérification de votre paiement : ' . $e->getMessage());
            $this->redirect('plans.php');
        }
    }
    
    /**
     * Gérer les webhooks Stripe
     */
    public function handleWebhook() {
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $endpointSecret = STRIPE_WEBHOOK_SECRET;
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Signature invalide
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Signature invalide
            http_response_code(400);
            exit();
        }
        
        // Gérer l'événement
        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;
                
            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;
                
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;
                
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
        }
        
        http_response_code(200);
    }
    
    /**
     * Gérer le paiement réussi d'une facture
     */
    private function handleInvoicePaymentSucceeded($invoice) {
        // Récupérer l'abonnement
        $subscription = \Stripe\Subscription::retrieve($invoice->subscription);
        
        // Récupérer l'abonnement dans la base de données
        $dbSubscription = $this->subscriptionModel->findByStripeSubscriptionId($subscription->id);
        
        if ($dbSubscription) {
            // Mettre à jour l'abonnement
            $this->subscriptionModel->updateSubscription($dbSubscription['id'], [
                'status' => $subscription->status,
                'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end)
            ]);
            
            // Envoyer un email de confirmation
            $user = $this->userModel->findById($dbSubscription['user_id']);
            if ($user) {
                $this->sendInvoiceEmail($user, $invoice);
            }
        }
    }
    
    /**
     * Gérer l'échec de paiement d'une facture
     */
    private function handleInvoicePaymentFailed($invoice) {
        // Récupérer l'abonnement
        $subscription = \Stripe\Subscription::retrieve($invoice->subscription);
        
        // Récupérer l'abonnement dans la base de données
        $dbSubscription = $this->subscriptionModel->findByStripeSubscriptionId($subscription->id);
        
        if ($dbSubscription) {
            // Mettre à jour l'abonnement
            $this->subscriptionModel->updateSubscription($dbSubscription['id'], [
                'status' => $subscription->status
            ]);
            
            // Envoyer un email d'échec de paiement
            $user = $this->userModel->findById($dbSubscription['user_id']);
            if ($user) {
                $this->sendPaymentFailedEmail($user, $invoice);
            }
        }
    }
    
    /**
     * Gérer la suppression d'un abonnement
     */
    private function handleSubscriptionDeleted($subscription) {
        // Récupérer l'abonnement dans la base de données
        $dbSubscription = $this->subscriptionModel->findByStripeSubscriptionId($subscription->id);
        
        if ($dbSubscription) {
            // Mettre à jour l'abonnement
            $this->subscriptionModel->updateSubscription($dbSubscription['id'], [
                'status' => 'canceled',
                'canceled_at' => date('Y-m-d H:i:s')
            ]);
            
            // Mettre à jour l'utilisateur
            $this->userModel->update($dbSubscription['user_id'], [
                'subscription_plan' => 'free'
            ]);
            
            // Envoyer un email de confirmation d'annulation
            $user = $this->userModel->findById($dbSubscription['user_id']);
            if ($user) {
                $this->sendSubscriptionCanceledEmail($user);
            }
        }
    }
    
    /**
     * Gérer la mise à jour d'un abonnement
     */
    private function handleSubscriptionUpdated($subscription) {
        // Récupérer l'abonnement dans la base de données
        $dbSubscription = $this->subscriptionModel->findByStripeSubscriptionId($subscription->id);
        
        if ($dbSubscription) {
            // Mettre à jour l'abonnement
            $this->subscriptionModel->updateSubscription($dbSubscription['id'], [
                'status' => $subscription->status,
                'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end)
            ]);
        }
    }
    
    /**
     * Afficher la page de gestion de l'abonnement
     */
    public function manageSubscription() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Récupérer l'abonnement actif de l'utilisateur
        $subscription = $this->subscriptionModel->getActiveSubscription($_SESSION['user_id']);
        
        if (!$subscription) {
            $this->setFlash('info', 'Vous n\'avez pas d\'abonnement actif.');
            $this->redirect('plans.php');
            return;
        }
        
        // Récupérer les détails du plan
        $plan = $this->subscriptionModel->getPlanDetails($subscription['plan_id']);
        
        // Récupérer l'abonnement Stripe
        try {
            $stripeSubscription = \Stripe\Subscription::retrieve($subscription['stripe_subscription_id']);
            
            $data = [
                'title' => 'Gérer mon abonnement',
                'currentPage' => 'manage_subscription',
                'subscription' => $subscription,
                'plan' => $plan,
                'stripeSubscription' => $stripeSubscription
            ];
            
            $this->render('subscription/manage', $data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de la récupération de votre abonnement : ' . $e->getMessage());
            $this->redirect('dashboard.php');
        }
    }
    
    /**
     * Annuler un abonnement
     */
    public function cancelSubscription() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Récupérer l'abonnement actif de l'utilisateur
        $subscription = $this->subscriptionModel->getActiveSubscription($_SESSION['user_id']);
        
        if (!$subscription) {
            $this->setFlash('info', 'Vous n\'avez pas d\'abonnement actif.');
            $this->redirect('plans.php');
            return;
        }
        
        try {
            // Annuler l'abonnement Stripe
            $stripeSubscription = \Stripe\Subscription::retrieve($subscription['stripe_subscription_id']);
            $stripeSubscription->cancel();
            
            // Mettre à jour l'abonnement dans la base de données
            $this->subscriptionModel->updateSubscription($subscription['id'], [
                'status' => 'canceled',
                'canceled_at' => date('Y-m-d H:i:s')
            ]);
            
            // Mettre à jour l'utilisateur
            $this->userModel->update($subscription['user_id'], [
                'subscription_plan' => 'free'
            ]);
            
            // Mettre à jour la session
            $_SESSION['user_subscription'] = 'free';
            
            // Envoyer un email de confirmation d'annulation
            $user = $this->userModel->findById($subscription['user_id']);
            if ($user) {
                $this->sendSubscriptionCanceledEmail($user);
            }
            
            $this->setFlash('success', 'Votre abonnement a été annulé avec succès. Il restera actif jusqu\'à la fin de la période en cours.');
            $this->redirect('dashboard.php');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de l\'annulation de votre abonnement : ' . $e->getMessage());
            $this->redirect('manage_subscription.php');
        }
    }
    
    /**
     * Mettre à jour la carte de crédit
     */
    public function updatePaymentMethod() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Récupérer l'abonnement actif de l'utilisateur
        $subscription = $this->subscriptionModel->getActiveSubscription($_SESSION['user_id']);
        
        if (!$subscription) {
            $this->setFlash('info', 'Vous n\'avez pas d\'abonnement actif.');
            $this->redirect('plans.php');
            return;
        }
        
        try {
            $user = $this->userModel->findById($_SESSION['user_id']);
            
            // Créer une session de mise à jour de la méthode de paiement
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'setup',
                'customer' => $subscription['stripe_customer_id'],
                'setup_intent_data' => [
                    'metadata' => [
                        'customer_id' => $subscription['stripe_customer_id'],
                        'subscription_id' => $subscription['stripe_subscription_id']
                    ]
                ],
                'success_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/payment_method_success.php?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/manage_subscription.php'
            ]);
            
            $data = [
                'title' => 'Mettre à jour la méthode de paiement',
                'currentPage' => 'update_payment_method',
                'sessionId' => $session->id,
                'stripePublicKey' => STRIPE_PUBLIC_KEY
            ];
            
            $this->render('subscription/update_payment_method', $data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de la création de la session de mise à jour : ' . $e->getMessage());
            $this->redirect('manage_subscription.php');
        }
    }
    
    /**
     * Traiter le succès de la mise à jour de la méthode de paiement
     */
    public function paymentMethodSuccess() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        $sessionId = $_GET['session_id'] ?? '';
        
        if (empty($sessionId)) {
            $this->redirect('manage_subscription.php');
            return;
        }
        
        try {
            // Récupérer la session Stripe
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            
            $this->setFlash('success', 'Votre méthode de paiement a été mise à jour avec succès.');
            $this->redirect('manage_subscription.php');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de la vérification de votre méthode de paiement : ' . $e->getMessage());
            $this->redirect('manage_subscription.php');
        }
    }
    
    /**
     * Afficher la page des méthodes de paiement
     */
    public function paymentMethods() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Récupérer l'utilisateur
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        // Vérifier si l'utilisateur a un ID client Stripe
        if (empty($user['stripe_customer_id'])) {
            $this->setFlash('error', 'Vous devez d\'abord souscrire à un abonnement pour gérer vos méthodes de paiement.');
            $this->redirect('plans.php');
            return;
        }
        
        try {
            // Récupérer le client Stripe
            $customer = \Stripe\Customer::retrieve($user['stripe_customer_id']);
            
            // Récupérer les méthodes de paiement
            $paymentMethods = \Stripe\PaymentMethod::all([
                'customer' => $customer->id,
                'type' => 'card'
            ]);
            
            // Récupérer la méthode de paiement par défaut
            $defaultPaymentMethod = $customer->invoice_settings->default_payment_method;
            
            $data = [
                'title' => 'Méthodes de paiement',
                'currentPage' => 'payment_methods',
                'paymentMethods' => $paymentMethods->data,
                'defaultPaymentMethod' => $defaultPaymentMethod,
                'stripePublicKey' => STRIPE_PUBLIC_KEY,
                'csrfToken' => $this->generateCsrfToken(),
                'flash' => $this->getFlash()
            ];
            
            $this->render('subscription/payment_methods', $data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de la récupération de vos méthodes de paiement : ' . $e->getMessage());
            $this->redirect('manage_subscription.php');
        }
    }
    
    /**
     * Ajouter une nouvelle méthode de paiement
     */
    public function addPaymentMethod() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Vérifier si la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('payment_methods.php');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('payment_methods.php');
            return;
        }
        
        // Récupérer les données du formulaire
        $paymentMethodId = $_POST['payment_method_id'] ?? '';
        $setDefault = isset($_POST['set_default']) && $_POST['set_default'] === 'on';
        
        if (empty($paymentMethodId)) {
            $this->setFlash('error', 'Méthode de paiement invalide.');
            $this->redirect('payment_methods.php');
            return;
        }
        
        // Récupérer l'utilisateur
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        // Vérifier si l'utilisateur a un ID client Stripe
        if (empty($user['stripe_customer_id'])) {
            $this->setFlash('error', 'Vous devez d\'abord souscrire à un abonnement pour ajouter une méthode de paiement.');
            $this->redirect('plans.php');
            return;
        }
        
        try {
            // Attacher la méthode de paiement au client
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->attach(['customer' => $user['stripe_customer_id']]);
            
            // Définir comme méthode de paiement par défaut si demandé
            if ($setDefault) {
                $customer = \Stripe\Customer::update($user['stripe_customer_id'], [
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethodId
                    ]
                ]);
            }
            
            $this->setFlash('success', 'Votre méthode de paiement a été ajoutée avec succès.');
            $this->redirect('payment_methods.php');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de l\'ajout de votre méthode de paiement : ' . $e->getMessage());
            $this->redirect('payment_methods.php');
        }
    }
    
    /**
     * Supprimer une méthode de paiement
     */
    public function deletePaymentMethod() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Vérifier si la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('payment_methods.php');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('payment_methods.php');
            return;
        }
        
        // Récupérer les données du formulaire
        $paymentMethodId = $_POST['payment_method_id'] ?? '';
        
        if (empty($paymentMethodId)) {
            $this->setFlash('error', 'Méthode de paiement invalide.');
            $this->redirect('payment_methods.php');
            return;
        }
        
        // Récupérer l'utilisateur
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        // Vérifier si l'utilisateur a un ID client Stripe
        if (empty($user['stripe_customer_id'])) {
            $this->setFlash('error', 'Vous devez d\'abord souscrire à un abonnement pour gérer vos méthodes de paiement.');
            $this->redirect('plans.php');
            return;
        }
        
        try {
            // Récupérer le client Stripe
            $customer = \Stripe\Customer::retrieve($user['stripe_customer_id']);
            
            // Vérifier si la méthode de paiement est la méthode par défaut
            if ($customer->invoice_settings->default_payment_method === $paymentMethodId) {
                $this->setFlash('error', 'Vous ne pouvez pas supprimer votre méthode de paiement par défaut. Veuillez d\'abord en définir une autre comme méthode par défaut.');
                $this->redirect('payment_methods.php');
                return;
            }
            
            // Détacher la méthode de paiement
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->detach();
            
            $this->setFlash('success', 'Votre méthode de paiement a été supprimée avec succès.');
            $this->redirect('payment_methods.php');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de la suppression de votre méthode de paiement : ' . $e->getMessage());
            $this->redirect('payment_methods.php');
        }
    }
    
    /**
     * Définir une méthode de paiement par défaut
     */
    public function setDefaultPaymentMethod() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Vérifier si la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('payment_methods.php');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('payment_methods.php');
            return;
        }
        
        // Récupérer les données du formulaire
        $paymentMethodId = $_POST['payment_method_id'] ?? '';
        
        if (empty($paymentMethodId)) {
            $this->setFlash('error', 'Méthode de paiement invalide.');
            $this->redirect('payment_methods.php');
            return;
        }
        
        // Récupérer l'utilisateur
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        // Vérifier si l'utilisateur a un ID client Stripe
        if (empty($user['stripe_customer_id'])) {
            $this->setFlash('error', 'Vous devez d\'abord souscrire à un abonnement pour gérer vos méthodes de paiement.');
            $this->redirect('plans.php');
            return;
        }
        
        try {
            // Mettre à jour le client Stripe
            $customer = \Stripe\Customer::update($user['stripe_customer_id'], [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId
                ]
            ]);
            
            $this->setFlash('success', 'Votre méthode de paiement par défaut a été mise à jour avec succès.');
            $this->redirect('payment_methods.php');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Une erreur est survenue lors de la mise à jour de votre méthode de paiement par défaut : ' . $e->getMessage());
            $this->redirect('payment_methods.php');
        }
    }
    
    /**
     * Obtenir ou créer un client Stripe
     */
    private function getOrCreateStripeCustomer($user) {
        // Vérifier si l'utilisateur a déjà un ID client Stripe
        if (!empty($user['stripe_customer_id'])) {
            try {
                // Récupérer le client existant
                return \Stripe\Customer::retrieve($user['stripe_customer_id']);
            } catch (\Exception $e) {
                // Le client n'existe plus, en créer un nouveau
            }
        }
        
        // Créer un nouveau client
        $customer = \Stripe\Customer::create([
            'email' => $user['email'],
            'name' => $user['name'],
            'metadata' => [
                'user_id' => $user['id']
            ]
        ]);
        
        // Mettre à jour l'utilisateur avec l'ID client Stripe
        $this->userModel->update($user['id'], [
            'stripe_customer_id' => $customer->id
        ]);
        
        return $customer;
    }
    
    /**
     * Envoyer un email de facture
     */
    private function sendInvoiceEmail($user, $invoice) {
        // URL de la facture
        $invoiceUrl = $invoice->hosted_invoice_url;
        
        // Sujet de l'email
        $subject = 'Votre facture LeadsBuilder';
        
        // Corps de l'email
        $message = "
        <html>
        <head>
            <title>Votre facture LeadsBuilder</title>
        </head>
        <body>
            <h2>Merci pour votre paiement !</h2>
            <p>Cher(e) {$user['name']},</p>
            <p>Nous vous confirmons que votre paiement a bien été reçu. Vous trouverez ci-dessous les détails de votre facture :</p>
            <ul>
                <li>Montant : " . number_format($invoice->amount_paid / 100, 2) . " €</li>
                <li>Date : " . date('d/m/Y', $invoice->created) . "</li>
                <li>Numéro de facture : {$invoice->number}</li>
            </ul>
            <p>Vous pouvez consulter et télécharger votre facture en cliquant sur le lien ci-dessous :</p>
            <p><a href='{$invoiceUrl}'>Voir ma facture</a></p>
            <p>Nous vous remercions pour votre confiance.</p>
            <p>Cordialement,<br>L'équipe LeadsBuilder</p>
        </body>
        </html>
        ";
        
        // En-têtes de l'email
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: LeadsBuilder <noreply@leadsbuilder.com>',
            'Reply-To: support@leadsbuilder.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Envoyer l'email
        mail($user['email'], $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Envoyer un email d'échec de paiement
     */
    private function sendPaymentFailedEmail($user, $invoice) {
        // URL de mise à jour de la méthode de paiement
        $updateUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/update_payment_method.php';
        
        // Sujet de l'email
        $subject = 'Problème de paiement - LeadsBuilder';
        
        // Corps de l'email
        $message = "
        <html>
        <head>
            <title>Problème de paiement</title>
        </head>
        <body>
            <h2>Problème de paiement détecté</h2>
            <p>Cher(e) {$user['name']},</p>
            <p>Nous avons rencontré un problème lors du traitement de votre paiement pour votre abonnement LeadsBuilder.</p>
            <p>Détails :</p>
            <ul>
                <li>Montant : " . number_format($invoice->amount_due / 100, 2) . " €</li>
                <li>Date : " . date('d/m/Y', $invoice->created) . "</li>
            </ul>
            <p>Pour éviter toute interruption de service, veuillez mettre à jour votre méthode de paiement en cliquant sur le lien ci-dessous :</p>
            <p><a href='{$updateUrl}'>Mettre à jour ma méthode de paiement</a></p>
            <p>Si vous avez des questions, n'hésitez pas à contacter notre service client.</p>
            <p>Cordialement,<br>L'équipe LeadsBuilder</p>
        </body>
        </html>
        ";
        
        // En-têtes de l'email
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: LeadsBuilder <noreply@leadsbuilder.com>',
            'Reply-To: support@leadsbuilder.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Envoyer l'email
        mail($user['email'], $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Envoyer un email de confirmation d'annulation d'abonnement
     */
    private function sendSubscriptionCanceledEmail($user) {
        // URL des plans
        $plansUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/plans.php';
        
        // Sujet de l'email
        $subject = 'Confirmation d\'annulation d\'abonnement - LeadsBuilder';
        
        // Corps de l'email
        $message = "
        <html>
        <head>
            <title>Confirmation d'annulation d'abonnement</title>
        </head>
        <body>
            <h2>Confirmation d'annulation d'abonnement</h2>
            <p>Cher(e) {$user['name']},</p>
            <p>Nous confirmons l'annulation de votre abonnement LeadsBuilder.</p>
            <p>Votre abonnement restera actif jusqu'à la fin de la période en cours, après quoi vous serez automatiquement rétrogradé vers le plan gratuit.</p>
            <p>Nous espérons vous revoir bientôt ! Si vous souhaitez réactiver votre abonnement, vous pouvez le faire à tout moment en visitant notre page des plans :</p>
            <p><a href='{$plansUrl}'>Voir les plans d'abonnement</a></p>
            <p>Nous vous remercions pour votre confiance.</p>
            <p>Cordialement,<br>L'équipe LeadsBuilder</p>
        </body>
        </html>
        ";
        
        // En-têtes de l'email
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: LeadsBuilder <noreply@leadsbuilder.com>',
            'Reply-To: support@leadsbuilder.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Envoyer l'email
        mail($user['email'], $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    private function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
