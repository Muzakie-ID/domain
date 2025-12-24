<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''; // Default Laragon password is empty
$dbname = getenv('DB_NAME') ?: 'subdomain_manager';

try {
    // First connect to MySQL server to check/create database
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");

    // Create domains table only if it doesn't exist
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'domains'");
    if ($tableCheck->rowCount() == 0) {
        $pdo->exec("CREATE TABLE domains (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE
        )");
    }

    // Create subdomains table only if it doesn't exist
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'subdomains'");
    if ($tableCheck->rowCount() == 0) {
        $pdo->exec("CREATE TABLE subdomains (
            id INT AUTO_INCREMENT PRIMARY KEY,
            domain_id INT NOT NULL,
            sub_name VARCHAR(255) NOT NULL,
            port VARCHAR(10) NOT NULL,
            description TEXT,
            FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
        )");
    }

} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>