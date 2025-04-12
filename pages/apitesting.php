<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '\..\vendor\autoload.php';

use Dotenv\Dotenv;

// Correct path to 'learntoearn' folder
$dotenv = Dotenv::createImmutable(realpath(__DIR__ . '\..'));
$dotenv->safeLoad();

// Try both getenv() and $_ENV
$api1 = getenv('GEMINI_API_KEY');
$api2 = $_ENV['GEMINI_API_KEY'] ?? 'NOT SET';

echo "<h2>getenv(): $api1</h2>";
echo "<h2>\$_ENV: $api2</h2>";

echo "<pre>";
print_r($_ENV);
echo "</pre>";
?>
