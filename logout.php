<?php
require_once 'config.php';

// DÉTRUIRE COMPLÈTEMENT LA SESSION
$_SESSION = array();

// DÉTRUIRE LE COOKIE DE SESSION
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// DÉTRUIRE LA SESSION
session_destroy();

// REDIRECTION ABSOLUE
redirect('index.php');
exit();
?>