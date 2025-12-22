<?php
require 'db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'add_domain') {
    $name = $_POST['name'];
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO domains (name) VALUES (?)");
        try {
            $stmt->execute([$name]);
        } catch (Exception $e) {
            // Handle duplicate or error
        }
    }
    header("Location: domains.php");
    exit;
}

if ($action == 'delete_domain') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM domains WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: domains.php");
    exit;
}

if ($action == 'save_subdomain') {
    $id = $_POST['id'] ?? '';
    $domain_id = $_POST['domain_id'];
    $sub_name = $_POST['sub_name'];
    $port = $_POST['port'];
    $description = $_POST['description'];

    if ($id) {
        // Update
        $stmt = $pdo->prepare("UPDATE subdomains SET domain_id=?, sub_name=?, port=?, description=? WHERE id=?");
        $stmt->execute([$domain_id, $sub_name, $port, $description, $id]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO subdomains (domain_id, sub_name, port, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$domain_id, $sub_name, $port, $description]);
    }
    header("Location: index.php");
    exit;
}

if ($action == 'delete_subdomain') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM subdomains WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php");
    exit;
}
?>