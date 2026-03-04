<?php
require_once __DIR__ . '/env.php';
loadEnv();

$servername = getenv("DB_HOST") ?: $_ENV['DB_HOST'] ?? '';
$username   = getenv("DB_USER") ?: $_ENV['DB_USER'] ?? '';
$password   = getenv("DB_PASS") ?: $_ENV['DB_PASS'] ?? '';
$database   = getenv("DB_NAME") ?: $_ENV['DB_NAME'] ?? '';
$port       = getenv("DB_PORT") ?: $_ENV['DB_PORT'] ?? 3306;

$conn = mysqli_connect($servername, $username, $password, $database, (int)$port);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = file_get_contents(__DIR__ . '/schema.sql');
if (mysqli_multi_query($conn, $sql)) {
    echo "Tables created successfully!";
} else {
    echo "Error: " . mysqli_error($conn);
}
mysqli_close($conn);
?>