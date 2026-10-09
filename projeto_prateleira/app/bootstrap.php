<?php
declare(strict_types=1);

$GLOBALS['cfg'] = require __DIR__ . '/../config/config.php';
function cfg(string $k) { return $GLOBALS['cfg'][$k] ?? null; }

ini_set('display_errors', cfg('debug') ? '1' : '0');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $c = cfg('db');
        $pdo = new PDO("mysql:host={$c['host']};dbname={$c['nome']};charset=utf8mb4", $c['usuario'], $c['senha'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function e($t): string { return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8'); }
function url(string $p = ''): string { return rtrim((string)cfg('url_base'), '/') . '/' . ltrim($p, '/'); }
function redirecionar(string $p): never { header('Location: ' . url($p)); exit; }
function brl(int $centavos): string { return 'R$ ' . number_format($centavos / 100, 2, ',', '.'); }

function csrf_campo(): string {
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function csrf_ok(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}
function flash(?string $m = null): ?string {
    if ($m !== null) { $_SESSION['flash'] = $m; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}

function slugify(string $t): string {
    $t = strtr(mb_strtolower($t), ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ü'=>'u','ç'=>'c']);
    $t = trim(preg_replace('/[^a-z0-9]+/', '-', $t), '-');
    return $t !== '' ? substr($t, 0, 50) : 'loja';
}

/** Usuário logado (com dados da loja) ou null. */
function usuario(): ?array {
    static $u = false;
    if ($u !== false) return $u;
    $id = $_SESSION['usuario_id'] ?? null;
    if (!$id) return $u = null;
    $s = db()->prepare('SELECT u.id, u.nome, u.email, u.loja_id, l.nome AS loja_nome, l.slug FROM usuarios u JOIN lojas l ON l.id = u.loja_id WHERE u.id = ?');
    $s->execute([$id]);
    return $u = ($s->fetch() ?: null);
}
function exigir_login(): array {
    $u = usuario();
    if (!$u) redirecionar('login.php');
    return $u;
}

/** Regra central do negócio: a loja só opera com assinatura ativa e dentro do prazo. */
function assinatura_ativa(int $lojaId): bool {
    $s = db()->prepare("SELECT 1 FROM assinaturas WHERE loja_id = ? AND status = 'ativa' AND vence_em >= CURDATE() LIMIT 1");
    $s->execute([$lojaId]);
    return (bool)$s->fetchColumn();
}
