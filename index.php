<?php
// DÉBUT DU FICHIER - RIEN AVANT LA BALISE PHP OUVERTE
require_once 'config.php';

// ÉTAPE 1: Si déjà connecté, rediriger IMMÉDIATEMENT
if (isLoggedIn()) {
    redirect('dashboard.php');
    exit();
}

// ÉTAPE 2: Initialiser les variables
$error = '';
$success = '';

// ÉTAPE 3: Traitement INSCRIPTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscription'])) {
    // Nettoyer les entrées
    $nom = cleanInput($_POST['nom']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    // Validation
    if (empty($nom) || empty($email) || empty($password)) {
        $error = "Tous les champs sont requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif (strlen($password) < 6) {
        $error = "Mot de passe trop court (min 6 caractères).";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $pdo = getDB();
            
            // Vérifier si email existe
            $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé.";
            } else {
                // Hasher le mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer l'utilisateur
                $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, email, motdepasse, role) VALUES (?, ?, ?, 'etudiant')");
                $stmt->execute([$nom, $email, $hashedPassword]);
                
                $success = "✅ Compte créé avec succès ! Connectez-vous.";
                
                // Vider le formulaire
                $_POST = array();
            }
        } catch(PDOException $e) {
            $error = "❌ Erreur serveur. Veuillez réessayer.";
        }
    }
}

// ÉTAPE 4: Traitement CONNEXION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connexion'])) {
    $email = cleanInput($_POST['email_login']);
    $password = $_POST['password_login'];
    
    if (empty($email) || empty($password)) {
        $error = "Email et mot de passe requis.";
    } else {
        try {
            $pdo = getDB();
            
            // Chercher l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['motdepasse'])) {
                // DÉBOGAGE: Afficher les données utilisateur
                debug($user);
                
                // STOCKER EN SESSION
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                // DÉBOGAGE: Vérifier la session
                debug($_SESSION);
                
                // Forcer l'écriture de la session
                session_write_close();
                
                // REDIRECTION ABSOLUMENT GARANTIE
                redirect('admin.php');
                exit();
            } else {
                $error = "❌ Email ou mot de passe incorrect.";
            }
        } catch(PDOException $e) {
            $error = "❌ Erreur serveur. Veuillez réessayer.";
        }
    }
}

