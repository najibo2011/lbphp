        </main>
        
        <!-- Footer -->
        <footer class="app-footer">
            <div class="footer-content">
                <div class="footer-links">
                    <a href="privacy.php">Politique de confidentialité</a>
                    <a href="gdpr_request.php">Vos droits RGPD</a>
                    <a href="terms.php">Conditions d'utilisation</a>
                    <a href="contact.php">Contact</a>
                </div>
                <p>&copy; <?= date('Y') ?> LeadsBuilder PHP. Tous droits réservés.</p>
            </div>
        </footer>
    </div>
    
    <!-- Scripts communs -->
    <script src="assets/js/common.js"></script>
    
    <style>
        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .footer-links {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .footer-links a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        
        .footer-links a:hover {
            color: #4f46e5;
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .footer-links {
                gap: 15px;
            }
            
            .footer-links a {
                font-size: 12px;
            }
        }
    </style>
</body>
</html>
