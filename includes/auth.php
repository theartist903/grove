<?php

// How long (in seconds) an admin session may sit idle before it's auto-logged-out.
const ADMIN_SESSION_IDLE_TIMEOUT = 60 * 60; // 1 hour

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('grove_admin_sess');
    session_set_cookie_params([
        'lifetime' => 0, // expires when the browser closes
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Enforce idle timeout: if the session has been inactive too long, kill it
    // before treating the visitor as logged in.
    if (!empty($_SESSION['admin_id']) && !empty($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > ADMIN_SESSION_IDLE_TIMEOUT) {
            admin_logout();
            return;
        }
    }

    $_SESSION['last_activity'] = time();
}

function admin_is_logged_in(): bool
{
    admin_session_start();
    return !empty($_SESSION['admin_id']);
}

function admin_require_login(): void
{
    admin_session_start();
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * @throws RuntimeException if the database itself is unreachable/misconfigured
 *         (as opposed to the credentials simply being wrong).
 */
function admin_attempt_login(string $username, string $password): bool
{
    require_once __DIR__ . '/db.php';

    try {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE username = :u');
        $stmt->execute(['u' => $username]);
        $row = $stmt->fetch();
    } catch (\Throwable $e) {
        error_log('Admin login DB error: ' . $e->getMessage());
        throw new RuntimeException('database unavailable', 0, $e);
    }

    if ($row && password_verify($password, $row['password_hash'])) {
        admin_session_start();
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_username'] = $username;
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

function admin_logout(): void
{
    admin_session_start();
    $_SESSION = [];

    // Also expire the session cookie itself, not just the server-side data.
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}