// ÉTAPE 5: Afficher la page
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEADER - Connexion</title>
    <style>
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .header {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .content {
            display: flex;
            min-height: 500px;
        }
        
        .left-panel {
            flex: 1;
            background: #f8f9fa;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .left-panel h2 {
            color: #4361ee;
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        .features {
            margin-top: 30px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: #4361ee;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .right-panel {
            flex: 1;
            padding: 40px;
        }
        
        /* ALERTS */
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .alert-error {
            background: #fee;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }
        
        /* TABS */
        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .tab-btn {
            flex: 1;
            padding: 15px;
            background: none;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .tab-btn.active {
            color: #4361ee;
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #4361ee;
            border-radius: 3px 3px 0 0;
        }
        
        /* FORMS */
        .form-container {
            display: none;
        }
        
        .form-container.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        /* BUTTONS */
        .btn {
            width: 100%;
            padding: 16px;
            background: #4361ee;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn:hover {
            background: #3a0ca3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-success {
            background: #4cc9f0;
        }
        
        .btn-success:hover {
            background: #3ab4d8;
        }
        
        /* DEMO BOX */
        .demo-box {
            margin-top: 30px;
            padding: 20px;
            background: #f0f7ff;
            border-radius: 10px;
            border-left: 4px solid #4361ee;
        }
        
        .demo-box h4 {
            color: #4361ee;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .demo-credentials {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-family: monospace;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .content {
                flex-direction: column;
            }
            
            .left-panel, .right-panel {
                padding: 30px 20px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
        img {
            height :100px;
            width :100px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <span>  Bien le bonjour à vous 😎! </span>
            <h1><i class="fas fa-graduation-cap"></i> LEADER PLATFORM</h1>
            <p>La plateforme d'échanges étudiants - Simplifiée et sécurisée</p>
        </div>
        
        <!-- CONTENT -->
        <div class="content">
            <!-- LEFT PANEL -->
            <div class="left-panel">
                <h2>Bienvenue étudiant !</h2>
                <p>Rejoignez la communauté LEADER pour échanger, apprendre et collaborer.</p>
                
                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div>
                            <h4>Publications & Discussions</h4>
                            <p>Partagez vos idées avec la communauté</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h4>Réseau Étudiant</h4>
                            <p>Connectez-vous avec d'autres étudiants</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h4>Environnement Sécurisé</h4>
                            <p>Vos données sont protégées</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- RIGHT PANEL -->
            <div class="right-panel">
                <!-- ALERTS -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <!-- TABS -->
                <div class="tabs">
                    <button class="tab-btn active" onclick="showForm('login')">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </button>
                    <button class="tab-btn" onclick="showForm('register')">
                        <i class="fas fa-user-plus"></i> Inscription
                    </button>
                </div>
                
                <!-- LOGIN FORM -->
                <div class="form-container active" id="loginForm">
                    <form method="POST" action="">
                        <input type="hidden" name="connexion" value="1">
                        
                        <div class="form-group">
                            <label for="email_login">
                                <i class="fas fa-envelope"></i> Adresse Email
                            </label>
                            <input type="email" id="email_login" name="email_login" 
                                   placeholder="votre@email.com" required
                                   value="<?php echo isset($_POST['email_login']) ? cleanInput($_POST['email_login']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password_login">
                                <i class="fas fa-lock"></i> Mot de passe
                            </label>
                            <input type="password" id="password_login" name="password_login" 
                                   placeholder="Votre mot de passe" required>
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>
                </div>
                
                <!-- REGISTER FORM -->
                <div class="form-container" id="registerForm">
                    <form method="POST" action="">
                        <input type="hidden" name="inscription" value="1">
                        
                        <div class="form-group">
                            <label for="nom">
                                <i class="fas fa-user"></i> Nom complet
                            </label>
                            <input type="text" id="nom" name="nom" 
                                   placeholder="RAVALISON Fitiavana Landry" required
                                   value="<?php echo isset($_POST['nom']) ? cleanInput($_POST['nom']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Adresse Email
                            </label>
                            <input type="email" id="email" name="email" 
                                   placeholder="votre@email.com" required
                                   value="<?php echo isset($_POST['email']) ? cleanInput($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i> Mot de passe
                            </label>
                            <input type="password" id="password" name="password" 
                                   placeholder="Minimum 6 caractères" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-lock"></i> Confirmer le mot de passe
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Répétez votre mot de passe" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Créer mon compte
                        </button>
                    </form>
                </div>
          
    
    <!-- DEBUG INFO (visible seulement avec ?debug dans l'URL) -->
    <?php if (isset($_GET['debug'])): ?>
    <div style="position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 15px; border-radius: 10px; max-width: 400px; z-index: 10000;">
        <h4 style="margin-bottom: 10px;">Debug Info</h4>
        <p>Session ID: <?php echo session_id(); ?></p>
        <p>Session Status: <?php echo session_status(); ?></p>
        <p>Cookies: <?php echo isset($_COOKIE['LEADER_SESSION']) ? 'Présent' : 'Absent'; ?></p>
        <p>User ID en session: <?php echo $_SESSION['user_id'] ?? 'Non défini'; ?></p>
    </div>
    <?php endif; ?>
    
    <script>
        // GESTION DES TABS
        function showForm(formType) {
            // Mettre à jour les boutons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Afficher le bon formulaire
            document.getElementById('loginForm').classList.remove('active');
            document.getElementById('registerForm').classList.remove('active');
            document.getElementById(formType + 'Form').classList.add('active');
        }
        
        // AUTO-REDIRECTION SI DÉJÀ CONNECTÉ
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier périodiquement si connecté
            setInterval(function() {
                fetch('check_login.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.loggedIn && window.location.pathname.includes('index.php')) {
                            window.location.href = 'dashboard.php';
                        }
                    });
            }, 2000);
            
            // Focus sur le premier champ
            const emailInput = document.querySelector('input[type="email"]');
            if (emailInput) emailInput.focus();
        });
        
        // ANIMATION DES CHAMPS
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>