<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['coaph_user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$name  = trim($input['name'] ?? '');

if ($name === '' || mb_strlen($name) > 80) {
    echo json_encode(['error' => 'Nome inválido.']);
    exit();
}

$file  = __DIR__ . '/../data/users.json';
$users = json_decode(file_get_contents($file), true) ?? [];
$username = $_SESSION['coaph_user']['username'];

foreach ($users as &$u) {
    if ($u['username'] === $username) {
        $u['name'] = $name;
        break;
    }
}

file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$_SESSION['coaph_user']['name'] = $name;

echo json_encode(['ok' => true, 'name' => $name]);
