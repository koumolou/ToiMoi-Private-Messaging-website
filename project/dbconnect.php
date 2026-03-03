<?php
require_once __DIR__ . '/env.php';
loadEnv();

$servername = $_ENV['DB_HOST'] ?? getenv("DB_HOST");
$username   = $_ENV['DB_USER'] ?? getenv("DB_USER");
$password   = $_ENV['DB_PASS'] ?? getenv("DB_PASS");
$database   = $_ENV['DB_NAME'] ?? getenv("DB_NAME");

var_dump(['DB_HOST' => $servername, 'DB_USER' => $username, 'DB_NAME' => $database]);

$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
?>