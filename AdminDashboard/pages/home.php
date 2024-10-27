<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require '../config.php';
$currentPage = basename($_SERVER['PHP_SELF'], ".php");

if (!isset($_SESSION['username'])) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}

// *********************************************COUNT****************************************************

$equipmentCountQuery = "SELECT COUNT(loan_details.status) FROM loan
    JOIN loan_details ON loan.id = loan_details.loan_id
    JOIN users ON loan.userid = users.id
    JOIN equipment ON loan_details.equipmentid = equipment.equipmentid
    WHERE loan_details.status = 'pending'
    AND users.username = :username ";
$equipmentCountStmt = $pdo->prepare($equipmentCountQuery);
$equipmentCountStmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
$equipmentCountStmt->execute();
$equipmentCountPending = $equipmentCountStmt->fetchColumn();

$equipmentCountQuery = "SELECT COUNT(loan_details.status) FROM loan
    JOIN loan_details ON loan.id = loan_details.loan_id
    JOIN users ON loan.userid = users.id
    JOIN equipment ON loan_details.equipmentid = equipment.equipmentid
    WHERE loan_details.status = 'borrowed'
    AND users.username = :username ";
$equipmentCountStmt = $pdo->prepare($equipmentCountQuery);
$equipmentCountStmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
$equipmentCountStmt->execute();
$equipmentCountBorrowed = $equipmentCountStmt->fetchColumn();

$equipmentCountQuery = "SELECT COUNT(loan_details.status) FROM loan
    JOIN loan_details ON loan.id = loan_details.loan_id
    JOIN users ON loan.userid = users.id
    JOIN equipment ON loan_details.equipmentid = equipment.equipmentid
    WHERE loan_details.status = 'returned'
    AND users.username = :username ";
$equipmentCountStmt = $pdo->prepare($equipmentCountQuery);
$equipmentCountStmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
$equipmentCountStmt->execute();
$equipmentCountReturned = $equipmentCountStmt->fetchColumn();

// $stmt = $pdo->query("SELECT * FROM equipment");
// $equipment = $stmt->fetchAll();
// *********************************************COUNT****************************************************

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BorrowEquipment System</title>
    <link rel="icon" type="image/x-icon" href="/AdminDashboard/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-client.css">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-table.css">
</head>

<body>
    <?php include('../includes/nav.php') ?>


    <div class="container">
        <h2 class="my-4">Dashboard</h2>
        <p>Welcome, <strong class="text-primary"><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card border-primary">
                    <div class="card-body ">
                        <h5 class="card-title">Equipment Pending <span class="badge bg-primary"><?= $equipmentCountPending ?></span></h5>
                        <p class="card-text">Manage Equipment Pending.</p>
                        <a href="/AdminDashboard/pages/equipment_pending.php" class="btn btn-primary">Go to Equipment Pending</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title">Equipment Borrowed <span class="badge bg-success"><?= $equipmentCountBorrowed ?></span></h5>
                        <p class="card-text">Manage Equipment Borrowed.</p>
                        <a href="#" class="btn btn-success">Go to Equipment Borrowed</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="card-title">Equipment Returned <span class="badge bg-warning"><?= $equipmentCountReturned ?></span></h5>
                        <p class="card-text">Manage Equipment Returned.</p>
                        <a href="#" class="btn btn-warning">Go to Equipment Returned</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>