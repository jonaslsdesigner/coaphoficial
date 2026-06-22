<?php
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$dataDir = dirname(__DIR__) . '/data/comments';
$ipFile  = dirname(__DIR__) . '/data/ip_usernames.json';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$post = preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['post'] ?? ''));
if (!$post) {
    http_response_code(400);
    echo json_encode(['error' => 'Post não especificado.']);
    exit();
}

$commentsFile = "$dataDir/$post.json";

function getClientIp(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            return trim(explode(',', $_SERVER[$key])[0]);
        }
    }
    return '0.0.0.0';
}

$ip = getClientIp();

/* ── GET: listar comentários + username salvo ─────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ipUsernames   = file_exists($ipFile) ? (json_decode(file_get_contents($ipFile), true) ?? []) : [];
    $savedUsername = $ipUsernames[$ip] ?? '';
    $comments      = file_exists($commentsFile) ? (json_decode(file_get_contents($commentsFile), true) ?? []) : [];

    echo json_encode(['comments' => $comments, 'savedUsername' => $savedUsername], JSON_UNESCAPED_UNICODE);
    exit();
}

/* ── POST: salvar comentário ──────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data     = json_decode(file_get_contents('php://input'), true) ?? [];
    $username = trim($data['username'] ?? '');
    $text     = trim($data['text']     ?? '');

    if (!$username || !$text) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome e comentário são obrigatórios.']);
        exit();
    }

    if (mb_strlen($username) > 50) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome muito longo (máx. 50 caracteres).']);
        exit();
    }

    if (mb_strlen($text) > 1500) {
        http_response_code(400);
        echo json_encode(['error' => 'Comentário muito longo (máx. 1500 caracteres).']);
        exit();
    }

    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $text     = htmlspecialchars($text,     ENT_QUOTES, 'UTF-8');

    /* Salva IP → username para pré-preencher da próxima vez */
    $ipUsernames        = file_exists($ipFile) ? (json_decode(file_get_contents($ipFile), true) ?? []) : [];
    $ipUsernames[$ip]   = $username;
    file_put_contents($ipFile, json_encode($ipUsernames, JSON_UNESCAPED_UNICODE), LOCK_EX);

    /* Salva o comentário */
    $comments   = file_exists($commentsFile) ? (json_decode(file_get_contents($commentsFile), true) ?? []) : [];
    $newComment = [
        'id'       => uniqid('c', true),
        'username' => $username,
        'text'     => $text,
        'date'     => date('Y-m-d H:i:s'),
    ];
    $comments[] = $newComment;
    file_put_contents($commentsFile, json_encode($comments, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);

    echo json_encode(['success' => true, 'comment' => $newComment], JSON_UNESCAPED_UNICODE);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Método não permitido.']);
