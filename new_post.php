<?php
// ============================================
// NEW_POST.PHP - Formulaire de publication
// ============================================

require_once 'config.php';

// VÉRIFIER SI ADMIN
if (!isLoggedIn()) {
    redirect('index.php');
    exit();
}

if (!isAdmin()) {
    echo '<script>alert("Accès réservé aux administrateurs"); window.location.href = "dashboard.php";</script>';
    exit();
}

// TRAITEMENT DU FORMULAIRE
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publier'])) {
    $titre = cleanInput($_POST['titre']);
    $contenu = cleanInput($_POST['contenu']);
    $categorie = cleanInput($_POST['categorie']);
    
    // Validation
    if (empty($titre) || empty($contenu)) {
        $message = "Le titre et le contenu sont obligatoires.";
        $message_type = 'error';
    } else {
        try {
            $pdo = getDB();
            
            // Insérer la publication
            $stmt = $pdo->prepare("INSERT INTO post (utilisateur_id, contenu) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $contenu]);
            
            // Récupérer l'ID de la publication
            $post_id = $pdo->lastInsertId();
            
            // Si un fichier est uploadé
            if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === 0) {
                $file = $_FILES['fichier'];
                
                // Vérifications de sécurité
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'application/pdf', 'text/plain'];
                $max_size = 10 * 1024 * 1024; // 10MB
                
                if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                    // Créer le dossier uploads s'il n'existe pas
                    $upload_dir = 'uploads/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Générer un nom de fichier unique
                    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $file_name = 'post_' . $post_id . '_' . time() . '.' . $file_ext;
                    $file_path = $upload_dir . $file_name;
                    
                    // Déplacer le fichier
                    if (move_uploaded_file($file['tmp_name'], $file_path)) {
                        // Mettre à jour la publication avec le chemin du fichier
                        $stmt = $pdo->prepare("UPDATE post SET fichier = ? WHERE id = ?");
                        $stmt->execute([$file_path, $post_id]);
                    }
                }
            }
            
            $message = "✅ Publication créée avec succès !";
            $message_type = 'success';
            
            // Réinitialiser le formulaire
            $_POST = array();
            
        } catch(PDOException $e) {
            $message = "❌ Erreur lors de la publication : " . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Publication - LEADER</title>
    <style>
        /* VARIABLES */
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --dark: #1d3557;
            --light: #f8f9fa;
            --gray: #6c757d;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --radius: 12px;
        }
        
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--dark);
            line-height: 1.6;
        }
        
        /* NAVBAR */
        .navbar {
            background: var(--primary);
            color: white;
            padding: 0 20px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-radius: 30px 30px 0 0;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }
        
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
        }
        
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .logout-btn {
            background: var(--danger);
        }
        
        .logout-btn:hover {
            background: #d1146c;
        }
        
        /* MAIN CONTAINER */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: flex;
            gap: 30px;
        }
        
        /* SIDEBAR */
        .sidebar {
            width: 280px;
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 90px;
        }
        
        .sidebar-header {
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light);
            margin-bottom: 25px;
        }
        
        .sidebar-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .nav-link {
            padding: 14px 18px;
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: var(--light);
            color: var(--primary);
            transform: translateX(5px);
        }
        
        .nav-link.active {
            background: var(--primary);
            color: white;
        }
        
        /* MAIN CONTENT */
        .main-content {
            flex: 1;
        }
        
        /* HEADER */
        .page-header {
            background: white;
            padding: 30px;
            border-radius: var(--radius);
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }
        
        .page-header h1 {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .page-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        /* FORM CONTAINER */
        .form-container {
            background: white;
            border-radius: var(--radius);
            padding: 40px;
            box-shadow: var(--shadow-lg);
        }
        
        /* MESSAGES */
        .alert {
            padding: 18px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease;
            border-left: 5px solid;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left-color: #4caf50;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left-color: #f44336;
        }
        
        .alert i {
            font-size: 1.5rem;
        }
        
        /* FORM STYLES */
        .form-group {
            margin-bottom: 30px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: #f8f9fa;
            font-family: inherit;
        }
        
        .form-group input[type="text"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            background-color: white;
        }
        
        textarea {
            min-height: 200px;
            resize: vertical;
            line-height: 1.6;
        }
        
        /* FILE UPLOAD */
        .file-upload-container {
            border: 2px dashed #c3cfe2;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: var(--transition);
            background-color: #f8f9fa;
            cursor: pointer;
        }
        
        .file-upload-container:hover {
            border-color: var(--primary);
            background-color: #f0f4ff;
            transform: translateY(-2px);
        }
        
        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }
        
        .upload-icon {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .file-upload-label h3 {
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .file-upload-label p {
            color: var(--gray);
            margin-bottom: 15px;
        }
        
        .file-types {
            background: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--gray);
            border: 1px solid #e0e0e0;
        }
        
        #file-input {
            display: none;
        }
        
        .preview-container {
            margin-top: 25px;
            padding: 20px;
            background: #f0f4ff;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            display: none;
        }
        
        .preview-container.show {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .preview-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .preview-content {
            max-width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .preview-content img,
        .preview-content video {
            max-width: 100%;
            max-height: 300px;
            display: block;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }
        
        .file-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .file-name {
            font-weight: 500;
            color: var(--dark);
        }
        
        .file-size {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .remove-file {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .remove-file:hover {
            background: #d1146c;
        }
        
        /* FORM ACTIONS */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn {
            padding: 16px 32px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-width: 150px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }
        
        .btn-secondary {
            background: #e9ecef;
            color: var(--gray);
        }
        
        .btn-secondary:hover {
            background: #dee2e6;
            transform: translateY(-3px);
        }
        
        .btn i {
            font-size: 1.2rem;
        }
        
        /* COUNTER */
        .counter {
            text-align: right;
            font-size: 0.9rem;
            color: var(--gray);
            margin-top: 5px;
        }
        
        .counter.warning {
            color: var(--warning);
        }
        
        .counter.danger {
            color: var(--danger);
        }
        
        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 25px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .file-upload-container {
                padding: 30px 20px;
            }
            
            .nav-container {
                flex-direction: column;
                height: auto;
                padding: 15px 0;
            }
            
            .nav-actions {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        
        /* ANIMATIONS */
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* LOADING */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading.show {
            display: block;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .campus{
            height: 70px;
            width: 70px;
            border-radius: 40px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-container">
            <img src="campus.jpg" class="campus">
            <a href="dashboard.php" class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                ISFPS LEADER 
            </a>
            
            <div class="nav-actions">
                <a href="dashboard.php" class="nav-btn">
                    <i class="fas fa-home"></i>
                    Tableau de bord
                </a>
                
                <div class="user-card" style="display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); padding: 8px 15px; border-radius: 8px;">
                    <div style="width: 35px; height: 35px; background: white; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        <?php echo strtoupper(substr($_SESSION['nom'], 0, 1)); ?>
                    </div>
                    <div style="color: white;">
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($_SESSION['nom']); ?></div>
                        <div style="font-size: 0.8rem; opacity: 0.9;">Administrateur</div>
                    </div>
                </div>
                
                <a href="logout.php" class="nav-btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </div>
        </div>
    </nav>
    
    <!-- MAIN CONTAINER -->
    <div class="container">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-cog"></i> Administration</h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="new_post.php" class="nav-link active">
                    <i class="fas fa-plus-circle"></i>
                    Nouvelle publication
                </a>
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-arrow-left"></i>
                    Retour 
            </nav>
        </div>
        
        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-edit pulse"></i>
                    Nouvelle publication
                </h1>
                <p>Rédigez et publiez du contenu pour la communauté LEADER</p>
            </div>
            
            <!-- ALERT MESSAGES -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php if ($message_type === 'success'): ?>
                        <i class="fas fa-check-circle"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle"></i>
                    <?php endif; ?>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- FORM CONTAINER -->
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" id="publication-form">
                    <!-- TITRE -->
                    <div class="form-group">
                        <label for="titre">
                            <i class="fas fa-heading"></i>
                            Titre de la publication
                        </label>
                        <input type="text" id="titre" name="titre" 
                               placeholder="Donnez un titre accrocheur à votre publication"
                               value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>"
                               required maxlength="200">
                        <div class="counter" id="titre-counter">0/200</div>
                    </div>
                    
                    <!-- CATÉGORIE -->
                    <div class="form-group">
                        <label for="categorie">
                            <i class="fas fa-tag"></i>
                            Catégorie
                        </label>
                        <select id="categorie" name="categorie" required>
                            <option value="">Sélectionnez une catégorie</option>
                            <option value="annonce" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] === 'annonce') ? 'selected' : ''; ?>>📢 Annonce officielle</option>
                            <option value="evenement" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] === 'evenement') ? 'selected' : ''; ?>>🎉 Événement</option>
                            <option value="academique" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] === 'academique') ? 'selected' : ''; ?>>📚 Académique</option>
                            <option value="culture" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] === 'culture') ? 'selected' : ''; ?>>🎭 Culture & Loisirs</option>
                            <option value="sport" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] === 'sport') ? 'selected' : ''; ?>>⚽ Sports</option>
                            <option value="emploi" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] === 'emploi') ? 'selected' : ''; ?>>💼 Offres d'emploi</option>
                        </select>
                    </div>
                    
                    <!-- CONTENU -->
                    <div class="form-group">
                        <label for="contenu">
                            <i class="fas fa-align-left"></i>
                            Contenu de la publication
                        </label>
                        <textarea id="contenu" name="contenu" 
                                  placeholder="Rédigez votre contenu ici... Vous pouvez utiliser des sauts de ligne pour structurer votre texte."
                                  required><?php echo isset($_POST['contenu']) ? htmlspecialchars($_POST['contenu']) : ''; ?></textarea>
                        <div class="counter" id="contenu-counter">0/5000</div>
                    </div>
                    
                    <!-- FICHIER -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-paperclip"></i>
                            Ajouter un fichier (optionnel)
                        </label>
                        <div class="file-upload-container" onclick="document.getElementById('file-input').click()">
                            <div class="file-upload-label">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <h3>Glissez-déposez ou cliquez pour ajouter un fichier</h3>
                                <p>Images, vidéos, PDF, documents texte</p>
                                <div class="file-types">
                                    <span>JPG, PNG, GIF, MP4, PDF, TXT, DOC</span>
                                </div>
                            </div>
                            <input type="file" id="file-input" name="fichier" accept="image/*,video/*,.pdf,.txt,.doc,.docx">
                        </div>
                        <div class="preview-container" id="preview-container">
                            <div class="preview-title">
                                <i class="fas fa-eye"></i>
                                Aperçu du fichier
                            </div>
                            <div class="preview-content" id="preview-content"></div>
                            <div class="file-info" id="file-info">
                                <div>
                                    <div class="file-name" id="file-name"></div>
                                    <div class="file-size" id="file-size"></div>
                                </div>
                                <button type="button" class="remove-file" onclick="removeFile()">
                                    <i class="fas fa-times"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FORM ACTIONS -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                            <i class="fas fa-arrow-left"></i>
                            Annuler
                        </button>
                        
                        <button type="submit" name="publier" class="btn btn-primary" id="submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            Publier maintenant
                        </button>
                    </div>
                </form>
                
                <!-- LOADING -->
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Publication en cours...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // COMPTEUR DE CARACTÈRES
        const titreInput = document.getElementById('titre');
        const titreCounter = document.getElementById('titre-counter');
        const contenuInput = document.getElementById('contenu');
        const contenuCounter = document.getElementById('contenu-counter');
        
        titreInput.addEventListener('input', function() {
            const length = this.value.length;
            titreCounter.textContent = `${length}/200`;
            titreCounter.className = 'counter ' + (length > 180 ? 'warning' : length > 200 ? 'danger' : '');
        });
        
        contenuInput.addEventListener('input', function() {
            const length = this.value.length;
            contenuCounter.textContent = `${length}/5000`;
            contenuCounter.className = 'counter ' + (length > 4500 ? 'warning' : length > 5000 ? 'danger' : '');
        });
        
        // Initialiser les compteurs
        titreInput.dispatchEvent(new Event('input'));
        contenuInput.dispatchEvent(new Event('input'));
        
        // GESTION DU FICHIER
        const fileInput = document.getElementById('file-input');
        const previewContainer = document.getElementById('preview-container');
        const previewContent = document.getElementById('preview-content');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const fileInfo = document.getElementById('file-info');
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileType = file.type.split('/')[0];
                
                // Vérifier la taille (max 10MB)
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('Le fichier est trop volumineux. Taille maximum : 10MB');
                    this.value = '';
                    return;
                }
                
                // Afficher les infos du fichier
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                
                // Afficher l'aperçu
                previewContainer.classList.add('show');
                
                if (fileType === 'image') {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContent.innerHTML = `<img src="${e.target.result}" alt="Aperçu de l'image">`;
                    }
                    reader.readAsDataURL(file);
                } else if (fileType === 'video') {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContent.innerHTML = `
                            <video controls>
                                <source src="${e.target.result}" type="${file.type}">
                                Votre navigateur ne supporte pas la lecture de vidéos.
                            </video>
                        `;
                    }
                    reader.readAsDataURL(file);
                } else {
                    previewContent.innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <i class="fas fa-file" style="font-size: 60px; color: #4361ee; margin-bottom: 20px;"></i>
                            <h3 style="color: #1d3557; margin-bottom: 10px;">${file.name}</h3>
                            <p style="color: #6c757d;">Type: ${file.type}</p>
                        </div>
                    `;
                }
            }
        });
        
        // Fonction pour formater la taille du fichier
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Fonction pour supprimer le fichier
        function removeFile() {
            fileInput.value = '';
            previewContainer.classList.remove('show');
        }
        
        // Drag and drop
        const dropZone = document.querySelector('.file-upload-container');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropZone.style.borderColor = '#4361ee';
            dropZone.style.backgroundColor = '#f0f4ff';
        }
        
        function unhighlight() {
            dropZone.style.borderColor = '#c3cfe2';
            dropZone.style.backgroundColor = '#f8f9fa';
        }
        
        dropZone.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }
        
        // VALIDATION DU FORMULAIRE
        const form = document.getElementById('publication-form');
        const loading = document.getElementById('loading');
        const submitBtn = document.getElementById('submit-btn');
        
        form.addEventListener('submit', function(e) {
            // Validation
            const titre = titreInput.value.trim();
            const contenu = contenuInput.value.trim();
            
            if (!titre) {
                alert('Veuillez saisir un titre pour votre publication.');
                titreInput.focus();
                e.preventDefault();
                return;
            }
            
            if (!contenu) {
                alert('Veuillez saisir le contenu de votre publication.');
                contenuInput.focus();
                e.preventDefault();
                return;
            }
            
            if (titre.length > 200) {
                alert('Le titre ne doit pas dépasser 200 caractères.');
                titreInput.focus();
                e.preventDefault();
                return;
            }
            
            if (contenu.length > 5000) {
                alert('Le contenu ne doit pas dépasser 5000 caractères.');
                contenuInput.focus();
                e.preventDefault();
                return;
            }
            
            // Afficher le loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication...';
            loading.classList.add('show');
        });
        
        // AUTO-SAVE DRAFT (optionnel)
        let saveTimeout;
        function autoSave() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                const draft = {
                    titre: titreInput.value,
                    contenu: contenuInput.value,
                    categorie: document.getElementById('categorie').value,
                    timestamp: new Date().getTime()
                };
                localStorage.setItem('post_draft', JSON.stringify(draft));
                console.log('Brouillon sauvegardé');
            }, 2000);
        }
        
        [titreInput, contenuInput].forEach(input => {
            input.addEventListener('input', autoSave);
        });
        
        // CHARGER LE BROUILLON AU CHARGEMENT
        document.addEventListener('DOMContentLoaded', function() {
            const draft = localStorage.getItem('post_draft');
            if (draft) {
                const data = JSON.parse(draft);
                if (new Date().getTime() - data.timestamp < 24 * 60 * 60 * 1000) { // 24h
                    if (confirm('Un brouillon non sauvegardé a été trouvé. Voulez-vous le charger ?')) {
                        titreInput.value = data.titre || '';
                        contenuInput.value = data.contenu || '';
                        document.getElementById('categorie').value = data.categorie || '';
                        
                        // Déclencher les événements input
                        titreInput.dispatchEvent(new Event('input'));
                        contenuInput.dispatchEvent(new Event('input'));
                    }
                }
            }
        });
        
        // NETTOYER LE BROUILLON APRÈS ENVOI
        window.addEventListener('beforeunload', function() {
            const form = document.getElementById('publication-form');
            if (!form.classList.contains('submitted')) {
                autoSave();
            }
        });
        
        // Marquer le formulaire comme soumis
        form.addEventListener('submit', function() {
            localStorage.removeItem('post_draft');
            form.classList.add('submitted');
        });
    </script>
</body>
</html>