<?php
// PDO db connection
$host = 'db';  // Dit moet overeenkomen met de servicenaam van MySQL in docker-compose.yml
$db   = 'mydb'; // De naam van je database
$user = 'user'; // Je MySQL-gebruikersnaam
$pass = 'test'; // Je MySQL-wachtwoord
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }
}

if (!function_exists('format_money')) {
    function format_money($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '€0,00';
        }

        $negative = false;
        if ($value[0] === '-') {
            $negative = true;
            $value = substr($value, 1);
        }

        if (strpos($value, '.') === false) {
            $integerPart = $value;
            $fractionPart = '00';
        } else {
            [$integerPart, $fractionPart] = explode('.', $value, 2);
        }

        $integerPart = ltrim($integerPart, '0');
        if ($integerPart === '') {
            $integerPart = '0';
        }

        $fractionPart = substr(str_pad($fractionPart, 2, '0'), 0, 2);
        $formattedInteger = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $integerPart);

        return ($negative ? '-' : '') . '€' . $formattedInteger . ',' . $fractionPart;
    }
}

if (!function_exists('password_meets_policy')) {
    function password_meets_policy($password, $username = '')
    {
        $password = (string) $password;
        $username = strtolower(trim((string) $username));
        $normalizedPassword = strtolower(trim($password));

        $commonPasswords = [
            'password',
            'wachtwoord',
            '123456',
            '1234567',
            '12345678',
            '123456789',
            'qwerty',
            'abcdefg',
            'letmein',
            'admin',
            'welcome',
        ];

        if (strlen($password) < 12) {
            return [false, 'Gebruik een wachtwoord van minimaal 12 tekens.'];
        }

        if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
            return [false, 'Gebruik hoofdletters, kleine letters, cijfers en een speciaal teken.'];
        }

        if (in_array($normalizedPassword, $commonPasswords, true)) {
            return [false, 'Kies een minder voor de hand liggend wachtwoord.'];
        }

        if ($username !== '' && strpos($normalizedPassword, $username) !== false) {
            return [false, 'Je wachtwoord mag niet je gebruikersnaam bevatten.'];
        }

        return [true, ''];
    }
}

if (!function_exists('auth_throttle_key')) {
    function auth_throttle_key($username)
    {
        return 'login_' . strtolower(trim((string) $username));
    }
}

if (!function_exists('auth_is_login_locked')) {
    function auth_is_login_locked($username)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return [false, 0];
        }

        $key = auth_throttle_key($username);
        $state = $_SESSION['auth_throttle'][$key] ?? null;

        if (!$state) {
            return [false, 0];
        }

        $now = time();
        $lockedUntil = (int) ($state['locked_until'] ?? 0);
        if ($lockedUntil > $now) {
            return [true, $lockedUntil - $now];
        }

        unset($_SESSION['auth_throttle'][$key]);
        return [false, 0];
    }
}

if (!function_exists('auth_record_login_failure')) {
    function auth_record_login_failure($username)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $key = auth_throttle_key($username);
        $now = time();
        $window = 15 * 60;
        $maxAttempts = 5;
        $state = $_SESSION['auth_throttle'][$key] ?? [
            'count' => 0,
            'first_failed_at' => $now,
            'locked_until' => 0,
        ];

        if (!isset($state['first_failed_at']) || ($now - (int) $state['first_failed_at']) > $window) {
            $state['count'] = 0;
            $state['first_failed_at'] = $now;
            $state['locked_until'] = 0;
        }

        $state['count'] = (int) $state['count'] + 1;

        if ($state['count'] >= $maxAttempts) {
            $state['locked_until'] = $now + $window;
            $state['count'] = 0;
            $state['first_failed_at'] = $now;
        }

        $_SESSION['auth_throttle'][$key] = $state;
    }
}

if (!function_exists('auth_clear_login_failures')) {
    function auth_clear_login_failures($username)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $key = auth_throttle_key($username);
        unset($_SESSION['auth_throttle'][$key]);
    }
}
?>