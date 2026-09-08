<?php
// TEMPORARY diagnostic page. Visit with ?key=grove-diag-2026 to run.
// DELETE THIS FILE once you're done diagnosing — it reveals config details.

if (($_GET['key'] ?? '') !== 'grove-diag-2026') {
    http_response_code(404);
    exit('Not found.');
}

header('Content-Type: text/plain');

$config = require __DIR__ . '/config/config.php';

echo "=== PHP ===\n";
echo 'PHP version: ' . PHP_VERSION . "\n";
echo 'pdo_mysql loaded: ' . (extension_loaded('pdo_mysql') ? 'yes' : 'NO - THIS IS THE PROBLEM') . "\n";
echo 'openssl loaded: ' . (extension_loaded('openssl') ? 'yes' : 'no') . "\n\n";

echo "=== DB config (password redacted) ===\n";
echo 'host: ' . $config['db']['host'] . "\n";
echo 'name: ' . $config['db']['name'] . "\n";
echo 'user: ' . $config['db']['user'] . "\n";
echo 'pass length: ' . strlen($config['db']['pass']) . "\n\n";

echo "=== DB connection test ===\n";
try {
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}";
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "CONNECTED OK\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo 'Tables found: ' . implode(', ', $tables) . "\n\n";

    if (in_array('admin_users', $tables, true)) {
        $count = $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
        echo "admin_users row count: $count\n";
    }
    if (in_array('registrations', $tables, true)) {
        $count = $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
        echo "registrations row count: $count\n";
    }
} catch (\Throwable $e) {
    echo 'CONNECTION FAILED: ' . get_class($e) . ': ' . $e->getMessage() . "\n";
}

echo "\n=== Mail (PHPMailer) availability ===\n";
$autoload = __DIR__ . '/vendor/autoload.php';
echo 'vendor/autoload.php exists: ' . (file_exists($autoload) ? 'yes' : 'NO - THIS IS A PROBLEM') . "\n";
if (file_exists($autoload)) {
    require_once $autoload;
    echo 'PHPMailer class loads: ' . (class_exists(\PHPMailer\PHPMailer\PHPMailer::class) ? 'yes' : 'NO') . "\n";
}

echo "\n=== Mail config (password redacted) ===\n";
echo 'host: ' . $config['mail']['host'] . "\n";
echo 'port: ' . $config['mail']['port'] . "\n";
echo 'encryption: ' . $config['mail']['encryption'] . "\n";
echo 'username: ' . $config['mail']['username'] . "\n";
echo 'pass length: ' . strlen($config['mail']['password']) . "\n";

$testTo = $_GET['testmail'] ?? '';
if ($testTo !== '') {
    echo "\n=== Live send test to $testTo ===\n";
    $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mailer->SMTPDebug = 2;
        $mailer->Debugoutput = function ($str, $level) {
            echo "[SMTP] $str\n";
        };
        $mailer->isSMTP();
        $mailer->Host = $config['mail']['host'];
        $mailer->Port = $config['mail']['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $config['mail']['username'];
        $mailer->Password = $config['mail']['password'];
        $mailer->SMTPSecure = $config['mail']['encryption'];
        $mailer->Timeout = 15;

        if ($config['mail']['host'] === 'localhost' || $config['mail']['host'] === '127.0.0.1') {
            $mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        $mailer->setFrom($config['mail']['from_address'], $config['mail']['from_name']);
        $mailer->addAddress($testTo);
        $mailer->Subject = 'The Grove diag test email';
        $mailer->Body = 'This is a test email from diag.php.';

        $mailer->send();
        echo "\nSEND OK\n";
    } catch (\Throwable $e) {
        echo "\nSEND FAILED: " . get_class($e) . ': ' . $e->getMessage() . "\n";
        echo 'PHPMailer ErrorInfo: ' . $mailer->ErrorInfo . "\n";
    }
} else {
    echo "\n(Add &testmail=you@example.com to the URL to attempt a real send and see the exact SMTP error.)\n";
}
