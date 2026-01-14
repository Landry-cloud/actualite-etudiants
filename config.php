<?php
// ============================================
// CONFIGURATION ULTIME - TOUT FIXÉ D'ICI
// ============================================

// 1. CONFIGURATION DES SESSIONS AVANT TOUT
session_name('LEADER_SESSION'); // Nom personnalisé
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_lifetime', 86400); // 24h
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 86400);

// 2. DÉMARRER LA SESSION AVANT TOUT AUTRE CODE
if (session_status() === PHP_SESSION_NONE) {
    // Forcer un nouveau démarrage
    if (isset($_COOKIE['LEADER_SESSION'])) {
        session_id($_COOKIE['LEADER_SESSION']);
    }
    session_start();
    
    // Régénérer l'ID pour la sécurité
    if (!isset($_SESSION['created'])) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// 3. BASE DE DONNÉES
define('DB_HOST', 'localhost');
define('DB_NAME', 'leader');
define('DB_USER', 'root');
define('DB_PASS', '');

// 4. FONCTION CONNEXION BDD (SIMPLE)
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        // En production, loguer l'erreur
        die("Erreur connexion BDD. Contactez l'admin.");
    }
}

// 5. FONCTION SÉCURITÉ
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// 6. VÉRIFICATION CONNEXION
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// 7. VÉRIFICATION ADMIN
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// 8. CRÉER ADMIN PAR DÉFAUT (À EXÉCUTER UNE SEULE FOIS)
function createDefaultAdmin() {
    try {
        $pdo = getDB();
        
        // Vérifier si existe
        $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
        $stmt->execute(['admin@leader.com']);
        
        if (!$stmt->fetch()) {
            // Créer admin
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, email, motdepasse, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute(['Administrateur', 'admin@leader.com', $hash]);
            return true;
        }
    } catch(Exception $e) {
        // Ignorer en silence
    }
    return false;
}

// DÉCOMMENTEZ POUR CRÉER L'ADMIN, PUIS RECOMMENTEZ
// createDefaultAdmin();

// 9. FONCTION REDIRECTION SÉCURISÉE
function redirect($url) {
    // Nettoyer l'URL
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    // Plusieurs méthodes pour assurer la redirection
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        echo '<script>window.location.href="' . $url . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '"></noscript>';
        exit();
    }
}

// 10. FONCTION DEBUG (À ENLEVER EN PRODUCTION)
function debug($data) {
    if (isset($_GET['debug'])) {
        echo '<pre style="background:#f0f0f0;padding:10px;border:1px solid #ccc;">';
        print_r($data);
        echo '</pre>';
    }
}

// Debug session
debug($_SESSION);
?>