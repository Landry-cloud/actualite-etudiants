<?php
// ============================================
// DASHBOARD ÉTUDIANT - Version simplifiée
// ============================================

require_once 'config.php';

// VÉRIFIER SI CONNECTÉ
if (!isLoggedIn()) {
    redirect('index.php');
    exit();
}

// SI ADMIN, REDIRIGER VERS DASHBOARD ADMIN
if (isAdmin()) {
    redirect('admin.php');
    exit();
}

// RÉCUPÉRER LES DONNÉES UTILISATEUR
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nom'];
$user_role = 'etudiant';

// FONCTION POUR RÉCUPÉRER LES PUBLICATIONS
function getStudentPosts() {
    try {
        $pdo = getDB();
        $stmt = $pdo->query("
            SELECT p.*, u.nom as auteur 
            FROM post p 
            JOIN utilisateur u ON p.utilisateur_id = u.id 
            ORDER BY p.created_at DESC
            LIMIT 20
        ");
        return $stmt->fetchAll();
    } catch(Exception $e) {
        return [];
    }
}

// TRAITEMENT COMMENTAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $post_id = intval($_POST['post_id']);
    $contenu = cleanInput($_POST['contenu']);
    
    if (!empty($contenu) && $post_id > 0) {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("INSERT INTO commentaire (post_id, utilisateur_id, contenu) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $user_id, $contenu]);
            
            // Redirection pour éviter double soumission
            redirect('dashboard.php');
            exit();
        } catch(Exception $e) {
            $error_message = "Erreur lors de l'ajout du commentaire";
        }
    }
}

// TRAITEMENT RÉACTION
if (isset($_GET['react'])) {
    $post_id = intval($_GET['post_id']);
    $reaction_type = cleanInput($_GET['type']);
    
    if ($post_id > 0 && in_array($reaction_type, ['like', 'love', 'haha', 'wow', 'sad', 'angry'])) {
        try {
            $pdo = getDB();
            
            // Vérifier si déjà réagi
            $check = $pdo->prepare("SELECT id FROM reaction WHERE entite_type = 'post' AND entite_id = ? AND utilisateur_id = ?");
            $check->execute([$post_id, $user_id]);
            
            if ($check->fetch()) {
                // Mettre à jour
                $stmt = $pdo->prepare("UPDATE reaction SET type_reaction = ? WHERE entite_type = 'post' AND entite_id = ? AND utilisateur_id = ?");
                $stmt->execute([$reaction_type, $post_id, $user_id]);
            } else {
                // Insérer
                $stmt = $pdo->prepare("INSERT INTO reaction (entite_type, entite_id, utilisateur_id, type_reaction) VALUES ('post', ?, ?, ?)");
                $stmt->execute([$post_id, $user_id, $reaction_type]);
            }
            
            redirect('dashboard.php');
            exit();
        } catch(Exception $e) {
            $error_message = "Erreur lors de l'ajout de la réaction";
        }
    }
}

