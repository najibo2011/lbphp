<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Inscription</h1>
            <p>Créez votre compte LeadsBuilder</p>
        </div>
        
        <form action="process_register.php" method="post" class="auth-form">
            <div class="form-group">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8">
                <small class="form-text">Le mot de passe doit contenir au moins 8 caractères</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8">
            </div>
            
            <div class="form-group terms">
                <label class="checkbox-container">
                    <input type="checkbox" name="terms" id="terms" required>
                    <span class="checkmark"></span>
                    J'accepte les <a href="terms.php" target="_blank">conditions d'utilisation</a> et la <a href="privacy.php" target="_blank">politique de confidentialité</a>
                </label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-primary btn-block">S'inscrire</button>
            </div>
            
            <div class="auth-footer">
                <p>Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a></p>
            </div>
        </form>
    </div>
</div>

<style>
    .auth-container {
        max-width: 500px;
        margin: 40px auto;
        padding: 20px;
    }
    
    .auth-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        padding: 30px;
    }
    
    .auth-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .auth-header h1 {
        font-size: 24px;
        color: #333;
        margin-bottom: 10px;
    }
    
    .auth-header p {
        color: #666;
        font-size: 14px;
    }
    
    .auth-form {
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    
    .form-control:focus {
        border-color: #4f46e5;
        outline: none;
    }
    
    .form-text {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #6b7280;
    }
    
    .terms {
        margin-top: 25px;
    }
    
    .checkbox-container {
        display: flex;
        align-items: center;
        cursor: pointer;
        font-size: 14px;
        color: #666;
    }
    
    .checkbox-container input {
        margin-right: 8px;
    }
    
    .checkbox-container a {
        color: #4f46e5;
        text-decoration: none;
    }
    
    .checkbox-container a:hover {
        text-decoration: underline;
    }
    
    .btn-primary {
        background-color: #4f46e5;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .btn-primary:hover {
        background-color: #4338ca;
    }
    
    .btn-block {
        display: block;
        width: 100%;
    }
    
    .auth-footer {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
        color: #666;
    }
    
    .auth-footer a {
        color: #4f46e5;
        text-decoration: none;
    }
    
    .auth-footer a:hover {
        text-decoration: underline;
    }
</style>
