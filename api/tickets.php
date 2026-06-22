<?php
ini_set('display_errors', 0);
error_reporting(0);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

function isAdmin(): bool {
    return !empty($_SESSION['coaph_user']);
}

function requireAdmin(): void {
    if (!isAdmin()) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autorizado.']);
        exit();
    }
}

$ticketsDir = dirname(__DIR__) . '/data/tickets';
$attachDir  = $ticketsDir . '/attachments';

foreach ([$ticketsDir, $attachDir] as $d) {
    if (!is_dir($d)) @mkdir($d, 0755, true);
}

/* ── Helpers ─────────────────────────────────────────────────── */

function generateProtocol(): string {
    return 'COAPH' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function ticketPath(string $protocol): string {
    global $ticketsDir;
    $safe = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($protocol));
    return "{$ticketsDir}/{$safe}.json";
}

function readTicket(string $protocol): ?array {
    $f = ticketPath($protocol);
    return file_exists($f) ? (json_decode(file_get_contents($f), true) ?? null) : null;
}

function saveTicket(array $t): void {
    file_put_contents(ticketPath($t['protocol']),
        json_encode($t, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

function handleFiles(string $key): array {
    global $attachDir;
    if (empty($_FILES[$key])) return [];

    $allowed = ['jpg','jpeg','png','gif','pdf','doc','docx','mp4','mov','wav','zip'];
    $files   = $_FILES[$key];
    $multi   = is_array($files['name']);
    $n       = $multi ? count($files['name']) : 1;
    $result  = [];

    for ($i = 0; $i < min($n, 3); $i++) {
        $err  = $multi ? $files['error'][$i]    : $files['error'];
        $size = $multi ? $files['size'][$i]     : $files['size'];
        $tmp  = $multi ? $files['tmp_name'][$i] : $files['tmp_name'];
        $name = $multi ? $files['name'][$i]     : $files['name'];

        if ($err !== UPLOAD_ERR_OK || $size > 16 * 1024 * 1024) continue;

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;

        $fname = uniqid('att_') . '.' . $ext;
        if (move_uploaded_file($tmp, "{$attachDir}/{$fname}")) {
            $result[] = [
                'name' => htmlspecialchars(basename($name), ENT_QUOTES, 'UTF-8'),
                'file' => $fname,
                'size' => $size,
            ];
        }
    }
    return $result;
}

function formatSize(int $bytes): string {
    if ($bytes < 1024) return "{$bytes} B";
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}

/* ── Routes ──────────────────────────────────────────────────── */

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

/* GET ticket */
if ($method === 'GET' && $action === 'get') {
    $proto = strtoupper(trim($_GET['protocol'] ?? ''));
    if (!$proto) { http_response_code(400); echo json_encode(['error' => 'Protocolo não informado.']); exit(); }

    $ticket = readTicket($proto);
    if (!$ticket) { http_response_code(404); echo json_encode(['error' => 'Chamado não encontrado. Verifique o número de protocolo.']); exit(); }

    echo json_encode($ticket, JSON_UNESCAPED_UNICODE);
    exit();
}

/* POST */
if ($method === 'POST') {
    $isJson = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    $body   = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
    $action = $body['action'] ?? $action;

    /* CREATE */
    if ($action === 'create') {
        $category  = htmlspecialchars(trim($body['category']    ?? ''), ENT_QUOTES, 'UTF-8');
        $subject   = htmlspecialchars(trim($body['subject']     ?? ''), ENT_QUOTES, 'UTF-8');
        $desc      = htmlspecialchars(trim($body['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $anon      = !empty($body['anonymous']);

        if (!$category || !$subject || !$desc) {
            http_response_code(400);
            echo json_encode(['error' => 'Categoria, assunto e descrição são obrigatórios.']);
            exit();
        }
        if (mb_strlen($subject) > 200 || mb_strlen($desc) > 8000) {
            http_response_code(400);
            echo json_encode(['error' => 'Conteúdo excede o limite permitido.']);
            exit();
        }

        $attachments = handleFiles('attachments');

        do { $proto = generateProtocol(); } while (file_exists(ticketPath($proto)));

        $now    = date('Y-m-d H:i:s');
        $ticket = [
            'protocol'  => $proto,
            'category'  => $category,
            'subject'   => $subject,
            'status'    => 'Aberto',
            'createdAt' => $now,
            'updatedAt' => $now,
            'anonymous' => $anon,
            'messages'  => [[
                'id'          => uniqid('m', true),
                'author'      => $anon ? 'Anônimo' : 'Denunciante',
                'content'     => $desc,
                'attachments' => $attachments,
                'date'        => $now,
                'isAdmin'     => false,
            ]],
        ];

        saveTicket($ticket);
        echo json_encode(['success' => true, 'protocol' => $proto, 'createdAt' => $now], JSON_UNESCAPED_UNICODE);
        exit();
    }

    /* REPLY */
    if ($action === 'reply') {
        $proto   = strtoupper(trim($body['protocol'] ?? ''));
        $message = htmlspecialchars(trim($body['message'] ?? ''), ENT_QUOTES, 'UTF-8');

        if (!$proto || !$message) {
            http_response_code(400);
            echo json_encode(['error' => 'Protocolo e mensagem são obrigatórios.']);
            exit();
        }
        if (mb_strlen($message) > 8000) {
            http_response_code(400);
            echo json_encode(['error' => 'Mensagem muito longa.']);
            exit();
        }

        $ticket = readTicket($proto);
        if (!$ticket) { http_response_code(404); echo json_encode(['error' => 'Chamado não encontrado.']); exit(); }
        if ($ticket['status'] === 'Fechado') { http_response_code(400); echo json_encode(['error' => 'Este chamado está fechado.']); exit(); }

        $attachments = handleFiles('attachments');
        $now = date('Y-m-d H:i:s');
        $reply = [
            'id'          => uniqid('m', true),
            'author'      => $ticket['anonymous'] ? 'Anônimo' : 'Denunciante',
            'content'     => $message,
            'attachments' => $attachments,
            'date'        => $now,
            'isAdmin'     => false,
        ];

        $ticket['messages'][] = $reply;
        $ticket['updatedAt']  = $now;
        if (in_array($ticket['status'], ['Resolvido'])) $ticket['status'] = 'Em Andamento';

        saveTicket($ticket);
        echo json_encode(['success' => true, 'message' => $reply], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

/* ── Admin: listar todos os chamados ─────────────────────── */
if ($method === 'GET' && $action === 'list') {
    requireAdmin();
    $tickets = [];
    foreach (glob("$ticketsDir/*.json") as $file) {
        $t = json_decode(file_get_contents($file), true);
        if ($t) $tickets[] = $t;
    }
    usort($tickets, function ($a, $b) {
        return strcmp($b['updatedAt'], $a['updatedAt']);
    });
    echo json_encode($tickets, JSON_UNESCAPED_UNICODE);
    exit();
}

/* ── Admin: responder como admin ─────────────────────────── */
if ($method === 'POST' && $action === 'admin_reply') {
    requireAdmin();
    $isJson  = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    $body    = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
    $proto   = strtoupper(trim($body['protocol'] ?? ''));
    $message = htmlspecialchars(trim($body['message'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (!$proto || !$message) {
        http_response_code(400);
        echo json_encode(['error' => 'Protocolo e mensagem são obrigatórios.']);
        exit();
    }

    $ticket = readTicket($proto);
    if (!$ticket) { http_response_code(404); echo json_encode(['error' => 'Chamado não encontrado.']); exit(); }
    if ($ticket['status'] === 'Fechado') { http_response_code(400); echo json_encode(['error' => 'Este chamado está fechado.']); exit(); }

    $now   = date('Y-m-d H:i:s');
    $reply = [
        'id'          => uniqid('m', true),
        'author'      => 'Equipe COAPH',
        'content'     => $message,
        'attachments' => [],
        'date'        => $now,
        'isAdmin'     => true,
    ];

    $ticket['messages'][]  = $reply;
    $ticket['updatedAt']   = $now;
    if ($ticket['status'] === 'Aberto') $ticket['status'] = 'Em Andamento';

    saveTicket($ticket);
    echo json_encode(['success' => true, 'message' => $reply], JSON_UNESCAPED_UNICODE);
    exit();
}

/* ── Admin: alterar status ───────────────────────────────── */
if ($method === 'POST' && $action === 'set_status') {
    requireAdmin();
    $isJson  = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    $body    = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
    $proto   = strtoupper(trim($body['protocol'] ?? ''));
    $status  = trim($body['status'] ?? '');

    $allowed = ['Aberto', 'Em Andamento', 'Resolvido', 'Fechado'];
    if (!$proto || !in_array($status, $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados inválidos.']);
        exit();
    }

    $ticket = readTicket($proto);
    if (!$ticket) { http_response_code(404); echo json_encode(['error' => 'Chamado não encontrado.']); exit(); }

    $ticket['status']    = $status;
    $ticket['updatedAt'] = date('Y-m-d H:i:s');
    saveTicket($ticket);
    echo json_encode(['success' => true, 'status' => $status], JSON_UNESCAPED_UNICODE);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Requisição inválida.']);
