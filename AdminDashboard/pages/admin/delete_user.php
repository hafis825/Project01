<?php
session_start();
include('../../config.php');

if (!isset($_GET['id'])) {
    header('Location: user_management.php');
    exit();
}

$user_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: user_management.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    header('Location: user_management.php');
    exit();
}
?>