$posts = getStudentPosts();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Étudiant - LEADER</title>

    <!-- Fichier CSS hors ligne -->
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <style>
        /* =======================
           RESET ET POLICE
        ======================= */
        * {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
        body {min-height:100vh;color:#333;transition: all 0.3s;}

        /* THEME */
        body.light-theme {background: linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);color:#333;}
        body.dark-theme {background:#1e1e2f;color:#eee;}
        body.dark-theme .student-header {background: linear-gradient(135deg,#4e54c8 0%,#8f94fb 100%);}
        body.dark-theme .welcome-card,
        body.dark-theme .post-card,
        body.dark-theme .comment,
        body.dark-theme .comments-section {background:#2b2b3a;color:#eee;}
        body.dark-theme .logout-btn {background:#8f94fb;color:white;}
        body.dark-theme .reaction-btn {background:#3a3a4f;border-color:#555;color:#eee;}
        body.dark-theme .reaction-btn:hover {background:#4e54c8;border-color:#8f94fb;color:white;}
        body.dark-theme textarea {background:#3a3a4f;border:2px solid #555;color:#eee;}

        /* HEADER ÉTUDIANT */
        .student-header {background: linear-gradient(135deg,#4361ee 0%,#3a0ca3 100%);color:white;padding:15px 30px;box-shadow:0 4px 12px rgba(0,0,0,0.1);}
        .header-content {max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;}
        .logo {display:flex;align-items:center;gap:10px;font-size:24px;font-weight:bold;}
        .logo i {font-size:28px;}
        .user-menu {display:flex;align-items:center;gap:20px;}
        .student-info {display:flex;align-items:center;gap:12px;padding:8px 15px;background:rgba(255,255,255,0.1);border-radius:25px;}
        .student-avatar {width:40px;height:40px;background:white;color:#4361ee;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:18px;}
        .student-name {font-weight:600;}
        .student-badge {background:#4cc9f0;padding:3px 10px;border-radius:15px;font-size:12px;font-weight:bold;}
        .logout-btn {background:white;color:#4361ee;padding:8px 20px;border-radius:20px;text-decoration:none;font-weight:600;transition:all 0.3s;}
        .logout-btn:hover {background:#f0f2ff;transform:translateY(-2px);}
        .theme-toggle {cursor:pointer;padding:8px 15px;background:#4cc9f0;color:white;border-radius:20px;font-weight:600;transition:0.3s;}
        .theme-toggle:hover {opacity:0.8;}

        /* MAIN CONTENT */
        .container {max-width:1200px;margin:30px auto;padding:0 20px;}
        .welcome-card {background:white;padding:30px;border-radius:15px;margin-bottom:30px;box-shadow:0 5px 20px rgba(0,0,0,0.05);border-left:5px solid #4cc9f0;}
        .welcome-card h1 {color:#4361ee;margin-bottom:10px;display:flex;align-items:center;gap:15px;}
        .welcome-card h1 i {color:#4cc9f0;}
        .section-title {color:#4361ee;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
        .posts-container {display:grid;gap:20px;}
        .post-card {background:white;border-radius:12px;overflow:hidden;box-shadow:0 3px 15px rgba(0,0,0,0.08);transition:all 0.3s;animation:fadeIn 0.5s ease-out;}
        .post-header {padding:20px;border-bottom:1px solid #eee;display:flex;align-items:center;gap:15px;}
        .author-avatar {width:45px;height:45px;background:linear-gradient(135deg,#4361ee,#3a0ca3);color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:18px;}
        .author-info {flex:1;}
        .author-name {font-weight:600;color:#333;}
        .post-date {font-size:14px;color:#666;display:flex;align-items:center;gap:5px;}
        .post-content {padding:20px;line-height:1.6;white-space:pre-line;}
        .reactions-bar {padding:15px 20px;border-top:1px solid #eee;display:flex;gap:10px;align-items:center;}
        .reaction-btn {padding:8px 15px;border:2px solid #eee;background:white;border-radius:20px;cursor:pointer;display:flex;align-items:center;gap:8px;transition:all 0.3s;font-size:14px;}
        .reaction-btn:hover {border-color:#4361ee;background:#f0f2ff;}
        .reaction-emoji {font-size:18px;}
        .reaction-count {margin-left:auto;color:#666;font-size:14px;}
        .comments-section {padding:20px;border-top:1px solid #eee;background:#f8f9fa;}
        .comment-form {margin-bottom:20px;}
        .comment-form textarea {width:100%;padding:12px;border:2px solid #ddd;border-radius:8px;resize:vertical;margin-bottom:10px;font-family:inherit;}
        .comment-btn {background:#4361ee;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:8px;}
        .comments-list {display:flex;flex-direction:column;gap:15px;}
        .comment {background:white;padding:15px;border-radius:8px;border-left:3px solid #4cc9f0;}
        .comment-author {font-weight:600;margin-bottom:5px;display:flex;align-items:center;gap:10px;}
        .comment-time {font-size:12px;color:#666;}
        .empty-state {text-align:center;padding:60px 20px;background:white;border-radius:15px;box-shadow:0 5px 20px rgba(0,0,0,0.05);}
        .empty-icon {font-size:60px;color:#ddd;margin-bottom:20px;}

        @media(max-width:768px){.header-content{flex-direction:column;gap:15px;text-align:center;}.user-menu{flex-direction:column;gap:10px;}.container{padding:0 15px;}.reactions-bar{flex-wrap:wrap;}}

        @keyframes fadeIn {from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
    </style>
</head>
<body class="light-theme">
    <!-- HEADER ÉTUDIANT -->
    <header class="student-header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                LEADER - Espace Étudiant
            </div>
            
            <div class="user-menu">
                <div class="student-info">
                    <div class="student-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div>
                        <div class="student-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="student-badge">Étudiant</div>
                    </div>
                </div>
                
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>

                <!-- TOGGLE THEME -->
                <div class="theme-toggle" id="themeToggle">
                    <i class="fas fa-adjust"></i> Thème
                </div>
            </div>
        </div>
    </header>
    
    <!-- MAIN CONTENT -->
    <div class="container">
        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h1>
                <i class="fas fa-hand-wave"></i>
                Bienvenue, <?php echo htmlspecialchars($user_name); ?> !
            </h1>
            <p>Retrouvez ici toutes les publications de la communauté étudiante. Partagez, réagissez et commentez !</p>
        </div>
        
        <!-- POSTS SECTION -->
        <h2 class="section-title">
            <i class="fas fa-newspaper"></i>
            Actualités récentes
        </h2>
        
        <div class="posts-container">
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3>Aucune publication pour le moment</h3>
                    <p>Soyez le premier à partager quelque chose !</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card" id="post-<?php echo $post['id']; ?>">
                        <div class="post-header">
                            <div class="author-avatar">
                                <?php echo strtoupper(substr($post['auteur'], 0, 1)); ?>
                            </div>
                            <div class="author-info">
                                <div class="author-name"><?php echo htmlspecialchars($post['auteur']); ?></div>
                                <div class="post-date">
                                    <i class="far fa-clock"></i>
                                    <?php 
                                        $date = new DateTime($post['created_at']);
                                        echo $date->format('d/m/Y à H:i');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['contenu'])); ?>
                        </div>
                        <div class="reactions-bar">
                            <?php
                            $reactions = ['like'=>'👍','love'=>'❤️','haha'=>'😄','wow'=>'😮','sad'=>'😢','angry'=>'😠'];
                            foreach($reactions as $type => $emoji):
                            ?>
                            <a href="dashboard.php?react=1&post_id=<?php echo $post['id']; ?>&type=<?php echo $type; ?>" class="reaction-btn">
                                <span class="reaction-emoji"><?php echo $emoji; ?></span>
                                <span><?php echo ucfirst($type); ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="comments-section">
                            <form method="POST" class="comment-form">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <textarea name="contenu" placeholder="Ajouter un commentaire..." rows="2" required></textarea>
                                <button type="submit" name="add_comment" class="comment-btn">
                                    <i class="fas fa-paper-plane"></i> Commenter
                                </button>
                            </form>

                            <?php
                            try {
                                $pdo = getDB();
                                $stmt = $pdo->prepare("
                                    SELECT c.*, u.nom as auteur 
                                    FROM commentaire c 
                                    JOIN utilisateur u ON c.utilisateur_id = u.id 
                                    WHERE c.post_id = ? 
                                    ORDER BY c.created_at DESC
                                ");
                                $stmt->execute([$post['id']]);
                                $comments = $stmt->fetchAll();
                            } catch(Exception $e) {
                                $comments = [];
                            }
                            ?>
                            <?php if (!empty($comments)): ?>
                                <div class="comments-list">
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="comment">
                                            <div class="comment-author">
                                                <strong><?php echo htmlspecialchars($comment['auteur']); ?></strong>
                                                <span class="comment-time">
                                                    <?php echo date('H:i', strtotime($comment['created_at'])); ?>
                                                </span>
                                            </div>
                                            <p><?php echo nl2br(htmlspecialchars($comment['contenu'])); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/fontawesome.min.js"></script>
    <script>
        // TOGGLE THEME
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        themeToggle.addEventListener('click', () => {
            if(body.classList.contains('light-theme')){
                body.classList.remove('light-theme');
                body.classList.add('dark-theme');
            } else {
                body.classList.remove('dark-theme');
                body.classList.add('light-theme');
            }
        });

        // AUTRE SCRIPTS EXISTANTS
        setInterval(() => {
            fetch('check_login.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.loggedIn) {
                        window.location.href = 'index.php';
                    }
                });
        }, 30000);

        document.querySelectorAll('.post-card').forEach(card => {
            card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-5px)');
            card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
        });

        document.querySelectorAll('.comment-form textarea').forEach(textarea => {
            textarea.addEventListener('focus', function() { this.parentElement.style.borderLeft = '3px solid #4361ee'; });
            textarea.addEventListener('blur', function() { this.parentElement.style.borderLeft = '3px solid #4cc9f0'; });
            textarea.addEventListener('input', function(){ this.style.height='auto'; this.style.height=this.scrollHeight+'px'; });
        });

        document.querySelectorAll('.reaction-btn').forEach(btn => {
            btn.addEventListener('click', function(e){
                if(!confirm('Ajouter cette réaction ?')) e.preventDefault();
            });
        });
    </script>
</body>
</html>
