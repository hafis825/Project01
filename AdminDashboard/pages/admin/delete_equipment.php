<?php
session_start();
require '../config.php';

if (!isset($_GET['id'])) {
    header('Location: user_management.php');
    exit();
}

$equipment_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
$stmt->execute([$equipment_id]);
$equipment = $stmt->fetch();

if (!$equipment) {
    header('Location: equipment_management.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->execute([$equipment_id]);
    header('Location: equipment_management.php');
    exit();
}
?>
