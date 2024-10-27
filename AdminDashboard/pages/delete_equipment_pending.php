<?php
session_start();
require '../config.php';

if (!isset($_GET['loanid'])) {
    header('Location: /AdminDashboard/pages/equipment_pending.php');
    exit();
}

$loan_id = $_GET['loanid'];

// ใช้ prepared statement เพื่อตรวจสอบว่า loan_id มีอยู่ในฐานข้อมูล
$stmt = $pdo->prepare("SELECT * FROM loan WHERE loanid = ?");
$stmt->execute([$loan_id]);
$loan = $stmt->fetch();

if (!$loan) {
    header('Location: equipment_pending.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // เริ่ม transaction
        $pdo->beginTransaction();

        // ลบข้อมูลจาก loan_details ก่อน
        $stmt = $pdo->prepare("DELETE FROM loan_details WHERE loan_id = (SELECT id FROM loan WHERE loanid = ?)");
        $stmt->execute([$loan_id]);

        // ลบข้อมูลจาก loan
        $stmt = $pdo->prepare("DELETE FROM loan WHERE loanid = ?");
        $stmt->execute([$loan_id]);

        // ทำการ commit transaction
        $pdo->commit();

        header('Location: equipment_pending.php');
        exit();
    } catch (Exception $e) {
        // ถ้ามีข้อผิดพลาด ให้ rollback
        $pdo->rollBack();
        // คุณสามารถเพิ่มการจัดการข้อผิดพลาดที่นี่
        echo "Error: " . $e->getMessage();
    }
}
?>
