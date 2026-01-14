<?php
require_once 'config.php';

$response = [
    'loggedIn' => isLoggedIn(),
    'userId' => $_SESSION['user_id'] ?? null,
    'userName' => $_SESSION['nom'] ?? null,
    'sessionId' => session_id()
];

header('Content-Type: application/json');
echo json_encode($response);
?>