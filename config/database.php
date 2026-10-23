<?php
$envPath = dirname(__DIR__) . '/.env';
if (!file_exists($envPath) && file_exists(dirname(__DIR__) . '/env')) {
    $envPath = dirname(__DIR__) . '/env';
}
$envVars = [];
if (file_exists($envPath)) {
    $rawContent = file_get_contents($envPath);
    // Strip UTF-8 BOM if present
    $rawContent = preg_replace('/^\xEF\xBB\xBF/', '', $rawContent);
    $lines = preg_split('/\r\n|\r|\n/', $rawContent);
    if ($lines) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';') || str_starts_with($line, '//')) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($k, $v) = explode('=', $line, 2);
                $k = preg_replace('/^\xEF\xBB\xBF/', '', trim($k));
                $v = trim(trim($v), "'\"");
                $envVars[$k] = $v;
            }
        }
    }
}

return [
    'host'    => $envVars['DB_HOST'] ?? '127.0.0.1',
    'port'    => $envVars['DB_PORT'] ?? '3306',
    'dbname'  => $envVars['DB_NAME'] ?? 'grm_b2b_db',
    'user'    => $envVars['DB_USER'] ?? 'root', 
    'pass'    => $envVars['DB_PASS'] ?? '',     
    'charset' => $envVars['DB_CHARSET'] ?? 'utf8mb4',
    'ssl'     => filter_var($envVars['DB_SSL'] ?? false, FILTER_VALIDATE_BOOLEAN)
];
