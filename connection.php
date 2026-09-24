<?php
    $host = '127.0.0.1';
    $db = 'sales_project';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //echo "connected";
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
?>
