<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store');

$loggedIn = !empty($_SESSION['coaph_user']);
$avatar   = null;

if ($loggedIn) {
    $username = $_SESSION['coaph_user']['username'];
    foreach (['jpg','png','webp','gif'] as $ext) {
        $path = __DIR__ . "/../assets/avatars/{$username}.{$ext}";
        if (file_exists($path)) {
            $avatar = "assets/avatars/{$username}.{$ext}";
            break;
        }
    }
}

echo json_encode([
    'loggedIn' => $loggedIn,
    'name'     => $_SESSION['coaph_user']['name']     ?? null,
    'username' => $_SESSION['coaph_user']['username'] ?? null,
    'role'     => $_SESSION['coaph_user']['role']     ?? null,
    'avatar'   => $avatar,
]);
