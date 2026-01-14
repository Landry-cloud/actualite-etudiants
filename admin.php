<?php
// 📌 DASHBOARD ADMINISTRATEUR
require_once 'config.php';

// Vérifier si l'utilisateur est admin
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

// 📌 MÉTHODE POST : Publier une nouvelle publication
if (isset($_POST['new_post'])) {
    $contenu = trim($_POST['contenu']);
    
    if (!empty($contenu)) {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO post (utilisateur_id, contenu) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $contenu]);
        header('Location: admin.php');
        exit();
    }
}

// 📌 MÉTHODE POST : Supprimer un utilisateur
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    
    // Empêcher de se supprimer soi-même
    if ($user_id != $_SESSION['user_id']) {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    header('Location: admin.php');
    exit();
}

// 📌 MÉTHODE PROCÉDURALE : Récupérer tous les utilisateurs
function getAllUsers() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT id, nom, email, role, created_at FROM utilisateur ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

// 📌 MÉTHODE PROCÉDURALE : Statistiques
function getStats() {
    $pdo = getDB();
    
    $stats = [
        'total_users' => 0,
        'total_posts' => 0,
        'total_comments' => 0,
        'total_admins' => 0,
        'total_students' => 0
    ];
    
    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count, role FROM utilisateur GROUP BY role");
    while ($row = $stmt->fetch()) {
        $stats['total_users'] += $row['count'];
        if ($row['role'] === 'admin') {
            $stats['total_admins'] = $row['count'];
        } else {
            $stats['total_students'] = $row['count'];
        }
    }
    
    // Compter les publications
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM post");
    $stats['total_posts'] = $stmt->fetch()['count'];
    
    // Compter les commentaires
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM commentaire");
    $stats['total_comments'] = $stmt->fetch()['count'];
    
    return $stats;
}

$users = getAllUsers();
$stats = getStats();
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - LEADER</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar admin-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-shield-alt"></i>
                <span>ADMIN LEADER</span>
            </div>
            
            <div class="nav-items">
                
                <a href="admin.php" class="nav-link active">
                    <i class="fas fa-cog"></i> Administration
                </a>
                
                <!-- Thème -->
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon">🌙🌞</i>
                </button>
                
                <!-- Déconnexion -->
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container admin-container">
        <!-- Sidebar Admin -->
        <aside class="admin-sidebar">
            <div class="admin-info">
                <div class="avatar large">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3><?php echo ($_SESSION['nom']); ?></h3>
                <span class="role-badge admin">Administrateur</span>
            </div>
            
            <nav class="admin-nav-menu">
                <a href="#stats" class="nav-item active">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistiques</span>
                </a>
                <a href="#users" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
                <a href="#posts" class="nav-item">
                    <i class="fas fa-newspaper"></i>
                    <span>Publications</span>
                </a>
                <a href="#settings" class="nav-item">
                    <i class="fas fa-cogs"></i>
                    <span>Paramètres</span>
                </a>
            </nav>
        </aside>

        <!-- Contenu principal Admin -->
        <main class="admin-content">
            <!-- En-tête -->
            <header class="admin-header">
                <h1><i class="fas fa-shield-alt"></i> Panneau d'administration</h1>
                <p>Gérez la plateforme LEADER</p>
            </header>

            <!-- Cartes statistiques -->
            <div class="stats-cards" id="stats">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Utilisateurs</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_students']; ?></h3>
                        <p>Étudiants</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_admins']; ?></h3>
                        <p>Administrateurs</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_posts']; ?></h3>
                        <p>Publications</p>
                    </div>
                </div>
            </div>

            <!-- Section utilisateurs -->
            <div class="admin-section" id="users">
                
                </div>
                
                <div class="table-container">
                    <h2>Voici tous les utilisateurs</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="avatar small">
                                                <?php echo strtoupper(substr($user['nom'], 0, 1)); ?>
                                            </div>
                                            <?php echo ($user['nom']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo ($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge <?php echo $user['role']; ?>">
                                            <?php echo $user['role'] === 'admin' ? 'Admin' : 'Étudiant'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="admin.php?delete_user=<?php echo $user['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Supprimer cet utilisateur ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Vous</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section nouvelle publication -->
            <div class="admin-section" id="posts">
                <div class="section-header">
                    <h2><i class="fas fa-newspaper"></i> Publier une annonce</h2>
                </div>
                
                <div class="new-post-form">
                    <form method="POST">
                        <div class="form-group">
                            <textarea name="contenu" placeholder="Écrivez votre annonce ici..." rows="4" required></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="new_post" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Publier l'annonce
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal ajout utilisateur -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" id="newUserName" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="newUserEmail" required>
                    </div>
                    <div class="form-group">
                        <label>Rôle</label>
                        <select id="newUserRole">
                            <option value="etudiant">Étudiant</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" id="newUserPassword" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal()">Annuler</button>
                <button class="btn btn-primary" onclick="addUser()">Ajouter</button>
            </div>
        </div>
    </div>
    

    <script>
        // 📌 JAVASCRIPT : Thème
        function toggleTheme() {
            const body = document.body;
            const icon = document.querySelector('.theme-toggle i');
            
            if (body.getAttribute('data-theme') === 'light') {
                body.setAttribute('data-theme', 'dark');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                body.setAttribute('data-theme', 'light');
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
            
            localStorage.setItem('theme', body.getAttribute('data-theme'));
        }

        // 📌 JAVASCRIPT : Modal utilisateur
        function showUserForm() {
            document.getElementById('userModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('show');
        }

        function addUser() {
            alert('Cette fonctionnalité sera ajoutée dans la prochaine version !');
            closeModal();
        }

        // Fermer modal en cliquant à l'extérieur
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeModal();
            }
        });

        // Charger le thème sauvegardé
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            
            const icon = document.querySelector('.theme-toggle i');
            if (savedTheme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
        });
    </script>
</body>
</html>