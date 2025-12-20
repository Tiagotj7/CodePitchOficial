<?php
// db.php

// Caminho do arquivo .env (ajuste se colocar o .env em outro lugar)
$envPath = __DIR__ . '/.env';

/**
 * Carrega variáveis do arquivo .env em um array associativo
 */
function loadEnvFile($path)
{
    $env = [];

    if (!file_exists($path)) {
        // Se quiser obrigar a existência do .env, pode trocar por: die("Arquivo .env não encontrado");
        return $env;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Ignora comentários e linhas vazias
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        // Separa na primeira ocorrência de "="
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $name  = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove aspas se houver
        $value = trim($value, "\"'");

        if ($name !== '') {
            $env[$name] = $value;
            // Opcional: também colocar em getenv()
            putenv("$name=$value");
        }
    }

    return $env;
}

// Carrega .env
$env = loadEnvFile($envPath);

// Lê valores do .env (ou usa default vazio se não achar)
$host   = isset($env['DB_HOST']) ? $env['DB_HOST'] : '';
$dbname = isset($env['DB_NAME']) ? $env['DB_NAME'] : '';
$user   = isset($env['DB_USER']) ? $env['DB_USER'] : '';
$pass   = isset($env['DB_PASS']) ? $env['DB_PASS'] : '';

if ($host === '' || $dbname === '' || $user === '') {
    die("Configuração de banco de dados inválida. Verifique o arquivo .env");
}

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}