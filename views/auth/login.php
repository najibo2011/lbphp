<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Connexion</h1>
            <p>Connectez-vous à votre compte LeadsBuilder</p>
        </div>
        
        <form action="process_login.php" method="post" class="auth-form">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group remember-me">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember_me" id="remember_me">
                    <span class="checkmark"></span>
                    Se souvenir de moi
                </label>
                <a href="forgot_password.php" class="forgot-password">Mot de passe oublié ?</a>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-primary btn-block">Se connecter</button>
            </div>
            
            <div class="auth-footer">
                <p>Vous n'avez pas de compte ? <a href="register.php">Inscrivez-vous</a></p>
            </div>
        </form>
        
        <div class="resend-verification">
            <p>Vous n'avez pas reçu l'email de vérification ?</p>
            <form action="resend_verification.php" method="post">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Votre adresse email" class="form-control" required>
                </div>
                <button type="submit" class="btn-secondary btn-block">Renvoyer l'email de vérification</button>
            </form>
        </div>
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
    
    .remember-me {
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    
    .forgot-password {
        font-size: 14px;
        color: #4f46e5;
        text-decoration: none;
    }
    
    .forgot-password:hover {
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
    
    .btn-secondary {
        background-color: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-secondary:hover {
        background-color: #e5e7eb;
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
    
    .resend-verification {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        text-align: center;
    }
    
    .resend-verification p {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
    }
</style>
