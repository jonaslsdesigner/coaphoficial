<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store');

if (empty($_SESSION['coaph_user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado.']);
    exit();
}

$username = $_SESSION['coaph_user']['username'];
$dir      = __DIR__ . '/../assets/avatars/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'Nenhum arquivo enviado.']);
        exit();
    }

    $file = $_FILES['avatar'];
    $mime = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    if (!isset($allowed[$mime])) {
        echo json_encode(['error' => 'Formato inválido. Use JPG, PNG, WEBP ou GIF.']);
        exit();
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['error' => 'Arquivo muito grande. Máximo 2 MB.']);
        exit();
    }

    // Remove avatar anterior
    foreach (['jpg','png','webp','gif'] as $ext) {
        $old = $dir . "{$username}.{$ext}";
        if (file_exists($old)) unlink($old);
    }

    $ext     = $allowed[$mime];
    $dest    = $dir . "{$username}.{$ext}";
    move_uploaded_file($file['tmp_name'], $dest);

    echo json_encode(['avatar' => "assets/avatars/{$username}.{$ext}"]);
    exit();
}

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    foreach (['jpg','png','webp','gif'] as $ext) {
        $f = $dir . "{$username}.{$ext}";
        if (file_exists($f)) unlink($f);
    }
    echo json_encode(['avatar' => null]);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Método não permitido.']);
