<?php
    $host = '127.0.0.1';
    $port = '3307';   // change to 3307 if XAMPP shows MySQL on a different port
    $db   = 'sales_project';
    $user = 'root';
    $pass = '';       // XAMPP default: empty password

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Stop here — never let the script continue without a database
        die('Database connection failed: ' . $e->getMessage());
    }
?>
